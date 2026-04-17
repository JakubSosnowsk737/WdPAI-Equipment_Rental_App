<?php ob_start(); ?>
<section>
    <h2>Witaj na stronie wypozyczalni sprzetu</h2>
    <p><a href="/register" class="btn">Zarejestruj sie</a></p>
</section>
<?php
$content = ob_get_clean();
$title = $title ?? 'WypozyczalniaPRO';
require __DIR__ . '/../layout.php';
