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
        <h1>WypozyczalniaPRO</h1>
    </header>
    <main>
        <?= $content ?? '' ?>
    </main>
</body>
</html>
