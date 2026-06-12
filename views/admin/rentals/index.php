<?php
use App\Models\Rental;
ob_start();
?>
<section>
    <h2>Wszystkie wypożyczenia</h2>
    <table class="data-table">
        <thead>
        <tr><th>ID</th><th>Klient</th><th>Od</th><th>Do</th><th>Status</th><th>Koszt</th><th>Akcje</th></tr>
        </thead>
        <tbody>
        <?php /** @var Rental[] $rentals */ foreach ($rentals as $r): ?>
            <tr>
                <td>#<?= (int) $r->id ?></td>
                <td><?= htmlspecialchars($r->customerName ?? '', ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($r->startDate, ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($r->endDate, ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($r->statusLabel(), ENT_QUOTES) ?></td>
                <td><?= number_format($r->totalCost, 2) ?> zł</td>
                <td>
                    <?php if ($r->status !== 'zakonczone' && $r->status !== 'anulowane'): ?>
                        <form method="post" action="/rentals/<?= (int) $r->id ?>/return" style="display:inline">
                            <?= App\Core\Csrf::field() ?>
                            <button class="btn-sm" type="submit">Oznacz zwrot</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php
$content = ob_get_clean();
$title = 'Wypożyczenia - panel';
require __DIR__ . '/../../layout.php';
