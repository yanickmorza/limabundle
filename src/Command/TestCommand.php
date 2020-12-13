<?php

namespace App\LimaBundle\Command;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class TestCommand 
{
    public static function postUpdate(Event $event)
    {
        $composer = $event->getComposer();
        $composer->echo('Installation MAJ !');
    }

    public static function postInstall(Event $event)
    {
        $composer = $event->getComposer();
        $composer->echo('Installation OK !');
    }

    /* public static function postPackageInstall(PackageEvent $event)
    {
        $installedPackage = $event->getOperation()->getPackage();
        $installedPackage->echo('Installation OK !');
    } */
}