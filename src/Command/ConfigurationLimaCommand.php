<?php

namespace App\LimaBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConfigurationLimaCommand extends Command
{
    protected static $defaultName = 'config:lima';

    protected function configure()
    {
        $this->setDescription('Installation de la configuration de Lima-Bundle');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filename = "config/routes/lima_bundle.yaml";

        if (!file_exists($filename)) {
        // *** Ecriture dans le fichier lima_bundle.yaml ***
        $path = "config/routes/lima_bundle.yaml";
        fopen($path, "w+");

        $texte = "app_file:
    # Charger les routes à partir du fichier de routage stockée dans LimaBundle
    resource: '@LimaBundle/Resources/config/routing_dev.yaml'
    prefix: /lima";

        file_put_contents($path, $texte);
        // *** Ecriture dans le fichier lima_bundle.yaml ***

        // *** Ecriture dans le fichier translation.yaml **
        $path = "config/packages/translation.yaml";
        fopen($path, "a");

        $texte = "framework:
    default_locale: fr
    translator:
        enabled: false
        default_path: '%kernel.project_dir%/translations'
        fallbacks:
            - fr";

        file_put_contents($path, $texte);
        // *** Ecriture dans le fichier translation.yaml **

        // ****** Ecriture dans le fichier twig.yaml ******
        $path = "config/packages/twig.yaml";
        fopen($path, "a");

        $texte = "twig:
    default_path: '%kernel.project_dir%/templates'
    form_themes: ['bootstrap_4_layout.html.twig']";

        file_put_contents($path, $texte);
        // ****** Ecriture dans le fichier twig.yaml ******

        // *** Copier les fichiers base.html.twig, _flashes.html.twig dans templates ***
        copy('vendor/yanickmorza/limabundle/src/Command/twig/_flashes.html.twig', 'templates/_flashes.html.twig');
        copy('vendor/yanickmorza/limabundle/src/Command/twig/base.html.twig', 'templates/base.html.twig');
        // *** Copier les fichiers base.html.twig, _flashes.html.twig dans templates ***

        }
        else {
            $io->warning('La configuration de Lima-Bundle est déjà présente !');

            return Command::FAILURE;
        }
        
        $io->success('La configuration de Lima-Bundle a été un succès !');

        return Command::SUCCESS;
    }
}
