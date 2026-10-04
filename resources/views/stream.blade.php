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

    <section class="station panel" id="stationPanel">
        <div class="station-tabs" role="tablist" aria-label="نمط التشغيل">
            <button class="station-tab on" id="tabLive" role="tab" aria-selected="true" type="button">البث المباشر</button>
            <button class="station-tab" id="tabManual" role="tab" aria-selected="false" type="button">تشغيل يدوي</button>
        </div>

        <div class="live-chip" id="liveChip"><i></i><span>مباشر</span></div>
        <div class="station-surah ar" id="npSurahAr">جارٍ الاتصال بالإذاعة…</div>
        <div class="station-meta">
            <span id="npReciter">—</span>
            <span id="npKhatma"></span>
        </div>

        <div class="station-player">
            <button class="play-main" id="playBtn" title="تشغيل / إيقاف (مسافة)" aria-label="تشغيل أو إيقاف">
                <svg class="i-play" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                <svg class="i-pause" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 5h3.5v14H7zM13.5 5H17v14h-3.5z"/></svg>
            </button>
            <div class="station-progress"><i id="ayahProgress"></i></div>
        </div>

        <div class="manual-controls" id="manualControls" hidden>
            <div class="picker" id="surahPicker">
                <button class="picker-btn" type="button" aria-haspopup="listbox">
                    <span class="picker-value">—</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div class="picker-menu" hidden>
                    <input class="picker-search" type="text" placeholder="ابحث عن سورة…" aria-label="ابحث عن سورة">
                    <div class="picker-list" role="listbox"></div>
                </div>
            </div>
            <div class="picker" id="reciterPicker">
                <button class="picker-btn" type="button" aria-haspopup="listbox">
                    <span class="picker-value">—</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div class="picker-menu" hidden>
                    <input class="picker-search" type="text" placeholder="ابحث عن قارئ…" aria-label="ابحث عن قارئ">
                    <div class="picker-list" role="listbox"></div>
                </div>
            </div>
            <p class="picker-hint">اختر السورة والقارئ وستبدأ التلاوة فورًا — تُشغَّل لك وحدك.</p>
        </div>
    </section>

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
<script src="/js/player.js?v=18" defer></script>
@endpush
