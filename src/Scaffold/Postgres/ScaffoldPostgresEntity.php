<?php

namespace App\LimaBundle\Scaffold\Postgres;

use App\LimaBundle\Scaffold\Postgres\UtilitairePostgresDatabase;

class ScaffoldPostgresEntity
{
    // ---- Generer un Entity ----
    public function genererPostgresEntity($objet, $namespace)
    {
        $utilitaireDatabase = new UtilitairePostgresDatabase;

        if ($namespace !== null) {
            @mkdir("../src/Entity/" . $namespace, 0755, true);
            $path_entity = "../src/Entity/" . $namespace;
            $nameSpace = str_replace("/", "\\", $namespace);
        } else {
            if (!is_dir("../src/Entity/")) {
                mkdir("../src/Entity/", 0755, true);
                $path_entity = "../src/Entity/";
                $nameSpace = "";
            } else {
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

                // Controle du type de champs VARCHAR TIMESTAMP DOUBLE PRECISION etc... dans postgreSQL
                if ($type == "character varying") {
                    $type = "string";
                } elseif ($type == "timestamp without time zone") {
                    $type = "datetime";
                } elseif ($type == "double precision") {
                    $type = "float";
                }

                // Enleve les '_' met la 1ere lettre en majuscule et supprime les espaces
                $Libelle = str_replace("_", " ", $entity);
                $Libelle = str_replace(" ", "", ucwords($Libelle));

                $Champ = ucfirst(substr($entity, 0, -3));
                $champ = substr($entity, 0, -3);
                $Class = ucfirst(substr($entity, 0, -3)) . "s";

                if (substr($entity, -3) == "_id") {
                    // --- getter setter avec _id ---                      
                    $private_type_entity .= "/**\n\t";
                    $private_type_entity .= "* @ORM\ManyToOne(targetEntity=\"App\Entity$nameSpace\\$Class\", inversedBy=\"$objet\")\n\t";
                    $private_type_entity .= "* @ORM\JoinColumn(nullable=false)\n\t";
                    $private_type_entity .= "*/\n\t";
                    $private_type_entity .= "private $" . $champ . ";\n\n\t";

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

                    // ----- Contruction des Getters et Setters -------
                    $getter_setter .= "public function get$Champ()\n\t";
                    $getter_setter .= "{\n\t\t";
                    $getter_setter .= "return \$this->$champ;\n\t";
                    $getter_setter .= "}\n\n\t";

                    $getter_setter .= "public function set$Champ($$champ)\n\t";
                    $getter_setter .= "{\n\t\t";
                    $getter_setter .= "\$this->$champ = $$champ;\n\n\t\t";
                    $getter_setter .= "return \$this;\n\t";
                    $getter_setter .= "}\n\n\t";
                    // --- getter setter avec _id ---
                } else {
                    // --- getter setter par defaut ---
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
                    } elseif ($type == "date") {
                        $getter_setter .= "public function get$Libelle()\n\t";
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
                    } elseif ($type == "date") {
                        $getter_setter .= "public function set$Libelle($$libelle): self\n\t";
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
                    // --- getter setter par defaut ---
                    // ----- Contruction des Getters et Setters -------
                }
            }
        }

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
/* @UniqueEntity(fields=\"rubrique\", message=\"Attention cette rubrique existe déjà !\") */
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
    }
    // ---- Generer un Entity ----

    // --- Supprimer un Entity ---
    public function supprimerPostgresEntity($objet, $namespace)
    {
        if ($namespace !== null) {
            $path_entity = "../src/Entity/" . $namespace . "/" . ucfirst($objet) . ".php";
        } else {
            $path_entity = "../src/Entity/" . ucfirst($objet) . ".php";
        }

        if (file_exists($path_entity)) {
            unlink($path_entity);
        }
    }
    // --- Supprimer un Entity ---
}
