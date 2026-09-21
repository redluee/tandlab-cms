<?php
/** @var array $settings */
/** @var array $members */

$pageTitle = 'Team - TANDLAB Brug- en Kroonwerk';
$active = 'team';

ob_start();
?>
<section class="section">
    <div class="container">
        <div class="section__header">
            <h2>Team</h2>
            <p>Wij zijn een erkend leerbedrijf. In het laboratorium werkt vakkundig personeel dat allemaal als leerling bij ons is begonnen. In ons hechte team werken we samen aan het beste resultaat voor uw gebit.</p>
        </div>
        <div class="team-grid">
            <?php foreach ($members as $member): ?>
                <div class="team-card">
                    <img src="/uploads/<?= e($member['photo_path'] ?? '') ?>" alt="<?= e($member['name']) ?>">
                    <h3><?= e($member['name']) ?></h3>
                    <?php if (!empty($member['role'])): ?>
                        <p class="role"><?= e($member['role']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($member['bio'])): ?>
                        <p class="bio"><?= e($member['bio']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/contact-section.php'; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/page.php';
