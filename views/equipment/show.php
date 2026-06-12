<?php
use App\Core\Session;
use App\Models\Equipment;
/** @var Equipment $eq */
/** @var string[] $images */
/** @var Equipment[] $related */
$available = $eq->isAvailable();
ob_start();
?>
<p><a href="/equipment" class="back">&laquo; powrót do katalogu</a></p>

<article class="product">
    <div class="product-media">
        <?php if (!empty($images)): ?>
            <img class="product-photo" src="<?= htmlspecialchars($images[0], ENT_QUOTES) ?>"
                 alt="<?= htmlspecialchars($eq->name, ENT_QUOTES) ?>">
            <?php if (count($images) > 1): ?>
                <div class="product-thumbs">
                    <?php foreach ($images as $img): ?>
                        <img src="<?= htmlspecialchars($img, ENT_QUOTES) ?>" alt="">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="product-placeholder" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <path d="M3.27 6.96 12 12.01l8.73-5.05"/>
                    <path d="M12 22.08V12"/>
                </svg>
                <span>Brak zdjęcia</span>
            </div>
        <?php endif; ?>
    </div>

    <div class="product-info">
        <span class="badge badge-cat"><?= htmlspecialchars($eq->categoryName ?? '', ENT_QUOTES) ?></span>
        <h2><?= htmlspecialchars($eq->name, ENT_QUOTES) ?></h2>
        <p class="product-desc">
            <?= $eq->description
                ? nl2br(htmlspecialchars($eq->description, ENT_QUOTES))
                : '<em>Brak opisu.</em>' ?>
        </p>

        <div class="price-box">
            <div class="price-wrap">
                <span class="price"><?= number_format($eq->dailyRate, 2) ?> zł</span>
                <span class="price-unit">/ dzień</span>
            </div>
            <span class="badge <?= $available ? 'badge-ok' : 'badge-no' ?>">
                <?= $available ? 'Dostępny' : 'Niedostępny' ?>
            </span>
        </div>

        <dl class="spec">
            <div class="spec-row">
                <dt>Kategoria</dt>
                <dd><?= htmlspecialchars($eq->categoryName ?? '-', ENT_QUOTES) ?></dd>
            </div>
            <div class="spec-row">
                <dt>Stawka dzienna</dt>
                <dd><?= number_format($eq->dailyRate, 2) ?> zł</dd>
            </div>
            <div class="spec-row">
                <dt>Dostępność</dt>
                <dd><?= $eq->availableQuantity ?> z <?= $eq->totalQuantity ?> szt.</dd>
            </div>
        </dl>

        <div class="product-actions">
            <?php if ($available && Session::isAuthenticated()): ?>
                <a href="/rentals/new?equipment_id=<?= (int) $eq->id ?>" class="btn">Wypożycz teraz</a>
            <?php elseif (!Session::isAuthenticated()): ?>
                <a href="/login" class="btn">Zaloguj się, aby wypożyczyć</a>
            <?php else: ?>
                <span class="badge badge-no">Sprzęt chwilowo niedostępny</span>
            <?php endif; ?>
        </div>
    </div>
</article>

<section class="info-steps">
    <h3>Jak wypożyczyć?</h3>
    <div class="steps">
        <div class="step">
            <span class="step-num">1</span>
            <div>
                <strong>Wybierz termin</strong>
                <p>Określ datę początku i końca wypożyczenia.</p>
            </div>
        </div>
        <div class="step">
            <span class="step-num">2</span>
            <div>
                <strong>Zarezerwuj online</strong>
                <p>Potwierdź rezerwację - system policzy koszt automatycznie.</p>
            </div>
        </div>
        <div class="step">
            <span class="step-num">3</span>
            <div>
                <strong>Odbierz sprzęt</strong>
                <p>Po zakończeniu po prostu go zwracasz w aplikacji.</p>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($related)): ?>
    <section class="related">
        <h3>Podobny sprzęt</h3>
        <div class="equipment-grid">
            <?php /** @var Equipment[] $related */ foreach ($related as $r): ?>
                <article class="equipment-card">
                    <h3><?= htmlspecialchars($r->name, ENT_QUOTES) ?></h3>
                    <p class="cat"><?= htmlspecialchars($r->categoryName ?? '', ENT_QUOTES) ?></p>
                    <p class="rate"><?= number_format($r->dailyRate, 2) ?> zł / dzień</p>
                    <p class="stock">Dostępne: <?= $r->availableQuantity ?> / <?= $r->totalQuantity ?></p>
                    <a href="/equipment/<?= (int) $r->id ?>" class="btn-sm">Szczegóły</a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = $eq->name;
require __DIR__ . '/../layout.php';
