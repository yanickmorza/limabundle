<?php

$filename = "config/routes/lima_bundle.yaml";

if (!file_exists($filename)) {

    // **** Ecriture dans le fichier lima_bundle.yaml ****
    $path = "config/routes/lima_bundle.yaml";
    fopen($path, "w+");

    $texte = "app_file:
        # Charger les routes à partir du fichier de routage stockée dans LimaBundle
        resource: '@LimaBundle/Resources/config/routing_dev.yaml'
        prefix: /lima";

    file_put_contents($path, $texte);
    // **** Ecriture dans le fichier lima_bundle.yaml ****

    // **** Ecriture dans le fichier services.yaml ****
    $path = "config/services.yaml";
    fopen($path, "a");

    $texte = "    App\LimaBundle\:
        resource: '../vendor/yanickmorza/limabundle/src/'";

    file_put_contents($path, PHP_EOL.$texte, FILE_APPEND);
    // **** Ecriture dans le fichier services.yaml ****

    // ****** Ecriture dans le fichier twig.yaml ******
    $path = "config/packages/twig.yaml";
    fopen($path, "a");

    $texte = "twig:
        default_path: '%kernel.project_dir%/templates'
        form_themes: ['bootstrap_4_layout.html.twig']";

    file_put_contents($path, $texte);
    // ****** Ecriture dans le fichier twig.yaml ******

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

    // *** Copier les fichiers base.html.twig, _flashes.html.twig dans templates ***
    copy('vendor/yanickmorza/limabundle/src/Command/_flashes.html.twig', 'templates/_flashes.html.twig');
    copy('vendor/yanickmorza/limabundle/src/Command/base.html.twig', 'templates/base.html.twig');
    // *** Copier les fichiers base.html.twig, _flashes.html.twig dans templates ***
}
