<?php
$pageTitle = 'Pagina niet gevonden - TANDLAB';
$active = '';
$settings = \App\Models\Setting::all();

ob_start();
?>
<section class="section error-404">
    <div class="container">
        <p class="error-404__code">404</p>
        <h1>Pagina niet gevonden</h1>
        <p>De pagina die u zoekt bestaat niet (meer) of is verplaatst.</p>
        <div class="error-404__actions">
            <a class="btn" href="/">Terug naar home</a>
        </div>
        <ul class="error-404__links">
            <li><a href="/tand">Tand</a></li>
            <li><a href="/team">Team</a></li>
            <li><a href="/#contact">Contact</a></li>
        </ul>
    </div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/page.php';
