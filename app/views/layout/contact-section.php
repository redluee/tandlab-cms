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
                    <dd class="contact-info__channel">
                        <a href="tel:<?= e(preg_replace('/\s+/', '', strip_tags($settings['phone'] ?? ''))) ?>">
                            <svg class="contact-info__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.96.36 1.9.7 2.79a2 2 0 0 1-.45 2.11L8.09 9.89a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.83.58 2.79.7A2 2 0 0 1 22 16.92Z"/>
                            </svg>
                            <span <?= edit('setting:phone', 'text') ?>><?= rich($settings['phone'] ?? '030-2441135', 'text') ?></span>
                        </a>
                    </dd>
                    <dt>E-mailadres</dt>
                    <dd class="contact-info__channel">
                        <a href="mailto:<?= e($settings['email'] ?? 'info@tandlab.nl') ?>">
                            <svg class="contact-info__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <rect x="3" y="5" width="18" height="14" rx="2"/>
                                <path d="m3 7 9 6 9-6"/>
                            </svg>
                            <span <?= edit('setting:email', 'text') ?>><?= rich($settings['email'] ?? 'info@tandlab.nl', 'text') ?></span>
                        </a>
                    </dd>
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
