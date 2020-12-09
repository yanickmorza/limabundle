<?php

namespace App\LimaBundle\Scaffold;

use Symfony\Component\HttpFoundation\Session\Session;

class ConnexionDatabase
{
    public function iniatialise_db()
    {
        $session = new Session();
        if ($session->get('database') !== null) {
            $db = $session->get('database');
        } 
        else {
            $session->set('database', 'postgres');
            $db = $session->get('database');
        }
        return $db;
    }

    public function db_connect()
    {
        $session = new Session();
        $connexionDatabase = new ConnexionDatabase();

        $driver = "pgsql";
        $session->set('driver', $driver);

        $port = "5432";
        $session->set('port', $port);

        $serveur = "localhost";
        $session->set('serveur', $serveur);

        $userdb = "postgres";
        $session->set('userdb', $userdb);

        $passdb = "xxxxxx";
        $session->set('passdb', $passdb);
        
        $dbname = $connexionDatabase->iniatialise_db();
        
        $dsn = $driver . ":host=$serveur;port=$port;dbname=$dbname";
        $opt = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC);

        return new \PDO($dsn, $userdb, $passdb, $opt);
    }
}
