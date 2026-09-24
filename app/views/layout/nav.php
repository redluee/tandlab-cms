<?php
/** @var string $active */
$editing = \App\Services\EditMode::on();
$navItems = [
    'home' => ['label' => 'Home', 'href' => $editing ? '/admin/bewerken/home' : '/'],
    'tand' => ['label' => 'Tand', 'href' => $editing ? '/admin/bewerken/tand' : '/tand'],
    'team' => ['label' => 'Team', 'href' => $editing ? '/admin/bewerken/team' : '/team'],
    'contact' => ['label' => 'Contact', 'href' => '#contact'],
];
?>
<header class="site-nav">
    <div class="container">
        <a class="site-nav__logo" href="/">
            <img src="/uploads/logo.webp" alt="Tandlab logo">
        </a>
        <ul class="site-nav__links">
            <span class="site-nav__indicator" aria-hidden="true"></span>
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
<script src="/assets/js/nav-indicator.js" defer></script>
