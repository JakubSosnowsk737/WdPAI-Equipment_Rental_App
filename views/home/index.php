<?php ob_start(); ?>
<section>
    <h2>Witaj na stronie wypozyczalni sprzetu</h2>
    <p>Wypozyczaj wiertarki, rowery, narzedzia ogrodowe i wiecej.</p>
    <p><a href="/equipment" class="btn">Zobacz katalog</a></p>
</section>
<?php
$content = ob_get_clean();
$title = $title ?? 'WypozyczalniaPRO';
require __DIR__ . '/../layout.php';
