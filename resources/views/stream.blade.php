@extends('layout')

@push('meta')
    @if ($link)
        <meta property="og:title" content="{{ $link->recipient_name ? 'تلاوة قرآنية عن '.$link->recipient_name : 'تلاوة قرآنية عامة' }} · صدقة">
        <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($link->message ?? 'تلاوة قرآنية متواصلة مُهداة بمحبة.', 120) }}">
        <meta property="og:type" content="website">
    @endif
@endpush

@section('content')
<div class="stream-root">

    @if ($isNew && $link)
        <div class="success-banner">
            <span>الرابط جاهز — شاركه:</span>
            <code>{{ route('stream', ['token' => $link->token]) }}</code>
            <button class="btn small" id="shareCopy">نسخ</button>
            <a class="btn small" id="shareWa" href="#">واتساب</a>
        </div>
    @endif

    @if ($link)
        <section class="dedication panel">
            @if ($link->recipient_name)
                <div class="type">{{ $dedicationTypes[$link->dedication_type] ?? 'صدقة عن' }}</div>
                <h1 class="name">{{ $link->recipient_name }}</h1>
            @else
                <h1 class="name">{{ ($link->dedication_type === 'gift') ? 'هدية عامة' : 'صدقة عامة' }}</h1>
            @endif
            @if ($link->message)
                <p class="message">«{{ $link->message }}»</p>
            @endif
            @if ($link->sender_name)
                <div class="from">من <b>{{ $link->sender_name }}</b> بمحبة</div>
            @endif
            <div class="stats">
                <b id="statAyahs">{{ ar_digits($link->ayahs_played) }}</b> آية تُليت ·
                <b id="statKhatmas">{{ ar_digits($link->khatmas) }}</b> ختمة ·
                <b>{{ ar_digits($link->views) }}</b> زيارة
            </div>
            @if (! $isNew)
                <div class="share-row">
                    <button class="btn small" id="shareCopy">نسخ الرابط</button>
                    <a class="btn small" id="shareWa" href="#">مشاركة واتساب</a>
                </div>
            @endif
        </section>
    @else
        <section class="dedication panel">
            <div class="type">إذاعة صدقة</div>
            <h1 class="name">القرآن الكريم</h1>
            <p class="message">تلاوة متواصلة — قارئ يختم المصحف كاملًا ثم يتلوه القارئ التالي، بلا توقف.</p>
            <div class="share-row">
                <button class="btn small" id="shareCopy">نسخ الرابط</button>
                <a class="btn small" id="shareWa" href="#">مشاركة واتساب</a>
            </div>
        </section>
    @endif

    <section class="station panel">
        <div class="live-chip" id="liveChip"><i></i><span>مباشر</span></div>
        <div class="station-surah ar" id="npSurahAr">اضغط زر التشغيل للانضمام</div>
        <div class="station-meta">
            <span id="npReciter">—</span>
            <span id="npKhatma"></span>
        </div>
        <button class="live-return" id="liveReturn" style="display:none">عُد إلى المباشر ←</button>
    </section>
</div>

<div class="player-bar" id="playerBar">
    <div class="player-inner">
        <button class="play-main" id="playBtn" title="تشغيل / إيقاف (مسافة)" aria-label="تشغيل أو إيقاف">
            <svg class="i-play" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
            <svg class="i-pause" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 5h3.5v14H7zM13.5 5H17v14h-3.5z"/></svg>
        </button>

        <div class="np">
            <div class="line1">
                <span class="surah-ar" id="npSurahBar">—</span>
            </div>
            <div class="line2">
                <span id="npStatus">اضغط زر التشغيل للانضمام إلى البث</span>
            </div>
            <div class="ayah-progress"><i id="ayahProgress"></i></div>
        </div>

        <div class="player-controls">
            <select id="surahSel" title="استمع لسورة منفردة" aria-label="استمع لسورة منفردة">
                @foreach ($clientPayload['chapters'] as $c)
                    <option value="{{ $c['id'] }}">{{ ar_digits($c['id']) }} · {{ $c['na'] }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="begin-overlay" id="beginOverlay">
    <div>
        <button class="begin-play" id="beginBtn" aria-label="ابدأ التلاوة">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
        </button>
        @if ($link)
            <div class="t1">
                @if ($link->recipient_name)
                    {{ $dedicationTypes[$link->dedication_type] ?? 'صدقة عن' }} <span class="ar">{{ $link->recipient_name }}</span>
                @else
                    {{ ($link->dedication_type === 'gift') ? 'هدية عامة' : 'صدقة عامة' }}
                @endif
            </div>
            <div class="t2">اضغط لتنضم إلى البث المباشر — تلاوة لا تتوقف، ختمةً بعد ختمة.</div>
        @else
            <div class="t1">إذاعة صدقة</div>
            <div class="t2">اضغط لتنضم إلى البث المباشر — تلاوة لا تتوقف، ختمةً بعد ختمة.</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.__QURAN__ = @json($clientPayload);
</script>
<script src="/js/player.js?v=16" defer></script>
@endpush
