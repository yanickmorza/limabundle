<?php

namespace App\LimaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\LimaBundle\Scaffold\Postgres\UtilitairePostgresDatabase;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use App\LimaBundle\Scaffold\Postgres\ScaffoldPostgresAuthUser;
use App\LimaBundle\Scaffold\Postgres\ScaffoldPostgresControleur;
use App\LimaBundle\Scaffold\Postgres\ScaffoldPostgresEntity;
use App\LimaBundle\Scaffold\Postgres\ScaffoldPostgresEnvironnement;
use App\LimaBundle\Scaffold\Postgres\ScaffoldPostgresExtension;
use App\LimaBundle\Scaffold\Postgres\ScaffoldPostgresForm;
use App\LimaBundle\Scaffold\Postgres\ScaffoldPostgresRelation;
use App\LimaBundle\Scaffold\Postgres\ScaffoldPostgresRepository;
use App\LimaBundle\Scaffold\Postgres\ScaffoldPostgresSecurity;
use App\LimaBundle\Scaffold\Postgres\ScaffoldPostgresSwiftMailerFunction;
use App\LimaBundle\Scaffold\Postgres\ScaffoldPostgresSwiftMailerYaml;
use App\LimaBundle\Scaffold\Postgres\ScaffoldPostgresTestEntity;
use App\LimaBundle\Scaffold\Postgres\ScaffoldPostgresVue;

class LimaController extends AbstractController
{
    /**
     * @Route("/dbindex", name="index", methods={"GET","POST"})
     */
    public function index(Request $request, UtilitairePostgresDatabase $utilitaireDatabase): Response
    {
        $session = new Session();

        if ($request->request->get('basedonnee')) {
            $session->set('database', $request->request->get('basedonnee'));
            $db = $session->get('database');
            $driver = $session->get('driver');
        } 
        else {
            $db = $session->get('database');
            $driver = $session->get('driver');
        }

        $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
        $twig = new Environment($loader);

        return new Response($twig->render('index.html.twig', [
            'headtitle' => 'Lima - ' . $db,
            'listetables' => $utilitaireDatabase->listerTables(),
            'affichebasedonnee' => $db,
            'listedatabases' => $utilitaireDatabase->listerDatabases(),
            'driver' => $driver
        ]));
    }

    /**
     * @Route("/swiftmailer", name="swiftmailer", methods={"GET","POST"})
     */
    public function swiftmailer(Request $request, UtilitairePostgresDatabase $utilitaireDatabase): Response
    {
        $session = new Session();
        $db = $session->get('database');

        $scaffoldPostgresSwiftMailerYaml = new ScaffoldPostgresSwiftMailerYaml;
        $scaffoldPostgresSwiftMailerFunction = new ScaffoldPostgresSwiftMailerFunction;
        $scaffoldPostgresSwiftMailerYaml = new ScaffoldPostgresSwiftMailerYaml;
        
        if ($request->request->get('_token') == 'swiftmailerYaml') {

            $transport = trim($request->request->get('transport', null, true));
            $encryption = trim($request->request->get('encryption', null, true));
            $auth_mode = trim($request->request->get('auth_mode', null, true));
            $host = trim($request->request->get('host', null, true));
            $port = trim($request->request->get('port', null, true));
            $username = trim($request->request->get('username', null, true));
            $password = trim($request->request->get('password', null, true)); 

            $scaffoldPostgresSwiftMailerYaml->swiftMailerPostgresYaml($transport, $encryption, $auth_mode, $host, $port, $username, $password);
            
            $this->addFlash('success', 'Le fichier "swiftmailer.yaml" a été regénéré avec succès');

            return $this->redirectToRoute('swiftmailer');
        } 
        elseif ($request->request->get('_token') == 'swiftmailerFunction') {

            $namespace = trim($request->request->get('namespace', null, true));
            $options = $request->request->get('options', null, true);

            foreach ($options as $objet) {
                $scaffoldPostgresSwiftMailerFunction->swiftMailerPostgresFunction($namespace, $objet);
            }

            $this->addFlash('success', 'L\'environement pour l\'envoi de mail a été créé avec succès');

            return $this->redirectToRoute('swiftmailer');

        }
        elseif ($request->request->get('_token') == 'supprimerswiftmailerYaml') {

            $scaffoldPostgresSwiftMailerYaml->supprimerSwiftMailerPostgresClass();

            $this->addFlash('success', 'L\'environement pour l\'envoi de mail a été supprimé avec succès');

            return $this->redirectToRoute('swiftmailer');
        }

        $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
        $twig = new Environment($loader);

        return new Response($twig->render('swiftmailer.html.twig', [
            'headtitle' => 'Configurer swiftmailer dans ' . $db,
            'titreyaml' => 'Enregistrer la configuration dans le fichier swiftmailer.yaml',
            'titrefunction' => 'Enregistrer la fonctionnalité dans un controller',
            'listetables' => $utilitaireDatabase->listerTables(),
            'affichebasedonnee' => $db
        ]));
    }

