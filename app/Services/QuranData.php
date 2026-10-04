<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class QuranData
{
    private ?array $chapters = null;

    public function dir(): string
    {
        return storage_path('app/quran');
    }

    public function chapters(): array
    {
        if ($this->chapters === null) {
            $path = $this->dir().'/chapters.json';
            $this->chapters = File::exists($path)
                ? json_decode(File::get($path), true)
                : [];
        }

        return $this->chapters;
    }

    public function page(int $n): ?array
    {
        if ($n < 1 || $n > config('quran.total_pages')) {
            return null;
        }
        $path = $this->dir()."/pages/{$n}.json";

        return File::exists($path) ? json_decode(File::get($path), true) : null;
    }

    /** verse_key => uthmani text, for all 6236 ayahs. */
    public function allVerses(): array
    {
        $out = [];
        for ($p = 1; $p <= config('quran.total_pages'); $p++) {
            foreach ($this->page($p) ?? [] as $v) {
                $out[$v['k']] = $v['t'];
            }
        }

        return $out;
    }

    /**
     * Compact payload injected into the player for client-side bootstrapping.
     */
    public function clientMeta(): array
    {
        $chapters = collect($this->chapters());

        return [
            'totalVerses' => config('quran.total_verses'),
            'totalPages' => config('quran.total_pages'),
            'counts' => $chapters->pluck('verses_count')->all(),
            'firstPage' => $chapters->pluck('first_page')->all(),
            'chapters' => $chapters->map(fn ($c) => [
                'id' => $c['id'],
                'n' => $c['name_simple'],
                'na' => $c['name_arabic'],
                'tn' => $c['translated_name'],
                'rev' => $c['revelation'] ?? 'makkah',
            ])->all(),
        ];
    }
}
