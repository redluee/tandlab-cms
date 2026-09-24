<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Db\Database;
use App\Models\Setting;
use App\Models\Tandwerk;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\ImageService;

$config = config_get();
$root = $config['app']['root'];
$pdo = Database::connect($config['db']);

echo "== Tandlab CMS installatie ==\n";
echo "Database driver: " . Database::driver() . "\n";

// 1. Schema
$schemaFile = Database::driver() === 'mysql'
    ? __DIR__ . '/../app/Db/schema.mysql.sql'
    : __DIR__ . '/../app/Db/schema.sqlite.sql';
$sql = file_get_contents($schemaFile);
$pdo->exec($sql);
echo "Schema toegepast.\n";

// 2. Branding assets (logo, bgHero) — always refreshed, not driver-dependent seed data.
$brandingDir = $config['uploads']['path'] . '/branding';
if (!is_dir($brandingDir)) {
    mkdir($brandingDir, 0775, true);
}
copyAsWebp($root . '/content/home/images/logo.png', $brandingDir . '/logo.webp');
copyAsWebp($root . '/content/home/images/bgHero.png', $brandingDir . '/bghero.webp');

// 3. Seed tandwerk (only if empty)
$tandCount = (int) $pdo->query('SELECT COUNT(*) FROM tandwerk')->fetchColumn();
if ($tandCount === 0) {
    $items = [
        [
            'title' => 'Laboratorium',
            'body' => "Wij vervaardigen al het werk zelf op ons laboratorium. Daarbij gebruiken we de nieuwste software en originele materialen van de bekendste merken.\n\nUiteraard maken wij ook nog steeds het ambachtelijke kroon- en brugwerk. Hierbij gebruiken wij hoog edele legeringen.",
            'image' => 'content/tand/images/laboratorium-pand.jpg',
            'alt' => 'Laboratorium',
        ],
        [
            'title' => 'Samenwerking',
            'body' => "Wij werken graag effectief en houden van een goede samenwerking, overleg en korte lijntjes met onze opdrachtgevers.\n\nDit doen wij mede met behulp van CBCT-scan, software en teamviewer.",
            'image' => 'content/tand/images/samenwerking.jpg',
            'alt' => 'Samenwerking',
        ],
        [
            'title' => 'Behandelkamer',
            'body' => "In onze behandelkamer doen we kleurbepalingen. En we controleren tussentijds het te plaatsen kroon- en brugwerk. Alles voor een optimaal en snel resultaat.\n\nU kunt op afspraak bij ons terecht van maandag t/m donderdag 8.00 – 16.45 uur, en vrijdag van 8.00 t/m 13.00 uur.",
            'image' => 'content/tand/images/behandelkamer.jpg',
            'alt' => 'Behandelkamer',
        ],
    ];

    foreach ($items as $index => $item) {
        $imagePath = ImageService::storeFromPath($root . '/' . $item['image'], 'tandwerk', 1200);
        Tandwerk::create([
            'title' => $item['title'],
            'body' => $item['body'],
            'image_path' => $imagePath,
            'alt' => $item['alt'],
            'sort_order' => $index,
            'active' => 1,
        ]);
    }
    echo "Tandwerk geseed (" . count($items) . " items).\n";
} else {
    echo "Tandwerk al aanwezig, overslaan.\n";
}

// 4. Seed team members (only if empty)
$teamCount = (int) $pdo->query('SELECT COUNT(*) FROM team_members')->fetchColumn();
if ($teamCount === 0) {
    $members = [
        ['name' => 'Ton Aertssen', 'role' => 'Eigenaar/directeur', 'bio' => 'In 1993 begonnen als goudtechnieker. Sinds 2005 eigenaar/directeur van het bedrijf.', 'photo' => 'ton-aertssen.png'],
        ['name' => 'Guido Nijenhuis', 'role' => 'Eigenaar/directeur', 'bio' => 'Vanaf 1994 werkzaam als porseleintechnieker. Sinds 2007 eigenaar/directeur van het bedrijf.', 'photo' => 'guido-nijenhuis.png'],
        ['name' => 'Guido Marsidi', 'role' => 'Porseleintechnieker', 'bio' => 'Vanaf 2004 werkzaam als porseleintechnieker.', 'photo' => 'guido-marsidi.png'],
        ['name' => 'Mohamed el Allati', 'role' => 'CAD-CAM specialist', 'bio' => 'Sinds 2006 aan het werk als tandtechnieker. Inmiddels onze CAD-CAM specialist.', 'photo' => 'mohamed-el-allati.png'],
        ['name' => 'Mirjam van der Zouwen-de Bruijn', 'role' => 'Goudafdeling', 'bio' => 'Vanaf 1996 aan het werk op de goudafdeling.', 'photo' => 'mirjam-van-der-zouwen-de-bruijn.png'],
        ['name' => 'Ginny Aertssen', 'role' => 'Administratief medewerkster', 'bio' => 'Sinds 2003 in dienst als administratief medewerkster.', 'photo' => 'ginny-aertssen.png'],
        ['name' => 'Lizzy Yap', 'role' => 'Administratief medewerkster', 'bio' => 'Sinds 2023 werkzaam als administratief medewerkster.', 'photo' => 'lizzy-yap.png'],
        ['name' => 'Damien Pol', 'role' => 'Tandtechnieker', 'bio' => 'Tandtechnieker, sinds 2021 in dienst.', 'photo' => 'damien-pol.png'],
        ['name' => 'Senna Flier', 'role' => 'Tandtechnieker', 'bio' => 'Tandtechnieker, sinds 2021 in dienst.', 'photo' => 'senna-flier.png'],
        ['name' => 'Frenk Nijenhuis', 'role' => 'Chauffeur', 'bio' => 'Chauffeur.', 'photo' => 'frenk-nijenhuis.png'],
        ['name' => 'Aleksandra Wisnios', 'role' => 'CADCAM medewerkster', 'bio' => 'CADCAM medewerkster.', 'photo' => 'aleksandra-wisnios.png'],
        ['name' => 'Maud Straver', 'role' => 'Leerling tandtechniek', 'bio' => 'Leerling tandtechniek.', 'photo' => 'maud-straver.png'],
    ];

    foreach ($members as $index => $member) {
        $sourcePath = $root . '/content/team/images/' . $member['photo'];
        $photoPath = is_file($sourcePath) ? ImageService::storeFromPath($sourcePath, 'team', 800) : null;
        TeamMember::create([
            'name' => $member['name'],
            'role' => $member['role'],
            'bio' => $member['bio'],
            'photo_path' => $photoPath,
            'sort_order' => $index,
            'active' => 1,
        ]);
    }
    echo "Team geseed (" . count($members) . " leden).\n";
} else {
    echo "Team al aanwezig, overslaan.\n";
}

