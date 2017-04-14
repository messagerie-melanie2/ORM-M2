Installation de l'ORM Mélanie2
==============================

Pré-requis
----------

L'ORM est écrite en PHP et nécessite une version suppérieure ou égale à PHP 5.4 pour fonctionner.  Les modules PHP nécessaire sont php5-ldap et php5-psql.  L'ORM peut utiliser du cache via memcached et le module php5-memcache.


Recupération de la librairie
----------------------------

La librairie ORM Mélanie2 peut être récupérée auprès du PNE Annuaire et Messagerie du MEDDE ou bien depuis les sources git.  La version peut ensuite être décompressée dans un répertoire.


Configuration du fichier php.ini
--------------------------------

Ajouter dans la configuration du php.ini (cli ou apache2) le chemin vers la librairie dans le champs "include_path".

Exemple:
> ; UNIX: "/path1:/path2"
> include_path = ".:/usr/share/php:/usr/share/libM2/LibrarySqlMelanie2"

Bien inclure le répertoire "LibrarySqlMelanie2/".


Documentation PHPDocs
---------------------

La documentation se trouve dans le répertoire "docs_html/". Il s'agit d'une documentation générée via phpdocs. On peut la consulter en ouvrant le fichier "index.html" dans un navigateur.


Création de la base de données
------------------------------

La base de données de l'ORM se trouve dans le répertoire "schema/". Actuellement le seul type de base de données supporté est PostgreSQL. La base de données doit être créé (avec son owner) et peut ensuite être initialisé avec le script sql "melanie2.initial.sql".

La date du script melanie2.initial.sql correspond à sa version. Les updates de version se trouve dans le répertoire schema/updates/. Il suffit d'appliquer les updates ayant une date supérieure au dernier schéma passé.
 

Configuration de l'ORM
----------------------

La configuration de l'ORM peut se trouver à plusieurs endroits. Cette configuration se règle dans le fichier config/env.php.

Par défault, la configuration est externe et doit se trouver dans /etc/LibM2. Chaque application qui utilise l'ORM défini son nom d'application, en mode multiple (mode par défault) la configuration est unique par application, on doit avoir la configuration dans /etc/LibM2/<nom_d'appli>. On peut changer ce comportement en passant en mode simple dans le fichier env.php.

Il est également possible d'utiliser la configuration directement dans l'ORM, il faut pour cela passer en mode internal. Il faut également configurer l'environnement, par exemple >default<, la configuration sera alors récupéré dans le répertoire config/default/.


Mise en production
------------------

Pour la production, les fichiers de configuration "config/ldap.php" et "config/sql.php" doivent être paramétrés.
Les répertoires "docs_html/", "documentation/", "tests/" et le fichier "example.php" peuvent être supprimés.