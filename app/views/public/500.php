<?php
/** @var string|null $debugDetail */
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Er ging iets mis - TANDLAB</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f4f5f6; color: #23272a; font-family: system-ui, sans-serif; text-align: center; padding: 1rem; }
        main { max-width: 32rem; }
        .code { font-size: 4rem; font-weight: 700; color: #78c39c; margin: 0; }
        a { display: inline-block; margin-top: 1rem; padding: .7rem 1.4rem; background: #78c39c; color: #fff; border-radius: 4px; text-decoration: none; }
        pre { text-align: left; overflow: auto; background: #fff; padding: 1rem; font-size: .8rem; }
    </style>
</head>
<body>
<main>
    <p class="code">500</p>
    <h1>Er ging iets mis</h1>
    <p>Er is een onverwachte fout opgetreden. Probeer het later opnieuw of neem telefonisch contact met ons op: 030-2441135.</p>
    <a href="/">Terug naar home</a>
    <?php if (!empty($debugDetail)): ?>
        <pre><?= htmlspecialchars($debugDetail, ENT_QUOTES, 'UTF-8') ?></pre>
    <?php endif; ?>
</main>
</body>
</html>
