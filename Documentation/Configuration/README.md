# Configuration

Cette documentation décrit la configuration des orm-api

## Création du fichier de configuration

Pour initialiser la configuration, il faut idéalement se baser sur le fichier [config.sample.inc.php](../../src/config.sample.inc.php) pour créer un fichier `config.inc.php`

## Valeurs par défaut

Pour consulter les valeurs par défaut de configuration, il faut se rendre dans le dossier [config/](../../src/config/).

Le fichier [config/default.inc.php](../../src/config/default.inc.php) contient toutes les valeurs de configuration principales par défaut.

Le fichier [config/mapping.inc.php](../../src/config/mapping.inc.php) contient toutes les valeurs de mapping entre les api et l'orm par défaut.

Le fichier [config/routing.inc.php](../../src/config/routing.inc.php) contient tout le routing par défaut.

## Propriétés de configuration

Voici la liste des propriétés de configuration à mettre dans le fichier `config.inc.php`. Pour plus de détails se référer au fichier [config.sample.inc.php](../../src/config.sample.inc.php).

### log_level

Permet de configurer le niveau de logs désiré de 0 a 4 :

* 0 -> pas de log
* 1 -> logs erreur
* 2 -> logs information
* 3 -> logs debug
* 4 -> logs trace (+ debug ORM)

Exemple pour des logs au niveau information :

```php
  'log_level' => 2,
```

### log_file

Chemin complet vers le fichier dans lequel les logs sont envoyés. Il est également possible de configurer une sortie standard ou du syslog pour les logs.

Exemple pour l'écriture dans un fichier de logs :

```php
  'log_file' => '/var/log/orm-api/api.log',
```

Exemple pour la sortie des logs en syslog :

```php
  'log_file' => 'syslog',
```

### namespace

Namespace a utiliser pour la librairie ORM. Les valeurs possibles actuellement sont `'Mce'`, `'Mel'`, `'Mi'`, `'Dgfip'`, `'Gn'`. Dans le doute se référer a l'ORM et le dossier `Api`.

Exemple pour l'utilisateur du namespace Mce :

```php
  'namespace' => 'Mce',
```

### base_url

URL de base pour l'accès aux API. Doit correspondre exactement à ce qui se trouve derrière l'url racine.

Exemple: pour une url "https://api.exemple.com/api/api.php" la base sera `"/api/api.php"`

Exemple de configuration :

```php
  'base_url' => '/api/api.php/',
```

### auth_type_none

Permet d'ouvrir l'accès aux API sans utiliser d'authentification.

ATTENTION: Passer cette valeur à true peut constituer une faille de sécurité. A n'utiliser que pour les tests ou associé avec un filtre sur les adresses IP

La valeur par défaut est `false`.

Exemple d'activation de l'accès sans authentification :

```php
  'auth_type_none' => true,
```

### auth_type_basic

Permet d'ouvrir l'accès aux API via une authentification Basic de type identifiant/mot de passe. L'authentification de l'utilisateur se fait alors via l'ORM (authentification LDAP).

Le format de header a utiliser pour une authentification Basic est le suivant :

```
Authorization: Basic <base64("username:password")>
```

Exemple d'activation de l'accès via une authentification Basic :

```php
  'auth_type_basic' => true,
```

### auth_type_apikey

Permet d'ouvrir l'accès aux API via une clé d'API definie dans la configuration. La liste des clés d'API utilisables est définie dans la configuration `api_keys`.

Le format de header a utiliser pour une authentification Apikey est le suivant :

```
Authorization: Apikey <clé d'API>
```

Exemple d'activation de l'accès via une authentification Apikey :

```php
  'auth_type_apikey' => false,
```

### auth_type_bearer

Permet d'ouvrir l'accès aux API via un token bearer. Actuellement cette authentification n'est pas implémentée et nécessite un lien avec un fournisseur d'identité supportant de l'OpenID Connect ou un traitement de token JWT.

Le format de header a utiliser pour une authentification Bearer est le suivant :

```
Authorization: Bearer <Token>
```

Exemple d'activation de l'accès via une authentification Apikey :

```php
  'auth_type_bearer' => false,
```

### api_keys

Liste des clés d'API utilisables pour authentifier les applications dans les API. Utilisé uniquement si `auth_type_apikey` a été configuré à `true`.

Ces clés d'API sont à générer manuellement ou via un site internet (par exemple https://generate-random.org/api-key-generator), nous vous conseillons de les faire le plus complexe possible (minimum 128 bits).

Exemple de liste de clés d'API configurées :

```php
  'api_keys' => [
    '<cle-api-1>',
    '<cle-api-2>',
  ],
```

### ip_address_filter

Permet de n'accepter les connexions aux API ne venant que de certaines adresses IP. Permet de renforcer la sécurité par exemple dans un le cas d'un accès depuis locahost.

Si la valeur configuré est à `true`, il faudrait ensuite configurer la liste des adresse IP autorisées dans la configuration `valid_ip_addresses_list`.

Exemple de configuration pour activer la restriction d'accès par adresses IP :

```php
  'ip_address_filter' => true,
```

### valid_ip_addresses_list

Liste des adresses IP autorisées à utiliser les API. Cette configuration n'est active que si `ip_address_filter` est à `true`;

Exemple de restriction par adresse IP de localhost :

```php
  'valid_ip_addresses_list' => [
    '127.0.0.1',
  ],
```

### mapping

Permet de surcharger tout ou partie du mapping par défaut configuré dans le fichier [config/mapping.inc.php](../../src/config/mapping.inc.php). Un [array_merge](https://www.php.net/manual/fr/function.array-merge.php) est appliqué entre les valeurs des deux configurations. Cela permet d'écraser une configuration de mapping par défaut en utilisant la même clé.

Exemple pour écraser la configuration du mapping d'un calendrier :

```php
  'mapping' => [
    'calendar' => [
      'name',
      'owner',
      'perm',
    ],
  ],
```

### routing

Permet de surcharger tout ou partie du routing par défaut configuré dans le fichier [config/routing.inc.php](../../src/config/routing.inc.php). Un [array_merge](https://www.php.net/manual/fr/function.array-merge.php) est appliqué entre les valeurs des deux configurations. Cela permet d'écraser une configuration de mapping par défaut en utilisant la même clé.

Exemple pour écraser la configuration du routing d'un calendrier :

```php
  'routing' => [
    'calendar'      => [
      'methods'   => [
          'GET'       => true,
          'POST'      => false,
          'DELETE'    => false,
      ],
      'routing' => [
          'events' => [
              'class' => 'Calendar',
              'methods'   => [
                  'GET'       => 'events',
              ],
          ],
      ],
    ],
  ],
```