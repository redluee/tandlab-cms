<?php
/** @var string $active */
$navItems = [
    'home' => ['label' => 'Home', 'href' => '/'],
    'tand' => ['label' => 'Tand', 'href' => '/tand'],
    'team' => ['label' => 'Team', 'href' => '/team'],
    'contact' => ['label' => 'Contact', 'href' => '/#contact'],
];
?>
<header class="site-nav">
    <div class="container">
        <a class="site-nav__logo" href="/">
            <img src="/uploads/logo.webp" alt="Tandlab logo">
        </a>
        <ul class="site-nav__links">
            <?php foreach ($navItems as $key => $item): ?>
                <li>
                    <a href="<?= e($item['href']) ?>" class="<?= $active === $key ? 'active' : '' ?>">
                        <?= e($item['label']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</header>
