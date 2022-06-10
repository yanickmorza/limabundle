# LIMA - Light Interface Maker Application

- C'est un bundle pour le Framework Symfony.

![](https://img.shields.io/badge/version-2.0-brightgreen)
![](https://img.shields.io/badge/symfony-5.4-informational)
![](https://img.shields.io/badge/symfony-6.0-informational)

## Auteur

- [@Yanick Morza](https://github.com/yanickmorza/limabundle.git)

## Variables d'environnement

Pour lancer le projet, vous aurez besoin de paramétrer les variables d'environnement suivantes dans votre fichier .env.local :

| Variables |  Descriptions |
| --- | --- |
| `DATABASE_URL` | Url de connexion à la base de données |

## Description

- Le bundle permet principalement de générer des SCRUD pour un Back-Office (Seach, Create, Read, Update, Delete).
- Pas dédié uniquement à cette tache, le bundle est également en mesure de concevoir une application multicomplexe en Symfony avec 60% de No-code, le reste est à votre libre imagination.
- Prérequis: <code>un serveur PHP >= 7.2, une base de données (PostgreSQL ou MySQL), et Composer</code>.

## Installation 

- 1 - <code>Installer le bundle: </code>
```bash 
composer require yanickmorza/limabundle @dev
```

- 2 - <code>Installer le service de LIMA:</code>
```bash
 php vendor/yanickmorza/limabundle/src/Command/installServiceLima.php
```

- 3 - <code>Installer la configuration de LIMA:</code> 
```bash
symfony console config:lima (ou) bin/console config:lima
```

 # Démarrer

- 1 - <code>Démarrer votre serveur web avec la commande 'Symfony' (http ou https):</code>
```bash 
symfony server:start
```

- 1 - <code>Si vous n'avez pas installé le binaire 'Symfony' alors:</code>

```bash 
php -S localhost:8000 -t public/
```

- 2 - <code>Ouvrir votre navigateur internet en http : <a href="http://localhost:8000/lima/">http://localhost:8000/lima/</a>
</code>

- 2 - <code>Ouvrir votre navigateur internet en https : <a href="https://localhost:8000/lima/">https://localhost:8000/lima/</a></code>