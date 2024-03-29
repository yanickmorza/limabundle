<?php

namespace App\LimaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\LimaBundle\Scaffold\Postgres\UtilitairePostgresDatabase;
use App\LimaBundle\Scaffold\Mysql\UtilitaireMysqlDatabase;
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
use App\LimaBundle\Scaffold\Postgres\ScaffoldPostgresUploaderFunction;
use App\LimaBundle\Scaffold\Postgres\ScaffoldPostgresVue;
use App\LimaBundle\Scaffold\Mysql\ScaffoldMysqlAuthUser;
use App\LimaBundle\Scaffold\Mysql\ScaffoldMysqlEntity;
use App\LimaBundle\Scaffold\Mysql\ScaffoldMysqlForm;
use App\LimaBundle\Scaffold\Mysql\ScaffoldMysqlRelation;
use App\LimaBundle\Scaffold\Mysql\ScaffoldMysqlRepository;
use App\LimaBundle\Scaffold\Mysql\ScaffoldMysqlTestEntity;
use App\LimaBundle\Scaffold\Mysql\ScaffoldMysqlVue;

class LimaController extends AbstractController
{
    /**
     * @Route("/index", name="index", methods={"GET","POST"})
     */
    public function index(Request $request, UtilitairePostgresDatabase $utilitairePostgresDatabase, UtilitaireMysqlDatabase $utilitaireMysqlDatabase): Response
    {
        $session = new Session();

        if ($session->get('database') == null && $session->get('driver') == null) {
    
            if ($request->request->get('_token') == 'connexiondb') {     
                $session->set('serveur', trim($request->request->get('serveur', null, true)));
                $session->set('port', trim($request->request->get('port', null, true)));
                $session->set('driver', trim($request->request->get('driver', null, true)));
                $session->set('database', trim($request->request->get('database', null, true)));
                $session->set('userdb', trim($request->request->get('userdb', null, true)));
                $session->set('passdb', trim($request->request->get('passdb', null, true)));
                
                return $this->redirectToRoute('index');
            }
            else {
                $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                $twig = new Environment($loader);

                return new Response($twig->render('connexion.html.twig', [
                    'headtitle' => 'Connexion à un serveur SQL',
                    'driver' => ''
                ]));
            }
        }
        else {

            if ($request->request->get('basedonnee')) {
                $session->set('database', $request->request->get('basedonnee'));
                $db = $session->get('database');
                $driver = $session->get('driver');
            } 
            else {
                $db = $session->get('database');
                $driver = $session->get('driver');
            }
    
            if ($driver == 'pgsql') {
                $listetables = $utilitairePostgresDatabase->listerTables();
                $listedatabases = $utilitairePostgresDatabase->listerDatabases();
            }
            else {
                $listetables = $utilitaireMysqlDatabase->listerTables();
                $listedatabases = $utilitaireMysqlDatabase->listerDatabases();
            }
    
            $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
            $twig = new Environment($loader);
    
            return new Response($twig->render('index.html.twig', [
                'headtitle' => 'Lima - ' . $db,
                'listetables' => $listetables,
                'listedatabases' => $listedatabases,
                'affichebasedonnee' => $db,
                'driver' => $driver
            ]));
        }
    }

