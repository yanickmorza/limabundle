<?php

namespace App\LimaBundle\Scaffold\Postgres;

use App\LimaBundle\Scaffold\Postgres\UtilitairePostgresDatabase;

class ScaffoldPostgresForm
{
    // ---- Generer un FORM ----
    public function genererPostgresForm($objet, $namespace)
    {
        $utilitaireDatabase = new UtilitairePostgresDatabase;
        if ($namespace !== null) {
            @mkdir("../src/Form/" . $namespace, 0755, true);
            $path_form = "../src/Form/" . $namespace;
            $nameSpace = str_replace("/", "\\", $namespace);
        } else {
            if (!is_dir("../src/Form/")) {
                mkdir("../src/Form/", 0755, true);
                $path_form = "../src/Form/";
                $nameSpace = "";
            } else {
                $path_form = "../src/Form/";
                $nameSpace = "";
            }
        }

        // initilaisation des types de champs
        $champs_form = "";
        $entityClass = "";
        $entityType = "";
        $datetype = "";
        $datetimetype = "";
        $choicetype = "";
        $textareatype = "";
        $Objet = ucfirst($objet);
        $fonction = "";
        $construct = "";
        $champPassword = "";
        $champPlainPassword = "";

        // initilaisation des compteurs
        $i = 0;
        $j = 0;
        $k = 0;
        $l = 0;
        $m = 0;
        $n = 0;
        // initilaisation des compteurs

        // --- Recuperer tous les champs de la table concernee
        $fields = $utilitaireDatabase->listerChamps($objet);
        foreach ($fields as $field) {

            $typechamps = $utilitaireDatabase->afficherTypeChamp($objet, $field);

            $type = strtolower($typechamps);
            $Field = ucfirst($field);

            if ($field != "id") {
                if (substr($field, -3) != "_id") {

                    $champ = $field;

                    if ($champ == 'plainpassword') {
                        $champPlainPassword .= "use Symfony\Component\Form\Extension\Core\Type\PasswordType;\n";
                    } elseif ($champ == 'password') {
                        $champPassword .= "use Symfony\Component\Form\Extension\Core\Type\HiddenType;\n";
                    }

                    if ($type == 'date') {
                        $i = ($i + 1);
                        if ($i <= 1) {
                            $datetype .= "use Symfony\Component\Form\Extension\Core\Type\DateType;\n";
                        }
                    } elseif ($type == 'timestamp without time zone') {
                        $j = ($j + 1);
                        if ($j <= 1) {
                            $datetimetype .= "use Symfony\Component\Form\Extension\Core\Type\DateTimeType;\n";
                        }
                    } elseif (($type == 'boolean') or ($type == 'json')) {
                        $k = ($k + 1);
                        $m = ($m + 1);
                        if ($k <= 1 or $m <= 1) {
                            $choicetype .= "use Symfony\Component\Form\Extension\Core\Type\ChoiceType;\n";
                        }
                    } elseif ($type == 'text') {
                        $l = ($l + 1);
                        if ($l <= 1) {
                            $textareatype .= "use Symfony\Component\Form\Extension\Core\Type\TextareaType;\n";
                        }
                    }

                    if ($type == 'date') {
                        $champs_form .= "->add('$champ', DateType::class, [
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd',
            'data' => new \DateTime()])\n\t\t";
                    } elseif ($type == 'timestamp without time zone') {
                        $champs_form .= "->add('$champ', DateTimeType::class, [
            'widget' => 'single_text',
            'input_format' => 'yyyy-MM-dd',
            'data' => new \DateTime()])\n\t\t";
                    } elseif ($type == 'json') {
                        $Champ = ucfirst(substr($field, 0, -1));
                        $entityClass .= "use App\Entity$nameSpace\\$Field;\n";
                        $champs_form .= "->add('$champ', ChoiceType::class, [
            'choices' => \$this->getChoix$Field(),
            'expanded' => true,
            'multiple' => true,
            'required' => false])\n\t\t";

                        $n = ($n + 1);
                        if ($n <= 1) {
                            $construct .= "private \$entityManager;\n\n\t";
                            $construct .= "public function __construct(EntityManagerInterface \$entityManager)\n\t";
                            $construct .= "{\n\t\t";
                            $construct .= "\$this->entityManager = \$entityManager;\n\t";
                            $construct .= "}\n\n\t";
                        }

                        $fonction .= "public function getChoix$Field()\n\t";
                        $fonction .= "{\n\t\t";
                        $fonction .= "\$choix = [];\n\t\t";
                        $fonction .= "\$$champ = \$this->entityManager->getRepository($Field::class)->findAll();\n\t\t";
                        $fonction .= "foreach (\$$champ as \$row) {\n\t\t\t";
                        $fonction .= "\$choix[] = [\$row->get$Champ() => \$row->get$Champ()];\n\t\t";
                        $fonction .= "}\n\t\t";
                        $fonction .= "return \$choix;\n\t";
                        $fonction .= "}\n\n\t";
                    } elseif ($type == 'boolean') {
                        $champs_form .= "->add('$champ', ChoiceType::class, [
            'placeholder' => '--- Choisir dans la liste ---',
            'choices' => ['OUI' => true, 'NON' => false],
            'attr' => ['class' => 'custom-select border-lima'],
            'required' => true])\n\t\t";
                    } elseif ($type == 'text') {
                        $champs_form .= "->add('$champ', TextareaType::class, ['required' => false])\n\t\t";
                    } else {
                        if ($champ == 'password') {
                            $champs_form .= "->add('$champ', HiddenType::class)\n\t\t";
                        } elseif ($champ == 'plainpassword') {
                            $champs_form .= "->add('$champ', PasswordType::class, ['required' => false])\n\t\t";
                        } else {
                            $champs_form .= "->add('$champ')\n\t\t";
                        }
                    }
                } elseif (substr($field, -3) == "_id") {
                    $champ = substr($field, 0, -3);
                    $Class = ucfirst(substr($field, 0, -3));
                    $entityClass .= "use App\Entity$nameSpace\\$Class;\n";

                    $n = ($n + 1);
                    if ($n <= 1) {
                        $construct .= "private \$entityManager;\n\n\t";
                        $construct .= "public function __construct(EntityManagerInterface \$entityManager)\n\t";
                        $construct .= "{\n\t\t";
                        $construct .= "\$this->entityManager = \$entityManager;\n\t";
                        $construct .= "}\n\n\t";
                    }

                    $k = ($k + 1);
                    $m = ($m + 1);
                    if ($k <= 1 or $m <= 1) {
                        $choicetype .= "use Symfony\Component\Form\Extension\Core\Type\ChoiceType;\n";
                    }

                    $fonction .= "public function getChoix$Class()\n\t";
                    $fonction .= "{\n\t\t";
                    $fonction .= "return \$this->entityManager->getRepository($Class::class)->findAll();\n\t";
                    $fonction .= "}\n\n\t";

                    $champs_form .= "->add('$champ', ChoiceType::class, [
            'placeholder' => '--- Choisir dans la liste ---',
            'choices' => \$this->getChoix$Class(),
            'choice_label' => 'id',
            'label' => '$Class :',
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'attr' => ['class' => 'custom-select border-lima']])\n\t\t";
                }
            }
        }
        $champs_form = trim($champs_form);
        $entityClass = trim($entityClass);
        // $construct = trim($construct);
        // $fonction = trim($fonction);
        // --- Recuperer tous les champs de la table concernee

        // ---- Créer et Ouvrir le fichier pour écriture
        fopen($path_form . "/" . $Objet . "Type.php", "w+");
        $ObjetCreate = $Objet . "Type";
        if ($namespace !== null) {
            $nameSpace = str_replace("/", "\\", $namespace);
            $entity =  "App\Entity$nameSpace\\$Objet";
        } else {
            $nameSpace = "";
            $entity =  "App\Entity\\$Objet";
        }
        $fichier_form = $path_form . "/" . $Objet . "Type.php";
        $texte_form = "<?php
namespace App\Form$nameSpace;

$entityClass
use $entity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManagerInterface;
$entityType$datetimetype$datetype$choicetype$textareatype$champPassword$champPlainPassword/*** UseUpload ***/\n
class $ObjetCreate extends AbstractType
{
    $construct
    $fonction
    public function buildForm(FormBuilderInterface \$builder, array \$options)
    {
        \$builder
        $champs_form;
    }
        
    public function configureOptions(OptionsResolver \$resolver)
    {
        \$resolver->setDefaults([
            'data_class' => $Objet::class,
        ]);
    }
}";
        file_put_contents($fichier_form, $texte_form);
    }
    // ---- Generer un FORM ----

    // --- Supprimer un FORM ---
    public function supprimerPostgresForm($objet, $namespace)
    {
        if ($namespace !== null) {
            $path_form = "../src/Form/" . $namespace . "/" . ucfirst($objet) . "Type.php";
        } else {
            $path_form = "../src/Form/" . ucfirst($objet) . "Type.php";
        }
        if (file_exists($path_form)) {
            unlink($path_form);
        }
    }
    // --- Supprimer un FORM ---
}