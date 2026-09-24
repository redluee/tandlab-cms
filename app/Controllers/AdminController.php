<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Image;
use App\Models\Setting;
use App\Models\Tandwerk;
use App\Models\TeamMember;
use App\Services\Csrf;
use App\Services\ImageService;
use RuntimeException;

class AdminController
{
    public function dashboard(): void
    {
        $active = 'dashboard';
        $pageTitle = 'Dashboard - TANDLAB CMS';
        $stats = [
            'tandwerk' => count(Tandwerk::allForAdmin()),
            'team' => count(TeamMember::allForAdmin()),
        ];
        require __DIR__ . '/../views/admin/dashboard.php';
    }

    // --- Instellingen ---

    public function settingsShow(): void
    {
        $active = 'instellingen';
        $pageTitle = 'Instellingen - TANDLAB CMS';
        $settings = Setting::all();
        require __DIR__ . '/../views/admin/settings.php';
    }

    public function settingsSave(): void
    {
        $this->assertCsrf();

        $fields = ['email_recipients', 'map_embed_url', 'privacy_url', 'scan_instructions'];

        $data = [];
        foreach ($fields as $field) {
            $data[$field] = trim((string) ($_POST[$field] ?? ''));
        }

        Setting::setMany($data);

        header('Location: /admin/instellingen');
        exit;
    }

    // --- Afbeeldingen ---

    public function mediaIndex(): void
    {
        $active = 'afbeeldingen';
        $pageTitle = 'Afbeeldingen - TANDLAB CMS';
        $images = Image::all();

        $usage = [];
        foreach ($images as $image) {
            $usedBy = Tandwerk::findByImagePath($image['filename']);
            if (!empty($usedBy)) {
                $usage[$image['filename']] = array_map(fn ($t) => $t['title'], $usedBy);
            }
        }

        require __DIR__ . '/../views/admin/media.php';
    }

    public function mediaList(): void
    {
        header('Content-Type: application/json');
        $images = array_map(
            static fn (array $image) => [
                'filename' => $image['filename'],
                'display_name' => $image['display_name'],
                'url' => '/uploads/' . $image['filename'],
            ],
            Image::all()
        );
        echo json_encode(['ok' => true, 'images' => $images]);
    }

    public function mediaUpload(): void
    {
        $this->assertCsrf();

        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if (!empty($_FILES['file']['name'])) {
            try {
                $filename = ImageService::storeUpload($_FILES['file']);
                $displayName = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
                Image::create($filename, $displayName);

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'ok' => true,
                        'filename' => $filename,
                        'display_name' => $displayName,
                    ]);
                    return;
                }
            } catch (RuntimeException $e) {
                $_SESSION['admin_error'] = $e->getMessage();

                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(422);
                    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
                    return;
                }
            }
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Geen bestand geselecteerd.']);
            return;
        }

        header('Location: /admin/afbeeldingen');
        exit;
    }

    public function mediaDelete(): void
    {
        $this->assertCsrf();

        $filename = (string) ($_POST['filename'] ?? '');
        $uploadsPath = config_get()['uploads']['path'];
        $real = realpath($uploadsPath . '/' . $filename);

        if ($real && str_starts_with($real, realpath($uploadsPath)) && is_file($real)) {
            unlink($real);
            Image::deleteByFilename($filename);
        }

        header('Location: /admin/afbeeldingen');
        exit;
    }

    public function mediaRename(): void
    {
        $this->assertCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $displayName = trim((string) ($_POST['display_name'] ?? ''));

        if ($id > 0 && !empty($displayName)) {
            Image::update($id, ['display_name' => $displayName]);
        }

        header('Location: /admin/afbeeldingen');
        exit;
    }

    private function assertCsrf(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(400);
            echo 'Ongeldig verzoek (CSRF).';
            exit;
        }
    }
}