    /**
     * @Route("/console", name="console", methods={"GET","POST"})
     */
    public function console(Request $request, KernelInterface $kernel): Response
    {
        $session = new Session();
        $db = $session->get('database');

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
        $twig = new Environment($loader);

        if ($request->request->get('_token') == 'console') {

            $commande = trim($request->request->get('commande', null, true));

            $input = new ArrayInput([
                'command' => $commande,
            ]);

            $output = new BufferedOutput();
            $application->run($input, $output);
            $content = $output->fetch();

            return new Response($twig->render('console.html.twig', [
                'affichebasedonnee' => $db,
                'output' => $content
            ]));
        }

        return new Response($twig->render('console.html.twig', [
            'headtitle' => 'Exécuter une commande dans ' . $db,
            'affichebasedonnee' => $db,
            'output' => 'cache:clear | list | asset:install'
        ]));
    }

    /**
     * @Route("/authsecurite", name="authsecurite", methods={"GET","POST"})
     */
    public function authsecurite(Request $request, UtilitairePostgresDatabase $utilitaireDatabase): Response
    {
        $session = new Session();
        $db = $session->get('database');

        $scaffoldPostgresSecurity = new ScaffoldPostgresSecurity;
        $scaffoldPostgresAuthUser = new ScaffoldPostgresAuthUser;

        if ($request->request->get('_token') == 'generersecurite') {

            $options = $request->request->get('options', null, true);
            $namespace = $request->request->get('namespace', null, true);
            $securite = $request->request->get('securite', null, true);
            $role = $request->request->get('role', null, true);

            foreach ($options as $objet) {
                $scaffoldPostgresSecurity->genererPostgresSecurity($objet, $role, $securite, $namespace);
            }

            $this->addFlash('success', 'La sécurité ' . $securite . ' a été généré avec succès');

            return $this->redirectToRoute('authsecurite');
            
        } 
        elseif ($request->request->get('_token') == 'authentification') {

            $options = $request->request->get('options', null, true);
            $namespace = $request->request->get('namespace', null, true);
            $authuser = $request->request->get('authuser', null, true);

            if ($authuser == "OUI") {
                foreach ($options as $objet) {
                    $scaffoldPostgresAuthUser->genererPostgresAuthuser($objet, $authuser, $namespace);
                }
                $this->addFlash('success', 'La création de l\'interface d\'authentification avec la table ' . $objet . ' a été un succès');
            }

            if ($authuser == "SUPPRIMER") {
                foreach ($options as $objet) {
                    $scaffoldPostgresAuthUser->supprimerPostgresAuthuser($objet, $namespace);
                }
                $this->addFlash('success', 'La suppression de l\'interface d\'authentification a été un succès');
            }

            return $this->redirectToRoute('authsecurite');
        }

        $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
        $twig = new Environment($loader);

        return new Response($twig->render('authsecurite.html.twig', [
            'headtitle' => 'Authentification et Sécurité dans ' . $db,
            'listedatabases' => $utilitaireDatabase->listerDatabases(),
            'listetables' => $utilitaireDatabase->listerTables(),
            'affichebasedonnee' => $db
        ]));
    }

