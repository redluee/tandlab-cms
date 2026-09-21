<?php
/** @var array|null $member */

ob_start();
?>
<div class="admin-header">
    <h1><?= $member ? 'Teamlid bewerken' : 'Nieuw teamlid' ?></h1>
</div>
<div class="card">
    <form class="stacked" method="post" action="/admin/team/opslaan" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(\App\Services\Csrf::token()) ?>">
        <?php if ($member): ?>
            <input type="hidden" name="id" value="<?= (int) $member['id'] ?>">
        <?php endif; ?>

        <?php if ($member && !empty($member['photo_path'])): ?>
            <img class="thumb-preview" src="/uploads/<?= e($member['photo_path']) ?>" alt="">
        <?php endif; ?>

        <div>
            <label for="name">Naam</label>
            <input type="text" id="name" name="name" value="<?= e($member['name'] ?? '') ?>" required>
        </div>
        <div>
            <label for="role">Functie</label>
            <input type="text" id="role" name="role" value="<?= e($member['role'] ?? '') ?>">
        </div>
        <div>
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio"><?= e($member['bio'] ?? '') ?></textarea>
        </div>
        <div>
            <label for="photo">Foto <?= $member ? '(laat leeg om te behouden)' : '' ?></label>
            <input type="file" id="photo" name="photo" accept="image/*">
        </div>
        <div>
            <label>
                <input type="checkbox" name="active" value="1" style="width:auto" <?= (!$member || $member['active']) ? 'checked' : '' ?>>
                Actief (zichtbaar op de website)
            </label>
        </div>
        <?php if (!$member): ?>
            <p class="admin-hint">Nieuwe teamleden worden onderaan toegevoegd. Volgorde wijzigen kan met slepen op het teamoverzicht.</p>
        <?php endif; ?>
        <div>
            <button type="submit" class="btn">Opslaan</button>
            <a class="btn btn--secondary" href="/admin/team">Annuleren</a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/admin-shell.php';
