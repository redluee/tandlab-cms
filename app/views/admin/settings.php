<?php
/** @var array $settings */
/** @var array $images */
$images = $images ?? [];

ob_start();
$error = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_error']);

function sval(array $settings, string $key): string
{
    return e($settings[$key] ?? '');
}

$heroSlides = json_decode($settings['hero_slides'] ?? '[]', true);
if (!is_array($heroSlides)) {
    $heroSlides = [];
}
// Backwards compatibility with the old two-slide fields.
if (empty($heroSlides)) {
    foreach (['hero_slide_1', 'hero_slide_2'] as $legacyKey) {
        if (!empty($settings[$legacyKey])) {
            $heroSlides[] = $settings[$legacyKey];
        }
    }
}
?>
<div class="admin-header">
    <h1>Instellingen</h1>
</div>
<?php if ($error): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
    <h2 style="margin-top:0">Contactgegevens</h2>
    <form class="stacked" method="post" action="/admin/instellingen" enctype="multipart/form-data">
        <input type="hidden" id="csrf_token" name="csrf_token" value="<?= e(\App\Services\Csrf::token()) ?>">

        <div>
            <label for="address">Adres</label>
            <textarea id="address" name="address"><?= sval($settings, 'address') ?></textarea>
        </div>
        <div>
            <label for="phone">Telefoonnummer</label>
            <input type="text" id="phone" name="phone" value="<?= sval($settings, 'phone') ?>">
        </div>
        <div>
            <label for="email">E-mailadres</label>
            <input type="email" id="email" name="email" value="<?= sval($settings, 'email') ?>">
        </div>
        <div>
            <label for="email_recipients">Ontvangers contactformulier (komma-gescheiden)</label>
            <input type="text" id="email_recipients" name="email_recipients" value="<?= sval($settings, 'email_recipients') ?>">
        </div>
        <div>
            <label for="opening_hours">Openingstijden</label>
            <textarea id="opening_hours" name="opening_hours"><?= sval($settings, 'opening_hours') ?></textarea>
        </div>
        <div>
            <label for="map_embed_url">Google Maps embed-URL</label>
            <input type="text" id="map_embed_url" name="map_embed_url" value="<?= sval($settings, 'map_embed_url') ?>">
        </div>
        <div>
            <label for="privacy_url">Privacy statement URL</label>
            <input type="text" id="privacy_url" name="privacy_url" value="<?= sval($settings, 'privacy_url') ?>">
        </div>
        <div>
            <label for="scan_instructions">Scan-instructies (voor tandartsen)</label>
            <textarea id="scan_instructions" name="scan_instructions"><?= sval($settings, 'scan_instructions') ?></textarea>
        </div>

        <h2>Home-pagina</h2>
        <div>
            <label for="hero_title">Hero titel</label>
            <input type="text" id="hero_title" name="hero_title" value="<?= sval($settings, 'hero_title') ?>">
        </div>
        <div>
            <label for="hero_intro">Hero intro</label>
            <textarea id="hero_intro" name="hero_intro"><?= sval($settings, 'hero_intro') ?></textarea>
        </div>
        <div>
            <label for="usp_1">USP 1</label>
            <input type="text" id="usp_1" name="usp_1" value="<?= sval($settings, 'usp_1') ?>">
        </div>
        <div>
            <label for="usp_2">USP 2</label>
            <input type="text" id="usp_2" name="usp_2" value="<?= sval($settings, 'usp_2') ?>">
        </div>
        <div>
            <label for="usp_3">USP 3</label>
            <input type="text" id="usp_3" name="usp_3" value="<?= sval($settings, 'usp_3') ?>">
        </div>
        <div>
            <label>Hero-afbeeldingen (banner)</label>
            <input type="hidden" id="hero_slides" name="hero_slides" value="<?= e(json_encode($heroSlides)) ?>">
            <div id="heroSlidesGrid" class="hero-slides-grid"></div>
            <div style="margin-top:10px">
                <button type="button" class="btn" onclick="openLibraryPicker()">Kies uit mediabibliotheek</button>
            </div>
        </div>

        <div>
            <button type="submit" class="btn">Opslaan</button>
        </div>
    </form>
</div>

