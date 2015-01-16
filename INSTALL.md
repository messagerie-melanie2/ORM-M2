Installation de l'ORM Mélanie2
==============================

PREREQUIS
---------

L'ORM est écrite en PHP et nécessite une version suppérieure ou égale à PHP 5.3 pour fonctionner.
Les modules PHP nécessaire sont php5-ldap et php5-psql.
L'ORM peut utiliser du cache via memcached et le module php5-memcache.


Recupération de la librairie
----------------------------

La librairie ORM Mélanie2 peut être récupérée auprès du PNE Annuaire et Messagerie du MEDDE ou bien depuis les sources git.
La version peut ensuite être décompressée dans un répertoire.


Configuration du fichier php.ini
--------------------------------

Ajouter dans la configuration du php.ini (cli ou apache2) le chemin vers la librairie dans le champs
"include_path".
Exemple:
> ; UNIX: "/path1:/path2"
> include_path = ".:/usr/share/php:/usr/share/libM2/LibrarySqlMelanie2"
Bien inclure le répertoire "LibrarySqlMelanie2/".


Documentation PHPDocs
---------------------

La documentation se trouve dans le répertoire "docs_html/". Il s'agit d'une documentation générée via
phpdocs. On peut la consulter en ouvrant le fichier "index.html" dans un navigateur.


Mise en production
------------------

Depuis la version 0.1.7 de l'ORM une configuration externe est possible. Le chemin vers la configuration externe est éditable dans le fichier config/env.php (par défaut /etc/LibM2).
De plus la configuration peut permettre de gérer plusieurs applications indépendantes ayant une configuration différentes. Dans ce cas il faut définir un type « external » dans le fichier config/env.php.
Pour la production, les fichiers de configuration "config/ldap.php" et "config/sql.php" doivent être paramétrés.
Les répertoires "docs_html/", "documentation/", "tests/" et le fichier "example.php" peuvent être supprimés.