<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Image;
use App\Models\Setting;
use App\Models\Tandwerk;
use App\Models\TeamMember;
use App\Services\Csrf;
use App\Services\ImageService;
use App\Services\PrivacyStatement;
use App\Services\Validator;
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

        $mapUrl = trim((string) ($_POST['map_embed_url'] ?? ''));
        if ($mapUrl !== '' && !Validator::googleMapsEmbedUrl($mapUrl)) {
            $_SESSION['admin_error'] = 'Ongeldige kaart-URL: gebruik een Google Maps embed-URL (https://www.google.com/maps/embed?... of https://www.google.com/maps?q=...&output=embed).';
            header('Location: /admin/instellingen');
            exit;
        }

        Setting::set('map_embed_url', $mapUrl);

        if (!empty($_FILES['privacy_pdf']['name'])) {
            try {
                PrivacyStatement::replace($_FILES['privacy_pdf']);
                $_SESSION['admin_success'] = 'Instellingen opgeslagen. Privacy statement vervangen; de vorige versie is bewaard.';
            } catch (RuntimeException $e) {
                $_SESSION['admin_error'] = $e->getMessage();
            }
        }

        header('Location: /admin/instellingen');
        exit;
    }

    public function privacyRestore(): void
    {
        $this->assertCsrf();

        try {
            PrivacyStatement::restorePrevious();
            $_SESSION['admin_success'] = 'Vorige versie hersteld. De vervangen versie is nu de "vorige".';
        } catch (RuntimeException $e) {
            $_SESSION['admin_error'] = $e->getMessage();
        }

        header('Location: /admin/instellingen');
        exit;
    }

    public function privacyDownload(array $params): void
    {
        $path = PrivacyStatement::pathFor((string) ($params['version'] ?? ''));
        if ($path === null) {
            http_response_code(404);
            echo 'Bestand niet gevonden.';
            return;
        }

        $download = isset($_GET['download']);
        header('Content-Type: application/pdf');
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="privacystatement-' . $params['version'] . '.pdf"');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
    }

    // --- Afbeeldingen ---

    public function mediaIndex(): void
    {
        $active = 'afbeeldingen';
        $pageTitle = 'Afbeeldingen - TANDLAB CMS';
        $images = Image::all();

        $usage = Image::pageUsage();

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
        $uploadsPath = realpath(config_get()['uploads']['path']);
        $real = realpath(config_get()['uploads']['path'] . '/' . $filename);

        if (Image::isProtected($filename)) {
            $_SESSION['admin_error'] = 'Deze afbeelding is onderdeel van de huisstijl en kan niet worden verwijderd.';
        } elseif (isset(Image::pageUsage()[$filename])) {
            $_SESSION['admin_error'] = 'Deze afbeelding wordt nog gebruikt op de website. Vervang haar eerst in de editor.';
        } elseif ($uploadsPath && $real && str_starts_with($real, $uploadsPath . DIRECTORY_SEPARATOR) && is_file($real)) {
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
