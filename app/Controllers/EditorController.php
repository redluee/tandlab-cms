<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Db\Database;
use App\Models\Image;
use App\Models\Setting;
use App\Models\Tandwerk;
use App\Models\TeamMember;
use App\Services\Csrf;
use App\Services\EditMode;
use App\Services\HtmlSanitizer;
use RuntimeException;

class EditorController
{
    private const SETTING_TYPES = [
        'hero_kicker' => 'text',
        'hero_title' => 'text',
        'hero_intro' => 'richtext',
        'tand_title' => 'text',
        'tand_intro' => 'richtext',
        'team_title' => 'text',
        'team_intro' => 'richtext',
        'contact_title' => 'text',
        'map_note' => 'text',
        'address' => 'richtext',
        'phone' => 'text',
        'email' => 'text',
        'opening_hours' => 'richtext',
        'hero_slides' => 'slides',
        'hero_interval' => 'number',
    ];

    private const HERO_INTERVAL_MIN = 2;
    private const HERO_INTERVAL_MAX = 30;

    private const TANDWERK_TEXT_FIELDS = ['title' => 'text', 'body' => 'richtext', 'alt' => 'text'];
    private const TEAM_TEXT_FIELDS = ['name' => 'text', 'role' => 'text', 'bio' => 'richtext'];

    public function show(array $params): void
    {
        $page = $params['page'] ?? '';
        if (!in_array($page, ['home', 'tand', 'team'], true)) {
            http_response_code(404);
            require __DIR__ . '/../views/public/404.php';
            return;
        }

        EditMode::enable();

        $settings = Setting::all();

        switch ($page) {
            case 'home':
                require __DIR__ . '/../views/public/home.php';
                break;
            case 'tand':
                $items = Tandwerk::allForAdmin();
                require __DIR__ . '/../views/public/tand.php';
                break;
            case 'team':
                $members = TeamMember::allForAdmin();
                require __DIR__ . '/../views/public/team.php';
                break;
        }
    }

    public function save(): void
    {
        header('Content-Type: application/json');

        if (!Csrf::validate($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Ongeldig verzoek (CSRF).']);
            return;
        }

        $payload = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Ongeldige gegevens ontvangen.']);
            return;
        }

        $pdo = Database::connect(config_get()['db']);
        $ids = [];

        try {
            $pdo->beginTransaction();

            if (isset($payload['settings']) && is_array($payload['settings'])) {
                $this->saveSettings($payload['settings']);
            }

            if (isset($payload['tandwerk']) && is_array($payload['tandwerk'])) {
                $ids = array_merge($ids, $this->saveTandwerk($payload['tandwerk']));
            }

            if (isset($payload['team']) && is_array($payload['team'])) {
                $ids = array_merge($ids, $this->saveTeam($payload['team']));
            }

            $pdo->commit();
        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
            return;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Opslaan mislukt door een onverwachte fout.']);
            return;
        }

        echo json_encode(['ok' => true, 'ids' => $ids]);
    }

    private function saveSettings(array $settings): void
    {
        $data = [];
        foreach ($settings as $key => $value) {
            if (!array_key_exists($key, self::SETTING_TYPES)) {
                throw new RuntimeException('Onbekende instelling: ' . $key);
            }
            $type = self::SETTING_TYPES[$key];

            if ($type === 'number') {
                if (!is_numeric($value)) {
                    throw new RuntimeException('Ongeldige waarde voor ' . $key . '.');
                }
                $intValue = (int) round((float) $value);
                if ($intValue < self::HERO_INTERVAL_MIN || $intValue > self::HERO_INTERVAL_MAX) {
                    throw new RuntimeException('Wisseltijd moet tussen ' . self::HERO_INTERVAL_MIN . ' en ' . self::HERO_INTERVAL_MAX . ' seconden liggen.');
                }
                $data[$key] = (string) $intValue;
                continue;
            }

            if ($type === 'slides') {
                if (!is_array($value)) {
                    throw new RuntimeException('Ongeldige afbeeldingenlijst voor ' . $key . '.');
                }
                $filenames = [];
                foreach ($value as $filename) {
                    if (!is_string($filename) || Image::findByFilename($filename) === null) {
                        throw new RuntimeException('Onbekende afbeelding in de banner.');
                    }
                    $filenames[] = $filename;
                }
                $data[$key] = json_encode($filenames);
                continue;
            }

            if (!is_string($value)) {
                throw new RuntimeException('Ongeldige waarde voor ' . $key . '.');
            }

            $clean = trim($value);
            if ($key === 'email' && $clean !== '' && filter_var($clean, FILTER_VALIDATE_EMAIL) === false) {
                throw new RuntimeException('Ongeldig e-mailadres.');
            }

            $data[$key] = HtmlSanitizer::clean($clean, $type);
        }

        if (!empty($data)) {
            Setting::setMany($data);
        }
    }

