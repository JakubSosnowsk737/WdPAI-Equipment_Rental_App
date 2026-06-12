<?php ob_start(); ?>
<section class="form-card">
    <h2>403 - brak uprawnień</h2>
    <p>Nie masz dostępu do tego zasobu.</p>
    <p><a href="/" class="btn">Wróć na stronę główną</a></p>
</section>
<?php
$content = ob_get_clean();
$title = '403';
require __DIR__ . '/../layout.php';
