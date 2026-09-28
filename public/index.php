<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\EditorController;
use App\Controllers\PublicController;
use App\Http\Router;
use App\Services\Auth;

$router = new Router();

$public = new PublicController();
$auth = new AuthController();
$admin = new AdminController();
$editor = new EditorController();

// Public site
$router->get('/', fn () => $public->home());
$router->get('/tand', fn () => $public->tand());
$router->get('/team', fn () => $public->team());

// Auth
$router->get('/admin/login', fn () => $auth->showLogin());
$router->post('/admin/login', fn () => $auth->login());
$router->post('/admin/logout', fn () => $auth->logout());

// Admin (protected)
$requireAuth = static function (): void {
    Auth::requireLogin();
};

$router->get('/admin', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->dashboard();
});

$router->get('/admin/bewerken/{page}', function (array $params) use ($requireAuth, $editor) {
    $requireAuth();
    $editor->show($params);
});
$router->post('/admin/bewerken/opslaan', function () use ($requireAuth, $editor) {
    $requireAuth();
    $editor->save();
});

$router->get('/admin/instellingen', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->settingsShow();
});
$router->post('/admin/instellingen', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->settingsSave();
});

$router->post('/admin/instellingen/privacy/herstellen', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->privacyRestore();
});
$router->get('/admin/instellingen/privacy/{version}', function (array $params) use ($requireAuth, $admin) {
    $requireAuth();
    $admin->privacyDownload($params);
});

$router->get('/admin/afbeeldingen', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->mediaIndex();
});
$router->get('/admin/afbeeldingen.json', function () use ($requireAuth, $admin) {
    $requireAuth();
    $admin->mediaList();
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
