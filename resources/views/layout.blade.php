<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'صدقة · إذاعة القرآن — تلاوة لا تتوقف')</title>
    <meta name="description" content="@yield('meta_description', 'تلاوة متواصلة للقرآن الكريم مجانًا وبدون توقف. أنشئ رابط صدقة جارية باسم من تحب — قارئٌ يختم المصحف كاملًا ثم يليه القارئ التالي.')">
    @stack('meta')
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='14' fill='%23f6f5f1'/%3E%3Cpath d='M41 38.5A12.5 12.5 0 1 1 25.5 23 10 10 0 0 0 41 38.5z' fill='%23167a5e'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Amiri:wght@400;700&family=Amiri+Quran&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/app.css?v=15">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('home') }}" aria-label="صدقة — الرئيسية">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21 13.3A9 9 0 1 1 10.7 3a7 7 0 0 0 10.3 10.3z"/></svg>
            صدقة
        </a>
        <span class="spacer"></span>
        <nav aria-label="القائمة الرئيسية">
            <a href="{{ route('radio') }}">استمع</a>
            <a href="{{ route('home') }}#create">أنشئ رابطًا</a>
        </nav>
        <button class="icon-btn" id="themeBtn" title="الوضع الليلي / النهاري" aria-label="تبديل الوضع الليلي">
            <svg class="i-moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
            <svg class="i-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        </button>
    </header>

    @yield('content')

    <footer class="site-footer">
        <span class="ar">وَرَتِّلِ ٱلْقُرْآنَ تَرْتِيلًا</span>
        صدقة · إذاعة القرآن — تلاوة لأجل الصدقة الجارية<br>
        نص القرآن: رواية عثمانية (تنزيل، عبر quran.com) · التلاوات: islamic.network و everyayah.com
    </footer>

    <div class="toast" id="toast" role="status"></div>

    @stack('scripts')
</body>
</html>
