<?php ob_start(); ?>
<section class="form-card">
    <h2>Logowanie</h2>
    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
    <?php endif; ?>
    <form method="post" action="/login">
        <?= App\Core\Csrf::field() ?>
        <label>Adres e-mail<input type="email" name="email" required maxlength="150" autocomplete="email"></label>
        <label>Hasło<input type="password" name="password" required autocomplete="current-password"></label>
        <button type="submit" class="btn">Zaloguj się</button>
    </form>
    <p><a href="/register">Nie masz konta? Zarejestruj się</a></p>
</section>
<?php
$content = ob_get_clean();
$title = 'Logowanie';
require __DIR__ . '/../layout.php';
