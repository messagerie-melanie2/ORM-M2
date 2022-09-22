Installation de l'ORM Mélanie2
==============================

<h2>PREREQUIS</h2>

L'ORM est écrite en PHP et nécessite une version suppérieure ou égale à PHP 7.3 pour fonctionner.  
Les modules PHP nécessaire sont php7.3-ldap et php7.3-psql.  
L'ORM peut utiliser du cache via memcached et le module php7.3-memcache.  
Il existe deux méthodes d'installation pour l'ORM, par composer (conseillé) ou par mise en place de l'archive.  


<h2>Méthode d'installation par Composer</h2>

L'ORM peut être installée via composer (voir https://getcomposer.org/) pour une intégration automatique dans l'application.

C'est la méthode la plus simple. Il suffit d'ajouter  

```
    "repositories" : [
        {
            "type" : "vcs",
            "url" : "https://github.com/messagerie-melanie2/orm-m2.git"
        }
    ],
    "require" : {
        "messagerie-melanie2/orm-m2" : "<version>"
    }
```

dans le fichier composer.json puis de faire un composer install ou update. 

La version actuelle de l'ORM est la 0.6.2.X. Par principe on met "~0.6.2.0" pour récupérer la dernière version de cette branche. Pour être sûr de la dernière version, voir https://github.com/messagerie-melanie2/ORM-M2/releases/latest

Cette méthode ne nécessite pas de faire d'include de l'ORM dans le code, par contre il faut include l'autoload.php du dossier vendor.


<h2>Méthode d'installation par l'archive</h2>

<h3>Recupération de la librairie</h3>

La librairie ORM Mélanie2 peut être récupérée depuis les sources github (https://github.com/messagerie-melanie2/ORM-M2/releases/latest).

La version peut ensuite être décompressée dans un répertoire.

<h3>Configuration du fichier php.ini</h3>

Ajouter dans la configuration du php.ini (cli ou apache2) le chemin vers la librairie dans le champs
"include_path".  
Exemple:
> ; UNIX: "/path1:/path2"  
> include_path = ".:/usr/share/php:/usr/share/libM2/LibrarySqlMelanie2"  

Bien inclure le répertoire "LibrarySqlMelanie2/".

Cette méthode oblige a faire un include de l'ORM dans le code, comme expliqué dans la documentation technique.


<h2>Configuration de l'ORM</h2>

Par défaut, l'ORM va chercher la configuration dans le répertoire /etc/LibM2.
La configuration à positionner dans ce répertoire est à récupếrer dans config/default (https://github.com/messagerie-melanie2/ORM-M2/tree/master/config/default).  
Le fichier ldap.php va permettre de configurer un ou plusieurs serveurs ldap.  
Le fichier sql.php va permettre de configurer un ou plusieurs serveur PostgreSQL (seul serveur supporté par l'ORM M2 pour l'instant).  
Le fichier config.php est plus général. En principe la seule modification à faire dans ce fichier est le nom de l'applications.

Il est possible de créer un fichier env.php dans le répertoire /etc/LibM2 pour modifier le fonctionnement de la configuration. S'inspirer du fichier env.php interne à l'ORM pour plus d'informations : https://github.com/messagerie-melanie2/ORM-M2/blob/master/env.php<br>
(ceci ne fonctionne pas si l'on souhaite modifier justement le path /etc/LibM2)<br>
l'usage d'un .env définissant la const CONFIGURATION_PATH_LIBM2 est possible

Pour plus de détails sur la configuration voir la documentation [CONFIGURATION.md](documentation/CONFIGURATION.md)

<h2>Production</h2>

Les répertoires "docs_html/", "documentation/", "tests/" et le fichier "example.php" peuvent être supprimés.
