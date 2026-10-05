@extends('layout')

@section('content')
<main>
    <section class="hero">
        <p class="bismillah">بِسْمِ ٱللَّهِ ٱلرَّحْمَـٰنِ ٱلرَّحِيمِ</p>
        <h1>إذاعة قرآنية لا تتوقف</h1>
        <p class="lead">
            تلاوة متواصلة للقرآن الكريم — قارئ يختم المصحف كاملًا ثم يتلوه القارئ التالي،
            وهكذا أبدًا. أنشئ رابطًا باسم من تحب، وكل من يفتحه تُكتب له التلاوة صدقةً جارية.
        </p>
        <div class="cta-row">
            <a class="btn primary" href="#create">أنشئ رابطًا</a>
            <a class="btn" href="{{ route('radio') }}">استمع الآن</a>
        </div>
    </section>

    <section class="section-head" id="create">
        <h2>أنشئ رابط تلاوة</h2>
        <p>كل الحقول اختيارية — أقل من دقيقة، والرابط يبقى للأبد.</p>
    </section>

    <div class="form-wrap">
        <form class="panel form-card" method="POST" action="{{ route('links.store') }}">
            @csrf

            <div class="field">
                <label>نوع التلاوة</label>
                <div class="radio-row" id="typeRow">
                    @foreach(['sadaqa' => 'صدقة', 'gift' => 'هدية'] as $value => $label)
                        <label class="radio-pill {{ ($value === 'sadaqa') ? 'on' : '' }}">
                            <input type="radio" name="dedication_type" value="{{ $value }}" {{ $value === 'sadaqa' ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="field">
                <label for="sender_name">اسمك</label>
                <input type="text" id="sender_name" name="sender_name" maxlength="100" value="{{ old('sender_name') }}">
            </div>

            <div class="field">
                <label for="recipient_name">لمن الصدقة</label>
                <input type="text" id="recipient_name" name="recipient_name" maxlength="100" value="{{ old('recipient_name') }}">
            </div>

            <div class="field">
                <label for="message">رسالة قصيرة</label>
                <textarea id="message" name="message" maxlength="600">{{ old('message') }}</textarea>
            </div>

            <div class="field">
                <label for="link_password">كلمة مرور للتعديل لاحقًا <span class="hint">اختياري</span></label>
                <input type="password" id="link_password" name="link_password" autocomplete="new-password">
            </div>

            <div class="field">
                <label>القرّاء <span class="hint">اختر من تتناوب بينهم التلاوة — الكل محدد افتراضيًا</span></label>
                <div class="check-grid">
                    @foreach ($reciters as $r)
                        <label class="check-pill">
                            <input type="checkbox" name="rotation[]" value="{{ $r['id'] }}" checked>
                            <span>{{ $r['ar'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn primary" style="width:100%;">أنشئ الرابط</button>
        </form>
    </div>

    <section class="hadith">
        <span class="ar">«إذا مات الإنسان انقطع عمله إلا من ثلاثة: صدقة جارية، أو علم ينتفع به، أو ولد صالح يدعو له»</span>
    </section>
</main>

@push('scripts')
<script>
    document.getElementById('typeRow').addEventListener('change', () => {
        document.querySelectorAll('#typeRow .radio-pill').forEach(p => p.classList.remove('on'));
        document.querySelector('#typeRow input:checked')?.closest('.radio-pill')?.classList.add('on');
    });
</script>
@endpush
@endsection
