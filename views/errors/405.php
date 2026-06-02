<?php ob_start(); ?>
<section class="form-card">
    <h2>405 - metoda niedozwolona</h2>
    <p>Ten zasob nie obsluguje uzytej metody HTTP.</p>
    <p><a href="/" class="btn">Wroc na strone glowna</a></p>
</section>
<?php
$content = ob_get_clean();
$title = '405';
require __DIR__ . '/../layout.php';
