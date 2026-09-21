<?php
/** @var array $settings */

$pageTitle = 'TANDLAB Kroon- en brugwerk';
$active = 'home';

$heroSlides = json_decode($settings['hero_slides'] ?? '[]', true);
if (!is_array($heroSlides) || empty($heroSlides)) {
    $heroSlides = array_filter([$settings['hero_slide_1'] ?? '', $settings['hero_slide_2'] ?? '']);
}

ob_start();
?>
<section class="hero">
    <?php foreach (array_values($heroSlides) as $index => $slide): ?>
        <div class="hero__slide<?= $index === 0 ? ' is-active' : '' ?>" style="background-image:url('/uploads/<?= e($slide) ?>')"></div>
    <?php endforeach; ?>
    <div class="hero__overlay"></div>
    <div class="hero__deco hero__deco--left" aria-hidden="true"></div>
    <div class="hero__deco hero__deco--right" aria-hidden="true"></div>
    <div class="hero__content">
        <h1 class="hero__title">
            <?= e($settings['hero_title'] ?? 'UW SPECIALIST IN KROON- EN BRUGWERK') ?>
        </h1>
        <p class="hero__intro"><?= e($settings['hero_intro'] ?? 'Welkom bij TANDLAB. Sinds 1985 is ons laboratorium gespecialiseerd in kroon- en brugwerk.') ?></p>
        <a class="btn" href="#contact">Ik wil contact</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__header">
            <h2>Welkom bij TANDLAB</h2>
            <p>We zijn een erkend leerbedrijf en werken als een hecht team aan het beste resultaat voor uw gebit.</p>
        </div>
        <div class="usp-grid usp-grid--shapes">
            <div class="usp-card usp-card--shape">
                <div class="usp-shape usp-shape--diamond">
                    <div class="usp-shape__inner">
                        <span class="usp-shape__badge">1985</span>
                        <h3 class="usp-shape__title"><?= e($settings['usp_1'] ?? '40+ jaar ervaring') ?></h3>
                        <p class="usp-shape__desc">Sinds 1985 specialist in kroon- en brugwerk.</p>
                    </div>
                </div>
            </div>
            <div class="usp-card usp-card--shape">
                <div class="usp-shape usp-shape--circle">
                    <div class="usp-shape__inner">
                        <span class="usp-shape__badge">Team</span>
                        <h3 class="usp-shape__title"><?= e($settings['usp_2'] ?? 'Lokale samenwerking') ?></h3>
                        <p class="usp-shape__desc">Korte lijntjes en persoonlijk overleg met onze opdrachtgevers.</p>
                    </div>
                </div>
            </div>
            <div class="usp-card usp-card--shape">
                <div class="usp-shape usp-shape--diamond">
                    <div class="usp-shape__inner">
                        <span class="usp-shape__badge">Tech</span>
                        <h3 class="usp-shape__title"><?= e($settings['usp_3'] ?? 'Moderne scan/3D-techniek') ?></h3>
                        <p class="usp-shape__desc">CBCT-scan, software en teamviewer voor optimaal resultaat.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/contact-section.php'; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/page.php';
