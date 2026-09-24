<?php
/** @var array $settings */
?>
<footer class="site-footer">
    <div class="container site-footer__inner">
        <p>&copy; <?= date('Y') ?> Tandlab &bull; Alle rechten voorbehouden<?php if (!empty($settings['privacy_url'])): ?> &bull; <a href="<?= e($settings['privacy_url']) ?>" target="_blank" rel="noopener">Privacy Statement</a><?php endif; ?></p>
    </div>
</footer>
