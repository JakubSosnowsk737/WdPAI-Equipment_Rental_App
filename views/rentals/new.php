<?php
use App\Models\Equipment;
/** @var Equipment $eq */
ob_start();
?>
<section class="form-card">
    <h2>Wypożyczenie: <?= htmlspecialchars($eq->name, ENT_QUOTES) ?></h2>
    <p>Stawka: <strong><?= number_format($eq->dailyRate, 2) ?> zł</strong> / dzień</p>
    <?php if (!empty($errors)): ?>
        <ul class="errors">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <form method="post" action="/rentals" id="rental-form">
        <?= App\Core\Csrf::field() ?>
        <input type="hidden" name="equipment_id" value="<?= (int) $eq->id ?>">
        <label>Ilość
            <input type="number" name="quantity" id="quantity"
                   min="1" max="<?= $eq->availableQuantity ?>" value="1" required>
        </label>

        <label>Termin wypożyczenia</label>

        <?php /* Kalendarz (JS) - wpisuje wartości do natywnych pól poniżej. */ ?>
        <div id="rental-calendar" class="calendar"
             data-rate="<?= htmlspecialchars((string) $eq->dailyRate, ENT_QUOTES) ?>"></div>

        <?php /* Natywne pola dat = jedyne pola formularza (fallback bez JS).
                  Kalendarz wpisuje do nich wartości i ukrywa ten kontener. */ ?>
        <div class="native-dates" id="native-dates">
            <label>Data od
                <input type="date" name="start_date" id="start_date" required>
            </label>
            <label>Data do
                <input type="date" name="end_date" id="end_date" required>
            </label>
        </div>

        <button type="submit" class="btn" id="rental-submit">Wypożycz</button>
    </form>
</section>
<script src="/js/calendar.js" defer></script>
<?php
$content = ob_get_clean();
$title = 'Nowe wypożyczenie';
require __DIR__ . '/../layout.php';
