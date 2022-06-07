<?php

namespace App\LimaBundle\Scaffold\Postgres;

use App\LimaBundle\Scaffold\Postgres\UtilitairePostgresDatabase;
use Symfony\Component\HttpFoundation\Session\Session;

class ScaffoldPostgresRepository
{
    // ---- Generer un Repository ----
    public function genererPostgresRepository($objet, $namespace)
    {
        $session = new Session();
        $db = $session->get('database');

        $utilitaireDatabase = new UtilitairePostgresDatabase;
        
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

        $sequence = $objet."_id_seq";

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

    // ------ Vider les donnees de la table -------
    public function truncateTable()
    {
        \$stmt = \$this->getEntityManager()->getConnection()->prepare('TRUNCATE TABLE $objet CASCADE')->executeQuery();
        \$stmt = \$this->getEntityManager()->getConnection()->prepare('ALTER SEQUENCE $sequence RESTART WITH 1')->executeQuery();       
        return \$stmt; 
    }
    // ------ Vider les donnees de la table -------

    // ----------- Exporter les donnees -----------
    public function exporterDonnee$Objet()
    {
        \$stmt = \$this->getEntityManager()->getConnection()->prepare('SELECT * FROM $objet ORDER BY id ASC');
        return \$stmt->executeQuery()->fetchAllAssociative();
    }
    // ----------- Exporter les donnees -----------

    // ----------- Uploader les donnees -----------
    public function uploaderDonnee$Objet()
	{
        \$tmp_file = \$_FILES['charger']['tmp_name'];
        \$colonnes = \$this->getEntityManager()->getConnection()->getSchemaManager()->listTableColumns('sites');

        \$parameter = '';
        \$values = '';
        \$datas = [];

			foreach (\$colonnes as \$colonne) 
            {
                if (\$colonne->getName() != 'id') {
                    \$values .= ':'.\$colonne->getName().', ';
                    \$parameter .= \$colonne->getName().', ';
                }
            }

        // Enlever la derniere virgule
        \$values = substr(\$values, 0, -2);
        \$parameter = substr(\$parameter, 0, -2);

        \$tmp_file = \$_FILES['charger']['tmp_name'];
        
        if ((\$tmp_file) && (\$tmp_file != \"none\")) {

            \$path_cache = \"../var/tmp/\".\$_FILES['charger']['name'];
			move_uploaded_file (\$tmp_file, \$path_cache);

			\$file = \$path_cache;
			\$fichier = file_get_contents(\$path_cache);

            \$newfile = trim(\$fichier);
            file_put_contents(\$file, \$newfile);

            \$handle = fopen(\$path_cache, \"r\");
	
				while ((\$data = fgetcsv(\$handle, 0, \";\")) !== FALSE) {
					\$i = 0;
					foreach (\$colonnes as \$colonne)
            		{
                		if (\$colonne->getName() != 'id') {
							\$datas[\$colonne->getName()] = [\$colonne->getName() => \$data[\$i]][\$colonne->getName()];
							\$i++;
						}
					}

                    \$rawSql = \"INSERT INTO sites (\$parameter) VALUES (\$values)\";
            		\$execution = \$this->getEntityManager()->getConnection()->prepare(\$rawSql)->executeQuery(\$datas);
				}

            fclose(\$handle);
            unlink(\$path_cache);
        }
        return \$execution;
    }
    // ----------- Uploader les donnees -----------
}
";
        file_put_contents($fichier_repository, $texte_repository);
    }
    // ---- Generer un Repository ----
    // --- Supprimer un Repository ---
    public function supprimerPostgresRepository($objet, $namespace)
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