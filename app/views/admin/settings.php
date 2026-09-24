<?php
/** @var array $settings */

ob_start();
$error = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_error']);

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
<p class="admin-hint">Home-, Tand- en Team-teksten en -afbeeldingen bewerk je via "Pagina bewerken". Hier staan alleen de overige instellingen.</p>

<div class="card">
    <form class="stacked" method="post" action="/admin/instellingen">
        <input type="hidden" name="csrf_token" value="<?= e(\App\Services\Csrf::token()) ?>">

        <div>
            <label for="email_recipients">Ontvangers contactformulier (komma-gescheiden)</label>
            <input type="text" id="email_recipients" name="email_recipients" value="<?= sval($settings, 'email_recipients') ?>">
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

        <div>
            <button type="submit" class="btn">Opslaan</button>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/admin-shell.php';
