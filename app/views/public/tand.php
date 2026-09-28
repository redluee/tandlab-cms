<?php
/** @var array $settings */
/** @var array $items */

$pageTitle = 'Kroon- en Brugwerk De Meern | TANDLAB';
$metaDescription = 'Bekijk voorbeelden van ons kroon- en brugwerk: vakkundig tandtechnisch werk uit ons laboratorium in De Meern, gemaakt met oog voor detail en precisie.';
$active = 'tand';
$editing = \App\Services\EditMode::on();

ob_start();
?>
<section class="page-hero page-hero--tand">
    <div class="container">
        <h1 <?= edit('setting:tand_title', 'text') ?>><?= rich($settings['tand_title'] ?? 'Tand', 'text') ?></h1>
        <p <?= edit('setting:tand_intro', 'richtext') ?>><?= rich($settings['tand_intro'] ?? 'Een overzicht van ons kroon- en brugwerk: vakkundig tandtechnisch werk, gemaakt met oog voor detail en precisie.', 'richtext') ?></p>
    </div>
</section>
<div class="tand-grid" id="tand-grid"<?= $editing ? ' data-edit-list="tandwerk"' : '' ?>>
    <?php foreach ($items as $index => $item): ?>
        <?php
        $isImageFirst = $index % 2 === 0;
        $ref = 'tandwerk:' . (int) $item['id'];
        ?>
        <div class="tand-bar<?= $editing && empty($item['active']) ? ' is-inactive' : '' ?>"<?= $editing ? ' data-edit-item="' . e($ref) . '" data-id="' . (int) $item['id'] . '"' : '' ?>>
            <div class="tand-bar__shape" aria-hidden="true"></div>
            <div class="container tand-bar__row">
                <?php if ($isImageFirst): ?>
                    <div class="tand-grid__cell tand-grid__cell--image">
                        <img src="/uploads/<?= e($item['image_path'] ?? '') ?>" alt="<?= e(strip_tags($item['alt'] ?? $item['title'])) ?>" loading="lazy" <?= edit($ref . ':image_path', 'image') ?><?= $editing ? ' data-edit-alt="' . e($ref . ':alt') . '"' : '' ?>>
                    </div>
                    <div class="tand-grid__cell tand-grid__cell--text">
                        <div>
                            <h3 <?= edit($ref . ':title', 'text') ?>><?= rich($item['title'], 'text') ?></h3>
                            <p <?= edit($ref . ':body', 'richtext') ?>><?= rich($item['body'], 'richtext') ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="tand-grid__cell tand-grid__cell--text">
                        <div>
                            <h3 <?= edit($ref . ':title', 'text') ?>><?= rich($item['title'], 'text') ?></h3>
                            <p <?= edit($ref . ':body', 'richtext') ?>><?= rich($item['body'], 'richtext') ?></p>
                        </div>
                    </div>
                    <div class="tand-grid__cell tand-grid__cell--image">
                        <img src="/uploads/<?= e($item['image_path'] ?? '') ?>" alt="<?= e(strip_tags($item['alt'] ?? $item['title'])) ?>" loading="lazy" <?= edit($ref . ':image_path', 'image') ?><?= $editing ? ' data-edit-alt="' . e($ref . ':alt') . '"' : '' ?>>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php if ($editing): ?>
<div class="container">
    <button type="button" class="editor-add-item" data-add-item="tandwerk" data-template="tand-item-template">+ Nieuw item</button>
</div>
<template id="tand-item-template">
    <div class="tand-bar" data-edit-item="tandwerk:__ID__" data-id="__ID__">
        <div class="tand-bar__shape" aria-hidden="true"></div>
        <div class="container tand-bar__row">
            <div class="tand-grid__cell tand-grid__cell--image">
                <img src="" alt="" loading="lazy" data-edit-type="image" data-edit="tandwerk:__ID__:image_path" data-edit-alt="tandwerk:__ID__:alt">
            </div>
            <div class="tand-grid__cell tand-grid__cell--text">
                <div>
                    <h3 data-edit-type="text" data-edit="tandwerk:__ID__:title">Nieuw werkstuk</h3>
                    <p data-edit-type="richtext" data-edit="tandwerk:__ID__:body">Beschrijving...</p>
                </div>
            </div>
        </div>
    </div>
</template>
<?php endif; ?>

<?php require __DIR__ . '/../layout/contact-section.php'; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/page.php';
