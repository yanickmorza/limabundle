<?php

namespace App\LimaBundle\Scaffold\Mysql;

use App\LimaBundle\Scaffold\Mysql\UtilitaireMysqlDatabase;
use Symfony\Component\HttpFoundation\Session\Session;

class ScaffoldMysqlRepository
{
    // ---- Generer un Repository ----
    public function genererMysqlRepository($objet, $namespace)
    {
        $session = new Session();
        $db = $session->get('database');

        $utilitaireDatabase = new UtilitaireMysqlDatabase;

        if ($namespace !== null) {
            @mkdir("../src/Repository/" . $namespace, 0755, true);
            $path_repository = "../src/Repository/" . $namespace;
            $nameSpace = str_replace("/", "\\", $namespace);
        } 
        else {
            if (!is_dir("../src/Repository/")) {
                mkdir("../src/Repository/", 0755, true);
                $path_repository = "../src/Repository/";
                $nameSpace = "";
            } 
            else {
                $path_repository = "../src/Repository/";
                $nameSpace = "";
            }
        }
        // ***** CHAMPS DATA INSERT
        $fieldsinsert = $utilitaireDatabase->listerChamps($objet);
        $z = -1;
        $ChampData = "";
        $ChampInsert = "";
        $ChampInsert .= "[";
        foreach ($fieldsinsert as $FieldInsert) {
            if ($FieldInsert != "id") {
                $ChampInsert .= "'$FieldInsert' => $$FieldInsert, ";
                $ChampData .= "$$FieldInsert = trim(\$data[$z]);\n\t\t\t\t\t\t";
            }
            $z = ($z + 1);
        }
        // Enlever la dernière virgule
        $ChampInsert = substr($ChampInsert, 0, -2);
        $ChampInsert .= "]";
        // ***** CHAMPS DATA INSERT

        // **** INSERT INTO TOUS LES CHAMPS ****
        $fields = $utilitaireDatabase->listerChamps($objet);
        $insert = "";
        $insert .= "INSERT INTO $objet ";
        $insert .= "(";
        foreach ($fields as $field) {
            if ($field != "id") {
                $insert .= "$field, ";
            }
        }
        // Enlever la dernière virgule
        $insert = substr($insert, 0, -2);
        $insert .= ")";
        $insert .= " VALUES ";
        $insert .= "(";
        foreach ($fields as $field) {
            if ($field != "id") {
                $insert .= ":$field, ";
            }
        }
        // Enlever la dernière virgule
        $insert = substr($insert, 0, -2);
        $insert .= ")";
        // **** INSERT INTO TOUS LES CHAMPS ****
        $Objet = ucfirst($objet);
        fopen($path_repository . "/" . $Objet . "Repository.php", "w+");
        $ObjetRepository = $Objet . "Repository";
        $ObjetTableau = $Objet . "[]";
        if ($namespace !== null) {
            $nameSpace = str_replace("/", "\\", $namespace);
            $entity =  "App\Entity$nameSpace\\$Objet";
        } else {
            $nameSpace = "";
            $entity =  "App\Entity\\$Objet";
        }

        $fichier_repository = $path_repository . "/" . $Objet . "Repository.php";
        $texte_repository = "<?php
        
namespace App\Repository$nameSpace;

use $entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
* @method $Objet|null find(\$id, \$lockMode = null, \$lockVersion = null)
* @method $Objet|null findOneBy(array \$criteria, array \$orderBy = null)
* @method $ObjetTableau    findAll()
* @method $ObjetTableau    findBy(array \$criteria, array \$orderBy = null, \$limit = null, \$offset = null)
*/
class $ObjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry \$registry)
    {
        parent::__construct(\$registry, $Objet::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add($Objet \$entity, bool \$flush): void
    {
        \$this->_em->persist(\$entity);
        if (\$flush) {
            \$this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove($Objet \$entity, bool \$flush): void
    {
        \$this->_em->remove(\$entity);
        if (\$flush) {
            \$this->_em->flush();
        }
    }
}
";
        file_put_contents($fichier_repository, $texte_repository);
    }
    // ---- Generer un Repository ----
    // --- Supprimer un Repository ---
    public function supprimerMysqlRepository($objet, $namespace)
    {
        if ($namespace !== null) {
            $path_repository = "../src/Repository/" . $namespace . "/" . ucfirst($objet) . "Repository.php";
        } else {
            $path_repository = "../src/Repository/" . ucfirst($objet) . "Repository.php";
        }
        if (file_exists($path_repository)) {
            unlink($path_repository);
        }
    }
    // --- Supprimer un Repository ---
}