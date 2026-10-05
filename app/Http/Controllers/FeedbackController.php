<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function form(Request $request)
    {
        return view('feedback', [
            'sent' => $request->boolean('sent'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:bug,feature'],
            'message' => ['required', 'string', 'max:2000'],
            'contact' => ['nullable', 'string', 'max:120'],
        ], [
            'type.required' => 'اختر نوع الرسالة',
            'message.required' => 'اكتب رسالتك أولًا',
            'message.max' => 'الرسالة طويلة جدًا',
            'contact.max' => 'بيانات التواصل طويلة جدًا',
        ]);

        Feedback::create([
            'type' => $validated['type'],
            'message' => trim($validated['message']),
            'contact' => $validated['contact'] ? trim($validated['contact']) : null,
            'url' => (string) $request->header('referer') ?: null,
        ]);

        return redirect()->route('feedback.form', ['sent' => 1]);
    }
}
