<?php
/** @var array $stats */

ob_start();
?>
<div class="admin-header">
    <h1>Welkom terug</h1>
</div>
<div class="card">
    <p><strong><?= (int) $stats['tandwerk'] ?></strong> werkstukken op de Tand-pagina.</p>
    <p><strong><?= (int) $stats['team'] ?></strong> teamleden.</p>
</div>
<div class="card">
    <p>Gebruik het menu links om de inhoud van de website te beheren: Tand-werkstukken, teamleden, contactgegevens en afbeeldingen.</p>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/admin-shell.php';
