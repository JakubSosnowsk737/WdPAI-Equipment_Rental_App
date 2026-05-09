<?php
use App\Models\Equipment;
ob_start();
?>
<section>
    <h2>Sprzet - panel administratora</h2>
    <p><a href="/admin/equipment/new" class="btn">+ Dodaj sprzet</a></p>
    <table class="data-table">
        <thead>
        <tr><th>ID</th><th>Nazwa</th><th>Kategoria</th><th>Stawka</th><th>Dostepne</th><th>Akcje</th></tr>
        </thead>
        <tbody>
        <?php /** @var Equipment[] $items */ foreach ($items as $eq): ?>
            <tr>
                <td><?= (int) $eq->id ?></td>
                <td><?= htmlspecialchars($eq->name, ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($eq->categoryName ?? '', ENT_QUOTES) ?></td>
                <td><?= number_format($eq->dailyRate, 2) ?> zl</td>
                <td><?= $eq->availableQuantity ?> / <?= $eq->totalQuantity ?></td>
                <td>
                    <a href="/admin/equipment/<?= (int) $eq->id ?>/edit" class="btn-sm">Edytuj</a>
                    <form method="post" action="/admin/equipment/<?= (int) $eq->id ?>/delete"
                          style="display:inline" onsubmit="return confirm('Usunac?')">
                        <button class="btn-sm btn-danger" type="submit">Usun</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php
$content = ob_get_clean();
$title = 'Admin - sprzet';
require __DIR__ . '/../../layout.php';
