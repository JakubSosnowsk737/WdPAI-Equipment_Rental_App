<?php ob_start(); ?>
<section class="form-card">
    <h2>500 - blad serwera</h2>
    <p>Cos poszlo nie tak. Sprobuj ponownie pozniej.</p>
    <p><a href="/" class="btn">Wroc na strone glowna</a></p>
</section>
<?php
$content = ob_get_clean();
$title = '500';
require __DIR__ . '/../layout.php';
