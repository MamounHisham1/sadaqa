<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        // Apply the saved theme before first paint (works on every page).
        (function () {
            try {
                var t = localStorage.getItem('theme') || 'light';
                if (t === 'parchment') t = 'light';
                document.documentElement.dataset.theme = t;
            } catch (e) { /* default */ }
        })();
    </script>
    <title>@yield('title', 'صدقة · إذاعة القرآن — تلاوة لا تتوقف')</title>
    <meta name="description" content="@yield('meta_description', 'تلاوة متواصلة للقرآن الكريم مجانًا وبدون توقف. أنشئ رابط صدقة جارية باسم من تحب — إذاعة مباشرة يسمعها الجميع في نفس اللحظة.')">
    <meta name="theme-color" content="#167a5e">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32.png">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="صدقة">
    @stack('meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Amiri:wght@400;700&family=Amiri+Quran&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/app.css?v=16">
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
        نص القرآن: رواية عثمانية (تنزيل، عبر quran.com) · التلاوات: mp3quran.net
    </footer>

    <button class="pwa-install" id="pwaInstall" hidden>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
        ثبّت التطبيق
    </button>

    <div class="toast" id="toast" role="status"></div>

    <script>
        // Theme toggle — lives in the layout so it works on every page.
        (function () {
            var btn = document.getElementById('themeBtn');
            if (!btn) return;
            btn.addEventListener('click', function () {
                var next = document.documentElement.dataset.theme === 'night' ? 'light' : 'night';
                document.documentElement.dataset.theme = next;
                try { localStorage.setItem('theme', next); } catch (e) { /* private mode */ }
            });
        })();

        // PWA install button — appears when the browser offers installation,
        // hides once installed (or when running as the installed app).
        (function () {
            var btn = document.getElementById('pwaInstall');
            var installed = function () {
                return localStorage.getItem('pwa-installed') === '1'
                    || window.matchMedia('(display-mode: standalone)').matches
                    || window.navigator.standalone === true;
            };
            var deferred = null;
            var refresh = function () {
                btn.hidden = !(deferred && !installed());
            };
            window.addEventListener('beforeinstallprompt', function (e) {
                e.preventDefault();
                deferred = e;
                refresh();
            });
            window.addEventListener('appinstalled', function () {
                try { localStorage.setItem('pwa-installed', '1'); } catch (e) { /* ignore */ }
                deferred = null;
                refresh();
            });
            btn.addEventListener('click', function () {
                if (!deferred) return;
                deferred.prompt();
                deferred.userChoice.then(function (choice) {
                    if (choice.outcome === 'accepted') {
                        try { localStorage.setItem('pwa-installed', '1'); } catch (e) { /* ignore */ }
                    }
                    deferred = null;
                    refresh();
                });
            });
            if (installed()) {
                try { localStorage.setItem('pwa-installed', '1'); } catch (e) { /* ignore */ }
            }
            refresh();
        })();

        // Service worker (HTTPS or localhost only).
        if ('serviceWorker' in navigator && (location.protocol === 'https:' || ['localhost', '127.0.0.1'].includes(location.hostname))) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js').catch(function () { /* offline shell is optional */ });
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
