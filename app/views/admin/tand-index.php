<?php
/** @var array $items */

ob_start();
$error = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_error']);
?>
<div class="admin-header">
    <h1>Tand</h1>
    <a class="btn" href="/admin/tand/nieuw">+ Nieuw werkstuk</a>
</div>
<?php if ($error): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>
<div class="card">
    <table>
        <thead>
        <tr>
            <th>Foto</th>
            <th>Titel</th>
            <th>Volgorde</th>
            <th>Status</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <?php if (!empty($item['image_path'])): ?>
                        <img class="thumb" src="/uploads/<?= e($item['image_path']) ?>" alt="">
                    <?php endif; ?>
                </td>
                <td><?= e($item['title']) ?></td>
                <td><?= (int) $item['sort_order'] ?></td>
                <td>
                    <span class="status-pill <?= $item['active'] ? 'status-pill--active' : 'status-pill--inactive' ?>">
                        <?= $item['active'] ? 'Actief' : 'Inactief' ?>
                    </span>
                </td>
                <td class="row-actions">
                    <a class="btn btn--secondary" href="/admin/tand/<?= (int) $item['id'] ?>/bewerken">Bewerken</a>
                    <form method="post" action="/admin/tand/<?= (int) $item['id'] ?>/verwijderen" onsubmit="return confirm('Weet u het zeker?')">
                        <input type="hidden" name="csrf_token" value="<?= e(\App\Services\Csrf::token()) ?>">
                        <button type="submit" class="btn btn--danger">Verwijderen</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
            <tr><td colspan="5">Nog geen werkstukken toegevoegd.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/admin-shell.php';
