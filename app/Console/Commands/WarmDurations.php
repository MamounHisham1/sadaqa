<?php

namespace App\Console\Commands;

use App\Services\RadioStream;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WarmDurations extends Command
{
    protected $signature = 'quran:warm-durations {reciter? : Reciter id, or "all" (default: all)}';

    protected $description = 'Pre-probe full-surah audio durations so the radio schedule is exact';

    public function handle(RadioStream $radio): int
    {
        $all = collect(config('quran.reciters'));
        $target = $this->argument('reciter');
        $targets = ($target && $target !== 'all')
            ? $all->where('id', $target)->values()
            : $all;

        if ($targets->isEmpty()) {
            $this->error("Unknown reciter: {$target}");

            return self::FAILURE;
        }

        foreach ($targets as $reciter) {
            $this->warm($reciter);
            cache()->forget("radio:durs:{$reciter['id']}");
        }

        return self::SUCCESS;
    }

    private function warm(array $reciter): void
    {
        $src = $reciter['surah_sources'][0];
        $bitrate = $src['bitrate'] ?? 128000;
        $surahs = range(1, config('quran.total_surahs'));

        $existing = DB::table('surah_durations')
            ->where('reciter_id', $reciter['id'])
            ->pluck('surah')
            ->all();
        $missing = array_diff($surahs, $existing);

        if (! $missing) {
            $this->info("{$reciter['id']}: already fully cached.");

            return;
        }

        $this->info("{$reciter['id']}: probing ".count($missing).' surah files...');
        $bar = $this->output->createProgressBar(count($missing));
        $bar->start();
        $rows = [];
        $cached = 0;

        foreach (array_chunk(array_values($missing), 24) as $chunk) {
            $responses = Http::pool(function ($pool) use ($chunk, $src) {
                foreach ($chunk as $surah) {
                    $pool->timeout(10)->head($src['base'].sprintf('%03d.mp3', $surah));
                }
            });

            foreach ($responses as $i => $res) {
                $surah = $chunk[$i];
                $len = $res ? (int) $res->header('Content-Length') : 0;
                if ($len > 0) {
                    $rows[] = [
                        'reciter_id' => $reciter['id'],
                        'surah' => $surah,
                        'seconds' => round($len * 8 / $bitrate, 2),
                    ];
                    $cached++;
                }
                $bar->advance();
            }

            if (count($rows) >= 300) {
                $this->flush($rows);
            }
        }
        $this->flush($rows);
        $bar->finish();
        $this->newLine();
        $this->info("{$reciter['id']}: cached {$cached} surah durations.");
    }

    private function flush(array &$rows): void
    {
        if ($rows) {
            DB::table('surah_durations')->insertOrIgnore($rows);
            $rows = [];
        }
    }
}