<div id="libraryPickerModal" class="media-edit-modal library-picker-modal" onclick="if (event.target === this) closeLibraryPicker()">
    <div class="media-edit-modal-content library-picker-content" onclick="event.stopPropagation()">
        <button type="button" class="library-picker-close" onclick="closeLibraryPicker()" aria-label="Sluiten">&times;</button>
        <h3>Kies uit mediabibliotheek</h3>
        <p class="library-picker-subtitle">Selecteer een of meer afbeeldingen voor de banner op de homepagina.</p>

        <div class="hero-library-upload">
            <label for="hero_library_upload_input" class="hero-library-upload-btn">
                <span class="hero-library-upload-icon">+</span>
                Afbeelding toevoegen aan bibliotheek
            </label>
            <input type="file" id="hero_library_upload_input" accept="image/*" style="display:none">
            <span id="hero_library_upload_status"></span>
        </div>

        <div class="hero-library-grid" id="heroLibraryGrid">
            <?php foreach ($images as $image): ?>
                <label class="hero-library-item">
                    <div class="hero-library-thumb">
                        <input type="checkbox" class="hero-library-checkbox" value="<?= e($image['filename']) ?>">
                        <span class="hero-library-check" aria-hidden="true"></span>
                        <img src="/uploads/<?= e($image['filename']) ?>" alt="">
                    </div>
                    <span class="hero-library-name"><?= e($image['display_name']) ?></span>
                </label>
            <?php endforeach; ?>
            <?php if (empty($images)): ?>
                <p id="heroLibraryEmpty">Nog geen afbeeldingen in de mediabibliotheek.</p>
            <?php endif; ?>
        </div>
        <div class="media-edit-modal-buttons">
            <button type="button" class="btn--secondary" onclick="closeLibraryPicker()">Annuleren</button>
            <button type="button" class="btn" onclick="confirmLibraryPicker()">Toevoegen</button>
        </div>
    </div>
