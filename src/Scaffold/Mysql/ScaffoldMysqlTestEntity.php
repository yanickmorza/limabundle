<?php

namespace App\LimaBundle\Scaffold\Mysql;

use App\LimaBundle\Scaffold\Mysql\UtilitaireMysqlDatabase;

class ScaffoldMysqlTestEntity
{
    // ---- Generer un TestEntity ----
    public function genererMysqlTestEntity($objet, $namespace)
    {
        $utilitaireDatabase = new UtilitaireMysqlDatabase;

        $Objet = ucfirst($objet);
        $ObjetCreate = $Objet."Test";
        $getter_setter = "";

        if ($namespace !== null) {
            @mkdir("../tests/Entity/".$namespace, 0755, true);
            $path_test_entity = "../tests/Entity/".$namespace;
            $nameSpace = str_replace("/", "\\", $namespace);
            $entity_test =  "App\Entity$nameSpace\\$Objet";
        }
        else {
            if (!is_dir("../tests/Entity/")) {
                mkdir("../tests/Entity/", 0755, true);
                $path_test_entity = "../tests/Entity/";
                $entity_test =  "App\Entity\\$Objet";
                $nameSpace = "";
            }
            else {
                $path_test_entity = "../tests/Entity/";
                $entity_test =  "App\Entity\\$Objet";
                $nameSpace = "";
            }
        }

        $entities = $utilitaireDatabase->listerChamps($objet);

        foreach ($entities as $entity)
        {
            $typechamps = $utilitaireDatabase->afficherTypeChamp($objet, $entity);
            $type = strtolower($typechamps);

            if ($entity != "id") {

                $Champ = ucfirst($entity);
                $chiffre = rand(50, 1500);
                $jour = date('Y-m-d', time());

                // Enleve les '_' met la 1ere lettre de chaque mot en majuscule et supprime les espaces
                $Champ = str_replace("_", " ", $Champ);
                $Champ = str_replace(" ", "", ucwords($Champ));

                // Controle du type de champs int, string, date etc...
                if ($type == "integer") {

                    if (substr($Champ, -2) == "Id") {
                        $Champ = substr($Champ, 0, -2);
                    }

                    $getter_setter .= "public function test$Champ()\n\t";
                    $getter_setter .= "{\n\t\t";
                    $getter_setter .= "\$testcase = new $Objet();\n\t\t";
                    $getter_setter .= "\$$objet = $chiffre;\n\t\t";
                    $getter_setter .= "\$testcase->set$Champ($$objet);\n\t\t";
                    $getter_setter .= "\$this->assertEquals($chiffre, \$testcase->get$Champ());\n\t";
                    $getter_setter .= "}\n\n\t";
                }
                elseif ($type == 'date' || $type == 'datetime') {
                    $getter_setter .= "public function test$Champ()\n\t";
                    $getter_setter .= "{\n\t\t";
                    $getter_setter .= "\$testcase = new $Objet();\n\t\t";
                    $getter_setter .= "\$$objet = $jour;\n\t\t";
                    $getter_setter .= "\$testcase->set$Champ($$objet);\n\t\t";
                    $getter_setter .= "\$this->assertSame($jour, \$testcase->get$Champ());\n\t";
                    $getter_setter .= "}\n\n\t";
                }
                elseif ($type == "character varying") {
                    $getter_setter .= "public function test$Champ()\n\t";
                    $getter_setter .= "{\n\t\t";
                    $getter_setter .= "\$testcase = new $Objet();\n\t\t";
                    $getter_setter .= "\$$objet = \"$objet\";\n\t\t";
                    $getter_setter .= "\$testcase->set$Champ($$objet);\n\t\t";
                    $getter_setter .= "\$this->assertEquals(\"$objet\", \$testcase->get$Champ());\n\t";
                    $getter_setter .= "}\n\n\t";
                }
                elseif ($type == "tinyint") {
                    $getter_setter .= "public function test$Champ()\n\t";
                    $getter_setter .= "{\n\t\t";
                    $getter_setter .= "\$testcase = new $Objet();\n\t\t";
                    $getter_setter .= "\$$objet = true;\n\t\t";
                    $getter_setter .= "\$testcase->set$Champ($$objet);\n\t\t";
                    $getter_setter .= "\$this->assertTrue(true);\n\t";
                    $getter_setter .= "}\n\n\t";
                }
            }
        }

        $getter_setter = trim($getter_setter);
        fopen($path_test_entity."/".$Objet."Test.php", "w+");
        
        $fichier_test_entity = $path_test_entity."/".$Objet."Test.php";
        $texte_test_entity = "<?php

namespace App\Tests\Entity$nameSpace;

use PHPUnit\Framework\TestCase;
use $entity_test;

class $ObjetCreate extends TestCase
{
    $getter_setter
}";

        file_put_contents($fichier_test_entity, $texte_test_entity);
    }
    // ---- Generer un TestEntity ----

    // --- Supprimer un TestEntity ---
    public function supprimerMysqlTestEntity($objet, $namespace)
    {
        if ($namespace !== null) {
            $path_test_entity = "../tests/Entity/".$namespace."/".ucfirst($objet)."Test.php";
        }
        else {
            $path_test_entity = "../tests/Entity/".ucfirst($objet)."Test.php";
        }

        if (file_exists($path_test_entity)) {
            unlink($path_test_entity);
        }
    }
    // --- Supprimer un TestEntity ---
}