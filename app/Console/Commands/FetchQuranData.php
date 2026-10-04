<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FetchQuranData extends Command
{
    protected $signature = 'quran:fetch';

    protected $description = 'Fetch all 604 mushaf pages and chapter metadata from api.quran.com into local static JSON';

    private const API = 'https://api.quran.com/api/v4';

    private const TOTAL_PAGES = 604;

    public function handle(): int
    {
        $dir = storage_path('app/quran');
        File::ensureDirectoryExists($dir.'/pages');

        $client = new Client(['timeout' => 30, 'verify' => false]);

        // --- Chapters (includes each surah's page range) ---
        $this->info('Fetching chapters...');
        $chaptersRes = $client->get(self::API.'/chapters', ['query' => ['language' => 'en']]);
        $raw = json_decode((string) $chaptersRes->getBody(), true);
        $chapters = collect($raw['chapters'])->map(fn ($c) => [
            'id' => $c['id'],
            'name_simple' => $c['name_simple'],
            'name_arabic' => $c['name_arabic'],
            'translated_name' => $c['translated_name']['name'] ?? '',
            'verses_count' => $c['verses_count'],
            'first_page' => $c['pages'][0],
            'revelation' => $c['revelation_place'],
        ])->all();
        File::put($dir.'/chapters.json', json_encode($chapters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->info(count($chapters).' chapters saved.');

        // --- Pages, 20 concurrent requests at a time ---
        $this->info('Fetching '.self::TOTAL_PAGES.' mushaf pages...');
        $requests = function () use ($client) {
            for ($p = 1; $p <= self::TOTAL_PAGES; $p++) {
                yield $p => new Request('GET', self::API."/verses/by_page/{$p}?fields=text_uthmani,juz_number&per_page=50");
            }
        };

        $bar = $this->output->createProgressBar(self::TOTAL_PAGES);
        $bar->start();
        $totalVerses = 0;

        $pool = new Pool($client, $requests(), [
            'concurrency' => 20,
            'fulfilled' => function (Response $res, $p) use ($client, $dir, &$totalVerses, $bar) {
                // A page can hold at most ~50 verses, but follow pagination just in case.
                $data = json_decode((string) $res->getBody(), true);
                $verses = $data['verses'] ?? [];
                $next = $data['pagination']['next_page'] ?? null;
                while ($next !== null) {
                    // Very unlikely; fetch extra pages synchronously for correctness.
                    $more = json_decode((string) $client->get(self::API."/verses/by_page/{$p}?fields=text_uthmani,juz_number&per_page=50&page={$next}")->getBody(), true);
                    $verses = array_merge($verses, $more['verses'] ?? []);
                    $next = $more['pagination']['next_page'] ?? null;
                }
                $out = array_map(fn ($v) => [
                    'k' => $v['verse_key'],
                    't' => trim($v['text_uthmani'] ?? ''),
                    'j' => $v['juz_number'] ?? null,
                ], $verses);
                File::put($dir."/pages/{$p}.json", json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                $totalVerses += count($out);
                $bar->advance();
            },
            'rejected' => function ($reason, $p) use ($bar) {
                $this->newLine();
                $this->error("Page {$p} failed: {$reason->getMessage()}");
                $bar->advance();
            },
        ]);

        $pool->promise()->wait();
        $bar->finish();
        $this->newLine();

        if ($totalVerses !== 6236) {
            $this->warn("Warning: expected 6236 verses, got {$totalVerses}. Re-run this command to fill gaps.");
        } else {
            $this->info("Done. {$totalVerses} verses across ".self::TOTAL_PAGES.' pages saved to storage/app/quran.');
        }

        return self::SUCCESS;
    }
}
