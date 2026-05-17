<?php
use App\Models\Rental;
ob_start();
?>
<section>
    <h2>Moje wypozyczenia</h2>
    <?php if (empty($rentals)): ?>
        <p>Brak wypozyczen. <a href="/equipment">Przegladaj katalog</a>.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
            <tr><th>ID</th><th>Od</th><th>Do</th><th>Status</th><th>Koszt</th></tr>
            </thead>
            <tbody>
            <?php /** @var Rental[] $rentals */ foreach ($rentals as $r): ?>
                <tr>
                    <td>#<?= (int) $r->id ?></td>
                    <td><?= htmlspecialchars($r->startDate, ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($r->endDate, ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($r->status, ENT_QUOTES) ?></td>
                    <td><?= number_format($r->totalCost, 2) ?> zl</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
$title = 'Moje wypozyczenia';
require __DIR__ . '/../layout.php';
