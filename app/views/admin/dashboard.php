<?php
/** @var array $stats */

ob_start();
?>
<div class="admin-header">
    <h1>Welkom terug</h1>
</div>
<div class="dashboard-cards">
    <a class="card dashboard-card" href="/admin/bewerken/home">
        <h2>Home</h2>
        <p>Hero, welkomsttekst en USP's bewerken.</p>
    </a>
    <a class="card dashboard-card" href="/admin/bewerken/tand">
        <h2>Tand</h2>
        <p><strong><?= (int) $stats['tandwerk'] ?></strong> werkstukken bewerken.</p>
    </a>
    <a class="card dashboard-card" href="/admin/bewerken/team">
        <h2>Team</h2>
        <p><strong><?= (int) $stats['team'] ?></strong> teamleden bewerken.</p>
    </a>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/admin-shell.php';
