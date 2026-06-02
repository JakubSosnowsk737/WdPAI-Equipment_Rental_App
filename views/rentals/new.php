<?php
use App\Models\Equipment;
/** @var Equipment $eq */
ob_start();
?>
<section class="form-card">
    <h2>Wypozyczenie: <?= htmlspecialchars($eq->name, ENT_QUOTES) ?></h2>
    <p>Stawka: <?= number_format($eq->dailyRate, 2) ?> zl / dzien</p>
    <?php if (!empty($errors)): ?>
        <ul class="errors">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <form method="post" action="/rentals">
        <?= App\Core\Csrf::field() ?>
        <input type="hidden" name="equipment_id" value="<?= (int) $eq->id ?>">
        <label>Ilosc
            <input type="number" name="quantity" min="1" max="<?= $eq->availableQuantity ?>" value="1" required>
        </label>
        <label>Data od
            <input type="date" name="start_date" required>
        </label>
        <label>Data do
            <input type="date" name="end_date" required>
        </label>
        <button type="submit" class="btn">Wypozycz</button>
    </form>
</section>
<?php
$content = ob_get_clean();
$title = 'Nowe wypozyczenie';
require __DIR__ . '/../layout.php';
