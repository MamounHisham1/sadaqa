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

    /**
     * Surah metadata (id, name_arabic, verses_count, ...). Reads the fetched
     * mushaf files when present; otherwise falls back to the canonical surah
     * table baked into config/quran.php so the radio never depends on them.
     */
    public function chapters(): array
    {
        if ($this->chapters === null) {
            $path = $this->dir().'/chapters.json';
            if (File::exists($path)) {
                $this->chapters = json_decode(File::get($path), true);
            } else {
                $this->chapters = collect(config('quran.surahs'))
                    ->map(fn ($s, $i) => [
                        'id' => $s['id'],
                        'name_simple' => '',
                        'name_arabic' => $s['na'],
                        'translated_name' => '',
                        'verses_count' => $s['count'],
                        'first_page' => 1,
                        'revelation' => 'makkah',
                    ])
                    ->all();
            }
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

    /** verse_key => uthmani text, for all 6236 ayahs (empty without quran:fetch). */
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
}
