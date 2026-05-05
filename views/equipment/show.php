<?php
use App\Core\Session;
use App\Models\Equipment;
/** @var Equipment $eq */
ob_start();
?>
<section class="equipment-detail">
    <p><a href="/equipment">&laquo; powrot do katalogu</a></p>
    <h2><?= htmlspecialchars($eq->name, ENT_QUOTES) ?></h2>
    <p class="cat"><?= htmlspecialchars($eq->categoryName ?? '', ENT_QUOTES) ?></p>
    <p><?= nl2br(htmlspecialchars($eq->description ?? '', ENT_QUOTES)) ?></p>
    <p class="rate"><?= number_format($eq->dailyRate, 2) ?> zl / dzien</p>
    <p>Dostepne: <strong><?= $eq->availableQuantity ?></strong> z <?= $eq->totalQuantity ?></p>
    <?php if ($eq->isAvailable() && Session::isAuthenticated()): ?>
        <a href="/rentals/new?equipment_id=<?= (int) $eq->id ?>" class="btn">Wypozycz</a>
    <?php elseif (!Session::isAuthenticated()): ?>
        <p><a href="/login" class="btn">Zaloguj sie aby wypozyczyc</a></p>
    <?php else: ?>
        <p><em>Sprzet chwilowo niedostepny.</em></p>
    <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
$title = $eq->name;
require __DIR__ . '/../layout.php';
