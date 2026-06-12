<?php ob_start(); ?>
<section class="form-card">
    <h2>400 - błędne żądanie</h2>
    <p>Żądanie zawiera niepoprawne dane.</p>
    <p><a href="/" class="btn">Wróć na stronę główną</a></p>
</section>
<?php
$content = ob_get_clean();
$title = '400';
require __DIR__ . '/../layout.php';
