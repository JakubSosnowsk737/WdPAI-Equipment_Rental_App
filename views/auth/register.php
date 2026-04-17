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
        <label>Imie<input type="text" name="first_name" required></label>
        <label>Nazwisko<input type="text" name="last_name" required></label>
        <label>Email<input type="email" name="email" required></label>
        <label>Haslo<input type="password" name="password" required minlength="6"></label>
        <button type="submit" class="btn">Zarejestruj</button>
    </form>
</section>
<?php
$content = ob_get_clean();
$title = 'Rejestracja';
require __DIR__ . '/../layout.php';
