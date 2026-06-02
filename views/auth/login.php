<?php ob_start(); ?>
<section class="form-card">
    <h2>Logowanie</h2>
    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
    <?php endif; ?>
    <form method="post" action="/login">
        <?= App\Core\Csrf::field() ?>
        <label>Email<input type="email" name="email" required maxlength="150"></label>
        <label>Haslo<input type="password" name="password" required></label>
        <button type="submit" class="btn">Zaloguj</button>
    </form>
    <p><a href="/register">Nie masz konta? Zarejestruj sie</a></p>
</section>
<?php
$content = ob_get_clean();
$title = 'Logowanie';
require __DIR__ . '/../layout.php';
