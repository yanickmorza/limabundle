<?php

namespace App\limabundle\LimaBundle\Scaffold\Postgres;

class ScaffoldPostgresSwiftMailerFunction
{
    public function swiftMailerPostgresFunction($namespace, $objet)
    {
        // ****** Ecriture dans le Controller concerne *******
        if ($namespace !== null) {
            $path_controller = "../src/Controller/".$namespace;
        } 
        else {
            $path_controller = "../src/Controller";
        }

        $Objet = ucfirst($objet);
        $use = "";
        $texteUse = "";
        $swift = "";
        $texteswift = "";
        $parametre = "";
        $texteparametre = "";
        $editswift = "";
        $texteditswift = "";

        $mapper = $path_controller."/".$Objet."Controller.php";
        
        if (file_exists($mapper)) {

            // ---- USE
            $handleUse = fopen($mapper, "r");
            if ($handleUse) {
                $use .= "use App\Swiftmailer\SwiftMailerClass;\n";
                $use .= "/*** Use ***/";

                while (!feof($handleUse))
                {
                    $bufferUse = fgets($handleUse);
                    $texteUse .= str_replace("/*** Use ***/", $use, $bufferUse);
                }

                $handleUse = fopen($mapper, "w+");
                fwrite($handleUse, $texteUse);
                fclose($handleUse);
            }
            // ---- USE

            // ---- PARAMETRES
            $handleparametre = fopen($mapper, "r");

            if ($handleparametre) {
                $parametre .= ", SwiftMailerClass \$mailer";

                while (!feof($handleparametre))
                {
                    $bufferparametre = fgets($handleparametre);
                    $texteparametre .= str_replace("/**/", $parametre, $bufferparametre);
                }

                $handleparametre = fopen($mapper, "w+");
                fwrite($handleparametre, $texteparametre);
                fclose($handleparametre);
            }
            // ---- PARAMETRES

            // --- SWIFTMAILER - NEW - INDEX
            $handleswift = fopen($mapper, "r");

            if ($handleswift) {
                $swift .= "\n\t\t\t";
                $swift .= "\$object = \$form->get('objet')->getData();\n\t\t\t";
                $swift .= "\$from = \$form->get('expediteur')->getData();\n\t\t\t";
                $swift .= "\$to = \$form->get('destinataire')->getData();\n\t\t\t";
                $swift .= "\$body = nl2br(\$form->get('message')->getData());\n\t\t\t";
                $swift .= "\n\t\t\t";
                $swift .= "\$mailer->transmettre(\$object, \$from, \$to, \$body);\n";
                
                while (!feof($handleswift))
                {
                    $bufferswift = fgets($handleswift);
                    $texteswift .= str_replace("/***/", $swift, $bufferswift);
                }

                $handleswift = fopen($mapper, "w+");
                fwrite($handleswift, $texteswift);
                fclose($handleswift);
            }
            // --- SWIFTMAILER - NEW - INDEX

            // --- SWIFTMAILER - EDIT
            $edithandleswift = fopen($mapper, "r");
            
            if ($edithandleswift) {
                $editswift .= "\n\t\t\t";
                $editswift .= "\$object = \$form->get('objet')->getData();\n\t\t\t";
                $editswift .= "\$from = \$form->get('expediteur')->getData();\n\t\t\t";
                $editswift .= "\$to = \$form->get('destinataire')->getData();\n\t\t\t";
                $editswift .= "\$body = nl2br(\$form->get('message')->getData());\n\t\t\t";
                $editswift .= "\n\t\t\t";
                $editswift .= "\$mailer->transmettre(\$object, \$from, \$to, \$body);\n";

                while (!feof($edithandleswift))
                {
                    $bufferswift = fgets($edithandleswift);
                    $texteditswift .= str_replace("/****/", $editswift, $bufferswift);
                }

                $edithandleswift = fopen($mapper, "w+");
                fwrite($edithandleswift, $texteditswift);
                fclose($edithandleswift);
            }
            // --- SWIFTMAILER - EDIT
        }
        // ****** Ecriture dans le Controller concerne *******
    }
}