    /**
     * @Route("/genererunerelation", name="genererunerelation", methods={"GET","POST"})
     */
    public function genererunerelation(Request $request, UtilitairePostgresDatabase $utilitaireDatabase): Response
    {
        $session = new Session();
        $db = $session->get('database');
        $scaffoldPostgresRelation = new ScaffoldPostgresRelation;

        if ($request->request->get('_token') == 'enregistrerrelation') {

            $options = $request->request->get('options', null, true);
            $namespace = $request->request->get('namespace', null, true);
            $relation = $request->request->get('relation', null, true);
            $othernamespace = $request->request->get('othernamespace', null, true);

            foreach ($options as $option) {
                $scaffoldPostgresRelation->genererPostgresRelation($option, $namespace, $relation, $othernamespace);
            }

            $this->addFlash('success', 'La relation entre ces tables a été un succès');

            return $this->redirectToRoute('genererunerelation');
        }

        $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
        $twig = new Environment($loader);

        return new Response($twig->render('genererunerelation.html.twig', [
            'headtitle' => 'Enregistrer une relation dans ' . $db,
            'listedatabases' => $utilitaireDatabase->listerDatabases(),
            'listetables' => $utilitaireDatabase->listerTables(),
            'affichebasedonnee' => $db
        ]));
    }

    /**
     * @Route("/supprimeruncrud", name="supprimeruncrud", methods={"GET","POST"})
     */
    public function supprimeruncrud(Request $request, UtilitairePostgresDatabase $utilitaireDatabase): Response
    {
        $scaffoldPostgresControleur = new ScaffoldPostgresControleur;
        $scaffoldPostgresEntity = new ScaffoldPostgresEntity;
        $scaffoldPostgresRepository = new ScaffoldPostgresRepository;
        $scaffoldPostgresForm = new ScaffoldPostgresForm;
        $scaffoldPostgresVue = new ScaffoldPostgresVue;
        $scaffoldPostgresTestEntity = new ScaffoldPostgresTestEntity;
        $scaffoldPostgresExtension = new ScaffoldPostgresExtension;

        if ($request->request->get('_token') == 'supprimer') {

            $options = $request->request->get('options', null, true);
            $namespace = $request->request->get('namespace', null, true);

            foreach ($options as $option) {

                $scaffoldPostgresControleur->supprimerPostgresControleur($option, $namespace);
                $scaffoldPostgresEntity->supprimerPostgresEntity($option, $namespace);
                $scaffoldPostgresRepository->supprimerPostgresRepository($option, $namespace);
                $scaffoldPostgresForm->supprimerPostgresForm($option, $namespace);
                $scaffoldPostgresVue->supprimerPostgresVue($option, $namespace);
                $scaffoldPostgresTestEntity->supprimerPostgresTestEntity($option, $namespace);
                $scaffoldPostgresExtension->supprimerPostgresExtension($option, $namespace);

                $this->addFlash('success', 'La suppression du SCRUD ' . $option . ' a été un succès');
            }

            return $this->redirectToRoute('supprimeruncrud');
        }

        // ---- Supprimer repertoire ---
        if ($request->request->get('_token') == 'supprimerrepertoire') {

            $repertoire = $request->request->get('repertoire', null, true);
            $tables = $request->request->get('tables', null, true);

            $pathController = "../src/Controller/" . $repertoire;
            $pathEntity = "../src/Entity/" . $repertoire;
            $pathForm = "../src/Form/" . $repertoire;
            $pathModel = "../src/Model/" . $repertoire;
            $pathRepository = "../src/Repository/" . $repertoire;
            $pathTwig = "../src/Twig/" . $repertoire;
            $pathTemplate = "../templates/" . $repertoire;
            $pathTest = "../tests/Entity/" . $repertoire;

            if (is_dir($pathController) || is_dir($pathEntity) || is_dir($pathForm) || is_dir($pathModel) || is_dir($pathRepository) || is_dir($pathTwig) || is_dir($pathTemplate) || is_dir($pathTest)) {

                array_map('unlink', glob($pathController . "/*"));
                @rmdir($pathController);

                array_map('unlink', glob($pathEntity . "/*"));
                @rmdir($pathEntity);

                array_map('unlink', glob($pathForm . "/*"));
                @rmdir($pathForm);

                array_map('unlink', glob($pathModel . "/*"));
                @rmdir($pathModel);

                array_map('unlink', glob($pathRepository . "/*"));
                @rmdir($pathRepository);

                array_map('unlink', glob($pathTwig . "/*"));
                @rmdir($pathTwig);

                foreach ($tables as $table) {
                    array_map('unlink', glob($pathTemplate . "/" . $table . "/*"));
                    @rmdir($pathTemplate . "/" . $table);
                }
                @rmdir($pathTemplate);

                array_map('unlink', glob($pathTest . "/*"));
                @rmdir($pathTest);
            }
        }
        // ---- Supprimer repertoire ---

        $session = new Session();
        $db = $session->get('database');

        $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
        $twig = new Environment($loader);

        return new Response($twig->render('supprimeruncrud.html.twig', [
            'headtitle' => 'Supprimer un SCRUD dans ' . $db,
            'listedatabases' => $utilitaireDatabase->listerDatabases(),
            'listetables' => $utilitaireDatabase->listerTables(),
            'affichebasedonnee' => $db
        ]));
    }

