<?php
/** @var string $content */
/** @var string $pageTitle */
/** @var string|null $metaDescription */
/** @var string $active */
/** @var array $settings */
$editing = \App\Services\EditMode::on();
$siteUrl = 'https://tandlab.nl';
$canonicalPath = match ($active ?? '') {
    'tand' => '/tand',
    'team' => '/team',
    default => '/',
};
$canonicalUrl = $siteUrl . $canonicalPath;
$ogImage = $siteUrl . '/uploads/' . ($settings['hero_slide_1'] ?? '');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'TANDLAB') ?></title>
    <?php if (!empty($metaDescription)): ?>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <?php endif; ?>
    <?php if (!$editing): ?>
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="TANDLAB">
    <meta property="og:title" content="<?= e($pageTitle ?? 'TANDLAB') ?>">
    <?php if (!empty($metaDescription)): ?>
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <?php endif; ?>
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <?php if (!empty($settings['hero_slide_1'])): ?>
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <?php endif; ?>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php if ($editing): ?>
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(\App\Services\Csrf::token()) ?>">
    <link rel="stylesheet" href="/assets/css/editor.css">
    <?php else: ?>
    <script type="application/ld+json"><?= json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => 'TANDLAB',
        'url' => $siteUrl,
        'telephone' => $settings['phone'] ?? '',
        'email' => $settings['email'] ?? '',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Zandweg 196A',
            'postalCode' => '3454 HE',
            'addressLocality' => 'De Meern',
            'addressCountry' => 'NL',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <?php endif; ?>
</head>
<body<?= $editing ? ' class="is-editing"' : '' ?>>
<?php if ($editing): ?>
<div class="editor-bar">
    <div class="editor-bar__pages">
        <a href="/admin/bewerken/home" class="<?= $active === 'home' ? 'active' : '' ?>">Home</a>
        <a href="/admin/bewerken/tand" class="<?= $active === 'tand' ? 'active' : '' ?>">Tand</a>
        <a href="/admin/bewerken/team" class="<?= $active === 'team' ? 'active' : '' ?>">Team</a>
    </div>
    <div class="editor-bar__status">
        <span id="editor-change-count">0 wijzigingen</span>
    </div>
    <div class="editor-bar__actions">
        <button type="button" id="editor-cancel" class="editor-btn editor-btn--secondary">Annuleren</button>
        <button type="button" id="editor-save" class="editor-btn">Opslaan</button>
        <a href="/admin" class="editor-btn editor-btn--link">Terug naar admin</a>
    </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/nav.php'; ?>
<main>
    <?= $content ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
<script src="/assets/js/hero-slider.js" defer></script>
<?php if ($editing): ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" integrity="sha256-ymhDBwPE9ZYOkHNYZ8bpTSm1o943EH2BAOWjAQB+nm4=" crossorigin="anonymous"></script>
<script src="/assets/js/media-picker.js" defer></script>
<script src="/assets/js/editor.js" defer></script>
<?php endif; ?>
</body>
</html>
