<?php
use App\Models\Equipment;
ob_start();
?>
<section>
    <h2>Dostepny sprzet</h2>
    <div class="equipment-grid">
        <?php /** @var Equipment[] $items */ foreach ($items as $eq): ?>
            <article class="equipment-card">
                <h3><?= htmlspecialchars($eq->name, ENT_QUOTES) ?></h3>
                <p class="cat"><?= htmlspecialchars($eq->categoryName ?? '', ENT_QUOTES) ?></p>
                <p><?= htmlspecialchars($eq->description ?? '', ENT_QUOTES) ?></p>
                <p class="rate"><?= number_format($eq->dailyRate, 2) ?> zl / dzien</p>
                <p>Dostepne: <?= $eq->availableQuantity ?> / <?= $eq->totalQuantity ?></p>
            </article>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
            <p>Brak sprzetu w katalogu.</p>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
$title = 'Katalog sprzetu';
require __DIR__ . '/../layout.php';
