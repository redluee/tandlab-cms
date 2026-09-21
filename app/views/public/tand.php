<?php
/** @var array $settings */
/** @var array $items */

$pageTitle = 'Tand - TANDLAB Brug- en Kroonwerk';
$active = 'tand';

ob_start();
?>
<section class="section">
    <div class="container">
        <div class="section__header">
            <h2>Tand</h2>
        </div>
        <div class="tand-grid">
            <?php foreach ($items as $index => $item): ?>
                <?php $isImageFirst = $index % 2 === 0; ?>
                <?php if ($isImageFirst): ?>
                    <div class="tand-grid__cell tand-grid__cell--image" style="background-image:url('/uploads/<?= e($item['image_path'] ?? '') ?>')" role="img" aria-label="<?= e($item['alt'] ?? $item['title']) ?>"></div>
                    <div class="tand-grid__cell tand-grid__cell--text">
                        <div>
                            <h3><?= e($item['title']) ?></h3>
                            <p><?= nl2br(e($item['body'])) ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="tand-grid__cell tand-grid__cell--text">
                        <div>
                            <h3><?= e($item['title']) ?></h3>
                            <p><?= nl2br(e($item['body'])) ?></p>
                        </div>
                    </div>
                    <div class="tand-grid__cell tand-grid__cell--image" style="background-image:url('/uploads/<?= e($item['image_path'] ?? '') ?>')" role="img" aria-label="<?= e($item['alt'] ?? $item['title']) ?>"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/contact-section.php'; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/page.php';
