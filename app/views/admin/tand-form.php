<?php
/** @var array|null $item */

ob_start();
?>
<div class="admin-header">
    <h1><?= $item ? 'Werkstuk bewerken' : 'Nieuw werkstuk' ?></h1>
</div>
<div class="card">
    <form class="stacked" method="post" action="/admin/tand/opslaan" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(\App\Services\Csrf::token()) ?>">
        <?php if ($item): ?>
            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
        <?php endif; ?>

        <?php if ($item && !empty($item['image_path'])): ?>
            <img class="thumb-preview" src="/uploads/<?= e($item['image_path']) ?>" alt="">
        <?php endif; ?>

        <div>
            <label for="title">Titel</label>
            <input type="text" id="title" name="title" value="<?= e($item['title'] ?? '') ?>" required>
        </div>
        <div>
            <label for="body">Omschrijving</label>
            <textarea id="body" name="body" required><?= e($item['body'] ?? '') ?></textarea>
        </div>
        <div>
            <label for="alt">Alt-tekst (afbeelding)</label>
            <input type="text" id="alt" name="alt" value="<?= e($item['alt'] ?? '') ?>">
        </div>
        <div>
            <label for="image">Foto <?= $item ? '(laat leeg om te behouden)' : '' ?></label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        <div>
            <label for="sort_order">Volgorde</label>
            <input type="number" id="sort_order" name="sort_order" value="<?= (int) ($item['sort_order'] ?? 0) ?>">
        </div>
        <div>
            <label>
                <input type="checkbox" name="active" value="1" style="width:auto" <?= (!$item || $item['active']) ? 'checked' : '' ?>>
                Actief (zichtbaar op de website)
            </label>
        </div>
        <div>
            <button type="submit" class="btn">Opslaan</button>
            <a class="btn btn--secondary" href="/admin/tand">Annuleren</a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/admin-shell.php';
