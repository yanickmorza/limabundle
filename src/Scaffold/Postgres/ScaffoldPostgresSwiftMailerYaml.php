<?php

namespace App\LimaBundle\Scaffold\Postgres;

class ScaffoldPostgresSwiftMailerYaml
{
    public function swiftMailerPostgresYaml($transport, $encryption, $auth_mode, $host, $port, $username, $password)
    {
        // **** Ecriture dans le fichier swiftmailer.yaml ****
        $path_swiftmaileryaml = "../config/packages/swiftmailer.yaml";
        fopen($path_swiftmaileryaml, "w+");

        $texte_swiftmaileryaml = "swiftmailer:
    transport:  $transport
    encryption: $encryption
    auth_mode:  $auth_mode
    host:       $host
    port:       '$port'
    username:   $username
    password:   $password
    spool: { type: 'memory' }";

        file_put_contents($path_swiftmaileryaml, $texte_swiftmaileryaml);
        // **** Ecriture dans le fichier swiftmailer.yaml ****

        // ------------ Class SwiftMailerClass ------------
        $swiftmailer = "../src/Swiftmailer";
        if (!is_dir($swiftmailer)) {
            mkdir($swiftmailer, 0755, true);
        }

        fopen($swiftmailer . "/SwiftMailerClass.php", "w+");

        $fichier_swiftmailer = $swiftmailer . "/SwiftMailerClass.php";
        $texte_swiftmailer = "<?php

namespace App\Swiftmailer;

class SwiftMailerClass
{
    private \$mailer;
        
    public function __construct(\Swift_Mailer \$mailer)
    {
        \$this->mailer = \$mailer;
    }
        
    public function transmettre(\$sujet, \$from, \$to, \$body)
    {
        \$message = (new \Swift_Message())
            ->setSubject(\$sujet)
            ->setFrom(\$from)
            ->setTo(\$to)
            ->setBody(\$body, 'text/html');
        
        return \$this->mailer->send(\$message);
    }
}
";
        file_put_contents($fichier_swiftmailer, $texte_swiftmailer);
        // ------------ Class SwiftMailerClass ------------
    }

    public function supprimerSwiftMailerPostgresClass()
    {
        $swiftmailer = "../src/Swiftmailer";
        if (is_dir($swiftmailer)) {
            array_map('unlink', glob($swiftmailer . "/*.php"));
            rmdir($swiftmailer);
        }

        // **** Ecriture dans le fichier swiftmailer.yaml ****
        $path_swiftmaileryaml = "../config/packages/swiftmailer.yaml";
        fopen($path_swiftmaileryaml, "w+");

        $texte_swiftmaileryaml = "swiftmailer:
    url: '%env(MAILER_URL)%'
    spool: { type: 'memory' }";

        file_put_contents($path_swiftmaileryaml, $texte_swiftmaileryaml);
        // **** Ecriture dans le fichier swiftmailer.yaml ****
    }
}