    /**
     * @Route("/connexion", name="connexion", methods={"GET"})
     */
    public function connexion():Response
    {
        $session = new Session();
        $session->clear();

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/uploader", name="uploader", methods={"GET","POST"})
     */
    public function uploader(Request $request): Response
    {
        $session = new Session();

        if ($session->get('driver')) {

            $db = $session->get('database');
            $driver = $session->get('driver');

            if ($driver == 'pgsql') {
                $utilitairePostgresDatabase = new UtilitairePostgresDatabase;
                $listetables = $utilitairePostgresDatabase->listerTables();
            }
            else {
                $utilitaireMysqlDatabase = new UtilitaireMysqlDatabase;
                $listetables = $utilitaireMysqlDatabase->listerTables();
            }

            $caffoldPostgresUploaderFunction = new ScaffoldPostgresUploaderFunction;

            if ($request->request->get('_token') == 'uploaderFunction') {

                $namespace = trim($request->request->get('namespace', null, true));
                $options = $request->request->all()['options'];
                $intitule = $request->request->get('intitule', null, true);

                    foreach ($options as $objet) {
                        $caffoldPostgresUploaderFunction->uploaderPostgresFunction($namespace, $objet, $intitule);
                    }

                    $this->addFlash('success', 'La fonctionnalité pour uploder un fichier a été créé avec succès');

                    return $this->redirectToRoute('uploader');
            }

            $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
            $twig = new Environment($loader);

            return new Response($twig->render('uploader.html.twig', [
                'headtitle' => 'Configurer un uplaod dans ' . $db,
                'titrefunction' => 'Enregistrer la fonctionnalité pour uploder un fichier',
                'listetables' => $listetables,
                'affichebasedonnee' => $db
            ]));
        }
        else {
            return $this->redirectToRoute('connexion');
        }
    }

    /**
     * @Route("/swiftmailer", name="swiftmailer", methods={"GET","POST"})
     */
    public function swiftmailer(Request $request, UtilitairePostgresDatabase $utilitairePostgresDatabase, UtilitaireMysqlDatabase $utilitaireMysqlDatabase): Response
    {
        $session = new Session();

        if ($session->get('driver')) {

            $db = $session->get('database');
            $driver = $session->get('driver');

            if ($driver == 'pgsql') {
                $listetables = $utilitairePostgresDatabase->listerTables();
            }
            else {
                $listetables = $utilitaireMysqlDatabase->listerTables();
            }

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
                $options = $request->request->all()['options'];

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
                'listetables' => $listetables,
                'affichebasedonnee' => $db
            ]));
        }
        else {
            return $this->redirectToRoute('connexion');
        }
    }

    /**
     * @Route("/console", name="console", methods={"GET","POST"})
     */
    public function console(Request $request, KernelInterface $kernel): Response
    {
        $session = new Session();

        $db = $session->get('database');

        if ($session->get('driver')) {

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
        else {
            return $this->redirectToRoute('connexion');
        }
    }

    /**
     * @Route("/authsecurite", name="authsecurite", methods={"GET","POST"})
     */
    public function authsecurite(Request $request, UtilitairePostgresDatabase $utilitairePostgresDatabase, UtilitaireMysqlDatabase $utilitaireMysqlDatabase): Response
    {
        $session = new Session();

        if ($session->get('driver')) {

            $db = $session->get('database');
            $driver = $session->get('driver');

            if ($driver == 'pgsql') {
                $listetables = $utilitairePostgresDatabase->listerTables();
                $listedatabases = $utilitairePostgresDatabase->listerDatabases();
                $scaffoldPostgresAuthUser = new ScaffoldPostgresAuthUser;
            }
            else {
                $listetables = $utilitaireMysqlDatabase->listerTables();
                $listedatabases = $utilitaireMysqlDatabase->listerDatabases();
                $scaffoldMysqlAuthUser = new ScaffoldMysqlAuthUser;
            }

            // Commun à Mysql et PostgreSQL
            $scaffoldPostgresSecurity = new ScaffoldPostgresSecurity;

            if ($request->request->get('_token') == 'generersecurite') {

                $options = $request->request->all()['options'];
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

                $options = $request->request->all()['options'];
                $namespace = $request->request->get('namespace', null, true);
                $authuser = $request->request->get('authuser', null, true);

                if ($authuser == "OUI") {
                    foreach ($options as $objet) {

                        if ($driver == 'pgsql') {
                            $scaffoldPostgresAuthUser->genererPostgresAuthuser($objet, $authuser, $namespace);
                        }
                        else {
                            $scaffoldMysqlAuthUser->genererMysqlAuthuser($objet, $authuser, $namespace);
                        }
                    }
                    $this->addFlash('success', 'La création de l\'interface d\'authentification avec la table ' . $objet . ' a été un succès');
                }

                if ($authuser == "SUPPRIMER") {
                    foreach ($options as $objet) {

                        if ($driver == 'pgsql') {
                            $scaffoldPostgresAuthUser->supprimerPostgresAuthuser($objet, $namespace);
                        }
                        else {
                            $scaffoldMysqlAuthUser->supprimerMysqlAuthuser($objet, $namespace);
                        }
                    }
                    $this->addFlash('success', 'La suppression de l\'interface d\'authentification a été un succès');
                }

                return $this->redirectToRoute('authsecurite');
            }

            $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
            $twig = new Environment($loader);

            return new Response($twig->render('authsecurite.html.twig', [
                'headtitle' => 'Authentification et Sécurité dans ' . $db,
                'listetables' => $listetables,
                'listedatabases' => $listedatabases,
                'affichebasedonnee' => $db
            ]));
        }
        else {
            return $this->redirectToRoute('connexion');
        }
    }

    /**
     * @Route("/genererunerelation", name="genererunerelation", methods={"GET","POST"})
     */
    public function genererunerelation(Request $request, UtilitairePostgresDatabase $utilitairePostgresDatabase, UtilitaireMysqlDatabase $utilitaireMysqlDatabase): Response
    {
        $session = new Session();

        if ($session->get('driver')) {

            $db = $session->get('database');
            $driver = $session->get('driver');

            if ($driver == 'pgsql') {
                $listetables = $utilitairePostgresDatabase->listerTables();
                $listedatabases = $utilitairePostgresDatabase->listerDatabases();
                $scaffoldPostgresRelation = new ScaffoldPostgresRelation;
            }
            else {
                $listetables = $utilitaireMysqlDatabase->listerTables();
                $listedatabases = $utilitaireMysqlDatabase->listerDatabases();
                $scaffoldMysqlRelation = new ScaffoldMysqlRelation;
            }

            if ($request->request->get('_token') == 'enregistrerrelation') {

                $options = $request->request->all()['options'];
                $namespace = $request->request->get('namespace', null, true);
                $relation = $request->request->get('relation', null, true);
                $othernamespace = $request->request->get('othernamespace', null, true);

                foreach ($options as $option) {

                    if ($driver == 'pgsql') {
                        $scaffoldPostgresRelation->genererPostgresRelation($option, $namespace, $relation, $othernamespace);
                    }
                    else {
                        $scaffoldMysqlRelation->genererMysqlRelation($option, $namespace, $relation, $othernamespace);
                    }
                }

                $this->addFlash('success', 'La relation entre ces tables a été un succès');

                return $this->redirectToRoute('genererunerelation');
            }

            $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
            $twig = new Environment($loader);

            return new Response($twig->render('genererunerelation.html.twig', [
                'headtitle' => 'Enregistrer une relation dans ' . $db,
                'listetables' => $listetables,
                'listedatabases' => $listedatabases,
                'affichebasedonnee' => $db
            ]));
        }
        else {
            return $this->redirectToRoute('connexion');
        }
    }

    /**
     * @Route("/supprimeruncrud", name="supprimeruncrud", methods={"GET","POST"})
     */
    public function supprimeruncrud(Request $request, UtilitairePostgresDatabase $utilitairePostgresDatabase, UtilitaireMysqlDatabase $utilitaireMysqlDatabase): Response
    {
        $session = new Session();

        if ($session->get('driver')) {

            $db = $session->get('database');
            $driver = $session->get('driver');

            // commun a Mysql et PostgreSql
            $scaffoldPostgresControleur = new ScaffoldPostgresControleur;
            $scaffoldPostgresExtension = new ScaffoldPostgresExtension;

            // PostgreSql
            $scaffoldPostgresEntity = new ScaffoldPostgresEntity;
            $scaffoldPostgresRepository = new ScaffoldPostgresRepository;
            $scaffoldPostgresForm = new ScaffoldPostgresForm;
            $scaffoldPostgresVue = new ScaffoldPostgresVue;
            $scaffoldPostgresTestEntity = new ScaffoldPostgresTestEntity;

            // Mysql
            $scaffoldMysqlEntity = new ScaffoldMysqlEntity;
            $scaffoldMysqlRepository = new ScaffoldMysqlRepository;
            $scaffoldMysqlForm = new ScaffoldMysqlForm;      
            $scaffoldMysqlVue = new ScaffoldMysqlVue;
            $scaffoldMysqlTestEntity = new ScaffoldMysqlTestEntity;
            

            if ($request->request->get('_token') == 'supprimer') {

                $options = $request->request->all()['options'];
                $namespace = $request->request->get('namespace', null, true);

                foreach ($options as $option) {

                    // commun a Mysql et PostgreSql
                    $scaffoldPostgresControleur->supprimerPostgresControleur($option, $namespace);
                    $scaffoldPostgresExtension->supprimerPostgresExtension($option, $namespace);

                    if ($driver == 'pgsql') {
                        $scaffoldPostgresEntity->supprimerPostgresEntity($option, $namespace);
                        $scaffoldPostgresRepository->supprimerPostgresRepository($option, $namespace);
                        $scaffoldPostgresForm->supprimerPostgresForm($option, $namespace);
                        $scaffoldPostgresVue->supprimerPostgresVue($option, $namespace);
                        $scaffoldPostgresTestEntity->supprimerPostgresTestEntity($option, $namespace);
                    }
                    else {
                        $scaffoldMysqlEntity->supprimerMysqlEntity($option, $namespace);
                        $scaffoldMysqlRepository->supprimerMysqlRepository($option, $namespace);
                        $scaffoldMysqlForm->supprimerMysqlForm($option, $namespace);               
                        $scaffoldMysqlVue->supprimerMysqlVue($option, $namespace);
                        $scaffoldMysqlTestEntity->supprimerMysqlTestEntity($option, $namespace);
                    }

                    $this->addFlash('success', 'La suppression du SCRUD ' . $option . ' a été un succès');
                }

                return $this->redirectToRoute('supprimeruncrud');
            }

            // ---- Supprimer repertoire ---
            if ($request->request->get('_token') == 'supprimerrepertoire') {

                $repertoire = $request->request->get('repertoire', null, true);
                $tables = $request->request->all()['tables'];

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
            $driver = $session->get('driver');

            if ($driver == 'pgsql') {
                $listetables = $utilitairePostgresDatabase->listerTables();
                $listedatabases = $utilitairePostgresDatabase->listerDatabases();
            }
            else {
                $listetables = $utilitaireMysqlDatabase->listerTables();
                $listedatabases = $utilitaireMysqlDatabase->listerDatabases();
            }

            $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
            $twig = new Environment($loader);

            return new Response($twig->render('supprimeruncrud.html.twig', [
                'headtitle' => 'Supprimer un SCRUD dans ' . $db,
                'listetables' => $listetables,
                'listedatabases' => $listedatabases,
                'affichebasedonnee' => $db
            ]));
        }
        else {
            return $this->redirectToRoute('connexion');
        }
    }

    /**
     * @Route("/genereruncrud", name="genereruncrud", methods={"GET","POST"})
     */
    public function genereruncrud(Request $request, UtilitairePostgresDatabase $utilitairePostgresDatabase, UtilitaireMysqlDatabase $utilitaireMysqlDatabase): Response
    {
        $session = new Session();

        if ($session->get('driver')) {

            $db = $session->get('database');
            $driver = $session->get('driver');

            // commun a Mysql et PostgreSql
            $scaffoldPostgresControleur = new ScaffoldPostgresControleur;
            $scaffoldPostgresExtension = new ScaffoldPostgresExtension;

            // PostgreSql
            $scaffoldPostgresEntity = new ScaffoldPostgresEntity;
            $scaffoldPostgresForm = new ScaffoldPostgresForm;
            $scaffoldPostgresRepository = new ScaffoldPostgresRepository;
            $scaffoldPostgresVue = new ScaffoldPostgresVue;
            $scaffoldPostgresTestEntity = new ScaffoldPostgresTestEntity;

            // Mysql
            $scaffoldMysqlEntity = new ScaffoldMysqlEntity;
            $scaffoldMysqlForm = new ScaffoldMysqlForm;
            $scaffoldMysqlRepository = new ScaffoldMysqlRepository;
            $scaffoldMysqlVue = new ScaffoldMysqlVue;
            $scaffoldMysqlTestEntity = new ScaffoldMysqlTestEntity;
            

            if ($request->request->get('_token') == 'generer') {

                $vue = $request->request->get('vue', null, true);
                $filtre = $request->request->get('filtre', null, true);
                $options = $request->request->all()['options'];
                $namespace = $request->request->get('namespace', null, true);

                foreach ($options as $option) {

                    // commun a Mysql et PostgreSql
                    if ($request->request->get('controller') == 'controller') {
                        $scaffoldPostgresControleur->genererPostgresControleur($option, $vue, $namespace);
                        $scaffoldPostgresExtension->genererPostgresExtension($option, $filtre, $namespace);
                    }

                    if ($driver == 'pgsql') {   // PostgreSql
                        if ($request->request->get('entity') == 'entity') {
                            $scaffoldPostgresEntity->genererPostgresEntity($option, $namespace);
                        }
                        if ($request->request->get('form') == 'form') {
                            $scaffoldPostgresForm->genererPostgresForm($option, $namespace);
                        }
                        if ($request->request->get('repository') == 'repository') {
                            $scaffoldPostgresRepository->genererPostgresRepository($option, $namespace);
                        }
                        if ($request->request->get('template') == 'template') {
                            $scaffoldPostgresVue->genererPostgresVue($option, $vue, $namespace);
                        }
                        if ($request->request->get('test') == 'test') {
                            $scaffoldPostgresTestEntity->genererPostgresTestEntity($option, $namespace);
                        }
                    }
                    else {  // Mysql
                        if ($request->request->get('entity') == 'entity') {
                            $scaffoldMysqlEntity->genererMysqlEntity($option, $namespace);
                        }
                        if ($request->request->get('form') == 'form') {
                            $scaffoldMysqlForm->genererMysqlForm($option, $namespace);
                        }
                        if ($request->request->get('repository') == 'repository') {
                            $scaffoldMysqlRepository->genererMysqlRepository($option, $namespace);
                        }
                        if ($request->request->get('template') == 'template') {
                            $scaffoldMysqlVue->genererMysqlVue($option, $vue, $namespace);
                        }
                        if ($request->request->get('test') == 'test') {
                            $scaffoldMysqlTestEntity->genererMysqlTestEntity($option, $namespace);
                        }
                    }

                    $this->addFlash('success', 'La création du SCRUD ' . $option . ' a été un succès');
                }

                return $this->redirectToRoute('genereruncrud');
            }

            if ($driver == 'pgsql') {
                $listetables = $utilitairePostgresDatabase->listerTables();
                $listedatabases = $utilitairePostgresDatabase->listerDatabases();
            }
            else {
                $listetables = $utilitaireMysqlDatabase->listerTables();
                $listedatabases = $utilitaireMysqlDatabase->listerDatabases();
            }

            $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
            $twig = new Environment($loader);

            return new Response($twig->render('genereruncrud.html.twig', [
                'headtitle' => 'Générer un SCRUD dans ' . $db,
                'listetables' => $listetables,
                'listedatabases' => $listedatabases,
                'affichebasedonnee' => $db
            ]));
        }
        else {
            return $this->redirectToRoute('connexion');
        }
    }

    /**
     * @Route("/basesettables", name="basesettables", methods={"GET","POST"})
     */
    public function basesettables(Request $request, UtilitairePostgresDatabase $utilitairePostgresDatabase, UtilitaireMysqlDatabase $utilitaireMysqlDatabase): Response
    {
        $session = new Session();

        // --- Creer le repertoire tmp
        $path_cache = "../var/tmp/";
        if (!is_dir($path_cache)) {
            mkdir($path_cache, 0755, true);
        }
        // --- Creer le repertoire tmp

        if ($session->get('driver')) {

            $driver = $session->get('driver');
            $scaffoldPostgresEnvironnement = new ScaffoldPostgresEnvironnement();

            // ---- Liste database et table ----
            if ($request->request->get('basedonnee')) {

                $basedonnee = $request->request->get('basedonnee');

                $session->set('database', $basedonnee);
                $db = $session->get('database');

                if ($driver == 'pgsql') {
                    $listetables = $utilitairePostgresDatabase->listerTables();
                    $listedatabases = $utilitairePostgresDatabase->listerDatabases();
                }
                else {
                    $listetables = $utilitaireMysqlDatabase->listerTables();
                    $listedatabases = $utilitaireMysqlDatabase->listerDatabases();
                }

                $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                $twig = new Environment($loader);

                return new Response($twig->render('basesettables.html.twig', [
                    'headtitle' => 'Bases de données et Tables dans ' . $db,
                    'listetables' => $listetables,
                    'listedatabases' => $listedatabases,
                    'affichebasedonnee' => $db
                ]));
            } 
            elseif ($session->get('database')) {

                // ----- Afficher SQL Table ----
                if ($request->request->get('_token') == 'champdata') {

                    $champdatatable = $request->request->get('champdatatable', null, true);

                    $db = $session->get('database');               

                    if ($driver == 'pgsql') {
                        $listetables = $utilitairePostgresDatabase->listerTables();
                        $listedatabases = $utilitairePostgresDatabase->listerDatabases();
                        $champdatatable = $utilitairePostgresDatabase->findChampDataTable($champdatatable);
                    }
                    else {
                        $listetables = $utilitaireMysqlDatabase->listerTables();
                        $listedatabases = $utilitaireMysqlDatabase->listerDatabases();
                        $champdatatable = $utilitaireMysqlDatabase->findChampDataTable($champdatatable);
                    }

                    $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                    $twig = new Environment($loader);

                    return new Response($twig->render('basesettables.html.twig', [
                        'headtitle' => 'Bases de données et Tables dans ' . $db,
                        'listetables' => $listetables,
                        'listedatabases' => $listedatabases,
                        'champdatatable' => $champdatatable,
                        'affichebasedonnee' => $db
                    ]));
                }
                // ----- Afficher SQL Table ----

                // --- Generer environnement ---
                if ($request->request->get('_token') == 'environnement') {
                    // Commun à Mysql et PostreSQL
                    $scaffoldPostgresEnvironnement->envDoctrinePostgresYaml();
                    $this->addFlash('success', 'Le nouvel environnement a été créé avec succès');

                    return $this->redirectToRoute('basesettables');
                }
                // --- Generer environnement ---

                // ---- Executer requete SQL ---
                if ($request->request->get('_token') == 'requete') {
                    $sql = $request->request->get('sql', null, true);
                    $utilitairePostgresDatabase->executerSql($sql);
                    $this->addFlash('success', 'L\'exécution de la requête a été un succès');
                    return $this->redirectToRoute('basesettables');
                }
                // ---- Executer requete SQL ---

                // ---- Afficher ALTER Table ---
                if ($request->request->get('_token') == 'alterdata') {

                    $alterdatatable = $request->request->get('alterdatatable', null, true);

                    $db = $session->get('database');

                    if ($driver == 'pgsql') {
                        $listetables = $utilitairePostgresDatabase->listerTables();
                        $listedatabases = $utilitairePostgresDatabase->listerDatabases();
                        $champdatatable = $utilitairePostgresDatabase->alterTableDb($alterdatatable);
                    }
                    else {
                        $listetables = $utilitaireMysqlDatabase->listerTables();
                        $listedatabases = $utilitaireMysqlDatabase->listerDatabases();
                        $champdatatable = $utilitaireMysqlDatabase->alterTableDb($alterdatatable);
                    }

                    $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                    $twig = new Environment($loader);

                    return new Response($twig->render('basesettables.html.twig', [
                        'headtitle' => 'Bases de données et Tables dans ' . $db,
                        'listetables' => $listetables,
                        'listedatabases' => $listedatabases,                   
                        'champdatatable' => $champdatatable,
                        'affichebasedonnee' => $db
                    ]));
                }
                // ---- Afficher ALTER Table ---

                // --- Supprimer les tables ----
                if ($request->request->get('_token') == 'deletetables') {

                    $db = $session->get('database');

                    if ($driver == 'pgsql') {
                        $listetables = $utilitairePostgresDatabase->listerTables();
                        $listedatabases = $utilitairePostgresDatabase->listerDatabases();
                        $champdatatable = $utilitairePostgresDatabase->deleteAllTableDb();
                    }
                    else {
                        $listetables = $utilitaireMysqlDatabase->listerTables();
                        $listedatabases = $utilitaireMysqlDatabase->listerDatabases();
                        $champdatatable = $utilitaireMysqlDatabase->deleteAllTableDb();
                    }

                    $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                    $twig = new Environment($loader);

                    return new Response($twig->render('basesettables.html.twig', [
                        'headtitle' => 'Bases de données et Tables dans ' . $db,                
                        'listetables' => $listetables,
                        'listedatabases' => $listedatabases,
                        'champdatatable' => $champdatatable,
                        'affichebasedonnee' => $db
                    ]));
                }
                // --- Supprimer les tables ----

                // ------ Roles et Users -------
                if ($request->request->get('_token') == 'roleuser') {

                    $db = $session->get('database');               

                    if ($driver == 'pgsql') {
                        $listetables = $utilitairePostgresDatabase->listerTables();
                        $listedatabases = $utilitairePostgresDatabase->listerDatabases();
                        $champdatatable = $utilitairePostgresDatabase->creerTableRoleUser();
                    }
                    else {
                        $listetables = $utilitaireMysqlDatabase->listerTables();
                        $listedatabases = $utilitaireMysqlDatabase->listerDatabases();
                        $champdatatable = $utilitaireMysqlDatabase->creerTableRoleUser();
                    }

                    $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                    $twig = new Environment($loader);

                    return new Response($twig->render('basesettables.html.twig', [
                        'headtitle' => 'Bases de données et Tables dans ' . $db,
                        'listetables' => $listetables,                   
                        'listedatabases' => $listedatabases,
                        'champdatatable' => $champdatatable,
                        'affichebasedonnee' => $db
                    ]));
                }
                // ------ Roles et Users -------

                // ------ Table messages -------
                if ($request->request->get('_token') == 'message') {

                    $db = $session->get('database');               

                    if ($driver == 'pgsql') {
                        $listetables = $utilitairePostgresDatabase->listerTables();
                        $listedatabases = $utilitairePostgresDatabase->listerDatabases();
                        $champdatatable = $utilitairePostgresDatabase->creerFormEnvoiMessage();
                    }
                    else {
                        $listetables = $utilitaireMysqlDatabase->listerTables();
                        $listedatabases = $utilitaireMysqlDatabase->listerDatabases();
                        $champdatatable = $utilitaireMysqlDatabase->creerFormEnvoiMessage();
                    }

                    $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                    $twig = new Environment($loader);

                    return new Response($twig->render('basesettables.html.twig', [
                        'headtitle' => 'Bases de données et Tables dans ' . $db,
                        'listetables' => $listetables,                  
                        'listedatabases' => $listedatabases,
                        'champdatatable' => $champdatatable,
                        'affichebasedonnee' => $db
                    ]));
                }
                // ------ Table messages -------

                // ---- Renommer database ------
                if ($request->request->get('_token') == 'renamebase') {

                    $db = $session->get('database');               

                    if ($driver == 'pgsql') {
                        $listetables = $utilitairePostgresDatabase->listerTables();
                        $listedatabases = $utilitairePostgresDatabase->listerDatabases();
                        $champdatatable = $utilitairePostgresDatabase->RenameDatabase();
                    }
                    else {
                        $listetables = $utilitaireMysqlDatabase->listerTables();
                        $listedatabases = $utilitaireMysqlDatabase->listerDatabases();
                        $champdatatable = $utilitaireMysqlDatabase->RenameDatabase();
                    }

                    $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                    $twig = new Environment($loader);

                    return new Response($twig->render('basesettables.html.twig', [
                        'headtitle' => 'Bases de données et Tables dans ' . $db,
                        'listetables' => $listetables,                  
                        'listedatabases' => $listedatabases,
                        'champdatatable' => $champdatatable,
                        'affichebasedonnee' => $db
                    ]));
                }
                // ---- Renommer database ------

                // --- Préparer Exportation ----
                if ($request->request->get('_token') == 'exportertable') {

                    $db = $session->get('database');              

                    if ($driver == 'pgsql') {
                        $listetables = $utilitairePostgresDatabase->listerTables();
                        $listedatabases = $utilitairePostgresDatabase->listerDatabases();
                        $champdatatable = $utilitairePostgresDatabase->exporterTables();
                    }
                    else {
                        $listetables = $utilitaireMysqlDatabase->listerTables();
                        $listedatabases = $utilitaireMysqlDatabase->listerDatabases();
                        $champdatatable = $utilitaireMysqlDatabase->exporterTables();
                    }

                    $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                    $twig = new Environment($loader);

                    return new Response($twig->render('basesettables.html.twig', [
                        'headtitle' => 'Bases de données et Tables dans ' . $db,
                        'exportertable' => $request->request->get('_token'),
                        'listetables' => $listetables,
                        'listedatabases' => $listedatabases,
                        'champdatatable' => $champdatatable,
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

                    $fichier = "../var/tmp/sql/".$driver."_". $db . ".sql";
                    $contenu = $request->request->get('champdatatable');

                    fopen($fichier, "w+");
                    file_put_contents($fichier, $contenu);
                }
                // --- Export Table Database ---

                // -- Affichage BasesEtTables --
                $basedonnee = $session->get('database');
                $session->set('database', $basedonnee);

                $db = $session->get('database');

                if ($driver == 'pgsql') {
                    $listetables = $utilitairePostgresDatabase->listerTables();
                    $listedatabases = $utilitairePostgresDatabase->listerDatabases();
                }
                else {
                    $listetables = $utilitaireMysqlDatabase->listerTables();
                    $listedatabases = $utilitaireMysqlDatabase->listerDatabases();
                }

                $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                $twig = new Environment($loader);

                return new Response($twig->render('basesettables.html.twig', [
                    'headtitle' => 'Bases de données et Tables dans ' . $db,
                    'listetables' => $listetables,
                    'listedatabases' => $listedatabases,
                    'affichebasedonnee' => $db
                ]));
                // -- Affichage BasesEtTables --
            } 
            else {
                // -- Affichage BasesEtTables --
                $db = $session->get('database');

                if ($driver == 'pgsql') {
                    $listetables = $utilitairePostgresDatabase->listerTables();
                    $listedatabases = $utilitairePostgresDatabase->listerDatabases();
                }
                else {
                    $listetables = $utilitaireMysqlDatabase->listerTables();
                    $listedatabases = $utilitaireMysqlDatabase->listerDatabases();
                }

                $loader = new FilesystemLoader($this->getParameter('kernel.project_dir') . '/vendor/yanickmorza/limabundle/src/Resources/views/index/');
                $twig = new Environment($loader);

                return new Response($twig->render('basesettables.html.twig', [
                    'headtitle' => 'Bases de données et Tables dans ' . $db,
                    'listetables' => $listetables,
                    'listedatabases' => $listedatabases,             
                    'affichebasedonnee' => $db
                ]));
                // -- Affichage BasesEtTables --
            }
            // ---- Liste database et table ----
        }
        else {
            return $this->redirectToRoute('connexion');
        }
    }
}