    private function saveTandwerk(array $payload): array
    {
        $ids = [];
        $nextSortOrder = null;

        foreach ((array) ($payload['create'] ?? []) as $item) {
            if (!is_array($item) || empty($item['tmp'])) {
                continue;
            }
            $data = $this->cleanFields($item, self::TANDWERK_TEXT_FIELDS);
            if (array_key_exists('image_path', $item)) {
                $data['image_path'] = $this->cleanImageField($item['image_path']);
            }
            if (array_key_exists('active', $item)) {
                $data['active'] = !empty($item['active']) ? 1 : 0;
            }
            if (trim((string) ($data['title'] ?? '')) === '' || trim((string) ($data['body'] ?? '')) === '') {
                throw new RuntimeException('Titel en tekst zijn verplicht voor een nieuw werkstuk.');
            }
            if ($nextSortOrder === null) {
                $nextSortOrder = Tandwerk::nextSortOrder();
            }
            $data['sort_order'] = $nextSortOrder++;
            $ids[(string) $item['tmp']] = Tandwerk::create($data);
        }

        foreach ((array) ($payload['update'] ?? []) as $id => $fields) {
            $id = (int) $id;
            if ($id <= 0 || !is_array($fields)) {
                continue;
            }
            $data = $this->cleanFields($fields, self::TANDWERK_TEXT_FIELDS);
            if (array_key_exists('image_path', $fields)) {
                $data['image_path'] = $this->cleanImageField($fields['image_path']);
            }
            if (array_key_exists('active', $fields)) {
                $data['active'] = !empty($fields['active']) ? 1 : 0;
            }
            if (array_key_exists('title', $data) && trim((string) $data['title']) === '') {
                throw new RuntimeException('Titel mag niet leeg zijn.');
            }
            if (array_key_exists('body', $data) && trim((string) $data['body']) === '') {
                throw new RuntimeException('Tekst mag niet leeg zijn.');
            }
            Tandwerk::updateFields($id, $data);
        }

        foreach ((array) ($payload['delete'] ?? []) as $id) {
            Tandwerk::delete((int) $id);
        }

        if (!empty($payload['order']) && is_array($payload['order'])) {
            Tandwerk::reorder($this->resolveOrder($payload['order'], $ids));
        }

        return $ids;
    }

    private function saveTeam(array $payload): array
    {
        $ids = [];
        $nextSortOrder = null;

        foreach ((array) ($payload['create'] ?? []) as $item) {
            if (!is_array($item) || empty($item['tmp'])) {
                continue;
            }
            $data = $this->cleanFields($item, self::TEAM_TEXT_FIELDS);
            if (array_key_exists('photo_path', $item)) {
                $data['photo_path'] = $this->cleanImageField($item['photo_path']);
            }
            if (array_key_exists('active', $item)) {
                $data['active'] = !empty($item['active']) ? 1 : 0;
            }
            if (trim((string) ($data['name'] ?? '')) === '') {
                throw new RuntimeException('Naam is verplicht voor een nieuw teamlid.');
            }
            if ($nextSortOrder === null) {
                $nextSortOrder = TeamMember::nextSortOrder();
            }
            $data['sort_order'] = $nextSortOrder++;
            $ids[(string) $item['tmp']] = TeamMember::create($data);
        }

        foreach ((array) ($payload['update'] ?? []) as $id => $fields) {
            $id = (int) $id;
            if ($id <= 0 || !is_array($fields)) {
                continue;
            }
            $data = $this->cleanFields($fields, self::TEAM_TEXT_FIELDS);
            if (array_key_exists('photo_path', $fields)) {
                $data['photo_path'] = $this->cleanImageField($fields['photo_path']);
            }
            if (array_key_exists('active', $fields)) {
                $data['active'] = !empty($fields['active']) ? 1 : 0;
            }
            if (array_key_exists('name', $data) && trim((string) $data['name']) === '') {
                throw new RuntimeException('Naam mag niet leeg zijn.');
            }
            TeamMember::updateFields($id, $data);
        }

        foreach ((array) ($payload['delete'] ?? []) as $id) {
            TeamMember::delete((int) $id);
        }

        if (!empty($payload['order']) && is_array($payload['order'])) {
            TeamMember::reorder($this->resolveOrder($payload['order'], $ids));
        }

        return $ids;
    }

    /**
     * @param array<string,string> $fieldTypes
     */
    private function cleanFields(array $item, array $fieldTypes): array
    {
        $data = [];
        foreach ($fieldTypes as $field => $type) {
            if (!array_key_exists($field, $item)) {
                continue;
            }
            if (!is_string($item[$field])) {
                throw new RuntimeException('Ongeldige waarde voor ' . $field . '.');
            }
            $data[$field] = HtmlSanitizer::clean(trim($item[$field]), $type);
        }
        return $data;
    }

    private function cleanImageField(mixed $value): ?string
    {
        $filename = trim((string) $value);
        if ($filename === '') {
            return null;
        }
        if (Image::findByFilename($filename) === null) {
            throw new RuntimeException('Onbekende afbeelding.');
        }
        return $filename;
    }

    /**
     * @param array<int,mixed> $order
     * @param array<string,int> $ids
     * @return int[]
     */
    private function resolveOrder(array $order, array $ids): array
    {
        $resolved = [];
        foreach ($order as $entry) {
            if (isset($ids[(string) $entry])) {
                $resolved[] = $ids[(string) $entry];
            } elseif (is_numeric($entry)) {
                $resolved[] = (int) $entry;
            }
        }
        return $resolved;
    }
}
