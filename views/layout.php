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
                <span>Witaj, <?= htmlspecialchars((string) Session::userName(), ENT_QUOTES) ?></span>
                <a href="/logout">Wyloguj</a>
            <?php else: ?>
                <a href="/login">Zaloguj</a>
                <a href="/register">Zarejestruj</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <?= $content ?? '' ?>
    </main>
</body>
</html>
