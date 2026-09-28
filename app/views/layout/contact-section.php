<?php
/** @var array $settings */
$defaultMapUrl = 'https://www.google.com/maps?q=Zandweg+196A,+3454+HE+De+Meern&output=embed';
$mapUrl = \App\Services\Validator::googleMapsEmbedUrl($settings['map_embed_url'] ?? '') ? $settings['map_embed_url'] : $defaultMapUrl;
?>
<section id="contact" class="section contact-section">
    <div class="container">
        <div class="section__header contact-title-wrap">
            <h2 class="contact-title">
                <span <?= edit('setting:contact_title', 'text') ?>><?= rich($settings['contact_title'] ?? 'Contact', 'text') ?></span>
            </h2>
        </div>
        <div class="contact-grid">
            <div class="contact-tile contact-tile--info">
                <dl class="contact-info">
                    <dt>Adres</dt>
                    <dd><span <?= edit('setting:address', 'richtext') ?>><?= rich($settings['address'] ?? "Tandlab\nZandweg 196A\n3454 HE De Meern", 'richtext') ?></span></dd>
                    <dt>Telefoonnummer</dt>
                    <dd><a href="tel:<?= e(preg_replace('/\s+/', '', strip_tags($settings['phone'] ?? ''))) ?>" <?= edit('setting:phone', 'text') ?>><?= rich($settings['phone'] ?? '030-2441135', 'text') ?></a></dd>
                    <dt>E-mailadres</dt>
                    <dd><a href="mailto:<?= e($settings['email'] ?? 'info@tandlab.nl') ?>" <?= edit('setting:email', 'text') ?>><?= rich($settings['email'] ?? 'info@tandlab.nl', 'text') ?></a></dd>
                    <dt>Openingstijden</dt>
                    <dd><span <?= edit('setting:opening_hours', 'richtext') ?>><?= rich($settings['opening_hours'] ?? "MA t/m DO: 8.00 – 12.30. 13.00 - 16.45 uur.\nVR: 8.00 t/m 13.00 uur", 'richtext') ?></span></dd>
                </dl>
            </div>
            <div class="contact-tile contact-map">
                <div class="map-embed">
                    <div class="map-skeleton" aria-hidden="true">
                        <span class="map-skeleton__card"></span>
                        <span class="map-skeleton__circle"></span>
                    </div>
                    <iframe src="<?= e($mapUrl) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Locatie Tandlab" onload="this.classList.add('is-loaded')"></iframe>
                </div>
                <p class="map-note" <?= edit('setting:map_note', 'text') ?>><?= rich($settings['map_note'] ?? 'De zandweg is eenrichtingsverkeer richting het westen', 'text') ?></p>
            </div>
        </div>
    </div>
</section>
