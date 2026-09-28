<?php
/** @var array $settings */

$pageTitle = 'Tandtechnisch Laboratorium De Meern | TANDLAB';
$metaDescription = 'TANDLAB is een tandtechnisch laboratorium in De Meern, gespecialiseerd in kroon- en brugwerk sinds 1985. Vakwerk voor tandartsen en patiënten.';
$active = 'home';

$heroSlides = json_decode($settings['hero_slides'] ?? '[]', true);
if (!is_array($heroSlides) || empty($heroSlides)) {
    $heroSlides = array_filter([$settings['hero_slide_1'] ?? '', $settings['hero_slide_2'] ?? '']);
}
$heroSlides = array_values($heroSlides);
$heroInterval = (int) ($settings['hero_interval'] ?? 6);
if ($heroInterval < 2 || $heroInterval > 30) {
    $heroInterval = 6;
}

ob_start();
?>
<section class="hero" data-interval="<?= e((string) ($heroInterval * 1000)) ?>" <?= edit('setting:hero_slides', 'slides') ?>>
    <?php foreach ($heroSlides as $index => $slide): ?>
        <div class="hero__slide<?= $index === 0 ? ' is-active' : '' ?>" style="background-image:url('/uploads/<?= e($slide) ?>')"></div>
    <?php endforeach; ?>
    <div class="hero__overlay"></div>
    <div class="hero__shapes" aria-hidden="true">
        <span class="hero__shape hero__shape--hourglass"></span>
        <span class="hero__shape-col">
            <span class="hero__shape hero__shape--diamond"></span>
            <span class="hero__shape hero__shape--circle"></span>
        </span>
    </div>
    <div class="hero__content">
        <p class="hero__kicker" <?= edit('setting:hero_kicker', 'text') ?>><?= rich($settings['hero_kicker'] ?? 'Tandlab', 'text') ?></p>
        <h1 class="hero__title" <?= edit('setting:hero_title', 'text') ?>>
            <?= rich($settings['hero_title'] ?? 'UW SPECIALIST IN KROON- EN BRUGWERK', 'text') ?>
        </h1>
        <p class="hero__intro" <?= edit('setting:hero_intro', 'richtext') ?>><?= rich($settings['hero_intro'] ?? 'Sinds 1985 vervaardigen wij hoogwaardig kroon- en brugwerk. Als erkend leerbedrijf combineert ons vaste team jarenlange ervaring met actuele technieken om passende werkstukken voor uw praktijk of gebit te leveren.', 'richtext') ?></p>
        <a class="btn" href="#contact">Ik wil contact</a>
    </div>
</section>

<?php require __DIR__ . '/../layout/contact-section.php'; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/page.php';
