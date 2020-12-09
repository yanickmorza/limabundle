# LIMA - Light Interface Maker Application
- C'est une application développée en PHP pour le Framework Symfony <code>Versions 5</code>.
- Elle permet principalement de générer des SCRUD pour un Back-Office (Seach, Create, Read, Update, Delete).
- Mais elle n'est pas uniquement dédiée qu'à cette tache.
- Elle également en mesure de concevoir une application multicomplexe en Symfony avec 60% de No-code, le reste est à votre libre imagination.
- Prérequis : <code>une version PHP-7.2 et une base de données PostgreSQL</code>.
- L'ORM LIMA orienté également vers une <code>base de données MySQL</code> est en cours de développement.

# Installer

- 1 - <code>composer require yanickmorza/limabundle</code>

- 2 - Ecrire son mot de passe PostgreSQL dans le fichier :
<code>/vendor/yanickmorza/limabundle/src/Scaffold/ConnexionDatabase.php</code>

# Démarrer

- 1 - Démarrer votre serveur web avec la commande 'Symfony' (http ou https) : 
<code>symfony serve</code>
- 1 - si vous n'avez pas installé la commande 'Symfony' :
<code>php -S localhost:8000 -t public/</code>

- 2 - Ouvrir votre navigateur internet en http : <a href="http://localhost:8000/lima/">http://localhost:8000/lima/</a>
- 2 - Ouvrir votre navigateur internet en https : <a href="https://localhost:8000/lima/">https://localhost:8000/lima/</a>