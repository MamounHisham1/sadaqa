@extends('layout')

@section('title', 'تعديل الرابط · صدقة')

@section('content')
<div class="stream-root">
    <section class="panel form-card simple-page">
        <h1 class="page-title">تعديل الرابط</h1>
        <p class="page-sub">
            هذا الرابط:
            <a href="{{ route('stream', ['token' => $link->token]) }}" dir="ltr">{{ $link->token }}</a>
            — التغييرات تحتاج كلمة المرور التي ضبطتها عند الإنشاء.
        </p>

        <form method="POST" action="{{ route('links.update', ['token' => $link->token]) }}">
            @csrf

            <div class="field">
                <label for="link_password">كلمة مرور الرابط *</label>
                <input type="password" id="link_password" name="link_password" required autocomplete="current-password">
                @error('link_password')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label>نوع التلاوة</label>
                <div class="radio-row" id="typeRow">
                    @foreach(['sadaqa' => 'صدقة', 'gift' => 'هدية'] as $value => $label)
                        <label class="radio-pill {{ ($link->dedication_type === $value) ? 'on' : '' }}">
                            <input type="radio" name="dedication_type" value="{{ $value }}" {{ $link->dedication_type === $value ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="field">
                <label for="recipient_name" id="recipientLabel">لمن الصدقة</label>
                <input type="text" id="recipient_name" name="recipient_name" maxlength="100" value="{{ old('recipient_name', $link->recipient_name) }}">
            </div>

            <div class="field">
                <label for="sender_name">اسمك</label>
                <input type="text" id="sender_name" name="sender_name" maxlength="100" value="{{ old('sender_name', $link->sender_name) }}">
            </div>

            <div class="field">
                <label for="message">رسالة قصيرة</label>
                <textarea id="message" name="message" maxlength="600">{{ old('message', $link->message) }}</textarea>
                @error('message')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label>القرّاء</label>
                <div class="check-grid">
                    @foreach (config('quran.reciters') as $r)
                        <label class="check-pill">
                            <input type="checkbox" name="rotation[]" value="{{ $r['id'] }}"
                                {{ in_array($r['id'], old('rotation', $link->rotation ?? [])) ? 'checked' : '' }}>
                            <span>{{ $r['ar'] }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="hint" style="margin-top:8px;">اتركها كلها محددة للتناوب الكامل.</p>
            </div>

            <button type="submit" class="btn primary" style="width:100%;">حفظ التعديلات</button>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('typeRow').addEventListener('change', () => {
        document.querySelectorAll('#typeRow .radio-pill').forEach(p => p.classList.remove('on'));
        document.querySelector('#typeRow input:checked')?.closest('.radio-pill')?.classList.add('on');
        const label = document.getElementById('recipientLabel');
        if (label) label.textContent = document.querySelector('#typeRow input[value="gift"]').checked ? 'إهداء لمن' : 'لمن الصدقة';
    });
    // match the label to the loaded type
    (function syncLabel() {
        const label = document.getElementById('recipientLabel');
        if (label && document.querySelector('#typeRow input[value="gift"]')?.checked) label.textContent = 'إهداء لمن';
    })();
</script>
@endpush
