<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Db\Database;
use App\Db\TeamCardColorMigration;

TeamCardColorMigration::up(Database::connect(config_get()['db']));
echo "Teamkaartkleuren bijgewerkt.\n";
