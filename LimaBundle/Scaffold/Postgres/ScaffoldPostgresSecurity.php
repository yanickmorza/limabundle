<?php 

namespace App\limabundle\LimaBundle\Scaffold\Postgres;

class ScaffoldPostgresSecurity
{
    public function genererPostgresSecurity($objet, $role, $securite, $namespace)
    {
        // *** Préparer l'écriture dans le Controller ****
        if ($namespace !== null) {
            $path_controller = "../src/Controller/".$namespace;
        } 
        else {
            $path_controller = "../src/Controller";
        }          
	    	$texteUse = "";
	        $texte = "";
            $use = "";
            $securityclass = "";
            $securityfunction = "";
                                      
            $mapper = $path_controller."/".ucfirst($objet)."Controller.php";
            // *** Préparer l'écriture dans le Controller ****

            
            if (file_exists($mapper)) {
                
                // ---- Placer les USE : Security ou IsGranted ----
                if ($securite == "class") {		    
                        $use .= "use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;\n";
                        $use .= "/*** Use ***/";
                        $securityclass .= "/**\n";
                        $securityclass .= "* @IsGranted(\"$role\", message=\"Désolé mais vous n'avez pas d'accès à cette page !\")\n";
                        $securityclass .= "*/";
                        
                            // ---- USE
                            $handleUse = fopen($mapper, "r");
                            if ($handleUse) {
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
                            
                            // --- SECURITY
                            $handle = fopen($mapper, "r");
                            if ($handle) {
                                while (!feof($handle))
                                {
                                    $buffer = fgets($handle);
                                    $texte .= str_replace("/*** SecurityClass ***/", $securityclass, $buffer);
                                }
                                        $handle = fopen($mapper, "w+");
                                        fwrite($handle, $texte);
                                        fclose($handle);
                            }
                            // --- SECURITY
                                            
                }
                elseif ($securite == "function") {		    
                        $use .= "use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;\n";
                        $use .= "/*** Use ***/";
                        $securityfunction .= "* @Security(\"is_granted('$role')\", message=\"Désolé mais vous n'avez pas d'accès à cette page !\")";

                            // ---- USE
                            $handleUse = fopen($mapper, "r");
                            if ($handleUse) {
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

                            // --- SECURITY
                            $handle = fopen($mapper, "r");
                            if ($handle) {
                                while (!feof($handle))
                                {
                                    $buffer = fgets($handle);
                                    $texte .= str_replace("* /*** SecurityFunction ***", $securityfunction, $buffer);
                                }
                                        $handle = fopen($mapper, "w+");
                                        fwrite($handle, $texte);
                                        fclose($handle);
                            }
                            // --- SECURITY
                                            
                }
                else {		    
                        $use .= "";
                        $securityclass .= "";
                        $securityfunction .= "";	    	            	
                }
			    // ---- Placer les USE : Security ou IsGranted ----
            }     
    }
}