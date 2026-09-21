<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\PublicController;
use App\Http\Router;
use App\Services\Auth;

$router = new Router();

$public = new PublicController();
$auth = new AuthController();
$admin = new AdminController();

// Public site
$router->get('/', fn () => $public->home());
$router->get('/tand', fn () => $public->tand());
$router->get('/team', fn () => $public->team());

// Auth
$router->get('/admin/login', fn () => $auth->showLogin());
$router->post('/admin/login', fn () => $auth->login());
$router->get('/admin/logout', fn () => $auth->logout());

// Admin (protected)
$requireAuth = static function (): void {
    Auth::requireLogin();
};

$router->get('/admin', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->dashboard();
});

$router->get('/admin/tand', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->tandIndex();
});
$router->get('/admin/tand/nieuw', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->tandForm([]);
});
$router->get('/admin/tand/{id}/bewerken', function (array $params) use ($requireAuth, $admin) {
    $requireAuth();
    $admin->tandForm($params);
});
$router->post('/admin/tand/opslaan', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->tandSave();
});
$router->post('/admin/tand/{id}/verwijderen', function (array $params) use ($requireAuth, $admin) {
    $requireAuth();
    $admin->tandDelete($params);
});

$router->get('/admin/team', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->teamIndex();
});
$router->get('/admin/team/nieuw', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->teamForm([]);
});
$router->get('/admin/team/{id}/bewerken', function (array $params) use ($requireAuth, $admin) {
    $requireAuth();
    $admin->teamForm($params);
});
$router->post('/admin/team/opslaan', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->teamSave();
});
$router->post('/admin/team/{id}/verwijderen', function (array $params) use ($requireAuth, $admin) {
    $requireAuth();
    $admin->teamDelete($params);
});
$router->post('/admin/team/volgorde', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->teamReorder();
});

$router->get('/admin/instellingen', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->settingsShow();
});
$router->post('/admin/instellingen', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->settingsSave();
});

$router->get('/admin/afbeeldingen', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->mediaIndex();
});
$router->post('/admin/afbeeldingen/upload', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->mediaUpload();
});
$router->post('/admin/afbeeldingen/verwijderen', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->mediaDelete();
});
$router->post('/admin/afbeeldingen/hernoemen', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->mediaRename();
});

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
