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

    // --- Tand ---

    public function tandIndex(): void
    {
        $active = 'tand';
        $pageTitle = 'Tand - TANDLAB CMS';
        $items = Tandwerk::allForAdmin();
        require __DIR__ . '/../views/admin/tand-index.php';
    }

    public function tandForm(array $params): void
    {
        $active = 'tand';
        $item = null;
        if (!empty($params['id'])) {
            $item = Tandwerk::find((int) $params['id']);
            if (!$item) {
                http_response_code(404);
                echo 'Niet gevonden';
                return;
            }
        }
        $pageTitle = ($item ? 'Werkstuk bewerken' : 'Nieuw werkstuk') . ' - TANDLAB CMS';
        require __DIR__ . '/../views/admin/tand-form.php';
    }

    public function tandSave(): void
    {
        $this->assertCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'body' => trim((string) ($_POST['body'] ?? '')),
            'alt' => trim((string) ($_POST['alt'] ?? '')),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'active' => isset($_POST['active']) ? 1 : 0,
        ];

        if (!empty($_FILES['image']['name'])) {
            try {
                $data['image_path'] = ImageService::storeUpload($_FILES['image'], 'tandwerk', 1200);
            } catch (RuntimeException $e) {
                $_SESSION['admin_error'] = $e->getMessage();
                header('Location: /admin/tand');
                exit;
            }
        }

        if ($id > 0) {
            Tandwerk::update($id, $data);
        } else {
            if (empty($data['image_path'])) {
                $data['image_path'] = null;
            }
            Tandwerk::create($data);
        }

        header('Location: /admin/tand');
        exit;
    }

    public function tandDelete(array $params): void
    {
        $this->assertCsrf();
        Tandwerk::delete((int) $params['id']);
        header('Location: /admin/tand');
        exit;
    }

    // --- Team ---

    public function teamIndex(): void
    {
        $active = 'team';
        $pageTitle = 'Team - TANDLAB CMS';
        $members = TeamMember::allForAdmin();
        require __DIR__ . '/../views/admin/team-index.php';
    }

    public function teamForm(array $params): void
    {
        $active = 'team';
        $member = null;
        if (!empty($params['id'])) {
            $member = TeamMember::find((int) $params['id']);
            if (!$member) {
                http_response_code(404);
                echo 'Niet gevonden';
                return;
            }
        }
        $pageTitle = ($member ? 'Teamlid bewerken' : 'Nieuw teamlid') . ' - TANDLAB CMS';
        require __DIR__ . '/../views/admin/team-form.php';
    }

    public function teamSave(): void
    {
        $this->assertCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'role' => trim((string) ($_POST['role'] ?? '')),
            'bio' => trim((string) ($_POST['bio'] ?? '')),
            'active' => isset($_POST['active']) ? 1 : 0,
        ];

        if (!empty($_FILES['photo']['name'])) {
            try {
                $data['photo_path'] = ImageService::storeUpload($_FILES['photo'], 'team', 800);
            } catch (RuntimeException $e) {
                $_SESSION['admin_error'] = $e->getMessage();
                header('Location: /admin/team');
                exit;
            }
        }

        if ($id > 0) {
            TeamMember::update($id, $data);
        } else {
            if (empty($data['photo_path'])) {
                $data['photo_path'] = null;
            }
            TeamMember::create($data);
        }

        header('Location: /admin/team');
        exit;
    }

    public function teamDelete(array $params): void
    {
        $this->assertCsrf();
        TeamMember::delete((int) $params['id']);
        header('Location: /admin/team');
        exit;
    }

    public function teamReorder(): void
    {
        header('Content-Type: application/json');

        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Ongeldig verzoek (CSRF).']);
            return;
        }

        $orderedIds = array_map('intval', (array) ($_POST['order'] ?? []));
        if (empty($orderedIds)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Geen volgorde ontvangen.']);
            return;
        }

        TeamMember::reorder($orderedIds);
        echo json_encode(['ok' => true]);
    }

    // --- Instellingen ---

    public function settingsShow(): void
    {
        $active = 'instellingen';
        $pageTitle = 'Instellingen - TANDLAB CMS';
        $settings = Setting::all();
        $images = Image::all();
        require __DIR__ . '/../views/admin/settings.php';
    }

    public function settingsSave(): void
    {
        $this->assertCsrf();

        $fields = [
            'address', 'phone', 'email', 'email_recipients', 'opening_hours',
            'map_embed_url', 'privacy_url', 'scan_instructions',
            'hero_title', 'hero_intro', 'usp_1', 'usp_2', 'usp_3',
        ];

        $data = [];
        foreach ($fields as $field) {
            $data[$field] = trim((string) ($_POST[$field] ?? ''));
        }

        // Slides kept/selected via the media library picker (existing uploads).
        $existingFilenames = json_decode((string) ($_POST['hero_slides'] ?? '[]'), true);
        if (!is_array($existingFilenames)) {
            $existingFilenames = [];
        }

        $knownFilenames = array_column(Image::all(), 'filename');
        $slides = [];
        foreach ($existingFilenames as $filename) {
            if (is_string($filename) && in_array($filename, $knownFilenames, true)) {
                $slides[] = $filename;
            }
        }

        $data['hero_slides'] = json_encode(array_values($slides));

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
