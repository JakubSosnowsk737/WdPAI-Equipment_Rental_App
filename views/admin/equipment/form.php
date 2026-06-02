<?php
use App\Models\Category;
use App\Models\Equipment;
/** @var Equipment|null $eq */
/** @var Category[] $categories */
$isEdit = $eq !== null;
ob_start();
?>
<section class="form-card">
    <h2><?= $isEdit ? 'Edytuj sprzet' : 'Dodaj sprzet' ?></h2>
    <?php if (!empty($errors)): ?>
        <ul class="errors">
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err, ENT_QUOTES) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <form method="post" action="<?= $isEdit ? '/admin/equipment/' . (int) $eq->id : '/admin/equipment' ?>">
        <?= App\Core\Csrf::field() ?>
        <label>Nazwa
            <input type="text" name="name" value="<?= htmlspecialchars($eq->name ?? '', ENT_QUOTES) ?>" required maxlength="150">
        </label>
        <label>Kategoria
            <select name="category_id" required>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c->id ?>" <?= $eq && $eq->categoryId === $c->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c->name, ENT_QUOTES) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Opis
            <textarea name="description" rows="3"><?= htmlspecialchars($eq->description ?? '', ENT_QUOTES) ?></textarea>
        </label>
        <label>Stawka dzienna (zl)
            <input type="number" step="0.01" min="0" name="daily_rate" value="<?= $eq->dailyRate ?? '' ?>" required>
        </label>
        <label>Ilosc sztuk
            <input type="number" min="1" name="total_quantity" value="<?= $eq->totalQuantity ?? 1 ?>" required>
        </label>
        <button type="submit" class="btn"><?= $isEdit ? 'Zapisz' : 'Dodaj' ?></button>
    </form>

    <?php if ($isEdit): ?>
        <h3>Obrazki</h3>
        <form method="post" enctype="multipart/form-data"
              action="/admin/equipment/<?= (int) $eq->id ?>/images">
            <?= App\Core\Csrf::field() ?>
            <input type="file" name="image" accept="image/*" required>
            <button type="submit" class="btn">Dodaj obraz</button>
        </form>
    <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
$title = $isEdit ? 'Edycja sprzetu' : 'Nowy sprzet';
require __DIR__ . '/../../layout.php';
