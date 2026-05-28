<?php ob_start(); ?>
<section class="form-card">
    <h2>400 - bledne zadanie</h2>
    <p>Zadanie zawiera niepoprawne dane.</p>
    <p><a href="/" class="btn">Wroc na strone glowna</a></p>
</section>
<?php
$content = ob_get_clean();
$title = '400';
require __DIR__ . '/../layout.php';
