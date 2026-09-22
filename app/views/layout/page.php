<?php
/** @var string $content */
/** @var string $pageTitle */
/** @var string $active */
/** @var array $settings */
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'TANDLAB') ?></title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php require __DIR__ . '/nav.php'; ?>
<main>
    <?= $content ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
<script src="/assets/js/hero-slider.js" defer></script>
</body>
</html>