    /**
     * @Route("/genereruncrud", name="genereruncrud", methods={"GET","POST"})
     */
    public function genereruncrud(Request $request, UtilitairePostgresDatabase $utilitaireDatabase): Response
    {
        $scaffoldPostgresControleur = new ScaffoldPostgresControleur;
        $scaffoldPostgresEntity = new ScaffoldPostgresEntity;
        $scaffoldPostgresRepository = new ScaffoldPostgresRepository;
        $scaffoldPostgresForm = new ScaffoldPostgresForm;
        $scaffoldPostgresVue = new ScaffoldPostgresVue;
        $scaffoldPostgresTestEntity = new ScaffoldPostgresTestEntity;
        $scaffoldPostgresExtension = new ScaffoldPostgresExtension;

        if ($request->request->get('_token') == 'generer') {

            $vue = $request->request->get('vue', null, true);
            $filtre = $request->request->get('filtre', null, true);
            $options = $request->request->get('options', null, true);
            $namespace = $request->request->get('namespace', null, true);

            foreach ($options as $option) {

                $scaffoldPostgresControleur->genererPostgresControleur($option, $vue, $namespace);
                $scaffoldPostgresEntity->genererPostgresEntity($option, $namespace);
                $scaffoldPostgresRepository->genererPostgresRepository($option, $namespace);
                $scaffoldPostgresForm->genererPostgresForm($option, $namespace);
                $scaffoldPostgresVue->genererPostgresVue($option, $vue, $namespace);
                $scaffoldPostgresTestEntity->genererPostgresTestEntity($option, $namespace);
                $scaffoldPostgresExtension->genererPostgresExtension($option, $filtre, $namespace);

                $this->addFlash('success', 'La création du SCRUD ' . $option . ' a été un succès');
            }

            return $this->redirectToRoute('genereruncrud');
        }

        $session = new Session();
        $db = $session->get('database');

        $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
        $twig = new Environment($loader);

        return new Response($twig->render('genereruncrud.html.twig', [
            'headtitle' => 'Générer un SCRUD dans ' . $db,
            'listedatabases' => $utilitaireDatabase->listerDatabases(),
            'listetables' => $utilitaireDatabase->listerTables(),
            'affichebasedonnee' => $db
        ]));
    }

