<?php
/** @var string $content */
/** @var string $pageTitle */
/** @var string $active */

$navItems = [
    'dashboard' => ['label' => 'Home', 'href' => '/admin'],
    'tand' => ['label' => 'Tand', 'href' => '/admin/tand'],
    'team' => ['label' => 'Team', 'href' => '/admin/team'],
    'instellingen' => ['label' => 'Instellingen', 'href' => '/admin/instellingen'],
    'afbeeldingen' => ['label' => 'Afbeeldingen', 'href' => '/admin/afbeeldingen'],
];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'TANDLAB CMS') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-sidebar__brand">TANDLAB CMS</div>
        <nav>
            <?php foreach ($navItems as $key => $item): ?>
                <a href="<?= e($item['href']) ?>" class="<?= $active === $key ? 'active' : '' ?>">
                    <?= e($item['label']) ?>
                </a>
            <?php endforeach; ?>
            <a href="/admin/logout">Uitloggen</a>
        </nav>
    </aside>
    <main class="admin-main">
        <?= $content ?>
    </main>
</div>
</body>
</html>
