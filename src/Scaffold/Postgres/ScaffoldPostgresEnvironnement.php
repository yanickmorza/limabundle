<?php

namespace App\LimaBundle\Scaffold\Postgres;

use App\LimaBundle\Scaffold\Postgres\UtilitairePostgresDatabase;
use Symfony\Component\HttpFoundation\Session\Session;

class ScaffoldPostgresEnvironnement
{
    public function envDoctrinePostgresYaml()
    {
        $session = new Session();
        $utilitaireDatabase = new UtilitairePostgresDatabase;
        
        $driver = $session->get('driver');
        $port = $session->get('port');
        $serveur = $session->get('serveur');
        $login = $session->get('userdb');
        $password = $session->get('passdb');

        $databases = $utilitaireDatabase->listerDatabases();
        
        $db = $session->get('database');
        $DataBase = ucfirst($db);

        $path_doctrineyaml = "../config/packages/doctrine.yaml";
        fopen($path_doctrineyaml, "w+");

	    $liste_db = "";
	    $orm_db = "";
	    $env = "";

        foreach ($databases as $database) 
        {
            if ($database != 'information_schema' && $database != 'performance_schema' && $database != 'sys' && $database != 'mysql' && $database != 'postgres' && $database != 'template0' && $database != 'template1') {
            
                $DB = ucfirst($database);
                
        $liste_db .= "
            $database:
                driver: $driver
                charset: utf8
                url: '$driver://$login:$password@$serveur:$port/$database'";

                if ($database != $db) {
        $orm_db .= "
            $database:
                connection: $database
                naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
                mappings:
                    $DB:
                        is_bundle: false
                        type: annotation
                        dir: '%kernel.project_dir%/src/Entity'
                        prefix: 'App\Entity'
                        alias: $DB";
                }
		    }
	    }

	    $liste_db = trim($liste_db);
	    $orm_db = trim($orm_db);
	    $env = trim($env);
	    
        $texte_doctrineyaml = "doctrine:
    dbal:
        connections:
            default:

            $liste_db

    orm:
        auto_generate_proxy_classes: true
        default_entity_manager: $db
        entity_managers:
            $db:
                connection: $db
                naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
                mappings:
                    $DataBase:
                        is_bundle: false
                        type: annotation
                        dir: '%kernel.project_dir%/src/Entity'
                        prefix: 'App\Entity'
                        alias: $DataBase
            $orm_db
";

		file_put_contents($path_doctrineyaml, $texte_doctrineyaml);

        // https://symfony.com/doc/current/doctrine/multiple_entity_managers.html
        
    }
}