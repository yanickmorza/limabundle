<?php

namespace App\limabundle\LimaBundle\Scaffold\Postgres;

use Symfony\Component\HttpFoundation\Session\Session;

class ScaffoldPostgresControleur
{
    // ---- Generer un Controller ----
    public function genererPostgresControleur($objet, $vue, $namespace)
    {
        $session = new Session();
        $db = $session->get('database');

        if ($namespace !== null) {
            @mkdir("../src/Controller/" . $namespace, 0755, true);
            $path_controller = "../src/Controller/" . $namespace . "/" . ucfirst($objet) . "Controller.php";
            $nameSpace = str_replace("/", "\\", $namespace);
        } else {
            if (!is_dir("../src/Controller/")) {
                mkdir("../src/Controller/", 0755, true);
                $path_controller = "../src/Controller/" . ucfirst($objet) . "Controller.php";
                $nameSpace = "";
            } else {
                $path_controller = "../src/Controller/" . ucfirst($objet) . "Controller.php";
                $nameSpace = "";
            }
        }

        // ---- Preparation des variables pour ecriture ----
        $Objet = ucfirst($objet);
        $ObjetCreate = $Objet . "Controller";
        $ObjetRepository = $Objet . "Repository";
        $ObjetType = $Objet . "Type";
        $nameIndex = $objet . "_index_" . $db;
        $nameEdit  = $objet . "_edit_" . $db;
        $nameDelete = $objet . "_delete_" . $db;
        $nameTruncate = $objet . "_truncate_" . $db;
        $nameNew  = $objet . "_new_" . $db;
        $nameUploader = $objet . "_uploader_" . $db;
        $nameExporter = $objet . "_exporter_" . $db;

        if ($namespace !== null) {
            $nameSpace = str_replace("/", "\\", $namespace);
            $reposi =  "App\Repository$nameSpace\\$ObjetRepository";
            $entity =  "App\Entity$nameSpace\\$Objet";
            $form   =  "App\Form$nameSpace\\$ObjetType";
            $render = "$namespace/";
        } else {
            $nameSpace = "";
            $reposi =  "App\Repository\\$ObjetRepository";
            $entity =  "App\Entity\\$Objet";
            $form   =  "App\Form\\$ObjetType";
            $render = "";
        }
        // ---- Preparation des variables pour ecriture ----

        // ----- 1 vue cochée -----
        if ($vue == 1) {
            fopen($path_controller, "w+");
            $texte_controller = "<?php

namespace App\Controller$nameSpace;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use $entity;
use $form;
use $reposi;
/*** Use ***/

/*** SecurityClass ***/
class $ObjetCreate extends AbstractController
{
    /**
    * @Route(\"/$objet\", name=\"$nameIndex\", methods={\"GET\",\"POST\"})
    * /*** SecurityFunction ***
    */
    public function index(Request \$request, $ObjetRepository \$liste/**/): Response
    {  	
        \$objet = new $Objet();       
        \$form = \$this->createForm($ObjetType::class, \$objet);
        \$form->handleRequest(\$request);
        
        if (\$form->isSubmitted() && \$form->isValid()) {
            /***/
            \$entityManager = \$this->getDoctrine()->getManager('$db');
            \$entityManager->persist(\$objet);
            \$entityManager->flush();
            
            \$this->addFlash('success', 'L\'enregistrement a été un succès');
            
            return \$this->redirectToRoute('$nameIndex');
        }
        
        return \$this->render('$render$objet/$objet.html.twig', [
            'titre' => 'Liste des $objet',
            'listes' => \$liste->findAll(),
            'form' => \$form->createView(),
            'edition' => \$objet->getId() !== null,
            'countliste' => count(\$liste->findAll())
        ]);
    }

    /**
    * @Route(\"/$objet/{id}/edit\", name=\"$nameEdit\", methods={\"GET\",\"POST\"})
    * /*** SecurityFunction ***
    */
    public function edit(Request \$request, $Objet \$objet, $ObjetRepository \$liste/**/): Response
    {	
        \$form = \$this->createForm($ObjetType::class, \$objet);
        \$form->handleRequest(\$request);

        if (\$form->isSubmitted() && \$form->isValid()) {
            /****/
            \$this->getDoctrine()->getManager('$db')->flush();          
            \$this->addFlash('success', 'La modification a été un succès');
            
            return \$this->redirectToRoute('$nameIndex');
        }

        if (\$this->isCsrfTokenValid('edit'.\$objet->getId(), \$request->request->get('_token'))) {

            return \$this->render('$render$objet/$objet.html.twig', [
                'titre' => 'Liste des $objet',
                'listes' => \$liste->findAll(),
                'form' => \$form->createView(),
                'edition' => \$objet->getId() !== null,
                'countliste' => count(\$liste->findAll())
            ]);
        }
        else {
            return \$this->redirectToRoute('$nameIndex');
        }
    }

    /**
    * @Route(\"/$objet/{id}/delete\", name=\"$nameDelete\", methods={\"DELETE\"})
    * /*** SecurityFunction ***
    */
    public function delete(Request \$request, $Objet \$id): Response
    {   	
        if (\$this->isCsrfTokenValid('delete'.\$id->getId(), \$request->request->get('_token'))) {
            \$entityManager = \$this->getDoctrine()->getManager('$db');
            \$entityManager->remove(\$id);
            \$entityManager->flush();          
            
            \$this->addFlash('success', 'La suppression a été un succès');
        }

        return \$this->redirectToRoute('$nameIndex');
    }
  
    /**
    * @Route(\"/$objet/truncate\", name=\"$nameTruncate\", methods={\"GET\",\"POST\"})
    * /*** SecurityFunction ***
    */
    public function truncate(Request \$request, $ObjetRepository \$liste): Response
    {   	
        if (\$this->isCsrfTokenValid('truncate', \$request->request->get('_token'))) {
            
            \$liste->truncateTable();
           
            \$this->addFlash('success', 'L\'exécution de la requête a été un succès');
        }
        
        return \$this->redirectToRoute('$nameIndex');
    }

    /**
    * @Route(\"/$objet/uploader\", name=\"$nameUploader\", methods={\"GET\",\"POST\"})
    * /*** SecurityFunction ***
    */
    public function uploader(Request \$request, $ObjetRepository \$upload): Response
    {
        if (\$this->isCsrfTokenValid('uploader', \$request->request->get('_token'))) {
            \$upload->uploaderDonnee$Objet();
            \$this->addFlash('success', 'L\'enregistrement a été un succès');           
            return \$this->redirectToRoute('$nameIndex');
        }
    }

    /**
    * @Route(\"/$objet/exporter\", name=\"$nameExporter\", methods={\"GET\",\"POST\"})
    * /*** SecurityFunction ***
    */
    public function exporter(Request \$request, $ObjetRepository \$export): Response
	{
        if (\$this->isCsrfTokenValid('exporter', \$request->request->get('_token'))) {

            \$lignes = \$export->exporterDonnee$Objet();

            if (count(\$lignes) > 0 ) {

                \$handle = fopen('php://output', 'w');
                \$d = ','; 
                \$e = '\"';
    
                foreach(\$lignes as \$ligne) {
                    fputcsv(\$handle, \$ligne, \$d, \$e);
                }
    
                fclose(\$handle);
            }

            \$response = new Response();
            \$response->headers->set('Content-Type', 'text/csv; charset=utf-8');
            \$response->headers->set('Content-Disposition', 'attachment; filename=$objet.csv');

            return \$response;
        }
    }
}";
        }
        // ----- 1 vue cochée ----- 

        // ---- 2 vues cochées ---- 
        else {
            fopen($path_controller, "w+");
            $texte_controller = "<?php

namespace App\Controller$nameSpace;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use $entity;
use $form;
use $reposi;
/*** Use ***/

/*** SecurityClass ***/
class $ObjetCreate extends AbstractController
{
    /**
    * @Route(\"/$objet\", name=\"$nameIndex\", methods={\"GET\",\"POST\"})
    * /*** SecurityFunction ***
    */   
    public function index($ObjetRepository \$liste): Response
    {      
        return \$this->render('$render$objet/$objet.html.twig', [
            'titre' => 'Liste des $objet',
            'listes' => \$liste->findAll(),
            'countliste' => count(\$liste->findAll())
        ]);
    }

    /**
    * @Route(\"/$objet/new\", name=\"$nameNew\", methods={\"GET\",\"POST\"})
    * /*** SecurityFunction ***
    */
    public function new(Request \$request/**/): Response
    {	
        \$objet = new $Objet();       
        \$form = \$this->createForm($ObjetType::class, \$objet);
        \$form->handleRequest(\$request);
        
        if (\$form->isSubmitted() && \$form->isValid()) {
            /***/
            \$entityManager = \$this->getDoctrine()->getManager('$db');
            \$entityManager->persist(\$objet);
            \$entityManager->flush();
            
            \$this->addFlash('success', 'L\'enregistrement a été un succès');
            
            return \$this->redirectToRoute('$nameIndex');
        }
        
        return \$this->render('$render$objet/form_$objet.html.twig', [
            'titre' => 'Enregistrer un $objet',          
            'form' => \$form->createView(),
            'edition' => \$objet->getId() !== null
        ]);
    }

    /**
    * @Route(\"/$objet/{id}/edit\", name=\"$nameEdit\", methods={\"GET\",\"POST\"})
    * /*** SecurityFunction ***
    */
    public function edit(Request \$request, $Objet \$objet/**/): Response
    {  	
        \$form = \$this->createForm($ObjetType::class, \$objet);
        \$form->handleRequest(\$request);

        if (\$form->isSubmitted() && \$form->isValid()) {
            /****/
            \$this->getDoctrine()->getManager('$db')->flush();          
            \$this->addFlash('success', 'La modification a été un succès');
            
            return \$this->redirectToRoute('$nameIndex');
        }

        if (\$this->isCsrfTokenValid('edit'.\$objet->getId(), \$request->request->get('_token'))) {

            return \$this->render('$render$objet/form_$objet.html.twig', [
                'titre' => 'Modifier un $objet',
                'form' => \$form->createView(),
                'edition' => \$objet->getId() !== null
            ]);
        }
        else {
            return \$this->redirectToRoute('$nameIndex');
        }
    }

    /**
    * @Route(\"/$objet/{id}/delete\", name=\"$nameDelete\", methods={\"DELETE\"})
    * /*** SecurityFunction ***
    */
    public function delete(Request \$request, $Objet \$id): Response
    {   	
        if (\$this->isCsrfTokenValid('delete'.\$id->getId(), \$request->request->get('_token'))) {
            \$entityManager = \$this->getDoctrine()->getManager('$db');
            \$entityManager->remove(\$id);
            \$entityManager->flush();          
            
            \$this->addFlash('success', 'La suppression a été un succès');
        }

        return \$this->redirectToRoute('$nameIndex');
    }
  
    /**
    * @Route(\"/$objet/truncate\", name=\"$nameTruncate\", methods={\"GET\",\"POST\"})
    * /*** SecurityFunction ***
    */
    public function truncate(Request \$request, $ObjetRepository \$liste): Response
    {
        if (\$this->isCsrfTokenValid('truncate', \$request->request->get('_token'))) {
            
            \$liste->truncateTable();
           
            \$this->addFlash('success', 'L\'exécution de la requête a été un succès');
        }
        
        return \$this->redirectToRoute('$nameIndex');
    }

    /**
    * @Route(\"/$objet/uploader\", name=\"$nameUploader\", methods={\"GET\",\"POST\"})
    * /*** SecurityFunction ***
    */
    public function uploader(Request \$request, $ObjetRepository \$upload): Response
    {
        if (\$this->isCsrfTokenValid('uploader', \$request->request->get('_token'))) {
            \$upload->uploaderDonnee$Objet();
            \$this->addFlash('success', 'L\'enregistrement a été un succès');           
            return \$this->redirectToRoute('$nameIndex');
        }
    }

    /**
    * @Route(\"/$objet/exporter\", name=\"$nameExporter\", methods={\"GET\",\"POST\"})
    * /*** SecurityFunction ***
    */
    public function exporter(Request \$request, $ObjetRepository \$export): Response
	{
        if (\$this->isCsrfTokenValid('exporter', \$request->request->get('_token'))) {

            \$lignes = \$export->exporterDonnee$Objet();

            if (count(\$lignes) > 0 ) {

                \$handle = fopen('php://output', 'w');
                \$d = ','; 
                \$e = '\"';
    
                foreach(\$lignes as \$ligne) {
                    fputcsv(\$handle, \$ligne, \$d, \$e);
                }
    
                fclose(\$handle);
            }

            \$response = new Response();
            \$response->headers->set('Content-Type', 'text/csv; charset=utf-8');
            \$response->headers->set('Content-Disposition', 'attachment; filename=$objet.csv');

            return \$response;
        }
    }
}";
        }
        // ---- 2 vues cochées ----

        file_put_contents($path_controller, $texte_controller);
    }
    // ---- Generer un Controller ----

    // --- Supprimer un Controller ---
    public function supprimerPostgresControleur($objet, $namespace)
    {
        if ($namespace !== null) {
            $path_controller = "../src/Controller/" . $namespace . "/" . ucfirst($objet) . "Controller.php";
        } else {
            $path_controller = "../src/Controller/" . ucfirst($objet) . "Controller.php";
        }

        if (file_exists($path_controller)) {
            unlink($path_controller);
        }
    }
    // --- Supprimer un Controller ---
}
