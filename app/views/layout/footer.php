<?php
/** @var array $settings */
?>
<footer class="site-footer">
    <div class="container site-footer__inner">
        <p>&copy; <?= date('Y') ?> Tandlab &bull; Alle rechten voorbehouden &bull; <a href="<?= e(\App\Services\PrivacyStatement::PUBLIC_URL) ?>?v=<?= (int) @filemtime(\App\Services\PrivacyStatement::currentPath()) ?>" target="_blank" rel="noopener">Privacy Statement</a></p>
    </div>
</footer>
