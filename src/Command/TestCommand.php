<?php

namespace App\LimaBundle;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class TestCommand 
{
    public static function postUpdate(Event $event)
    {
        $composer = $event->getComposer();
        $composer->echo('Installation MAJ !');
    }

    public static function postPackageInstall(PackageEvent $event)
    {
        $installedPackage = $event->getOperation()->getPackage();
        $installedPackage->echo('Installation OK !');
    }
}