<?php
use App\Models\Equipment;
use App\Models\Category;
ob_start();
?>
<section>
    <div class="page-head">
        <h2>Dostepny sprzet</h2>
        <p class="sub">Przegladaj i rezerwuj sprzet dostepny w wypozyczalni.</p>
    </div>
    <div class="search-bar">
        <input type="text" id="eq-search" placeholder="Szukaj sprzetu...">
        <select id="eq-category">
            <option value="">-- wszystkie kategorie --</option>
            <?php /** @var Category[] $categories */ foreach ($categories as $c): ?>
                <option value="<?= (int) $c->id ?>"><?= htmlspecialchars($c->name, ENT_QUOTES) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div id="eq-grid" class="equipment-grid">
        <?php /** @var Equipment[] $items */ foreach ($items as $eq): ?>
            <article class="equipment-card">
                <h3><?= htmlspecialchars($eq->name, ENT_QUOTES) ?></h3>
                <p class="cat"><?= htmlspecialchars($eq->categoryName ?? '', ENT_QUOTES) ?></p>
                <p><?= htmlspecialchars($eq->description ?? '', ENT_QUOTES) ?></p>
                <p class="rate"><?= number_format($eq->dailyRate, 2) ?> zl / dzien</p>
                <p class="stock">Dostepne: <?= $eq->availableQuantity ?> / <?= $eq->totalQuantity ?></p>
                <a href="/equipment/<?= (int) $eq->id ?>" class="btn-sm">Szczegoly</a>
            </article>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
            <p>Brak sprzetu w katalogu.</p>
        <?php endif; ?>
    </div>
</section>
<script src="/js/search.js" defer></script>
<?php
$content = ob_get_clean();
$title = 'Katalog sprzetu';
require __DIR__ . '/../layout.php';
