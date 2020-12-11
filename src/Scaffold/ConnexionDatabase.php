<?php

namespace App\LimaBundle\Scaffold;

use Symfony\Component\HttpFoundation\Session\Session;

class ConnexionDatabase
{
    public function db_connect()
    {
        $session = new Session();

        $serveur = $session->get('serveur');
        $port = $session->get('port');
        $driver = $session->get('driver');
        $database = $session->get('database');
        $userdb = $session->get('userdb');
        $passdb = $session->get('passdb');

        $dsn = $driver . ":host=$serveur;port=$port;dbname=$database";
        $opt = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC);

        return new \PDO($dsn, $userdb, $passdb, $opt);
    }
}