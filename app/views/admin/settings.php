<?php
/** @var array $settings */

ob_start();
$error = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_error']);
$success = $_SESSION['admin_success'] ?? null;
unset($_SESSION['admin_success']);
$cur = \App\Services\PrivacyStatement::pathFor('huidig');
$hasPrevious = \App\Services\PrivacyStatement::pathFor('vorige') !== null;
$fmt = static fn (?string $p): string => $p ? date('d-m-Y H:i', (int) filemtime($p)) : '';

function sval(array $settings, string $key): string
{
    return e($settings[$key] ?? '');
}
?>
<div class="admin-header">
    <h1>Instellingen</h1>
</div>
<?php if ($error): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
<?php endif; ?>
<p class="admin-hint">Home-, Tand- en Team-teksten en -afbeeldingen bewerk je via "Pagina bewerken". Hier staan alleen de overige instellingen.</p>

<div class="card">
    <form class="stacked" id="settings-form" method="post" action="/admin/instellingen" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(\App\Services\Csrf::token()) ?>">

        <div>
            <label for="map_embed_url">Google Maps embed-URL</label>
            <input type="text" id="map_embed_url" name="map_embed_url" value="<?= sval($settings, 'map_embed_url') ?>">
        </div>
        <div>
            <label for="privacy_pdf">Privacy statement (PDF)</label>
            <div class="file-row">
                <?php if ($cur): ?>
                    <a class="btn btn--icon" href="/admin/instellingen/privacy/huidig" target="_blank" rel="noopener"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg> Huidige versie</a>
                <?php endif; ?>
                <input type="file" id="privacy_pdf" name="privacy_pdf" accept="application/pdf,.pdf">
            </div>
            <?php if ($hasPrevious): ?>
                <p class="admin-hint">
                    Vorige versie:
                    <a class="icon-link" href="/admin/instellingen/privacy/vorige" target="_blank" rel="noopener" title="Vorige versie bekijken (<?= e($fmt(\App\Services\PrivacyStatement::pathFor('vorige'))) ?>)" aria-label="Vorige versie bekijken"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg></a>
                    <button type="submit" class="btn btn--link" formaction="/admin/instellingen/privacy/herstellen" formnovalidate data-confirm="Vorige versie terugzetten als actueel privacy statement?" data-confirm-title="Herstellen" data-confirm-ok="Herstellen">Herstellen</button>
                </p>
            <?php endif; ?>
        </div>

        <div>
            <button type="submit" class="btn" id="settings-save" disabled>Opslaan</button>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
$content .= <<<'HTML'
<script>
(function () {
    var form = document.getElementById('settings-form');
    var save = document.getElementById('settings-save');
    var fields = form.querySelectorAll('input[type="text"], input[type="file"]');
    var originals = new Map();
    fields.forEach(function (f) { originals.set(f, f.value); });

    function note(field) {
        var row = field.closest('.file-row') || field;
        var el = row.nextElementSibling;
        if (!el || !el.classList.contains('change-note')) {
            el = document.createElement('p');
            el.className = 'change-note';
            el.hidden = true;
            row.insertAdjacentElement('afterend', el);
        }
        return el;
    }

    function update() {
        var changed = false;
        fields.forEach(function (f) {
            var isFile = f.type === 'file';
            var dirty = isFile ? f.files.length > 0 : f.value !== originals.get(f);
            var el = note(f);
            f.classList.toggle('is-changed', dirty);
            el.hidden = !dirty;
            if (isFile) {
                var row = f.closest('.file-row');
                el.style.paddingLeft = (f.getBoundingClientRect().left - row.getBoundingClientRect().left) + 'px';
            }
            if (dirty) {
                changed = true;
                el.textContent = '';
                var badge = document.createElement('strong');
                badge.textContent = 'Gewijzigd';
                el.appendChild(badge);
                el.appendChild(document.createTextNode(' — '));
                var undo = document.createElement('button');
                undo.type = 'button';
                undo.className = 'btn btn--link';
                undo.textContent = 'Ongedaan maken';
                undo.addEventListener('click', function () {
                    f.value = isFile ? '' : originals.get(f);
                    update();
                });
                el.appendChild(undo);
            }
        });
        save.disabled = !changed;
    }

    form.addEventListener('input', update);
    form.addEventListener('change', update);
    window.addEventListener('pageshow', update);
    window.addEventListener('resize', update);
    update();
})();
</script>
HTML;
require __DIR__ . '/../layout/admin-shell.php';
