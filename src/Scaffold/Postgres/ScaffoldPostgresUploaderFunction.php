<?php

namespace App\LimaBundle\Scaffold\Postgres;

class ScaffoldPostgresUploaderFunction
{
    public function uploaderPostgresFunction($namespace, $objet, $intitule)
    {
        // ****** Ecriture dans le Controller et Form concerne *******
        if ($namespace !== null) {
            $path_controller = "../src/Controller/".$namespace;
            $path_form = "../src/Form/".$namespace;
        } 
        else {
            $path_controller = "../src/Controller";
            $path_form = "../src/Form/";
        }

        $Objet = ucfirst($objet);
        $Intitule = ucfirst($intitule);
        $use = "";
        $useform = "";
        $texteUse = "";
        $texteUseform = "";
        $textechampform = "";
        $champform = "";
        $upload = "";
        $texteupload = "";
        $editupload = "";
        $texteditupload = "";

        $controller = $path_controller."/".$Objet."Controller.php";
        $formtype = $path_form."/".$Objet."Type.php";
        
        // ------ Ecrire dans le controller concerne
        if (file_exists($controller)) {

            // ---- USE
            $handleUse = fopen($controller, "r");

            if ($handleUse) {
                $use .= "use Symfony\Component\HttpFoundation\File\Exception\FileException;\n";
                $use .= "/*** Use ***/";

                while (!feof($handleUse))
                {
                    $bufferUse = fgets($handleUse);
                    $texteUse .= str_replace("/*** Use ***/", $use, $bufferUse);
                }

                $handleUse = fopen($controller, "w+");
                fwrite($handleUse, $texteUse);
                fclose($handleUse);
            }
            // ---- USE

            // --- UPLOADER - NEW - INDEX
            $handleupload = fopen($controller, "r");

            if ($handleupload) {

                $file = $intitule."File";
                $originalFilename = $file."->getClientOriginalName()";
                $guessExtension = $file."->guessExtension()";
                $move = $file."->move";

                $upload .= "\n\t\t\t";
                $upload .= "\$$file = \$form->get('$intitule')->getData();\n\n\t\t\t";
                $upload .= "if (\$$file) {\n\n\t\t\t\t";
                $upload .= "\$originalFilename = pathinfo(\$$originalFilename, PATHINFO_FILENAME);\n\t\t\t\t";
                $upload .= "\$safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', \$originalFilename);\n\t\t\t\t";
                $upload .= "\$newFilename = \$safeFilename.'-'.uniqid().'.'.\$$guessExtension;\n\n\t\t\t\t";
                $upload .= "try {\n\t\t\t\t\t";
                $upload .= "\$$move('../public/uploads/$objet', \$newFilename);\n\t\t\t\t";
                $upload .= "} catch (FileException \$e) {}\n\n\t\t\t\t";
                $upload .= "\$objet->set$Intitule(\$newFilename);\n\t\t\t";
                $upload .= "}\n";

                while (!feof($handleupload))
                {
                    $bufferupload = fgets($handleupload);
                    $texteupload .= str_replace("/***/", $upload, $bufferupload);
                }

                $handleupload = fopen($controller, "w+");
                fwrite($handleupload, $texteupload);
                fclose($handleupload);
            }
            // --- UPLOADER - NEW - INDEX

            // --- UPLOADER - EDIT
            $edithandleupload = fopen($controller, "r");
            
            if ($edithandleupload) {

                $file = $intitule."File";
                $originalFilename = $file."->getClientOriginalName()";
                $guessExtension = $file."->guessExtension()";
                $move = $file."->move";

                $editupload .= "\n\t\t\t";
                $editupload .= "\$$file = \$form->get('$intitule')->getData();\n\n\t\t\t";
                $editupload .= "if (\$$file) {\n\n\t\t\t\t";
                $editupload .= "\$originalFilename = pathinfo(\$$originalFilename, PATHINFO_FILENAME);\n\t\t\t\t";
                $editupload .= "\$safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', \$originalFilename);\n\t\t\t\t";
                $editupload .= "\$newFilename = \$safeFilename.'-'.uniqid().'.'.\$$guessExtension;\n\n\t\t\t\t";
                $editupload .= "try {\n\t\t\t\t\t";
                $editupload .= "\$$move('../public/uploads/$objet', \$newFilename);\n\t\t\t\t";
                $editupload .= "} catch (FileException \$e) {}\n\n\t\t\t\t";
                $editupload .= "\$objet->set$Intitule(\$newFilename);\n\t\t\t";
                $editupload .= "}\n";

                while (!feof($edithandleupload))
                {
                    $bufferupload = fgets($edithandleupload);
                    $texteditupload .= str_replace("/****/", $editupload, $bufferupload);
                }

                $edithandleupload = fopen($controller, "w+");
                fwrite($edithandleupload, $texteditupload);
                fclose($edithandleupload);
            }
            // --- UPLOADER - EDIT
        }
        // ------ Ecrire dans le controller concerne

        // ------ Ecrire dans le form concerne
        if (file_exists($formtype)) {

            // ---- USE
            $handleUseForm = fopen($formtype, "r");

            if ($handleUseForm) {

                $useform .= "use Symfony\Component\Form\Extension\Core\Type\FileType;\n";
                $useform .= "use Symfony\Component\Validator\Constraints\File;";

                while (!feof($handleUseForm))
                {
                    $bufferUseForm = fgets($handleUseForm);
                    $texteUseform .= str_replace("/*** UseUpload ***/", $useform, $bufferUseForm);
                }

                $handleUseForm = fopen($formtype, "w+");
                fwrite($handleUseForm, $texteUseform);
                fclose($handleUseForm);
            }
            // ---- USE

            // ---- CHAMP 
            $handleChampForm = fopen($formtype, "r");

            if ($handleChampForm) {

                $champform .= "'$intitule', FileType::class, [\n\t\t\t";
                $champform .= "'mapped' => false,\n\t\t\t";
                $champform .= "'required' => false,\n\t\t\t";
                $champform .= "'constraints' => [\n\t\t\t\t";
                $champform .= "new File([\n\t\t\t\t\t";
                $champform .= "'maxSize' => '1024k',\n\t\t\t\t\t";
                $champform .= "'mimeTypes' => [\n\t\t\t\t\t\t";
                $champform .= "'application/pdf',\n\t\t\t\t\t\t";
                $champform .= "'application/x-pdf',\n\t\t\t\t\t";
                $champform .= "],\n\t\t\t\t\t";
                $champform .= "'mimeTypesMessage' => 'Veuillez télécharger un document PDF valide',\n\t\t\t\t";
                $champform .= "])\n\t\t\t";
                $champform .= "],\n\t\t";
                $champform .= "]";

                while (!feof($handleChampForm))
                {
                    $bufferchampform = fgets($handleChampForm);
                    $textechampform .= str_replace("'$intitule'", $champform, $bufferchampform);
                }

                $handleChampForm = fopen($formtype, "w+");
                fwrite($handleChampForm, $textechampform);
                fclose($handleChampForm);
            }
            // ---- CHAMP

        }
        // ------ Ecrire dans le form concerne

        // ****** Ecriture dans le Controller et Form concerne *******
    }
}