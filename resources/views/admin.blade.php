@extends('layout')

@section('title', 'لوحة الإدارة · صدقة')

@section('content')
<div class="stream-root">
@if (! $authed)
    <section class="panel form-card simple-page" style="max-width:460px;">
        <h1 class="page-title">لوحة الإدارة</h1>
        <p class="page-sub">أدخل كلمة مرور الإدارة.</p>
        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <div class="field">
                <label for="admin_password">كلمة المرور</label>
                <input type="password" id="admin_password" name="password" required autocomplete="current-password">
                @error('password')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn primary" style="width:100%;">دخول</button>
        </form>
    </section>
@else
    <section class="panel form-card simple-page">
        <div class="admin-head">
            <h1 class="page-title">لوحة الإدارة</h1>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="btn small" type="submit">خروج</button>
            </form>
        </div>

        <h2 class="admin-section">الرسائل والمشاكل</h2>
        @forelse ($feedback as $f)
            <div class="fb-item {{ $f->resolved ? 'resolved' : '' }}">
                <div class="fb-row">
                    <span class="fb-type {{ $f->type }}">{{ $f->type === 'bug' ? 'مشكلة' : 'ميزة' }}</span>
                    <span class="fb-date" dir="ltr">{{ $f->created_at->format('Y-m-d H:i') }}</span>
                    <form method="POST" action="{{ route('admin.feedback.toggle', $f) }}">
                        @csrf
                        <button class="btn small" type="submit">{{ $f->resolved ? 'إعادة فتح' : 'تمّت المعالجة' }}</button>
                    </form>
                </div>
                <p class="fb-message">{{ $f->message }}</p>
                @if ($f->contact)
                    <div class="fb-contact">تواصل: <b dir="ltr">{{ $f->contact }}</b></div>
                @endif
                @if ($f->url)
                    <div class="fb-contact">من: <span dir="ltr" class="fb-url">{{ $f->url }}</span></div>
                @endif
            </div>
        @empty
            <p class="page-sub">لا رسائل بعد.</p>
        @endforelse

        <h2 class="admin-section">الروابط (آخر ١٠٠)</h2>
        <div class="links-table">
            @foreach ($links as $l)
                <div class="link-row">
                    <a href="{{ route('stream', ['token' => $l->token]) }}" dir="ltr">{{ $l->token }}</a>
                    <span>{{ $l->recipient_name ?: 'صدقة عامة' }}</span>
                    <span class="link-meta">{{ ar_digits($l->khatmas) }} ختمة</span>
                    <span class="link-meta" title="{{ $l->created_at->diffForHumans() }}">{{ $l->created_at->format('m-d') }}</span>
                    <span class="badge">{{ $l->password ? '🔒' : '' }}</span>
                </div>
            @endforeach
        </div>
    </section>
@endif
</div>
@endsection
