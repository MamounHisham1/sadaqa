<?php

namespace App\Http\Controllers;

use App\Models\StreamLink;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'reciters' => config('quran.reciters'),
            'totalLinks' => StreamLink::count(),
        ]);
    }

    public function store(Request $request)
    {
        // Every field is optional; the link stays valid either way.
        $validated = $request->validate([
            'dedication_type' => ['nullable', 'in:sadaqa,gift'],
            'sender_name' => ['nullable', 'string', 'max:100'],
            'recipient_name' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:600'],
            'rotation' => ['nullable', 'array', 'max:12'],
            'rotation.*' => ['string', 'in:'.implode(',', array_column(config('quran.reciters'), 'id'))],
        ], [
            'sender_name.max' => 'الاسم طويل جدًا',
            'recipient_name.max' => 'الاسم طويل جدًا',
            'message.max' => 'الرسالة طويلة جدًا',
        ]);

        $clean = fn ($v) => $v ? trim($v) : null;

        // Keep only known ids, deduped, in catalog order; empty = all reciters.
        $catalog = array_column(config('quran.reciters'), 'id');
        $rotation = collect($catalog)
            ->intersect($validated['rotation'] ?? [])
            ->values()
            ->all() ?: null;

        $link = null;
        // Short numeric tokens have a small keyspace; retry on the rare collision.
        for ($attempt = 0; $attempt < 8; $attempt++) {
            try {
                $link = StreamLink::create([
                    'token' => (string) random_int(
                        (int) str_repeat('1', config('quran.token_length')),
                        (int) str_repeat('9', config('quran.token_length'))
                    ),
                    'recipient_name' => $clean($validated['recipient_name'] ?? null),
                    'dedication_type' => $validated['dedication_type'] ?? 'sadaqa',
                    'sender_name' => $clean($validated['sender_name'] ?? null),
                    'message' => $clean($validated['message'] ?? null),
                    'rotation' => $rotation,
                ]);
                break;
            } catch (\Illuminate\Database\UniqueConstraintViolationException) {
                continue;
            }
        }

        if (! $link) {
            return back()->withInput()->withErrors(['token' => 'تعذّر إنشاء الرابط، حاول مرة أخرى.']);
        }

        return redirect()->route('stream', ['token' => $link->token, 'new' => '1']);
    }
}
