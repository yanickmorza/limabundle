<?php

namespace App\LimaBundle\Scaffold\Mysql;

use Symfony\Component\HttpFoundation\Session\Session;
use App\LimaBundle\Scaffold\Mysql\UtilitaireMysqlDatabase;

class ScaffoldMysqlAuthUser
{
    // -------------- Generer Authuser -------------
    public function genererMysqlAuthuser($option, $authuser, $namespace)
    {
        if ($authuser == "OUI") {

            $session = new Session;
            $db = $session->get('database');

            // ----- Construction de la vue Authentification -----
            $vue_authentification = "../templates/authentification";
            if (!is_dir($vue_authentification)) {
                mkdir($vue_authentification, 0755, true);
            }

            fopen($vue_authentification . "/authentification.html.twig", "w+");
            $fichier_vue_authentification = $vue_authentification . "/authentification.html.twig";
            $texte_vue_authentification = "{% extends 'base.html.twig' %}
{% block title %} Authentification {% endblock %}
{% block body %}
<div class=\"col\">
<div class=\"card mb-3\">
<div class=\"card-header breadcrumb-item\">
    <i class=\"titre\">Veuillez vous authentifier dans {{ db }}</i>
    <a href=\"{{ path('app_register') }}\" class=\"close\" title=\"Creer un compte\"><img src=\"{{ asset('bundles/lima/assets/images/administrateur.png') }}\" width=\"32px\"></a>
</div>
<div class=\"card-body\">
<form method=\"post\">
    <div class=\"form-group row\">
        <div class=\"col-12\">
        {% if error %}
            <div class=\"alert alert-danger\">{{ error.messageKey | trans(error.messageData, 'security') }}</div>
        {% endif %}
        {% if app.user %}
            <div class=\"mb-3\">
                Vous êtes déjà connecté en tant que : {{ app.user.username }}, <a href=\"{{ path('app_logout') }}\">Logout</a>
            </div>
        {% endif %}
        </div>
    </div>
    <div class=\"form-group row\">
        <div class=\"col-3\">
            <input type=\"hidden\" name=\"_csrf_token\" value=\"{{ csrf_token('authenticate') }}\">
        </div>
    </div>
    <div class=\"form-group row\">
        <div class=\"col-6\">
        <label for=\"inputUsername\">Utilisateur :</label>
        <div class=\"col-6\">
            <input type=\"text\" value=\"{{ last_username }}\" name=\"username\" id=\"inputUsername\" class=\"form-control\" autocomplete=\"off\" required autofocus>
        </div>
        </div>
    </div>
    <div class=\"form-group row\">
        <div class=\"col-6\">
        <label for=\"inputPassword\">Mot de passe :</label>
        <div class=\"col-6\">
            <input type=\"password\" name=\"password\" id=\"inputPassword\" class=\"form-control\" required>
        </div>
        </div>
    </div>
    <div class=\"form-group row\">
        <div class=\"col-8\">
            <button class=\"btn btn-primary\" type=\"submit\">Se connecter</button>
        </div>
    </div>
</form>
</div>
</div>
</div>
{% endblock %}";

            file_put_contents($fichier_vue_authentification, $texte_vue_authentification);
            // ----- Construction de la vue Authentification -----

            // ------- Construction de la vue Registration -------
            $vue_registration = "../templates/registration";
            if (!is_dir($vue_registration)) {
                mkdir($vue_registration, 0755, true);
            }

            fopen($vue_registration . "/register.html.twig", "w+");
            $fichier_vue_registration = $vue_registration . "/register.html.twig";
            $texte_vue_registration = "{% extends 'base.html.twig' %}
{% block title %} Enregistrement {% endblock %}
{% block body %}
<div class=\"col\">
<div class=\"card mb-3\">
<div class=\"card-header breadcrumb-item\">
    <i class=\"titre\">Enregistrer un compte dans {{ db }}</i>
    <a href=\"{{ path('app_login') }}\" class=\"close\" title=\"Se connecter\"><img src=\"{{ asset('bundles/lima/assets/images/administrateur.png') }}\" width=\"32px\"></a>
</div>
<div class=\"card-body\">
    {{ form_start(registrationForm) }}
    <div class=\"form-group row\">
        <div class=\"col-3\">{{ form_row(registrationForm.username, {'label': 'Utilisateur :', 'attr': {'autocomplete': 'off'}}) }}</div>
    </div>
    <div class=\"form-group row\">
        <div class=\"col-3\">{{ form_row(registrationForm.password, {'label': 'Mot de passe :'}) }}</div>
    </div>
    <div class=\"form-group row\">
        <div class=\"col-8\">
            <button class=\"btn btn-primary\">Enregistrer</button>
        </div>
    </div>
    {{ form_end(registrationForm) }}
</div>
</div>
</div>
{% endblock %}";

            file_put_contents($fichier_vue_registration, $texte_vue_registration);
            // ------- Construction de la vue Registration -------

            // ----- Construction du controleur Registration -----
            if ($namespace !== null) {
                @mkdir("../src/Controller/" . $namespace, 0755, true);
                $registrationcontroller = "../src/Controller/" . $namespace . "/RegistrationController.php";
                $nameSpace = str_replace("/", "\\", $namespace);
            } else {
                if (!is_dir("../src/Controller/")) {
                    mkdir("../src/Controller/", 0755, true);
                    $registrationcontroller = "../src/Controller/RegistrationController.php";
                    $nameSpace = "";
                } else {
                    $registrationcontroller = "../src/Controller/RegistrationController.php";
                    $nameSpace = "";
                }
            }

            $Objet = ucfirst($option);
            if ($namespace !== null) {
                $nameSpace = str_replace("/", "\\", $namespace);
                $entity =  "App\Entity$nameSpace\\$Objet";
            } else {
                $nameSpace = "";
                $entity =  "App\Entity\\$Objet";
            }


            fopen($registrationcontroller, "w+");
            $texte_registration_controller = "<?php

namespace App\Controller$nameSpace;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Form$nameSpace\RegistrationFormType;
use App\Authentification\Authentification;
use $entity;

class RegistrationController extends AbstractController
{
    /**
     * @Route(\"/register\", name=\"app_register\")
     */
    public function register(Request \$request, UserPasswordEncoderInterface \$passwordEncoder, GuardAuthenticatorHandler \$guardHandler, Authentification \$authenticator): Response
    {
        \$session = new Session();
        \$session->set('database', '$db');
        \$db = \$session->get('database');

        \$user = new $Objet();
        \$form = \$this->createForm(RegistrationFormType::class, \$user);
        \$form->handleRequest(\$request);

        if (\$form->isSubmitted() && \$form->isValid()) {
            // Encoder le mot de passe
            \$user->setPassword(
                \$passwordEncoder->encodePassword(
                    \$user,
                    \$form->get('password')->getData()
                )
            );

            \$entityManager = \$this->getDoctrine()->getManager('$db');
            \$entityManager->persist(\$user);
            \$entityManager->flush();

            // Ajouter un besoin ici, comme envoyer un email

            return \$guardHandler->authenticateUserAndHandleSuccess(
                \$user,
                \$request,
                \$authenticator,
                'main' // Nom du pare-feu dans security.yaml
            );
        }

        return \$this->render('registration/register.html.twig', [
            'registrationForm' => \$form->createView(),
            'db' => \$db
        ]);
    }
}";

            file_put_contents($registrationcontroller, $texte_registration_controller);
            // ----- Construction du controleur Registration -----

            // --- Construction du controleur Authentification ---
            if ($namespace !== null) {
                @mkdir("../src/Controller/" . $namespace, 0755, true);
                $authentificationcontroller = "../src/Controller/" . $namespace . "/AuthentificationController.php";
                $nameSpace = str_replace("/", "\\", $namespace);
            } else {
                if (!is_dir("../src/Controller/")) {
                    mkdir("../src/Controller/", 0755, true);
                    $authentificationcontroller = "../src/Controller/AuthentificationController.php";
                    $nameSpace = "";
                } else {
                    $authentificationcontroller = "../src/Controller/AuthentificationController.php";
                    $nameSpace = "";
                }
            }

            fopen($authentificationcontroller, "w+");
            $texte_controller = "<?php

namespace App\Controller$nameSpace;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Session\Session;

class AuthentificationController extends AbstractController
{
    /**
     * @Route(\"/login\", name=\"app_login\")
     */
    public function login(AuthenticationUtils \$authenticationUtils): Response
    {
        \$session = new Session();
        \$session->set('database', '$db');
        \$db = \$session->get('database');

        \$error = \$authenticationUtils->getLastAuthenticationError();
        \$lastUsername = \$authenticationUtils->getLastUsername();

        return \$this->render('authentification/authentification.html.twig', [
            'last_username' => \$lastUsername,
            'error' => \$error,
            'db' => \$db
        ]);
    }

    /**
     * @Route(\"/logout\", name=\"app_logout\")
     */
    public function logout()
    {
        throw new \Exception('Cette méthode peut être vide - elle sera interceptée par la clé de déconnexion du pare-feu.');
    }
}";

            file_put_contents($authentificationcontroller, $texte_controller);
            // --- Construction du controleur Authentification ---

            // ---- Construction de la class Authentification ----
            $path_authentification = "../src/Authentification";
            if (!is_dir($path_authentification)) {
                mkdir($path_authentification, 0755, true);
            }

            if ($namespace !== null) {
                $nameSpace = str_replace("/", "\\", $namespace);
                $entity =  "App\Entity$nameSpace\\$Objet";
            } else {
                $nameSpace = "";
                $entity =  "App\Entity\\$Objet";
            }

            fopen($path_authentification . "/Authentification.php", "w+");
            $fichier_authentification = $path_authentification . "/Authentification.php";
            $texte_authentification = "<?php

namespace App\Authentification;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use $entity;

class Authentification extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private \$entityManager;
    private \$urlGenerator;
    private \$csrfTokenManager;
    private \$passwordEncoder;

    public function __construct(EntityManagerInterface \$entityManager, UrlGeneratorInterface \$urlGenerator, CsrfTokenManagerInterface \$csrfTokenManager, UserPasswordEncoderInterface \$passwordEncoder)
    {
        \$this->entityManager = \$entityManager;
        \$this->urlGenerator = \$urlGenerator;
        \$this->csrfTokenManager = \$csrfTokenManager;
        \$this->passwordEncoder = \$passwordEncoder;
    }

    public function supports(Request \$request)
    {
        return 'app_login' === \$request->attributes->get('_route')
            && \$request->isMethod('POST');
    }

    public function getCredentials(Request \$request)
    {
        \$credentials = [
            'username' => \$request->request->get('username'),
            'password' => \$request->request->get('password'),
            'csrf_token' => \$request->request->get('_csrf_token'),
        ];
        \$request->getSession()->set(
            Security::LAST_USERNAME,
            \$credentials['username']
        );

        return \$credentials;
    }

    public function getUser(\$credentials, UserProviderInterface \$userProvider)
    {
        \$token = new CsrfToken('authenticate', \$credentials['csrf_token']);
        if (!\$this->csrfTokenManager->isTokenValid(\$token)) {
            throw new InvalidCsrfTokenException();
        }

        \$user = \$this->entityManager->getRepository($Objet::class)->findOneBy(['username' => \$credentials['username'], 'activer' => true]);

        if (!\$user) {
            // Echec de l'authentification avec une erreur personnalisée
            throw new CustomUserMessageAuthenticationException('Nom d\'utilisateur introuvable !');
        }

        return \$user;
    }

    public function checkCredentials(\$credentials, UserInterface \$user)
    {
        return \$this->passwordEncoder->isPasswordValid(\$user, \$credentials['password']);
    }

    public function onAuthenticationSuccess(Request \$request, TokenInterface \$token, \$providerKey)
    {
        if (\$targetPath = \$this->getTargetPath(\$request->getSession(), \$providerKey)) {
            return new RedirectResponse(\$targetPath);
        }

        return new RedirectResponse(\$this->urlGenerator->generate('index'));
    }

    protected function getLoginUrl()
    {
        return \$this->urlGenerator->generate('app_login');
    }
}";
            file_put_contents($fichier_authentification, $texte_authentification);
            // ---- Construction de la class Authentification ----

            // ------------ Class AccessDeniedHandler ------------
            $access_denied = "../src/Security";
            if (!is_dir($access_denied)) {
                mkdir($access_denied, 0755, true);
            }

            fopen($access_denied . "/AccessDeniedHandler.php", "w+");
            $fichier_access_denied = $access_denied . "/AccessDeniedHandler.php";
            $texte_access_denied = "<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
            
class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function handle(Request \$request, AccessDeniedException \$accessDeniedException)
    {
        return new Response(
            '
            <!DOCTYPE html>
            <html lang=\"fr\">
            <head>
            <title>Erreur 403</title>
            <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />
            <meta charset=\"utf-8\">
            <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\">
            <!-- Bootstrap CSS -->
            <link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css\">
            </head>
            <body>          
            <div class=\"row\">
                <div class=\"col text-center\">
                    <img src=\"../bundles/lima/assets/images/erreur_403.png\" title=\"Attention !\" alt=\"erreur_403.png\">
                </div>
            </div>           
            <div class=\"row\">
                <div class=\"col text-center m-5\">
                    <div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
                        <b>Vous n\'avez pas le rôle nécessitant un accès à cette page !</b>
                    </div>
                </div>
            </div>       
            </body>
            </html>',
                403
        );
    }
}";
            file_put_contents($fichier_access_denied, $texte_access_denied);
            // ------------ Class AccessDeniedHandler ------------

