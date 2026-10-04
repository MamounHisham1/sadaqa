<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * The global radio timeline — full surahs, pure function of the epoch.
 *
 * There is no mutable "now" state: the position at any instant is computed
 * from a fixed broadcast epoch (stream_state.started_at) and the durations
 * of the surah files. Each rotation set (a link's chosen reciters, or all
 * reciters for /radio) gets its own consistent schedule from the same
 * epoch, so everyone on a link hears the same reciter at the same moment.
 * A pass p of the cycle uses rotation[p % count] — one reciter per khatma.
 *
 * Costs are O(1) in listeners and links: the walk from the epoch is a few
 * thousand arithmetic steps per request, cached per rotation for 1s.
 */
class RadioStream
{
    public function __construct(private QuranData $quran) {}

    /**
     * Validate a rotation against the catalog (catalog order preserved).
     * Empty/unknown input resolves to all reciters.
     *
     * @return array list of reciter config entries
     */
    public function resolveRotation(?array $ids): array
    {
        $all = config('quran.reciters');
        if (! $ids) {
            return array_values($all);
        }
        $wanted = array_flip($ids);
        $picked = array_values(array_filter($all, fn ($r) => isset($wanted[$r['id']])));

        return $picked ?: array_values($all);
    }

    public function rotationKey(array $rotation): string
    {
        return md5(implode(',', array_column($rotation, 'id')));
    }

    /** Broadcast epoch (unix ts). Initialised once, never changes. */
    public function epoch(): int
    {
        $row = DB::table('stream_state')->where('id', 1)->first();
        $ts = $row
            ? (is_numeric($row->started_at) ? (int) $row->started_at : (strtotime((string) $row->started_at) ?: 0))
            : 0;
        if (! $ts) {
            $ts = now()->getTimestamp();
            if ($row) {
                DB::table('stream_state')->where('id', 1)->update(['started_at' => date('Y-m-d H:i:s', $ts)]);
            } else {
                DB::table('stream_state')->insert(['id' => 1, 'started_at' => date('Y-m-d H:i:s', $ts)]);
            }
        }

        return $ts;
    }

    /** Current station position for a rotation set, payload-cached for 1s. */
    public function now(?array $rotationIds = null): array
    {
        $rotation = $this->resolveRotation($rotationIds);
        $key = $this->rotationKey($rotation);
        $cached = Cache::get("radio:now:{$key}");
        if (is_array($cached) && now()->getTimestamp() - $cached['computed_at'] < 1) {
            return $cached;
        }

        return $this->compute($rotation, $key);
    }

    private function compute(array $rotation, string $key): array
    {
        $elapsed = max(0, now()->getTimestamp() - $this->epoch());
        $total = config('quran.total_surahs');

        $durCache = [];
        $durFor = function (int $pass, int $surah) use (&$durCache, $rotation) {
            $idx = $pass % count($rotation);
            if (! isset($durCache[$idx])) {
                $durCache[$idx] = $this->durations($rotation[$idx]['id']);
            }

            return $durCache[$idx][$surah];
        };

        // Walk the timeline from the epoch. At murattal pace this is a few
        // thousand iterations per day since the epoch — microseconds.
        $surah = 1;
        $pass = 0;
        $steps = 0;
        while ($elapsed >= ($d = $durFor($pass, $surah)) && $steps < 500000) {
            $elapsed -= $d;
            $surah++;
            if ($surah > $total) {
                $surah = 1;
                $pass++;
            }
            $steps++;
        }

        $rec = $rotation[$pass % count($rotation)];
        $nextSurah = $surah >= $total ? 1 : $surah + 1;
        $nextPass = $surah >= $total ? $pass + 1 : $pass;
        $nextRec = $rotation[$nextPass % count($rotation)];

        // Keep the current surah's duration honest.
        if ($steps <= 2) {
            $this->probe($rec, $surah);
        }

        $payload = [
            'surah' => $surah,
            'pass' => $pass,
            'reciter' => $rec['id'],
            'offset' => round($elapsed, 1),
            'duration' => round((float) $durFor($pass, $surah), 1),
            'ayah_count' => $this->quran->chapters()[$surah - 1]['verses_count'] ?? 0,
            'next' => [
                'surah' => $nextSurah,
                'reciter' => $nextRec['id'],
                'duration' => round((float) $durFor($nextPass, $nextSurah), 1),
                'pass' => $nextPass,
                'ayah_count' => $this->quran->chapters()[$nextSurah - 1]['verses_count'] ?? 0,
            ],
            'computed_at' => now()->getTimestamp(),
        ];
        Cache::put("radio:now:{$key}", $payload, 2);

        return $payload;
    }

    /** Durations for a reciter: probed values from DB, heuristic elsewhere. */
    public function durations(string $reciterId): array
    {
        return Cache::remember("radio:durs:{$reciterId}", 300, function () use ($reciterId) {
            $known = DB::table('surah_durations')
                ->where('reciter_id', $reciterId)
                ->pluck('seconds', 'surah');
            $heuristic = $this->heuristics();
            $out = [];
            foreach (range(1, config('quran.total_surahs')) as $surah) {
                $out[$surah] = (float) ($known[$surah] ?? $heuristic[$surah] ?? 3000);
            }
            ksort($out);

            return $out;
        });
    }

    /**
     * Fallback schedule from canonical ayah counts (~32s/ayah at murattal
     * pace). Needs no fetched files, so the radio works out of the box;
     * probed durations replace it via quran:warm-durations.
     */
    public function heuristics(): array
    {
        return Cache::remember('radio:heuristics', 86400, function () {
            $out = [];
            foreach (config('quran.surahs') as $surah) {
                $out[$surah['id']] = max(8.0, $surah['count'] * 32);
            }
            ksort($out);

            return $out;
        });
    }

    /** Resolve the true duration of one surah file via HEAD content-length. */
    public function probe(array $reciter, int $surah): void
    {
        $exists = DB::table('surah_durations')
            ->where('reciter_id', $reciter['id'])
            ->where('surah', $surah)
            ->exists();
        if ($exists) {
            return;
        }

        $lock = Cache::lock("radio:probe:{$reciter['id']}:{$surah}", 5);
        if (! $lock->get()) {
            return;
        }

        try {
            $src = $reciter['surah_sources'][0];
            $url = $src['base'].sprintf('%03d.mp3', $surah);
            $res = Http::timeout(8)->throw(false)->head($url);
            $len = (int) $res->header('Content-Length');
            if ($res->successful() && $len > 0 && ($bitrate = $src['bitrate'] ?? 128000) > 0) {
                DB::table('surah_durations')->insertOrIgnore([
                    'reciter_id' => $reciter['id'],
                    'surah' => $surah,
                    'seconds' => round($len * 8 / $bitrate, 2),
                ]);
                Cache::forget("radio:durs:{$reciter['id']}");
            }
        } catch (\Throwable) {
            // Heuristic schedule remains; we'll probe again later.
        } finally {
            optional($lock)->release();
        }
    }
}
