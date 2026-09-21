<?php
/** @var array $settings */
$mapUrl = $settings['map_embed_url'] ?? 'https://www.google.com/maps?q=Zandweg+196A+3454+HE+De+Meern&output=embed';
?>
<section id="contact" class="section contact-section">
    <div class="container">
        <div class="contact-grid">
            <div>
                <div class="section__header" style="text-align:left">
                    <h2>Contact</h2>
                </div>
                <dl class="contact-info">
                    <dt>Adres</dt>
                    <dd>
                        <?= nl2br(e($settings['address'] ?? "Tandlab\nZandweg 196A\n3454 HE De Meern")) ?>
                        <?php if (!empty($settings['privacy_url'])): ?>
                            <br><a href="<?= e($settings['privacy_url']) ?>" target="_blank" rel="noopener">Privacy Statement</a>
                        <?php endif; ?>
                    </dd>
                    <dt>Telefoonnummer</dt>
                    <dd><a href="tel:<?= e(preg_replace('/\s+/', '', $settings['phone'] ?? '')) ?>"><?= e($settings['phone'] ?? '030-2441135') ?></a></dd>
                    <dt>E-mailadres</dt>
                    <dd><a href="mailto:<?= e($settings['email'] ?? 'info@tandlab.nl') ?>"><?= e($settings['email'] ?? 'info@tandlab.nl') ?></a></dd>
                    <dt>Openingstijden</dt>
                    <dd><?= nl2br(e($settings['opening_hours'] ?? "MA t/m DO: 8.00 – 12.30. 13.00 - 16.45 uur.\nVR: 8.00 t/m 13.00 uur")) ?></dd>
                </dl>
            </div>
            <div class="contact-map">
                <iframe src="<?= e($mapUrl) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Locatie Tandlab"></iframe>
                <p class="map-note">De zandweg is eenrichtingsverkeer richting het westen</p>
            </div>
        </div>
    </div>
</section>
