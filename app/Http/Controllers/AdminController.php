<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\StreamLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AdminController extends Controller
{
    private function authed(Request $request): bool
    {
        return (bool) $request->session()->get('admin-authed');
    }

    public function dashboard(Request $request)
    {
        if (env('ADMIN_PASSWORD') === null || env('ADMIN_PASSWORD') === '') {
            return response('Set ADMIN_PASSWORD in .env first.', 503);
        }
        if (! $this->authed($request)) {
            return view('admin', ['authed' => false]);
        }

        return view('admin', [
            'authed' => true,
            'feedback' => Feedback::orderBy('resolved')->latest()->limit(200)->get(),
            'links' => StreamLink::latest()->limit(100)->get(),
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate(['password' => ['required', 'string']]);

        $key = 'admin-login:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['password' => 'محاولات كثيرة، انتظر قليلًا']);
        }
        RateLimiter::hit($key, 120);

        if (! hash_equals((string) env('ADMIN_PASSWORD'), $data['password'])) {
            return back()->withErrors(['password' => 'كلمة المرور غير صحيحة']);
        }

        $request->session()->put('admin-authed', true);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin-authed');

        return redirect()->route('admin.dashboard');
    }

    public function toggleResolved(Request $request, Feedback $feedback)
    {
        if (! $this->authed($request)) {
            abort(403);
        }
        $feedback->update(['resolved' => ! $feedback->resolved]);

        return back();
    }
}