    /**
     * @Route("/basesettables", name="basesettables", methods={"GET","POST"})
     */
    public function basesettables(Request $request, UtilitairePostgresDatabase $utilitaireDatabase): Response
    {
        $session = new Session();
        $scaffoldPostgresEnvironnement = new ScaffoldPostgresEnvironnement();

        // ---- Liste database et table ----
        if ($request->request->get('basedonnee')) {
            $basedonnee = $request->request->get('basedonnee');
            $session->set('database', $basedonnee);
            $db = $session->get('database');

            $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
            $twig = new Environment($loader);

            return new Response($twig->render('basesettables.html.twig', [
                'headtitle' => 'Bases de données et Tables dans ' . $db,
                'listedatabases' => $utilitaireDatabase->listerDatabases(),
                'listetables' => $utilitaireDatabase->listerTables(),
                'affichebasedonnee' => $db
            ]));
        } elseif ($session->get('database')) {

            // ----- Afficher SQL Table ----
            if ($request->request->get('_token') == 'champdata') {

                $champdatatable = $request->request->get('champdatatable', null, true);
                $db = $session->get('database');

                $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                $twig = new Environment($loader);

                return new Response($twig->render('basesettables.html.twig', [
                    'headtitle' => 'Bases de données et Tables dans ' . $db,
                    'champdatatable' => $utilitaireDatabase->findChampDataTable($champdatatable),
                    'listedatabases' => $utilitaireDatabase->listerDatabases(),
                    'listetables' => $utilitaireDatabase->listerTables(),
                    'affichebasedonnee' => $db
                ]));
            }
            // ----- Afficher SQL Table ----

            // --- Generer environnement ---
            if ($request->request->get('_token') == 'environnement') {
                $scaffoldPostgresEnvironnement->envDoctrinePostgresYaml();
                $this->addFlash('success', 'Le nouvel environnement a été créé avec succès');
                return $this->redirectToRoute('basesettables');
            }
            // --- Generer environnement ---

            // ---- Executer requete SQL ---
            if ($request->request->get('_token') == 'requete') {
                $sql = $request->request->get('sql', null, true);
                $utilitaireDatabase->executerSql($sql);
                $this->addFlash('success', 'L\'exécution de la requête a été un succès');
                return $this->redirectToRoute('basesettables');
            }
            // ---- Executer requete SQL ---

            // ---- Afficher ALTER Table ---
            if ($request->request->get('_token') == 'alterdata') {
                $alterdatatable = $request->request->get('alterdatatable', null, true);
                $db = $session->get('database');

                $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                $twig = new Environment($loader);

                return new Response($twig->render('basesettables.html.twig', [
                    'headtitle' => 'Bases de données et Tables dans ' . $db,
                    'champdatatable' => $utilitaireDatabase->alterTableDb($alterdatatable),
                    'listedatabases' => $utilitaireDatabase->listerDatabases(),
                    'listetables' => $utilitaireDatabase->listerTables(),
                    'affichebasedonnee' => $db
                ]));
            }
            // ---- Afficher ALTER Table ---

            // --- Supprimer les tables ----
            if ($request->request->get('_token') == 'deletetables') {
                $db = $session->get('database');

                $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                $twig = new Environment($loader);

                return new Response($twig->render('basesettables.html.twig', [
                    'headtitle' => 'Bases de données et Tables dans ' . $db,
                    'champdatatable' => $utilitaireDatabase->deleteAllTableDb(),
                    'listedatabases' => $utilitaireDatabase->listerDatabases(),
                    'listetables' => $utilitaireDatabase->listerTables(),
                    'affichebasedonnee' => $db
                ]));
            }
            // --- Supprimer les tables ----

            // ------ Roles et Users -------
            if ($request->request->get('_token') == 'roleuser') {
                $db = $session->get('database');

                $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                $twig = new Environment($loader);

                return new Response($twig->render('basesettables.html.twig', [
                    'headtitle' => 'Bases de données et Tables dans ' . $db,
                    'champdatatable' => $utilitaireDatabase->creerTableRoleUser(),
                    'listedatabases' => $utilitaireDatabase->listerDatabases(),
                    'listetables' => $utilitaireDatabase->listerTables(),
                    'affichebasedonnee' => $db
                ]));
            }
            // ------ Roles et Users -------

            // ------ Table messages -------
            if ($request->request->get('_token') == 'message') {
                $db = $session->get('database');

                $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                $twig = new Environment($loader);

                return new Response($twig->render('basesettables.html.twig', [
                    'headtitle' => 'Bases de données et Tables dans ' . $db,
                    'champdatatable' => $utilitaireDatabase->creerFormEnvoiMessage(),
                    'listedatabases' => $utilitaireDatabase->listerDatabases(),
                    'listetables' => $utilitaireDatabase->listerTables(),
                    'affichebasedonnee' => $db
                ]));
            }
            // ------ Table messages -------

            // ---- Renommer database ------
            if ($request->request->get('_token') == 'renamebase') {
                $db = $session->get('database');

                $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                $twig = new Environment($loader);

                return new Response($twig->render('basesettables.html.twig', [
                    'headtitle' => 'Bases de données et Tables dans ' . $db,
                    'champdatatable' => $utilitaireDatabase->RenameDatabase(),
                    'listedatabases' => $utilitaireDatabase->listerDatabases(),
                    'listetables' => $utilitaireDatabase->listerTables(),
                    'affichebasedonnee' => $db
                ]));
            }
            // ---- Renommer database ------

            // --- Préparer Exportation ----
            if ($request->request->get('_token') == 'exportertable') {
                $db = $session->get('database');

                $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                $twig = new Environment($loader);

                return new Response($twig->render('basesettables.html.twig', [
                    'headtitle' => 'Bases de données et Tables dans ' . $db,
                    'exportertable' => $request->request->get('_token'),
                    'champdatatable' => $utilitaireDatabase->exporterTables(),
                    'listedatabases' => $utilitaireDatabase->listerDatabases(),
                    'listetables' => $utilitaireDatabase->listerTables(),
                    'affichebasedonnee' => $db
                ]));
            }
            // --- Préparer Exportation ----

            // --- Export Table Database ---
            if ($request->request->get('_token') == 'exporterrequete') {

                $db = $session->get('database');

                // --- Creer le repertoire sql
                $path_sql = "../var/tmp/sql/";
                if (!is_dir($path_sql)) {
                    mkdir($path_sql, 0755, true);
                }

                $fichier = "../var/tmp/sql/" . $db . ".sql";
                $contenu = $request->request->get('champdatatable');

                fopen($fichier, "w+");
                file_put_contents($fichier, $contenu);
            }
            // --- Export Table Database ---

            // -- Affichage BasesEtTables --
            $basedonnee = $session->get('database');
            $session->set('database', $basedonnee);
            $db = $session->get('database');

            $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
            $twig = new Environment($loader);

            return new Response($twig->render('basesettables.html.twig', [
                'headtitle' => 'Bases de données et Tables dans ' . $db,
                'listedatabases' => $utilitaireDatabase->listerDatabases(),
                'listetables' => $utilitaireDatabase->listerTables(),
                'affichebasedonnee' => $db
            ]));
            // -- Affichage BasesEtTables --
        } else {
            // --- Creer le repertoire tmp
            $path_cache = "../var/tmp/";
            if (!is_dir($path_cache)) {
                mkdir($path_cache, 0755, true);
            }
            // --- Creer le repertoire tmp

            // -- Affichage BasesEtTables --
            $db = $session->get('database');
            $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
            $twig = new Environment($loader);

            return new Response($twig->render('basesettables.html.twig', [
                'headtitle' => 'Bases de données et Tables dans ' . $db,
                'listedatabases' => $utilitaireDatabase->listerDatabases(),
                'listetables' => $utilitaireDatabase->listerTables(),
                'affichebasedonnee' => $db
            ]));
            // -- Affichage BasesEtTables --
        }
        // ---- Liste database et table ----
    }
}
