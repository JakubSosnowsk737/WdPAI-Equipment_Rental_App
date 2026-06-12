<?php ob_start(); ?>
<section class="form-card">
    <h2>500 - błąd serwera</h2>
    <p>Coś poszło nie tak. Spróbuj ponownie później.</p>
    <p><a href="/" class="btn">Wróć na stronę główną</a></p>
</section>
<?php
$content = ob_get_clean();
$title = '500';
require __DIR__ . '/../layout.php';
