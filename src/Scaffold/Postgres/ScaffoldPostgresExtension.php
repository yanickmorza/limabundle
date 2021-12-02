<?php

namespace App\LimaBundle\Scaffold\Postgres;

class ScaffoldPostgresExtension
{
    // ---- Generer une extension ----
    public function genererPostgresExtension($objet, $filtre, $namespace)
    {
        if ($filtre == "OUI") {

            $Objet = ucfirst($objet);

            if ($namespace !== null) {
                @mkdir("../src/Twig/".$namespace, 0755, true);
                $path_extension = "../src/Twig/".$namespace;
                $nameSpace = str_replace("/", "\\", $namespace);
                $entity =  "App\Entity$nameSpace\\$objet";
            }
            else {
                if (!is_dir("../src/Twig/")) {
                    mkdir("../src/Twig/", 0755, true);
                    $path_extension = "../src/Twig";
                    $nameSpace = "";
                    $entity =  "App\Entity\\$Objet";
                }
                else {
                    $path_extension = "../src/Twig";
                    $nameSpace = "";
                    $entity =  "App\Entity\\$Objet";
                }
            }

            $ObjetExtension = $Objet."Extension";
            $ObjetsMajSingulier = substr($Objet, 0, -1);
            $objetsMinSingulier = substr($objet, 0, -1);
            $get = "get$ObjetsMajSingulier()";

            fopen($path_extension."/".ucfirst($objet)."Extension.php", "w+");

            $fichier_extension = $path_extension."/".ucfirst($objet)."Extension.php";
            $texte_extension = "<?php

namespace App\Twig$nameSpace;

use Twig\Extension\AbstractExtension;
use Doctrine\ORM\EntityManagerInterface;
use Twig\TwigFilter;
use $entity;
        
class $ObjetExtension extends AbstractExtension
{
    private \$entityManager;

    public function __construct(EntityManagerInterface \$entityManager)
    {
        \$this->entityManager = \$entityManager;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('$objetsMinSingulier', [\$this, 'getNom$ObjetsMajSingulier']),
        ];
    }
        
    public function getNom$ObjetsMajSingulier(int \$id)
    {
        $$objet = \$this->entityManager->getRepository($Objet::class)->findOneBy(['id' => \$id]);
        return $$objet->$get;
    }
}";

            file_put_contents($fichier_extension, $texte_extension);
        }
    }
    // ---- Generer une extension ----

    // --- Supprimer une extension ---
    public function supprimerPostgresExtension($objet, $namespace)
    {
        if ($namespace !== null) {
            $path_extension = "../src/Twig/".$namespace;
        }
        else {
            $path_extension = "../src/Twig";
        }

        $filename = $path_extension."/".ucfirst($objet)."Extension.php";
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
    // --- Supprimer une extension ---
}