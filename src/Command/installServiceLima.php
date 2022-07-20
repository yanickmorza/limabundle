<?php

$filename = "config/routes/lima_bundle.yaml";

if (!file_exists($filename)) {

    // **** Ecriture dans le fichier services.yaml ****
    $path = "config/services.yaml";
    fopen($path, "a");

    $texte = "    App\LimaBundle\:
        resource: '../vendor/yanickmorza/limabundle/src/'

    app.lima_bundle.command.command_make_crud:
        class: App\LimaBundle\Command\CommandMakeCrud
        arguments: ['@maker.doctrine_helper', '@maker.renderer.form_type_renderer']
    ";

    file_put_contents($path, PHP_EOL.$texte, FILE_APPEND);
    // **** Ecriture dans le fichier services.yaml ****

    // **** Creer un fichier repere ****
    $pathrepere = 'lima.lock';
    fopen($pathrepere, 'w+');
    $texte = 'Installation Service Lima';
    file_put_contents($pathrepere, $texte);
    // **** Creer un fichier repere ****
} 
else {
    //echo "\n\n Le service de Lima-Bundle est déjà installé ! \n\n";
}
