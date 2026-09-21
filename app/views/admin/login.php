<?php
/** @var string|null $error */
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen - TANDLAB CMS</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <h1>TANDLAB CMS</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert--error"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/admin/login" class="stacked">
            <input type="hidden" name="csrf_token" value="<?= e(\App\Services\Csrf::token()) ?>">
            <div>
                <label for="username">Gebruikersnaam</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div>
                <label for="password">Wachtwoord</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn" style="width:100%">Inloggen</button>
        </form>
    </div>
</div>
</body>
</html>
