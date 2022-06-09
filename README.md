# LIMA - Light Interface Maker Application
- C'est un bundle pour le Framework Symfony <code>Versions 5 et 6</code>.
- Le bundle permet principalement de générer des SCRUD pour un Back-Office (Seach, Create, Read, Update, Delete).
- Pas dédié uniquement à cette tache, le bundle est également en mesure de concevoir une application multicomplexe en Symfony avec 60% de No-code, le reste est à votre libre imagination.
- Prérequis : <code>un serveur PHP >= 7.2, une base de données (PostgreSQL ou MySQL), les outils Composer et Git</code>.

# Installer

- 1 - <code>Installer le bundle: composer require yanickmorza/limabundle @dev</code>
- 2 - <code>Installer le service de LIMA: php vendor/yanickmorza/limabundle/src/Command/installServiceLima.php</code>
- 3 - <code>Installer la configuration de LIMA: symfony console config:lima (ou) bin/console config:lima</code>

# Démarrer

- 1 - Démarrer votre serveur web avec la commande 'Symfony' (http ou https) : 
<code>symfony server:start</code>
- 1 - Si vous n'avez pas installé le binaire 'Symfony' alors :
<code>php -S localhost:8000 -t public/</code>

- 2 - Ouvrir votre navigateur internet en http : <a href="http://localhost:8000/lima/">http://localhost:8000/lima/</a>
- 2 - Ouvrir votre navigateur internet en https : <a href="https://localhost:8000/lima/">https://localhost:8000/lima/</a>
