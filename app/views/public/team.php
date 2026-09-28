<?php
/** @var array $settings */
/** @var array $members */

$pageTitle = 'Ons Team | Tandtechnisch Laboratorium TANDLAB';
$metaDescription = 'Maak kennis met het team van TANDLAB in De Meern: ervaren tandtechnici, stap voor stap opgeleid tot vakspecialist in kroon- en brugwerk.';
$active = 'team';
$editing = \App\Services\EditMode::on();

ob_start();
?>
<section class="page-hero">
    <div class="container">
        <h1 <?= edit('setting:team_title', 'text') ?>><?= rich($settings['team_title'] ?? 'Ons team', 'text') ?></h1>
        <p <?= edit('setting:team_intro', 'richtext') ?>><?= rich($settings['team_intro'] ?? 'Wij zijn een erkend leerbedrijf. In het laboratorium werkt vakkundig personeel dat allemaal als leerling bij ons is begonnen. In ons hechte team werken we samen aan het beste resultaat voor uw gebit.', 'richtext') ?></p>
    </div>
</section>
<section class="section team-section">
    <div class="container">
        <div class="team-grid" id="team-grid"<?= $editing ? ' data-edit-list="team"' : '' ?>>
            <?php foreach ($members as $member): ?>
                <?php $ref = 'team:' . (int) $member['id']; ?>
                <div class="team-card<?= $editing && empty($member['active']) ? ' is-inactive' : '' ?>"<?= $editing ? ' data-edit-item="' . e($ref) . '" data-id="' . (int) $member['id'] . '"' : '' ?>>
                    <img src="/uploads/<?= e($member['photo_path'] ?? '') ?>" alt="<?= e(strip_tags($member['name'])) ?>" <?= edit($ref . ':photo_path', 'image') ?>>
                    <h3 <?= edit($ref . ':name', 'text') ?>><?= rich($member['name'], 'text') ?></h3>
                    <?php if ($editing || !empty($member['role'])): ?>
                        <p class="role" <?= edit($ref . ':role', 'text') ?>><?= rich($member['role'] ?? '', 'text') ?></p>
                    <?php endif; ?>
                    <?php if ($editing || !empty($member['bio'])): ?>
                        <p class="bio" <?= edit($ref . ':bio', 'richtext') ?>><?= rich($member['bio'] ?? '', 'richtext') ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($editing): ?>
        <button type="button" class="editor-add-item" data-add-item="team" data-template="team-item-template">+ Nieuw teamlid</button>
        <template id="team-item-template">
            <div class="team-card" data-edit-item="team:__ID__" data-id="__ID__">
                <img src="" alt="" data-edit-type="image" data-edit="team:__ID__:photo_path">
                <h3 data-edit-type="text" data-edit="team:__ID__:name">Nieuw teamlid</h3>
                <p class="role" data-edit-type="text" data-edit="team:__ID__:role">Functie</p>
                <p class="bio" data-edit-type="richtext" data-edit="team:__ID__:bio">Korte bio...</p>
            </div>
        </template>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../layout/contact-section.php'; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/page.php';
