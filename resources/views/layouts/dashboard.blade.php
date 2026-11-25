<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <meta name="darkreader-lock">
    <title>@yield('title', 'Dashboard Jamaah - ROZWIA Islamic Travel')</title>
    @include('partials.favicon')

    <!-- styling vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Abhaya+Libre:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html, body {
            color-scheme: light only;
        }
        .font-serif-display {
            font-family: 'Abhaya Libre', 'Playfair Display', serif;
        }
    </style>
</head>

<body class="bg-[#EAE0D0] font-sans text-stone-800 antialiased">

    <div class="min-h-screen">
        @yield('content')
    </div>

    <script>
        (function () {
            const key = 'rozwia_scroll_' + window.location.pathname;
            const saved = sessionStorage.getItem(key);
            if (saved !== null) {
                sessionStorage.removeItem(key);
                window.addEventListener('DOMContentLoaded', () => {
                    window.scrollTo({ top: parseInt(saved, 10) || 0, behavior: 'instant' });
                });
            }
            const remember = () => sessionStorage.setItem(key, String(window.scrollY));
            document.addEventListener('submit', remember, true);
            document.addEventListener('click', (e) => {
                const a = e.target.closest('a[href]');
                if (!a) return;
                try {
                    const url = new URL(a.href, window.location.origin);
                    if (url.origin === window.location.origin && url.pathname === window.location.pathname && !url.hash) {
                        remember();
                    }
                } catch (_) {}
            }, true);
        })();
    </script>

</body>
</html>
