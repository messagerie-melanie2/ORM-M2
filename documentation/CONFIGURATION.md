<h1>Configuration de l'ORM</h1>

Par défaut, l'ORM va chercher la configuration dans le répertoire /etc/LibM2.
La configuration à positionner dans ce répertoire est à récupếrer dans [config/default](https://github.com/messagerie-melanie2/ORM-M2/tree/master/config/default).
 
Il est possible de créer un fichier env.php dans le répertoire /etc/LibM2 pour modifier le fonctionnement de la configuration. S'inspirer du fichier [env.php](https://github.com/messagerie-melanie2/ORM-M2/blob/master/env.php) interne à l'ORM pour plus d'informations.

<h2>Table des matières</h2>

[[_TOC_]]

<h2>Configuration de la base de données PostgreSQL</h2>

La configuration de la base de données PostgreSQL se fait dans le fichier [config/default/sql.php](https://github.com/messagerie-melanie2/ORM-M2/tree/master/config/default/sql.php)

La configuration SQL se fait dans un "array" `$SERVERS`. Il s'agit d'un tableau car l'ORM supporte la configuration de plusieurs serveurs SQL (utile pour les restaurations de base j-1, j-n ou à terme pour la scalabilité des serveurs). Le serveur à utiliser par défaut doit être défini dans `$SGBD_SERVER` (serveur sgbd par défaut) et `$CURRENT_BACKEND` (le backend courant utilisé)

Pour la configuration du serveur, se reporter à la configuration d'exemple qui contient les valeurs à définir :

```PHP
      // Configuration de la base de données original
      'sgbd.test' => array(
          /**
           * Connexion persistante
           */
          'persistent' => 'false',
          /**
           * Hostname ou IP vers le serveur SGBD
           */
          'hostspec' => 'sgbd.test',
          /**
           * Mot de passe pour l'utilisateur
           */
          'password' => 'P4ss.',
          /**
           * Base de données
           */
          'database' => 'mybase',
          /**
           * Port de connexion
           */
          'port' => 5432,
          /**
           * Utilisateur pour la connexion à la base
           */
          'username' => 'myuser',
          /**
           * Protocole de connexion
           */
          'protocol' => 'tcp',
          /**
           * Encodage de la base de données
           */
          'charset' => 'utf-8',
          /**
           * Type de base : pgsql, mysql
           */
          'phptype' => 'pgsql'
      )
```

L'activation de la persistance `persistent` est déconseillée, car elle fonctionne mal en PHP (elle n'est pas multiprocesses et la réutilisation des connexions ne fonctionne pas très bien).  
Pour le `phptype` actuellement le seul supporté est `pgsql`.

<h2>Configuration de l'annuaire LDAP</h2>

La configuration de l'annuaire LDAP se fait dans le fichier [config/default/ldap.php](https://github.com/messagerie-melanie2/ORM-M2/tree/master/config/default/ldap.php)

<h3>Utilisation d'un ou plusieurs annuaires</h3>

La configuration des serveurs d'annuaire se fait dans un "array" `$SERVERS`. Jusqu'à 4 serveurs d'annuaire différents sont supportés :
*  `$AUTH_LDAP` configuration de l'annuaire pour l'authentification
*  `$SEARCH_LDAP` configuration de l'annuaire pour les recherches LDAP
*  `$AUTOCOMPLETE_LDAP` configuration de l'annuaire utilisé pour l'autocomplétion
*  `$MASTER_LDAP` configuration de l'annuaire accessible en écriture

Le même annuaire peut être utilisé pour une ou plusieurs de ces fonctionnalités (par exemple, un annuaire en lecture pour les 3 premières fonctionnalités et un annuaire en écriture pour la dernière). Si le même annuaire est utilisé pour plusieurs fonctionnalités les connexions seront réutilisées.

**Information** depuis l'ORM 0.6 il est possible de configurer un autre annuaire : `$OTHER_LDAP`
Cet annuaire permet d'aller récupérer les données d'un utilisateur qui ne sont pas contenu dans le premier annuaire (ex: un annuaire technique et un annuaire page blanche)

<h3>Configuration d'un serveur LDAP</h3>

Pour configurer le serveur LDAP il suffit de reprendre la configuration du fichier d'exemple fournit avec l'ORM :
```
            /* Serveur LDAP IDA de test */
            "ldap.test" => array(
                    /* Host vers le serveur d'annuaire, précédé par ldaps:// si connexion SSL */
                    "hostname" => "ldaps://ldap.test",
                    /* Port de connexion au LDAP */
                    "port" => 636,
                    /* Base DN de recherche */
                    "base_dn" => "dc=example,dc=com",
                    /* Version du protocole LDAP */
                    "version" => 3,
                    /* Connexion TLS */
                    "tls" => false,
                    // Configuration des attributs et filtres de recherche
                    // Filtre de recherche pour la méthode get user infos
                    "get_user_infos_filter" => "(uid=%%uid%%)",
                    // Liste des attributs à récupérer pour la méthode get user infos
                    "get_user_infos_attributes" => array('fullname','email','uid','service','info'),
                    // Base de recherche pour les objets de partage //
                    "shared_base_dn" => "dc=partage,dc=example,dc=com",
            ),
```

<h3>Configuration des filtres pré-remplis</h3>

**Information** par rapport à la 0.5 la version 0.6 ne nécessite plus de configurer les champs `get_user_*_attributes`. Les filtres peuvent désormais utiliser n'importe quel champ configuré dans le mapping (voir ci-dessous) (uid, fullname, email, ...), même chose pour les attributs.

L'ORM propose aux applications plusieurs filtres pré-remplis permettant de faire des recherches LDAP, pour modifier le comportement par défaut d'un de ces filtres il suffit de le rajouter dans la configuration du serveur. Il existe désormais des filtres par défaut par environnement, ci-dessous les filtres par défaut sont ceux pour la MCE. Voici la liste des filtres proposés par l'ORM :

*  "get_user_infos_filter" et "get_user_infos_attributes" pour la récupération des informations d'utilisateur de l'annuaire en fonction de son identifiant. Filtre MCE par défaut : `(uid=%%uid%%)`
*  "get_user_bal_partagees_filter" et "get_user_bal_partagees_attributes" permet de récupérer une liste de boites partagées de l'utilisateur en fonction de son username (uid). Filtre MCE par défaut : `(mcedelegation=%%uid%%:*)` 
*  "get_user_bal_emission_filter" et "get_user_bal_emission_attributes" permet de récupérer une liste de boites partagées de l'utilisateur dont il a les droits d'émission en fonction de son username (uid). Filtre MCE par défaut : `(mcedelegation=%%uid%%:*)`
*  "get_user_bal_gestionnaire_filter" et "get_user_bal_gestionnaire_attributes" permet de récupérer une liste de boites partagées de l'utilisateur dont il a les droits de gestionnaire en fonction de son username (uid). Filtre MCE par défaut : `(mcedelegation=%%uid%%:*)`
*  "get_user_infos_from_email_filter" et "get_user_infos_from_email_attributes" permet de récupérer les informations d'un utilisateur de l'annuaire en fonction de son adresse e-mail. Filtre MCE par défaut : `(mail=%%email%%)`
* "get_user_groups_filter" et "get_user_groups_attributes" permet de récupérer les groupes LDAP qui appartiennent à l'utilisateur. Filtre MCE par défaut : non implémenté dans la MCE
* "get_groups_user_member_filter" et "get_groups_user_member_attributes" permet de récupérer la liste des groupes LDAP auxquels l'utilisateur est un membre. Filtre MCE par défaut : non implémenté dans la MCE
* "get_lists_user_member_filter" et "get_lists_user_member_attributes" permet de récupérer la liste des listes de diffusion LDAP auxquelles l'utilisateur est un membre. Filtre MCE par défaut : non implémenté dans la MCE

<h3>Configuration du mapping des attributs</h3>

**Information** par rapport à la 0.5 le mapping de la version 0.6 est différents. L'ancien format n'est pas compatible avec le nouveau format de mapping (mais ne générera pas d'erreur). Il existe également désormais un mapping par défaut par environnement

Comme les annuaires des ministères sont tous différents, il est nécessaire de configurer un mapping. La configuration d'un mapping s'effectue par l'ajout d'un `"mapping" => array()` à l'intérieur de la configuration du serveur LDAP. Ce tableau contient alors une liste de clé valeur correspond à `<nom générique du champ> => <nom de l'attribut dans l'annuaire>`. 

Par exemple :
```
          // Gestion du mapping des champs LDAP
          "mapping" => array(
              "uid"                     => 'uid',         // Identifiant de l'utilisateur
              "fullname"                => 'cn',          // Nom complet de l'utilisateur
              "name"                    => 'displayname', // Display name de l'utilisateur
              "email"                   => 'mail',        // Adresse e-mail principale de l'utilisateur en reception
          ),
```

De plus il est maintenant possible de définir un type de donnés pour les champs LDAP. Le type par défaut est "stringLdap" c'est à dire que le champ agira comme une chaine de caractère (si le champ LDAP contient plusieurs valeurs, seule la première sera récupérée).

Voici la liste des formats de données supportés par le mapping de l'ORM actuellement (pour que  `MappingMce` soit accessible il faut mettre `use LibMelanie\Config\MappingMce;` en haut du ficher avant le "class", sinon il faut utiliser `LibMelanie\Config\MappingMce::` à la place) :
 - chaine de caractère : `MappingMce::stringLdap` (possibilité d'utiliser `MappingMce::prefixLdap` pour utiliser une valeur qui contient ce préfixe, dans ce cas il ne prendre pas la première valeur mais celle qui contient ce préfixe)
 - tableau : `MappingMce::arrayLdap`
 - booleén : `MappingMce::booleanLdap` (possibilité d'utiliser `MappingMce::falseLdapValue` et `MappingMce::trueLdapValue` pour définir les deux valeurs possible)
 - `MappingMce::defaut` permet de définir une valeur par défaut si aucune n'est définie

Voici quelques exemples sur comment utiliser ces champs :

```
          // Gestion du mapping des champs LDAP
          "mapping" => array(
              "email_list"              => [MappingMce::name => 'mail', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en reception pour l'utilisateur
              "update_personnal_info"   => [MappingMce::name => 'mineqmajinfoperso', MappingMce::defaut => true, MappingMce::trueLdapValue => '1', MappingMce::falseLdapValue => '0', MappingMce::type => MappingMce::booleanLdap],             // Droit pour l'utilisateur de modifier ses infos perso dans l'annuaire
              "observation"             => [MappingMce::name => 'info', MappingMce::prefixLdap => 'OBSERVATION:', MappingMce::type => MappingMce::stringLdap],
          ),
```

Voici le mapping par défaut configuré pour l'environnement MCE (les champs "fullname", "street", "postalcode", "locality" et "title" sont récupérés du second annuaire LDAP)

```
    "dn"                      => 'dn',                            // DN de l'utilisateur
    "uid"                     => 'uid',                           // Identifiant de l'utilisateur
    "fullname"                => 'cn',                            // Nom complet de l'utilisateur
    "email"                   => 'mail',                          // Adresse e-mail principale de l'utilisateur en reception
    "email_list"              => [MappingMce::name => 'mailalternateaddress', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en reception pour l'utilisateur
    "email_send"              => 'mail',                          // Adresse e-mail principale de l'utilisateur en emission
    "email_send_list"         => [MappingMce::name => 'mailalternateaddress', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en émission pour l'utilisateur
    "shares"                  => [MappingMce::name => 'mcedelegation', MappingMce::type => MappingMce::arrayLdap], // Liste des partages pour cette boite
    "server_routage"          => [MappingMce::name => 'mailhost', MappingMce::type => MappingMce::arrayLdap], // Champ utilisé pour le routage des messages
    "type"                    => 'mcetypecompte',                 // Type d'entrée (boite individuelle, partagée, ressource, ...)
    "street"                  => 'street',                        // Rue
    "postalcode"              => 'postalcode',                    // Code postal
    "locality"                => 'l',                             // Ville
    "title"                   => 'title',                         // Titre
```

<h3>Configuration des Items</h3>

Depuis les versions 0.6.1.X de l'ORM une nouvelle notion à fait son apparition dans la configuration `ldap.php` : les Items. Les items permettent d'appliquer des configurations spécifiques pour certaines fonctions utilisées dans les outils se basant sur l'ORM. Typiquement, il est possible de configurer un Item pour chaque module du plugin mel_moncompte du webmail. Ces Items servent à configurer une authentification spécifique, ou des valeurs par défaut à appliquer à la création d'un objet ldap.

Ces Items sont à configurer dans le fichier de configuration `ldap.php`, directement dans la configuration d'un serveur, au même niveau que le `"mapping"` ou le `"hostname"`

Par exemple, pour le MTE nous avons une configuration définie pour deux Items :

```
/* Gestion des items */
"items" => [
    "workspace" => [
        'creation' => true,
        'bind_dn' => 'dn=exemple-creation-dans-le-ldap',
        'bind_password' => '<password>',
        'default' => [
            'objectClass' => [
                'top',
                'posixGroup',
                'mineqMelListe',                           
            ],
            'gidNumber' => ['99999'],
            'mineqTypeEntree' => ['LDIS'],
            'mineqOrdreAffichage' => ['0000'],
            'mineqPortee' => ['20'],
            'mineqMelRemise' => ['LISTE'],
        ],
    ],
    'grouplistes' => [
        'bind_dn' => 'uid=listeadmin,ou=exemple',
        'bind_password' => "<password>",
    ],
],
```

Le premier Item `workspace` est utilisé par le Bureau Numérique permet de créer et modifier des listes ldap, il y a donc la configuration du compte qui a les droits d'écriture de ces objets, ainsi que les valeurs par défaut lors de la création de l'objet (en plus des valeurs positionnées par le Bnum lui-même via l'ORM).

Le second Item `grouplistes` permet de configurer une authentification avec un compte spécifique pour la manipulation des listes dans le plugin Moncompte du webmail (les utilisateurs n'ayant pas les droits de manipuler ces objets dans le ldap).

Voici la liste de tous les Items actuellement utilisables et configurables (soit par le webmail, soit par le Bureau Numérique) : 

- `webmail.moncompte.accessinternet` : Utilisé pour écrire/lire l'information d'accès internet de l'utilisateur
- `webmail.moncompte.changepassword` : Utilisé pour récupérer les infos sur l'utilisateur sur le changement de mot de passe
- `webmail.moncompte.gestionnaireabsence` : Utilisé pour configurer le gestionnaire d'absence dans l'entrée de l'utilisateur
- `webmail.moncompte.userlistes` : Utilisé pour récupérer les listes dont l'utilisateur est propriétaire
- `webmail.moncompte.grouplistes` : Utilisé pour modifier/lire les listes
- `webmail.moncompte.informationspersonnelles` : Utilisé pour modifier les informations personnelles dans l'entrée de l'utilisateur
- `webmail.moncompte.synchronisationmobile` : Utilisé pour récupérer/écrire les informations de synchronisation de l'utilisateur
- `webmail.workspace` : Utilisé par le Bureau Numérique pour créer les listes associées aux espaces de travail

Chaque nom d'Item est découpé en plusieurs niveaux. Par exemple il est possible de configurer un Item `webmail` qui s'appliquera sur tous les objets qui ont webmail dans leur nom, ou `moncompte` pour se limiter a tous les objets de Moncompte ou bien spécifiquement le nom de l'objet comme `informationspersonnelles` par exemple.

<h2>Configuration générale de l'ORM</h2>

La configuration générale de l'ORM se fait dans le fichier [config/default/config.php](https://github.com/messagerie-melanie2/ORM-M2/tree/master/config/default/config.php)

<h3>Liste de champs intéressants à configurer</h3>

  - `APP_NAME` Nom de l'application (Roundcube, ZPush, SabreDAV, Pacome, ...)
  - `CALENDAR_CALDAV_URL` / `TASKSLIST_CALDAV_URL` / `ADDRESSBOOK_CARDDAV_URL` Configuration des urls CalDAV/CardDAV pour les afficher à l'utilisateur dans les applications
  - `SEL_ENABLED` Activation des "selaforme" mécanisme pour protéger le nombre de connexion maximum aux bases de données et/ou serveurs LDAP par serveur
  - `SEL_MAX_ACQUIRE` Nombre de connexions simultanées par serveur (nécessite `SEL_ENABLED`)
  - `SEL_NB_ESSAI` Si le max de connexions est atteint, nombre de fois où il réessaye avant de sortir une erreur
  - `SEL_TEMPS_ATTENTE` Temps d'attente en millisecondes entre les nouvelles tentatives de connexion
  - `SEL_FILE_NAME` Nom des fichiers utilisés pour gérer le mécanisme de "selaforme"
 
<h2>Ajout de nouveaux fichiers de configuration</h2>

Pour ajouter de nouveaux fichiers de configuration qui seront accessible depuis l'application utilisant l'ORM, il suffit de les positionner dans le répertoire de configuration de l'ORM et d'ajouter la liste de ces fichiers au fichier [config/default/includes.php](https://github.com/messagerie-melanie2/ORM-M2/tree/master/config/default/includes.php)