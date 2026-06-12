<?php ob_start(); ?>
<section class="form-card">
    <h2>404 - nie znaleziono</h2>
    <p>Strona, której szukasz, nie istnieje.</p>
    <p><a href="/" class="btn">Wróć na stronę główną</a></p>
</section>
<?php
$content = ob_get_clean();
$title = '404';
require __DIR__ . '/../layout.php';
