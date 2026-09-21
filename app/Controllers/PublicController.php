<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Setting;
use App\Models\Tandwerk;
use App\Models\TeamMember;

class PublicController
{
    public function home(): void
    {
        $settings = Setting::all();

        require __DIR__ . '/../views/public/home.php';
    }

    public function tand(): void
    {
        $settings = Setting::all();
        $items = Tandwerk::allActive();

        require __DIR__ . '/../views/public/tand.php';
    }

    public function team(): void
    {
        $settings = Setting::all();
        $members = TeamMember::allActive();

        require __DIR__ . '/../views/public/team.php';
    }
}
