<?php
/** @var array $members */

ob_start();
$error = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_error']);
?>
<div class="admin-header">
    <h1>Team</h1>
    <a class="btn" href="/admin/team/nieuw">+ Nieuw teamlid</a>
</div>
<?php if ($error): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>
<p class="admin-hint">Sleep de rijen aan de handgreep om de volgorde te wijzigen. Wijzigingen worden direct opgeslagen.</p>
<div class="card">
    <table id="team-table">
        <thead>
        <tr>
            <th></th>
            <th>Foto</th>
            <th>Naam</th>
            <th>Functie</th>
            <th>Status</th>
            <th></th>
        </tr>
        </thead>
        <tbody id="team-table-body">
        <?php foreach ($members as $member): ?>
            <tr data-id="<?= (int) $member['id'] ?>">
                <td class="drag-handle" title="Sleep om te herordenen" aria-hidden="true">&#9776;</td>
                <td>
                    <?php if (!empty($member['photo_path'])): ?>
                        <img class="thumb" src="/uploads/<?= e($member['photo_path']) ?>" alt="">
                    <?php endif; ?>
                </td>
                <td><?= e($member['name']) ?></td>
                <td><?= e($member['role']) ?></td>
                <td>
                    <span class="status-pill <?= $member['active'] ? 'status-pill--active' : 'status-pill--inactive' ?>">
                        <?= $member['active'] ? 'Actief' : 'Inactief' ?>
                    </span>
                </td>
                <td class="row-actions">
                    <a class="btn btn--secondary" href="/admin/team/<?= (int) $member['id'] ?>/bewerken">Bewerken</a>
                    <form method="post" action="/admin/team/<?= (int) $member['id'] ?>/verwijderen" onsubmit="return confirm('Weet u het zeker?')">
                        <input type="hidden" name="csrf_token" value="<?= e(\App\Services\Csrf::token()) ?>">
                        <button type="submit" class="btn btn--danger">Verwijderen</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($members)): ?>
            <tr><td colspan="6">Nog geen teamleden toegevoegd.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" integrity="sha256-ymhDBwPE9ZYOkHNYZ8bpTSm1o943EH2BAOWjAQB+nm4=" crossorigin="anonymous"></script>
<script>
(function () {
    var body = document.getElementById('team-table-body');
    if (!body || typeof Sortable === 'undefined') {
        return;
    }

    var csrfToken = <?= json_encode(\App\Services\Csrf::token()) ?>;

    Sortable.create(body, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function () {
            var order = Array.prototype.map.call(
                body.querySelectorAll('tr[data-id]'),
                function (row) { return row.getAttribute('data-id'); }
            );

            var params = new URLSearchParams();
            params.append('csrf_token', csrfToken);
            order.forEach(function (id) { params.append('order[]', id); });

            fetch('/admin/team/volgorde', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            }).then(function (response) {
                if (!response.ok) {
                    throw new Error('Opslaan mislukt');
                }
            }).catch(function () {
                alert('De nieuwe volgorde kon niet worden opgeslagen. Herlaad de pagina en probeer het opnieuw.');
            });
        }
    });
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/admin-shell.php';
