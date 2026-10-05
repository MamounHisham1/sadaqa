@extends('layout')

@section('title', 'طلب ميزة أو الإبلاغ عن مشكلة · صدقة')

@section('content')
<div class="stream-root">
    <section class="panel form-card simple-page">
        @if ($sent)
            <h1 class="page-title">وصلتنا رسالتك ♥</h1>
            <p class="page-sub">شكرًا لك — نراجع كل رسالة تصلنا.</p>
            <a class="btn" href="{{ route('radio') }}">العودة إلى الإذاعة</a>
        @else
            <h1 class="page-title">طلب ميزة أو الإبلاغ عن مشكلة</h1>
            <p class="page-sub">ساعدنا نتحسّن — رسالتك تصل فريق العمل مباشرة.</p>

            <form method="POST" action="{{ route('feedback.store') }}">
                @csrf
                <div class="field">
                    <div class="radio-row" id="fbTypeRow">
                        <label class="radio-pill on">
                            <input type="radio" name="type" value="bug" checked>
                            مشكلة
                        </label>
                        <label class="radio-pill">
                            <input type="radio" name="type" value="feature">
                            طلب ميزة
                        </label>
                    </div>
                </div>

                <div class="field">
                    <label for="fb_message">رسالتك</label>
                    <textarea id="fb_message" name="message" maxlength="2000" required></textarea>
                    @error('message')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="fb_contact">وسيلة تواصل <span class="hint">اختياري · بريد أو معرف تلغرام لنرد عليك</span></label>
                    <input type="text" id="fb_contact" name="contact" maxlength="120">
                    @error('contact')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn primary" style="width:100%;">إرسال</button>
            </form>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('fbTypeRow').addEventListener('change', () => {
        document.querySelectorAll('#fbTypeRow .radio-pill').forEach(p => p.classList.remove('on'));
        document.querySelector('#fbTypeRow input:checked')?.closest('.radio-pill')?.classList.add('on');
    });
</script>
@endpush