            // ---- Construction de Entity User UserInterface ----
            $utilitaireDatabase = new UtilitaireMysqlDatabase;

            if ($namespace !== null) {
                @mkdir("../src/Entity/" . $namespace, 0755, true);
                $path_entity = "../src/Entity" . $namespace;
                $nameSpace = str_replace("/", "\\", $namespace);
            } else {
                if (!is_dir("../src/Entity/")) {
                    mkdir("../src/Entity/", 0755, true);
                    $path_entity = "../src/Entity";
                    $nameSpace = "";
                } else {
                    $path_entity = "../src/Entity";
                    $nameSpace = "";
                }
            }

            // --- Recuperer tous les champs de la table concernee
            $private_type_entity = "";
            $getter_setter       = "";
            $private_mappe       = "";
            $Objet = ucfirst($option);

            $entities = $utilitaireDatabase->listerChamps($option);

            foreach ($entities as $entity) {
                $typechamps = $utilitaireDatabase->afficherTypeChamp($option, $entity);

                if ($entity != "id") {
                    $type = strtolower($typechamps);
                    $libelle = $entity;

                    // Controle du type de champs VARCHAR lONGTEXT, TINYINT, INT
                    if ($type == "varchar") {
                        $type = "string";
                    }
                    elseif ($type == "longtext") {
                        $type = "json";
                    }
                    elseif ($type == "tinyint") {
                        $type = "boolean";
                    }
                    elseif ($type == "int") {
                        $type = "integer";
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
                        $private_type_entity .= "* @ORM\ManyToOne(targetEntity=\"App\Entity$nameSpace\\$Class\", inversedBy=\"$option\")\n\t";
                        $private_type_entity .= "* @ORM\JoinColumn(nullable=false)\n\t";
                        $private_type_entity .= "*/\n\t";
                        $private_type_entity .= "private $" . $champ . ";\n\n\t";

                        // ---- Mapper vers la table de correspondance ----                      
                        $texte = "";
                        $private_mappe = "";

                        $mapper = $path_entity . "/" . $Class . ".php";
                        if (file_exists($mapper)) {
                            $chaine   = file_get_contents($mapper);
                            $trouve   = $option;
                            $position = strpos($chaine, $trouve);

                            if ($position === false) {
                                $private_mappe .= "/**\n\t";
                                $private_mappe .= "* @ORM\OneToMany(targetEntity=\"App\Entity$nameSpace\\$Objet\", mappedBy=\"$champ\")\n\t";
                                $private_mappe .= "*/\n\t";
                                $private_mappe .= "private $" . $option . ";\n\t";
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
                        if ($type != 'json') {
                            // --- getter setter par defaut ---
                            $private_type_entity .= "/**\n\t";
                            $private_type_entity .= "* @ORM\Column(type=\"$type\")\n\t";
                            $private_type_entity .= "*/\n\t";
                            $private_type_entity .= "private $" . $entity . ";\n\n\t";

                            if ($type == "text") {
                                $getter_setter .= "public function get$Libelle()\n\t";
                            } elseif ($type == "integer") {
                                $getter_setter .= "public function get$Libelle()\n\t";
                            } else {
                                $getter_setter .= "public function get$Libelle()\n\t";
                            }

                            $getter_setter .= "{\n\t\t";
                            $getter_setter .= "return \$this->$libelle;\n\t";
                            $getter_setter .= "}\n\n\t";

                            if ($type == "text") {
                                $getter_setter .= "public function set$Libelle($$libelle)\n\t";
                            } elseif ($type == "integer") {
                                $getter_setter .= "public function set$Libelle($$libelle)\n\t";
                            } else {
                                $getter_setter .= "public function set$Libelle($$libelle)\n\t";
                            }
                            $getter_setter .= "{\n\t\t";
                            $getter_setter .= "\$this->$libelle = $$libelle;\n\n\t\t";
                            $getter_setter .= "return \$this;\n\t";
                            $getter_setter .= "}\n\n\t";
                            // --- getter setter par defaut ---
                        }
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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
* @ORM\Entity(repositoryClass=\"App\Repository$nameSpace\\$ObjetRepository\")
* @UniqueEntity(fields={\"username\"}, message=\"Il existe déjà un compte utilisateur avec cet username !\")
*/
class $Objet implements UserInterface
{
    /**
    * @ORM\Id()
    * @ORM\GeneratedValue()
    * @ORM\Column(type=\"integer\")
    */
    private \$id;

    /**
    * @ORM\Column(type=\"json\")
    */
    private \$roles = [];

    $private_type_entity

    /*** ***/

    public function __construct()
    {
        \$this->activer = true;
    }

    public function getId()
    {
        return \$this->id;
    }

    /**
    * @see UserInterface
    */
    public function getRoles(): array
    {
        return \$this->roles;
    }

    public function setRoles(array \$roles): self
    {
        \$this->roles = \$roles;

        return \$this;
    }

    /**
    * @see UserInterface
    */
    public function getSalt()
    {
        // N'est pas nécessaire lors de l'utilisation de l'algorithme \"bcrypt\" dans security.yaml
    }

    /**
    * @see UserInterface
    */
    public function eraseCredentials()
    {
        // Si on stocke des données sensibles temporaires sur l'utilisateur, effacez-les ici.
        // \$this->password = null;
    }

    $getter_setter
}";

            file_put_contents($fichier_entity, $texte_entity);
            // ---- Construction de Entity User UserInterface ----

            // ***** Ecriture dans le Controller Utilisateur *****
            if ($namespace !== null) {
                $path_controller = "../src/Controller/".$namespace;
            } 
            else {
                $path_controller = "../src/Controller";
            }

            $use = "";
            $texteUse = "";
            $encoder = "";
            $texteencoder = "";
            $parametre = "";
            $texteparametre = "";
            $editencoder = "";
            $texteditencoder = "";

            $mapper = $path_controller."/".$Objet."Controller.php";
            
            if (file_exists($mapper)) {

                // ---- USE
                $handleUse = fopen($mapper, "r");
                if ($handleUse) {
                    $use .= "use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;\n";
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
                    $parametre .= ", UserPasswordEncoderInterface \$passwordEncoder";

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

                // --- PASSWORD-ENCODER - NEW - INDEX
                $handlencoder = fopen($mapper, "r");

                if ($handlencoder) {
                    $encoder .= "\n\t\t\t";
                    $encoder .= "\$objet->setPassword(\$passwordEncoder->encodePassword(\$objet, \$form->get('plainpassword')->getData()));\n";

                    while (!feof($handlencoder))
                    {
                        $bufferencoder = fgets($handlencoder);
                        $texteencoder .= str_replace("/***/", $encoder, $bufferencoder);
                    }
                            $handlencoder = fopen($mapper, "w+");
                            fwrite($handlencoder, $texteencoder);
                            fclose($handlencoder);
                }
                // --- PASSWORD-ENCODER - NEW - INDEX

                // --- PASSWORD-ENCODER - EDIT
                $edithandlencoder = fopen($mapper, "r");
                
                if ($edithandlencoder) {
                    $editencoder .= "\n\t\t\t";
                    $editencoder .= "if (\$form->get('plainpassword')->getData() !== null) {\n\t\t\t\t";
                    $editencoder .= "\$objet->setPassword(\$passwordEncoder->encodePassword(\$objet, \$form->get('plainpassword')->getData()));\n\t\t\t";
                    $editencoder .= "}\n\t\t\t";
                    $editencoder .= "else {\n\t\t\t\t";
                    $editencoder .= "\$objet->setPassword(\$form->get('password')->getData());\n\t\t\t";
                    $editencoder .= "}\n";

                    while (!feof($edithandlencoder))
                    {
                        $bufferencoder = fgets($edithandlencoder);
                        $texteditencoder .= str_replace("/****/", $editencoder, $bufferencoder);
                    }
                            $edithandlencoder = fopen($mapper, "w+");
                            fwrite($edithandlencoder, $texteditencoder);
                            fclose($edithandlencoder);
                }
                // --- PASSWORD-ENCODER - EDIT
            }
            // ***** Ecriture dans le Controller Utilisateur *****

            // ---------- Construction de Form UserType ----------
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

            if ($namespace !== null) {
                $nameSpace = str_replace("/", "\\", $namespace);
                $entity =  "App\Entity$nameSpace\\$Objet";
            } else {
                $nameSpace = "";
                $entity =  "App\Entity\\$Objet";
            }

            fopen($path_form . "/RegistrationFormType.php", "w+");
            $fichier_form = $path_form . "/RegistrationFormType.php";
            $texte_form = "<?php

namespace App\Form$nameSpace;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use $entity;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface \$builder, array \$options)
    {
        \$builder
            ->add('username')
            ->add('password', PasswordType::class, [
                // N'est pas mis sur l'objet directement, c'est lu et encodé dans le contrôleur
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe !',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères !',
                        // Longueur maximale autorisée par Symfony pour des raisons de sécurité
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver \$resolver)
    {
        \$resolver->setDefaults([
            'data_class' => $Objet::class,
        ]);
    }
}";
            file_put_contents($fichier_form, $texte_form);
            // ---------- Construction de Form UserType ----------

            // ---------- Construction de security.yaml ----------
            $Objet = ucfirst($option);

            if ($namespace !== null) {
                $nameSpace = str_replace("/", "\\", $namespace);
                $entity =  "App\Entity$nameSpace\\$Objet";
            } 
            else {
                $nameSpace = "";
                $entity =  "App\Entity\\$Objet";
            }

            $fichier_security = "../config/packages/security.yaml";
            $texte_security = "security:
    encoders:
        $entity:
            algorithm: auto
        
    providers:
        app_user_provider:
            entity:
                class: $entity
                property: username
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            anonymous: true
            access_denied_handler: App\Security\AccessDeniedHandler
 
            form_login:
                login_path: login
                check_path: login
                csrf_token_generator: security.csrf.token_manager
                    
            logout:
                path: /logout
                target: /login
            guard:
                authenticators:
                - App\Authentification\Authentification
";

            file_put_contents($fichier_security, $texte_security);
            // ---------- Construction de security.yaml ----------
        }
    }
    // -------------- Generer Authuser -------------

    // ------------ Supprimer Authuser -------------
    public function supprimerMysqlAuthuser($option, $namespace)
    {
        // ----- Supprimer la class Authentification ------
        $path_class = "../src/Authentification";
        $class = $path_class . "/Authentification.php";
        $authclass = "../src/Authentification/Authentification.php";

        if (file_exists($authclass)) {
            unlink($authclass);
        }
        if (file_exists($class)) {
            unlink($class);
        }
        if (is_dir($path_class)) {
            rmdir("../src/Authentification");
        }
        // ----- Supprimer la class Authentification ------

        // ---- Supprimer la class AccessDeniedHandler ----
        $AccessDenied = "../src/Security";
        $ClassAccessDenied = $AccessDenied . "/AccessDeniedHandler.php";
        $AccessDeniedClass = "../src/Security/AccessDeniedHandler.php";

        if (file_exists($AccessDeniedClass)) {
            unlink($AccessDeniedClass);
        }
        if (file_exists($ClassAccessDenied)) {
            unlink($ClassAccessDenied);
        }
        if (is_dir($AccessDenied)) {
            rmdir("../src/Security");
        }
        // ---- Supprimer la class AccessDeniedHandler ----

        // ---- Supprimer Controller Authentification -----
        if ($namespace !== null) {
            $authcontroller = "../src/Controller/".$namespace."/AuthentificationController.php";
        }
        else {
            $authcontroller = "../src/Controller/AuthentificationController.php";
        }
        if (file_exists($authcontroller)) {
            unlink($authcontroller);
        }
        // ---- Supprimer Controller Authentification -----

        // ------ Supprimer Controller Registration -------
        if ($namespace !== null) {
            $registrercontroller = "../src/Controller/".$namespace."/RegistrationController.php";
        }
        else {
            $registrercontroller = "../src/Controller/RegistrationController.php";
        }
        if (file_exists($registrercontroller)) {
            unlink($registrercontroller);
        }
        // ------ Supprimer Controller Registration -------

        // ----- Supprimer templates Authentification -----
        $path_vue_authentification = "../templates/authentification";
        $view = $path_vue_authentification . "/authentification.html.twig";
        
        if (file_exists($view)) {
            unlink($view);
        }
        if (is_dir($path_vue_authentification)) {
            rmdir("../templates/authentification");
        }
        // ----- Supprimer templates Authentification -----

        // ------- Supprimer templates Registration -------
        $path_vue_registration = "../templates/registration";
        $view = $path_vue_registration . "/register.html.twig";

        if (file_exists($view)) {
            unlink($view);
        }
        if (is_dir($path_vue_registration)) {
            rmdir("../templates/registration");
        }
        // ------- Supprimer templates Registration -------

        // ------- Supprimer Entity Option (Objet) --------
        if ($namespace !== null) {
            $path_entity = "../src/Entity/" . $namespace . "/" . ucfirst($option) . ".php";
        } 
        else {
            $path_entity = "../src/Entity/" . ucfirst($option) . ".php";
        }
        if (file_exists($path_entity)) {
            unlink($path_entity);
        }
        // ------- Supprimer Entity Option (Objet) --------

        // -------- Supprimer FORM Option (Objet) ---------  
        if ($namespace !== null) {
            $path_form = "../src/Form/" . $namespace . "/RegistrationFormType.php";
        } 
        else {
            $path_form = "../src/Form/RegistrationFormType.php";
        }
        if (file_exists($path_form)) {
            unlink($path_form);
        }
        // -------- Supprimer FORM Option (Objet) ---------

        // --------- Construction de security.yaml --------
        $fichier_security = "../config/packages/security.yaml";
        $texte_security = "security: 
    # https://symfony.com/doc/current/security.html#where-do-users-come-from-user-providers
    providers:
        users_in_memory: { memory: null }
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            anonymous: true
            provider: users_in_memory
    
            # activate different ways to authenticate
            # https://symfony.com/doc/current/security.html#firewalls-authentication
    
            # https://symfony.com/doc/current/security/impersonating_user.html
            # switch_user: true
    
    # Easy way to control access for large sections of your site
    # Note: Only the *first* access control that matches will be used
    access_control:
        # - { path: ^/admin, roles: ROLE_ADMIN }
        # - { path: ^/profile, roles: ROLE_USER }
";

        file_put_contents($fichier_security, $texte_security);
    // ------- Construction de security.yaml -------
    }
    // ------------ Supprimer Authuser -------------
}