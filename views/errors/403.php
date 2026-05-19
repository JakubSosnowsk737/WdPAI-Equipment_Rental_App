<?php ob_start(); ?>
<section class="form-card">
    <h2>403 - brak uprawnien</h2>
    <p>Nie masz dostepu do tego zasobu.</p>
    <p><a href="/" class="btn">Wroc na strone glowna</a></p>
</section>
<?php
$content = ob_get_clean();
$title = '403';
require __DIR__ . '/../layout.php';
