<?php ob_start(); ?>
<section class="hero">
    <span class="eyebrow">Wypożyczalnia sprzętu</span>
    <h1>Wypożycz sprzęt szybko i wygodnie</h1>
    <p>Wiertarki, rowery, narzędzia ogrodowe, elektronika i wiele więcej.
       Przeglądaj katalog, rezerwuj online i odbieraj bez formalności.</p>
    <p><a href="/equipment" class="btn">Zobacz katalog</a></p>
</section>
<?php
$content = ob_get_clean();
$title = $title ?? 'WypożyczalniaPRO';
require __DIR__ . '/../layout.php';