</div>

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
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .media-edit-modal-content h3 {
        margin-top: 0;
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
        font: inherit;
    }

    .btn--secondary:hover {
        background-color: #5a6268;
    }

    .library-picker-modal {
        background-color: rgba(20, 24, 22, 0.55);
        backdrop-filter: blur(3px);
    }

    .library-picker-content {
        position: relative;
        max-width: 680px;
        padding: 28px 32px 24px;
        border-radius: 14px;
        box-shadow: 0 20px 50px rgba(16, 24, 20, 0.25), 0 2px 8px rgba(16, 24, 20, 0.08);
    }

    .library-picker-content h3 {
        font-size: 1.3rem;
        color: var(--admin-gray-900);
    }

    .library-picker-subtitle {
        margin: -6px 0 18px;
        color: var(--admin-gray-700);
        font-size: 0.9rem;
    }

    .library-picker-close {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 32px;
        height: 32px;
        border: none;
        border-radius: 50%;
        background: var(--admin-gray-100);
        color: var(--admin-gray-700);
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
        transition: background .15s ease, color .15s ease;
    }

    .library-picker-close:hover {
        background: var(--admin-gray-200);
        color: var(--admin-gray-900);
    }

    .hero-library-upload {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        padding-bottom: 18px;
        border-bottom: 1px solid var(--admin-gray-200);
    }

    .hero-library-upload-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--admin-gray-100);
        color: var(--admin-gray-900);
        border: 1px dashed var(--admin-gray-700);
        padding: 9px 18px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
        transition: background .15s ease, border-color .15s ease;
    }

    .hero-library-upload-btn:hover {
        background: rgba(120, 195, 156, 0.15);
        border-color: var(--admin-green-dark);
    }

    .hero-library-upload-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--admin-green);
        color: #16321f;
        font-weight: 700;
        line-height: 1;
    }

    #hero_library_upload_status {
        font-size: 12px;
        color: var(--admin-gray-700);
    }

    .hero-slides-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 8px;
    }

    .hero-slide-thumb {
        position: relative;
        width: 140px;
    }

    .hero-slide-thumb img {
        width: 100%;
        height: 90px;
        object-fit: cover;
        border-radius: 4px;
        display: block;
        background-color: #808080;
    }

    .hero-slide-thumb button {
        position: absolute;
        top: 4px;
        right: 4px;
        background: rgba(0, 0, 0, 0.6);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        line-height: 1;
        cursor: pointer;
    }

    .hero-library-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(128px, 1fr));
        gap: 14px;
        max-height: 52vh;
        overflow-y: auto;
        padding: 4px 4px 8px;
    }

    .hero-library-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        cursor: pointer;
    }

    .hero-library-thumb {
        position: relative;
        width: 100%;
        aspect-ratio: 1 / 1;
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid transparent;
        box-shadow: 0 1px 3px rgba(16, 24, 20, 0.1);
        background-color: #808080;
        transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }

    .hero-library-item:hover .hero-library-thumb {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(16, 24, 20, 0.16);
    }

    .hero-library-item.is-selected .hero-library-thumb {
        border-color: var(--admin-green-dark);
        box-shadow: 0 0 0 4px rgba(120, 195, 156, 0.3);
    }

    .hero-library-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .hero-library-checkbox {
        position: absolute;
        top: 8px;
        left: 8px;
        width: 22px;
        height: 22px;
        margin: 0;
        z-index: 2;
        opacity: 0;
        cursor: pointer;
    }

    .hero-library-check {
        position: absolute;
        top: 8px;
        left: 8px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.9);
        background: rgba(35, 39, 42, 0.35);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
        pointer-events: none;
        transition: background .15s ease, border-color .15s ease;
    }

    .hero-library-checkbox:checked + .hero-library-check {
        background: var(--admin-green-dark);
        border-color: var(--admin-green-dark);
    }

    .hero-library-checkbox:checked + .hero-library-check::after {
        content: '';
        position: absolute;
        left: 7px;
        top: 3px;
        width: 5px;
        height: 10px;
        border: solid #fff;
        border-width: 0 2px 2px 0;
        transform: rotate(40deg);
    }

    .hero-library-checkbox:focus-visible + .hero-library-check {
        outline: 2px solid var(--admin-green-dark);
        outline-offset: 2px;
    }

    .hero-library-name {
        width: 100%;
        text-align: center;
        color: var(--admin-gray-700);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<script>
    (function () {
        var slidesInput = document.getElementById('hero_slides');
        var grid = document.getElementById('heroSlidesGrid');
        var slides = JSON.parse(slidesInput.value || '[]');

        function render() {
            grid.innerHTML = '';
            slides.forEach(function (filename, index) {
                var wrap = document.createElement('div');
                wrap.className = 'hero-slide-thumb';

                var img = document.createElement('img');
                img.src = '/uploads/' + filename;
                wrap.appendChild(img);

                var removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.textContent = '×';
                removeBtn.setAttribute('aria-label', 'Verwijderen');
                removeBtn.onclick = function () {
                    slides.splice(index, 1);
                    sync();
                };
                wrap.appendChild(removeBtn);

                grid.appendChild(wrap);
            });
        }

        function sync() {
            slidesInput.value = JSON.stringify(slides);
            render();
        }

        function updateSelectedState(checkbox) {
            var item = checkbox.closest('.hero-library-item');
            if (item) {
                item.classList.toggle('is-selected', checkbox.checked);
            }
        }

        window.openLibraryPicker = function () {
            document.querySelectorAll('.hero-library-checkbox').forEach(function (cb) {
                cb.checked = slides.indexOf(cb.value) !== -1;
                updateSelectedState(cb);
            });
            document.getElementById('libraryPickerModal').classList.add('show');
        };

        window.closeLibraryPicker = function () {
            document.getElementById('libraryPickerModal').classList.remove('show');
        };

        var libraryGrid = document.getElementById('heroLibraryGrid');
        var uploadInput = document.getElementById('hero_library_upload_input');
        var uploadStatus = document.getElementById('hero_library_upload_status');

        libraryGrid.addEventListener('change', function (event) {
            if (event.target.classList.contains('hero-library-checkbox')) {
                updateSelectedState(event.target);
            }
        });

        function addLibraryItem(filename, displayName) {
            var emptyMsg = document.getElementById('heroLibraryEmpty');
            if (emptyMsg) {
                emptyMsg.remove();
            }

            var label = document.createElement('label');
            label.className = 'hero-library-item is-selected';

            var thumb = document.createElement('div');
            thumb.className = 'hero-library-thumb';

            var checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'hero-library-checkbox';
            checkbox.value = filename;
            checkbox.checked = true;
            thumb.appendChild(checkbox);

            var check = document.createElement('span');
            check.className = 'hero-library-check';
            check.setAttribute('aria-hidden', 'true');
            thumb.appendChild(check);

            var img = document.createElement('img');
            img.src = '/uploads/' + filename;
            thumb.appendChild(img);

            label.appendChild(thumb);

            var span = document.createElement('span');
            span.className = 'hero-library-name';
            span.textContent = displayName;
            label.appendChild(span);

            libraryGrid.appendChild(label);
        }

        uploadInput.addEventListener('change', function () {
            var file = uploadInput.files[0];
            if (!file) {
                return;
            }

            var formData = new FormData();
            formData.append('file', file);
            formData.append('csrf_token', document.getElementById('csrf_token').value);

            uploadStatus.textContent = 'Uploaden...';

            fetch('/admin/afbeeldingen/upload', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.ok) {
                        addLibraryItem(data.filename, data.display_name);
                        if (slides.indexOf(data.filename) === -1) {
                            slides.push(data.filename);
                        }
                        sync();
                        uploadStatus.textContent = 'Toegevoegd.';
                    } else {
                        uploadStatus.textContent = data.error || 'Uploaden mislukt.';
                    }
                })
                .catch(function () {
                    uploadStatus.textContent = 'Uploaden mislukt.';
                })
                .finally(function () {
                    uploadInput.value = '';
                });
        });

        window.confirmLibraryPicker = function () {
            document.querySelectorAll('.hero-library-checkbox:checked').forEach(function (cb) {
                if (slides.indexOf(cb.value) === -1) {
                    slides.push(cb.value);
                }
            });
            closeLibraryPicker();
            sync();
        };

        render();
    })();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/admin-shell.php';
