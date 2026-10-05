<?php

namespace App\Http\Controllers;

use App\Models\StreamLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class LinkEditController extends Controller
{
    /** Edit form: visible to anyone; changes require the link's password. */
    public function show(string $token)
    {
        $link = StreamLink::where('token', $token)->firstOrFail();
        abort_if(! $link->password, 404, 'This link has no password set.');

        return view('edit', ['link' => $link]);
    }

    public function update(Request $request, string $token)
    {
        $link = StreamLink::where('token', $token)->firstOrFail();
        abort_if(! $link->password, 404, 'This link has no password set.');

        $validated = $request->validate([
            'link_password' => ['required', 'string'],
            'dedication_type' => ['nullable', 'in:sadaqa,gift'],
            'recipient_name' => ['nullable', 'string', 'max:100'],
            'sender_name' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:600'],
            'rotation' => ['nullable', 'array', 'max:12'],
            'rotation.*' => ['string', 'in:'.implode(',', array_column(config('quran.reciters'), 'id'))],
        ], [
            'link_password.required' => 'اكتب كلمة المرور',
            'recipient_name.max' => 'الاسم طويل جدًا',
            'sender_name.max' => 'الاسم طويل جدًا',
            'message.max' => 'الرسالة طويلة جدًا',
        ]);

        $key = 'link-edit:'.$token.':'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            return back()->withErrors(['link_password' => 'محاولات كثيرة، انتظر قليلًا']);
        }
        RateLimiter::hit($key, 120);

        if (! Hash::check($validated['link_password'], $link->password)) {
            return back()->withInput()->withErrors(['link_password' => 'كلمة المرور غير صحيحة']);
        }
        RateLimiter::clear($key);

        $clean = fn ($v) => $v ? trim($v) : null;
        $catalog = array_column(config('quran.reciters'), 'id');
        $rotation = collect($catalog)
            ->intersect($validated['rotation'] ?? [])
            ->values()
            ->all() ?: null;

        $link->update([
            'recipient_name' => $clean($validated['recipient_name'] ?? null),
            'dedication_type' => $validated['dedication_type'] ?? 'sadaqa',
            'sender_name' => $clean($validated['sender_name'] ?? null),
            'message' => $clean($validated['message'] ?? null),
            'rotation' => $rotation,
        ]);

        return redirect()->route('stream', ['token' => $link->token, 'edited' => 1]);
    }
}
