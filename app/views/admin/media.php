<?php
/** @var array $images */
/** @var array $usage */
$usage = $usage ?? [];

ob_start();
$error = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_error']);
?>
<style>
    .media-edit-modal {
        display: none;
        position: fixed;
        z-index: 100;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .media-edit-modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .media-edit-modal-content {
        background-color: #fefefe;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 400px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .media-edit-modal-content h3 {
        margin-top: 0;
    }

    .media-edit-modal-content input {
        width: 100%;
        padding: 8px;
        margin: 8px 0;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .media-edit-modal-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        margin-top: 16px;
    }

    .media-edit-modal-buttons button {
        padding: 8px 16px;
    }

    .btn--secondary {
        background-color: #6c757d;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn--secondary:hover {
        background-color: #5a6268;
    }

    .media-lightbox-content {
        background: transparent;
        padding: 0;
        box-shadow: none;
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }

    .media-lightbox-content img {
        max-width: 90vw;
        max-height: 80vh;
        border-radius: 4px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        background-color: #808080;
    }

    .media-lightbox-content figcaption {
        color: #fff;
        text-align: center;
    }

    .media-lightbox-close {
        align-self: flex-end;
        background: transparent;
        border: none;
        color: #fff;
        font-size: 28px;
        line-height: 1;
        cursor: pointer;
        padding: 4px;
    }

    .media-lightbox-close:hover {
        color: #ccc;
    }

    .media-grid img {
        cursor: pointer;
    }
</style>

<div class="admin-header">
    <h1>Afbeeldingen</h1>
</div>
<?php if ($error): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>
<div class="card">
    <h2 style="margin-top:0">Uploaden</h2>
    <form method="post" action="/admin/afbeeldingen/upload" enctype="multipart/form-data" style="display:flex; gap:12px; align-items:center">
        <input type="hidden" name="csrf_token" value="<?= e(\App\Services\Csrf::token()) ?>">
        <input type="file" name="file" accept="image/*" required>
        <button type="submit" class="btn">Uploaden &amp; optimaliseren</button>
    </form>
</div>
<div class="card">
    <h2 style="margin-top:0">Mediabibliotheek</h2>
    <div class="media-grid">
        <?php foreach ($images as $image): ?>
            <figure>
                <figcaption><?= e($image['display_name']) ?></figcaption>
                <img src="/uploads/<?= e($image['filename']) ?>" alt="" onclick="openLightbox('/uploads/<?= e($image['filename']) ?>', <?= e(json_encode($image['display_name'])) ?>)">
                <div style="display:flex; flex-direction:column; gap:6px; margin-top:6px">
                    <button type="button" class="btn" style="width:100%" onclick="openEditModal(<?= e(json_encode($image)) ?>)">Hernoemen</button>
                    <form method="post" action="/admin/afbeeldingen/verwijderen" onsubmit="return confirmDelete(event, <?= e(json_encode($usage[$image['filename']] ?? [])) ?>)">
                        <input type="hidden" name="csrf_token" value="<?= e(\App\Services\Csrf::token()) ?>">
                        <input type="hidden" name="filename" value="<?= e($image['filename']) ?>">
                        <button type="submit" class="btn btn--danger" style="width:100%">Verwijderen</button>
                    </form>
                </div>
            </figure>
        <?php endforeach; ?>
        <?php if (empty($images)): ?>
            <p>Nog geen afbeeldingen geüpload.</p>
        <?php endif; ?>
    </div>
</div>

<div id="editModal" class="media-edit-modal">
    <div class="media-edit-modal-content">
        <h3>Afbeelding hernoemen</h3>
        <form method="post" action="/admin/afbeeldingen/hernoemen">
            <input type="hidden" name="csrf_token" value="<?= e(\App\Services\Csrf::token()) ?>">
            <input type="hidden" name="id" id="modalImageId" value="">
            <label for="modalDisplayName">Naam:</label>
            <input type="text" id="modalDisplayName" name="display_name" required>
            <div class="media-edit-modal-buttons">
                <button type="button" class="btn--secondary" onclick="closeEditModal()">Annuleren</button>
                <button type="submit" class="btn">Opslaan</button>
            </div>
        </form>
    </div>
</div>

<div id="imageLightbox" class="media-edit-modal" onclick="if (event.target === this) closeLightbox()">
    <figure class="media-lightbox-content" onclick="event.stopPropagation()">
        <button type="button" class="media-lightbox-close" onclick="closeLightbox()" aria-label="Sluiten">&times;</button>
        <img id="lightboxImage" src="" alt="">
        <figcaption id="lightboxCaption"></figcaption>
    </figure>
</div>

<script>
    function confirmDelete(event, usedByTitles) {
        const form = event.target;
        if (form.dataset.confirmed) {
            return true;
        }
        const used = usedByTitles && usedByTitles.length > 0;
        TandlabDialog.confirm({
            title: 'Afbeelding verwijderen',
            message: used ? 'Deze afbeelding wordt gebruikt op de volgende pagina\'s:' : 'Weet je zeker dat je deze afbeelding wilt verwijderen?',
            list: used ? usedByTitles : null,
            after: used ? 'Als je verwijdert, verdwijnt de afbeelding ook daar. Weet je het zeker?' : null,
            okLabel: 'Verwijderen',
            danger: true
        }).then(function (ok) {
            if (ok) {
                form.dataset.confirmed = '1';
                form.requestSubmit();
            }
        });
        return false;
    }

    function openEditModal(image) {
        document.getElementById('modalImageId').value = image.id;
        document.getElementById('modalDisplayName').value = image.display_name;
        document.getElementById('editModal').classList.add('show');
        document.getElementById('modalDisplayName').focus();
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('show');
    }

    function openLightbox(src, caption) {
        document.getElementById('lightboxImage').src = src;
        document.getElementById('lightboxCaption').textContent = caption;
        document.getElementById('imageLightbox').classList.add('show');
    }

    function closeLightbox() {
        document.getElementById('imageLightbox').classList.remove('show');
        document.getElementById('lightboxImage').src = '';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('editModal');
        if (event.target === modal) {
            closeEditModal();
        }
    };

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeLightbox();
        }
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/admin-shell.php';
