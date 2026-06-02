<?php ob_start(); ?>
<section class="hero">
    <span class="eyebrow">Wypozyczalnia sprzetu</span>
    <h1>Wypozycz sprzet szybko i wygodnie</h1>
    <p>Wiertarki, rowery, narzedzia ogrodowe, elektronika i wiele wiecej.
       Przegladaj katalog, rezerwuj online i odbieraj bez formalnosci.</p>
    <p><a href="/equipment" class="btn">Zobacz katalog</a></p>
</section>
<?php
$content = ob_get_clean();
$title = $title ?? 'WypozyczalniaPRO';
require __DIR__ . '/../layout.php';
