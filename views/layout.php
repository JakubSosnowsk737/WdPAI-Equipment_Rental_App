<?php
use App\Core\Session;
Session::start();
$role = Session::userRole();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title><?= htmlspecialchars($title ?? 'WypozyczalniaPRO', ENT_QUOTES) ?></title>
    <?php /* Ustawienie motywu przed renderem - eliminuje miganie (FOUC). */ ?>
    <script>
        (function () {
            try {
                var t = localStorage.getItem('theme');
                if (!t) {
                    t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                document.documentElement.setAttribute('data-theme', t);
            } catch (e) {}
        })();
    </script>
    <link rel="stylesheet" href="/css/style.css?v=3">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a href="/" class="brand">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <path d="M3.27 6.96 12 12.01l8.73-5.05"/>
                        <path d="M12 22.08V12"/>
                    </svg>
                </span>
                <span>Wypozyczalnia<span class="brand-accent">PRO</span></span>
            </a>

            <nav class="nav">
                <a href="/equipment">Katalog</a>
                <?php if (Session::isAuthenticated()): ?>
                    <a href="/rentals/mine">Moje wypozyczenia</a>
                    <?php if ($role === 'admin'): ?>
                        <a href="/admin/users">Uzytkownicy</a>
                        <a href="/admin/equipment">Sprzet</a>
                    <?php endif; ?>
                    <?php if (in_array($role, ['admin', 'pracownik'], true)): ?>
                        <a href="/admin/rentals">Wypozyczenia</a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>

            <div class="header-actions">
                <button id="themeToggle" class="theme-toggle" type="button"
                        aria-label="Przelacz tryb jasny/ciemny" title="Przelacz motyw">
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9z"/>
                    </svg>
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"/>
                        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                    </svg>
                </button>

                <?php if (Session::isAuthenticated()): ?>
                    <span class="user-chip"><?= htmlspecialchars((string) Session::userName(), ENT_QUOTES) ?></span>
                    <a href="/logout" class="btn-sm btn-ghost">Wyloguj</a>
                <?php else: ?>
                    <a href="/login" class="btn-sm btn-ghost">Zaloguj</a>
                    <a href="/register" class="btn-sm">Zarejestruj</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <?php $flash = Session::flash('success'); if ($flash): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash, ENT_QUOTES) ?></div>
        <?php endif; ?>
        <?php $flash = Session::flash('error'); if ($flash): ?>
            <div class="alert alert-error"><?= htmlspecialchars($flash, ENT_QUOTES) ?></div>
        <?php endif; ?>
        <?= $content ?? '' ?>
    </main>

    <footer class="site-footer">
        &copy; <?= date('Y') ?> WypozyczalniaPRO &middot; system wypozyczania sprzetu
    </footer>

    <script src="/js/theme.js" defer></script>
</body>
</html>
