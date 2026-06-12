<?php ob_start(); ?>
<section class="form-card">
    <h2>Rejestracja</h2>
    <?php if (!empty($errors)): ?>
        <ul class="errors">
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err, ENT_QUOTES) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <form method="post" action="/register">
        <?= App\Core\Csrf::field() ?>
        <label>Imię<input type="text" name="first_name" required maxlength="80" autocomplete="given-name"></label>
        <label>Nazwisko<input type="text" name="last_name" required maxlength="80" autocomplete="family-name"></label>
        <label>Adres e-mail<input type="email" name="email" required maxlength="150" autocomplete="email"></label>
        <label>Hasło<input type="password" name="password" required minlength="8" maxlength="200" autocomplete="new-password"></label>
        <button type="submit" class="btn">Zarejestruj się</button>
    </form>
    <p><a href="/login">Masz już konto? Zaloguj się</a></p>
</section>
<?php
$content = ob_get_clean();
$title = 'Rejestracja';
require __DIR__ . '/../layout.php';
