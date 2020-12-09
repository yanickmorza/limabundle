<?php

namespace App\LimaBundle\Scaffold\Postgres;

use App\LimaBundle\Scaffold\UtilitaireDatabase;

class ScaffoldPostgresRelation
{
    // ---- Generer une Relation entre plusieurs tables ----
    public function genererPostgresRelation($objet, $namespace, $relation, $othernamespace)
    {
        $utilitaireDatabase = new UtilitaireDatabase;

        if ($namespace !== null) {
            @mkdir("../src/Entity/" . $namespace, 0755, true);
            $path_entity = "../src/Entity/" . $namespace;
            $nameSpace = str_replace("/", "\\", $namespace);
        } 
        else {
            if (!is_dir("../src/Entity/")) {
                mkdir("../src/Entity/", 0755, true);
                $path_entity = "../src/Entity/";
                $nameSpace = "";
            } 
            else {
                $path_entity = "../src/Entity/";
                $nameSpace = "";
            }
        }

        // --- Recuperer tous les champs de la table concernee
        $private_type_entity = "";
        $getter_setter       = "";
        $private_mappe       = "";
        $Objet = ucfirst($objet);

        $entities = $utilitaireDatabase->listerChamps($objet);

        foreach ($entities as $entity) {
            $typechamps = $utilitaireDatabase->afficherTypeChamp($objet, $entity);

            if ($entity != "id") {
                $type = strtolower($typechamps);
                $libelle = $entity;

                // --- Controler les types de champs VARCHAR, TIMESTAMP, DOUBLE PRECISION, etc... (postgreSQL)
                if ($type == "character varying") {
                    $type = "string";
                } 
                elseif ($type == "timestamp without time zone") {
                    $type = "datetime";
                } 
                elseif ($type == "double precision") {
                    $type = "float";
                }
                // --- Controler les types de champs VARCHAR, TIMESTAMP, DOUBLE PRECISION, etc... (postgreSQL)

                // Enleve les '_' met la 1ere lettre en majuscule et supprime les espaces
                $Libelle = str_replace("_", " ", $entity);
                $Libelle = str_replace(" ", "", ucwords($Libelle));

                $Champ = ucfirst(substr($entity, 0, -3));
                $champ = substr($entity, 0, -3);
                $Class = ucfirst(substr($entity, 0, -3)) . "s";

                if ((substr($entity, -3) == "_id") and ($relation == "manytoone")) {

                    if ($othernamespace) {

                        foreach ($othernamespace as $NameSpace) {

                            // ---- Inversed By Table dans Base concernee ----
                            $NameSpace = ucfirst($NameSpace);
                            $private_type_entity .= "/**\n\t";
                            $private_type_entity .= "* @ORM\ManyToOne(targetEntity=\"App\Entity\\$NameSpace\\$Class\", inversedBy=\"$objet\")\n\t";
                            $private_type_entity .= "* @ORM\JoinColumn(nullable=false)\n\t";
                            $private_type_entity .= "*/\n\t";
                            $private_type_entity .= "private $" . $champ . ";\n\n\t";
                            // ---- Inversed By table dans Base concernee ----

                            // ----- Ecrire dans le FORMTYPE -----
                            $texteUse = "";
                            $nameSpaceForm = str_replace("\\", "", $namespace);
                            $mapperUse = "../src/Form" . $nameSpaceForm. "/" . $Objet . "Type.php";
                            $handleUse = fopen($mapperUse, "r");

                            if ($handleUse) {
                                while (!feof($handleUse)) {
                                    $nameSpaceUse = str_replace("\\", "", $nameSpace);
                                    $bufferUse = fgets($handleUse);
                                    $texteUse .= str_replace("use App\Entity\\$nameSpaceUse\\$Class;", "use App\Entity\\$NameSpace\\$Class;", $bufferUse);
                                }

                                $handleUse = fopen($mapperUse, "w+");
                                fwrite($handleUse, $texteUse);
                                fclose($handleUse);
                            }
                            // ----- Ecrire dans le FORMTYPE -----

                            // ---- Mappage Table dans Base concernee ----
                            $texte = "";
                            $path_entity_mapper = "../src/Entity";
                            $mapper = $path_entity_mapper . "/" . $NameSpace."/".$Class. ".php";

                            if (file_exists($mapper)) {

                                $chaine   = file_get_contents($mapper);
                                $trouve   = $objet;
                                $position = strpos($chaine, $trouve);
        
                                if ($position === false) {
                                    $private_mappe .= "/**\n\t";
                                    $private_mappe .= "* @ORM\OneToMany(targetEntity=\"App\Entity$nameSpace\\$Objet\", mappedBy=\"$champ\")\n\t";
                                    $private_mappe .= "*/\n\t";
                                    $private_mappe .= "private $" . $objet . ";\n\t";
                                    $private_mappe .= "\n\t/*** ***/";
                                   
                                    $handle = fopen($mapper, "r");
        
                                    if ($handle) {
                                        while (!feof($handle)) {
                                            $buffer = fgets($handle);
                                            $texte .= str_replace("/*** ***/", $private_mappe, $buffer);
                                        }
                                        $handle = fopen($mapper, "w+");
                                        fwrite($handle, $texte);
                                        fclose($handle);
                                    }
                                }
                            }
                            // ---- Mappage table dans Base concernee ----
                        }
                    }
                    else {
                        // --- Declaration du private ---
                        $private_type_entity .= "/**\n\t";
                        $private_type_entity .= "* @ORM\ManyToOne(targetEntity=\"App\Entity$nameSpace\\$Class\", inversedBy=\"$objet\")\n\t";
                        $private_type_entity .= "* @ORM\JoinColumn(nullable=false)\n\t";
                        $private_type_entity .= "*/\n\t";
                        $private_type_entity .= "private $" . $champ . ";\n\n\t";
                        // --- Declaration du private ---
                    }
                    
                    // ---- Mapper vers la table de correspondance ----                      
                    $texte = "";
                    $private_mappe = "";
                    $mapper = $path_entity . "/" . $Class . ".php";

                    if (file_exists($mapper)) {
                        $chaine   = file_get_contents($mapper);
                        $trouve   = $objet;
                        $position = strpos($chaine, $trouve);

                        if ($position === false) {
                            $private_mappe .= "/**\n\t";
                            $private_mappe .= "* @ORM\OneToMany(targetEntity=\"App\Entity$nameSpace\\$Objet\", mappedBy=\"$champ\")\n\t";
                            $private_mappe .= "*/\n\t";
                            $private_mappe .= "private $" . $objet . ";\n\t";
                            $private_mappe .= "\n\t/*** ***/";
                           
                            $handle = fopen($mapper, "r");

                            if ($handle) {
                                while (!feof($handle)) {
                                    $buffer = fgets($handle);
                                    $texte .= str_replace("/*** ***/", $private_mappe, $buffer);
                                }
                                $handle = fopen($mapper, "w+");
                                fwrite($handle, $texte);
                                fclose($handle);
                            }
                        }
                    }
                    // ---- Mapper vers la table de correspondance ----

                    // ----- Contruction du Getter et Setter -------
                    $getter_setter .= "public function get$Champ()\n\t";
                    $getter_setter .= "{\n\t\t";
                    $getter_setter .= "return \$this->$champ;\n\t";
                    $getter_setter .= "}\n\n\t";

                    $getter_setter .= "public function set$Champ($$champ)\n\t";
                    $getter_setter .= "{\n\t\t";
                    $getter_setter .= "\$this->$champ = $$champ;\n\n\t\t";
                    $getter_setter .= "return \$this;\n\t";
                    $getter_setter .= "}\n\n\t";
                    // ----- Contruction du Getter et Setter -------
                }
                elseif ((substr($entity, -3) == "_id") and ($relation == "onetoone")) {

                    if ($othernamespace) {

                        foreach ($othernamespace as $NameSpace) {

                            // ---- Inversed By Table dans Base concernee ----
                            $NameSpace = ucfirst($NameSpace);
                            $private_type_entity .= "/**\n\t";
                            $private_type_entity .= "* @ORM\OneToOne(targetEntity=\"App\Entity\\$NameSpace\\$Class\", cascade={\"persist\", \"remove\"})\n\t";
                            $private_type_entity .= "* @ORM\JoinColumn(nullable=false)\n\t";
                            $private_type_entity .= "*/\n\t";
                            $private_type_entity .= "private $" . $champ . ";\n\n\t";
                            // ---- Inversed By table dans Base concernee ----

                            // ----- Ecrire dans le FORMTYPE -----
                            $texteUse = "";
                            $nameSpaceForm = str_replace("\\", "", $namespace);
                            $mapperUse = "../src/Form" . $nameSpaceForm. "/" . $Objet . "Type.php";
                            $handleUse = fopen($mapperUse, "r");

                            if ($handleUse) {
                                while (!feof($handleUse)) {
                                    $nameSpaceUse = str_replace("\\", "", $nameSpace);
                                    $bufferUse = fgets($handleUse);
                                    $texteUse .= str_replace("use App\Entity\\$nameSpaceUse\\$Class;", "use App\Entity\\$NameSpace\\$Class;", $bufferUse);
                                }

                                $handleUse = fopen($mapperUse, "w+");
                                fwrite($handleUse, $texteUse);
                                fclose($handleUse);
                            }
                            // ----- Ecrire dans le FORMTYPE -----
                        }
                    }
                    else {
                        // --- Declaration du private ---
                        $private_type_entity .= "/**\n\t";
                        $private_type_entity .= "* @ORM\OneToOne(targetEntity=\"App\Entity$nameSpace\\$Class\", cascade={\"persist\", \"remove\"})\n\t";                    
                        $private_type_entity .= "* @ORM\JoinColumn(nullable=false)\n\t";
                        $private_type_entity .= "*/\n\t";
                        $private_type_entity .= "private $" . $champ . ";\n\t";
                        // --- Declaration du private ---

                        // ----- Contruction du Getter et Setter -------
                        $getter_setter .= "public function get$Champ()\n\t";
                        $getter_setter .= "{\n\t\t";
                        $getter_setter .= "return \$this->$champ;\n\t";
                        $getter_setter .= "}\n\n\t";

                        $getter_setter .= "public function set$Champ($$champ)\n\t";
                        $getter_setter .= "{\n\t\t";
                        $getter_setter .= "\$this->$champ = $$champ;\n\n\t\t";
                        $getter_setter .= "return \$this;\n\t";
                        $getter_setter .= "}\n\n\t";
                        // ----- Contruction du Getter et Setter -------
                    }
                }
                else {
                    // --- Getters Setters des autres champs de la table ---
                    if ($type == "json") {
                        $crocher = " = []";
                        $private_type_entity .= "/**\n\t";
                        $private_type_entity .= "* @ORM\Column(type=\"$type\")\n\t";
                        $private_type_entity .= "*/\n\t";
                        $private_type_entity .= "private $" . $entity . $crocher.";\n\n\t";
                    }
                    else {
                        $private_type_entity .= "/**\n\t";
                        $private_type_entity .= "* @ORM\Column(type=\"$type\")\n\t";
                        $private_type_entity .= "*/\n\t";
                        $private_type_entity .= "private $" . $entity . ";\n\n\t";
                    }

                    if ($type == "string") {
                        $getter_setter .= "public function get$Libelle(): ?string\n\t";
                    } elseif ($type == "text") {
                        $getter_setter .= "public function get$Libelle(): ?string\n\t";
                    } elseif ($type == "integer") {
                        $getter_setter .= "public function get$Libelle(): ?int\n\t";
                    } elseif ($type == "datetime") {
                        $getter_setter .= "public function get$Libelle(): ?\DateTimeInterface\n\t";
                    } else {
                        if ($type == "boolean") {
                            $type = "bool";
                            $getter_setter .= "public function get$Libelle(): ?$type\n\t";
                        }
                        elseif ($type == "json") {
                            $getter_setter .= "public function get$Libelle(): ?array\n\t";
                        }
                        else {
                            $getter_setter .= "public function get$Libelle(): ?$type\n\t";
                        }                       
                    }

                        $getter_setter .= "{\n\t\t";
                        $getter_setter .= "return \$this->$libelle;\n\t";
                        $getter_setter .= "}\n\n\t";

                    if ($type == "string") {
                        $getter_setter .= "public function set$Libelle(string $$libelle): self\n\t";
                    } elseif ($type == "text") {
                        $getter_setter .= "public function set$Libelle(string $$libelle): self\n\t";
                    } elseif ($type == "integer") {
                        $getter_setter .= "public function set$Libelle(int $$libelle): self\n\t";
                    } elseif ($type == "datetime") {
                        $getter_setter .= "public function set$Libelle(\DateTimeInterface $$libelle): self\n\t";
                    } else {
                        if ($type == "boolean") {
                            $type = "bool";
                            $getter_setter .= "public function set$Libelle($type $$libelle): self\n\t";
                        }
                        elseif ($type == "json") {
                            $getter_setter .= "public function set$Libelle(array $$libelle): self\n\t";
                        }
                        else {
                            $getter_setter .= "public function set$Libelle($type $$libelle): self\n\t";
                        }
                    }

                        $getter_setter .= "{\n\t\t";
                        $getter_setter .= "\$this->$libelle = $$libelle;\n\n\t\t";
                        $getter_setter .= "return \$this;\n\t";
                        $getter_setter .= "}\n\n\t";
                    // --- Getters Setters des autres champs de la table ---
                }
            }
        }

        // ------ Creation du fichier entity ------
        $private_type_entity = trim($private_type_entity);
        $getter_setter = trim($getter_setter);

        fopen($path_entity . "/" . $Objet . ".php", "w+");
        $ObjetRepository = $Objet . "Repository";

        $fichier_entity = $path_entity . "/" . $Objet . ".php";
        $texte_entity = "<?php

namespace App\Entity$nameSpace;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
* @ORM\Entity(repositoryClass=\"App\Repository$nameSpace\\$ObjetRepository\")
*/
/* @UniqueEntity(fields=\"element\", message=\"Attention cet element existe déjà !\") */
class $Objet
{
    /**
    * @ORM\Id()
    * @ORM\GeneratedValue()
    * @ORM\Column(type=\"integer\")
    */
    private \$id;

    $private_type_entity

    /*** ***/

    public function getId(): ?int
    {
        return \$this->id;
    }

    $getter_setter
}";

        file_put_contents($fichier_entity, $texte_entity);
        // ------ Creation du fichier entity ------
    }
    // ---- Generer une Relation entre plusieurs tables ----
}