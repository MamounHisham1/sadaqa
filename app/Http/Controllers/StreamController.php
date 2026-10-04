<?php

namespace App\Http\Controllers;

use App\Models\StreamLink;
use App\Services\QuranData;
use App\Services\RadioStream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class StreamController extends Controller
{
    public function __construct(private RadioStream $radio) {}

    public function show(Request $request, string $token, QuranData $quran)
    {
        $link = StreamLink::where('token', $token)->firstOrFail();
        $link->increment('views');

        return view('stream', $this->viewData($quran, $link, $request->boolean('new')));
    }

    public function radio(Request $request, QuranData $quran)
    {
        return view('stream', $this->viewData($quran, null, false));
    }

    /** Global radio position — everyone hears this. Rotation via ?r=id,id. */
    public function now(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('r', '')))
            ->map(fn ($s) => trim($s))
            ->filter()
            ->all();

        return response()->json($this->radio->now($ids ?: null))
            ->header('Cache-Control', 'no-store');
    }

    public function page(int $page, QuranData $quran)
    {
        $verses = $quran->page($page);
        abort_if($verses === null, 404);

        // Mushaf pages are immutable; let browsers and proxies cache hard.
        return response()->json($verses)->header('Cache-Control', 'public, max-age=31536000, immutable');
    }

    public function played(Request $request, string $token)
    {
        $validated = $request->validate([
            'ayahs' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $link = StreamLink::where('token', $token)->first();
        if (! $link) {
            return response()->json(['ok' => false], 404);
        }

        $total = $link->ayahs_played + $validated['ayahs'];
        $link->forceFill([
            'ayahs_played' => $total,
            'khatmas' => intdiv($total, config('quran.total_verses')),
            'last_played_at' => now(),
        ])->save();

        return response()->json(['ok' => true, 'ayahs_played' => $total]);
    }

    private function viewData(QuranData $quran, ?StreamLink $link, bool $isNew): array
    {
        return [
            'link' => $link,
            'isNew' => $isNew,
            'reciters' => config('quran.reciters'),
            'dedicationTypes' => config('quran.dedication_types'),
            'clientPayload' => [
                'counts' => collect($quran->chapters())->pluck('verses_count')->all(),
                'chapters' => collect($quran->chapters())
                    ->map(fn ($c) => ['id' => $c['id'], 'na' => $c['name_arabic'], ])
                    ->all(),
                'reciters' => collect(config('quran.reciters'))
                    ->map(fn ($r) => ['id' => $r['id'], 'ar' => $r['ar'], 'surah_sources' => $r['surah_sources']])
                    ->all(),
                'rotation' => $link?->rotation,
                'link' => $link ? [
                    'token' => $link->token,
                    'recipient' => $link->recipient_name,
                ] : null,
            ],
        ];
    }
}