// 5. Seed site settings (only fill missing keys, never overwrite edited content)
$existing = Setting::all();

$heroSlide1 = (!empty($existing['hero_slide_1']))
    ? $existing['hero_slide_1']
    : ImageService::storeFromPath($root . '/content/home/images/hero-slide-1.jpg', 'hero', 1920);
$heroSlide2 = (!empty($existing['hero_slide_2']))
    ? $existing['hero_slide_2']
    : ImageService::storeFromPath($root . '/content/home/images/hero-slide-2.jpg', 'hero', 1920);

$defaults = [
    'address' => "Tandlab\nZandweg 196A\n3454 HE De Meern",
    'phone' => '030-2441135',
    'email' => 'info@tandlab.nl',
    'email_recipients' => 'info@tandlab.nl',
    'opening_hours' => "MA t/m DO: 8.00 – 12.30. 13.00 - 16.45 uur.\nVR: 8.00 t/m 13.00 uur",
    'map_embed_url' => 'https://www.google.com/maps?q=Zandweg+196A+3454+HE+De+Meern&output=embed',
    'privacy_url' => '/assets/docs/privacystatement.pdf',
    'scan_instructions' => 'Neem contact met ons op voor de scan-instructies voor tandartsen.',
    'hero_kicker' => 'Tandlab',
    'hero_title' => 'UW SPECIALIST IN KROON- EN BRUGWERK',
    'hero_intro' => 'Sinds 1985 vervaardigen wij hoogwaardig kroon- en brugwerk. Als erkend leerbedrijf combineert ons vaste team jarenlange ervaring met actuele technieken om passende werkstukken voor uw praktijk of gebit te leveren.',
    'hero_slide_1' => $heroSlide1,
    'hero_slide_2' => $heroSlide2,
    'tand_title' => 'Tand',
    'team_title' => 'Team',
    'team_intro' => 'Wij zijn een erkend leerbedrijf. In het laboratorium werkt vakkundig personeel dat allemaal als leerling bij ons is begonnen. In ons hechte team werken we samen aan het beste resultaat voor uw gebit.',
    'contact_title' => 'Contact',
    'map_note' => 'De zandweg is eenrichtingsverkeer richting het westen',
    'hero_interval' => '6',
];

foreach ($defaults as $key => $value) {
    if (!array_key_exists($key, $existing) || $existing[$key] === '') {
        Setting::set($key, $value);
    }
}
echo "Instellingen geseed.\n";

// 6. First admin user
$options = getopt('', ['user::', 'password::']);
$username = $options['user'] ?? (getenv('ADMIN_USER') ?: null);
$password = $options['password'] ?? (getenv('ADMIN_PASSWORD') ?: null);

if (User::findByUsername($username ?? 'admin') === null) {
    if (!$username || !$password) {
        echo "Geen admin-gebruiker aangemaakt (geef --user=... --password=... op om er een aan te maken).\n";
    } else {
        User::create($username, $password);
        echo "Admin-gebruiker '{$username}' aangemaakt.\n";
    }
} else {
    echo "Admin-gebruiker '{$username}' bestaat al, overslaan.\n";
}

echo "Klaar.\n";

function copyAsWebp(string $source, string $destination): void
{
    if (!is_file($source)) {
        return;
    }
    if (is_file($destination)) {
        return;
    }
    $mime = mime_content_type($source);
    $image = match ($mime) {
        'image/png' => imagecreatefrompng($source),
        'image/jpeg' => imagecreatefromjpeg($source),
        'image/webp' => imagecreatefromwebp($source),
        default => null,
    };
    if ($image === null) {
        return;
    }
    imagepalettetotruecolor($image);
    imagealphablending($image, true);
    imagesavealpha($image, true);
    imagewebp($image, $destination, 90);
    imagedestroy($image);
}
