<?php
use App\Core\Session;
Session::start();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'WypozyczalniaPRO', ENT_QUOTES) ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <a href="/" class="logo">WypozyczalniaPRO</a>
        <nav>
            <?php if (Session::isAuthenticated()): ?>
                <a href="/equipment">Katalog</a>
                <a href="/rentals/mine">Moje wypozyczenia</a>
                <?php if (Session::userRole() === 'admin'): ?>
                    <a href="/admin/users">Uzytkownicy</a>
                    <a href="/admin/equipment">Sprzet</a>
                <?php endif; ?>
                <span>| <?= htmlspecialchars((string) Session::userName(), ENT_QUOTES) ?></span>
                <a href="/logout">Wyloguj</a>
            <?php else: ?>
                <a href="/login">Zaloguj</a>
                <a href="/register">Zarejestruj</a>
            <?php endif; ?>
        </nav>
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
</body>
</html>
