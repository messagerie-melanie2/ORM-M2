# Guide d'utilisation de l'ORM dans le code

[[_TOC_]]

## A/ Gestion des namespaces

### 1 - Introduction

Toute la configuration de l'environnement se fait à partir du namespace. Par exemple, pour définir un utilisateur sur l'environnement du Ministère de l'Intérieur il faut utiliser `LibMelanie\Api\Mi\User` alors que pour un utilisateur de l'environnement Ministère de la Transition Écologique ce sera `LibMelanie\Api\Mel\User` (Mel = nom de la messagerie du MTE).

### 2 - Utilisation des namespaces dynamiques

Un namespace peut être défini au début du fichier php,

```php
use LibMelanie\Api\Mel\User;
```

Ou bien utilisé a chaque appel d'une classe

```php
$user = new LibMelanie\Api\Mel\User();
```

L'inconvénient de ces deux usages est qu'ils sont statiques. Il n'est donc pas possible de configurer l'environnement du ministère de destination via cette méthode. Il faut donc passer par l'utilisation de namespaces dynamiques.

Une façon simple de le faire est de passer par une variable.

```php
$namespace = 'LibMelanie\Api\Mel';
$class = $namespace . '\User'
$user = new $class();
```

Cela permet d'avoir une valeur dynamique en fonction de l'environnement

```php
if ($environnement == 'mi') {
    $namespace = 'LibMelanie\Api\Mi';
}
else if ($environnement == 'mte') {
    $namespace = 'LibMelanie\Api\Mel';
}

$class = $namespace . '\User'
$user = new $class();
```

### 3 - Utilisation de la réflection

Pour aller un peu plus loin et avoir une instanciation dynamique des objets en fonction d'une configuration du namespace, il est possible d'utiliser la réflection. Par exemple ce code peut être intégré dans une class pour générer les bons objets, la méthode `Class->user()` permettant alors d'instancier un objet User en fonction d'un namespace.

```php
  /**
   * Generate an object from the ORM with the right Namespace
   * 
   * @param string $objectName Object name (add sub namespace if needed, ex : Event, Users\Type)
   * @param array $params [Optionnal] parameters of the constructor
   * 
   * @return staticClass object of the choosen type
   */
  protected function object($objectName, $params = []) {
    $class = new \ReflectionClass(static::$_objectsNS . $objectName);
    return $class->newInstanceArgs($params);
  }

  /**
   * Generate user object from the ORM with the right Namespace
   * 
   * @param array $params [Optionnal] parameters of the constructor
   * 
   * @return \LibMelanie\Api\Defaut\User
   */
  public function user($params = []) {
    return $this->object('User', $params);
  }
```

Même chose pour la récupération d'une constante de classe, il est possible d'utiliser la lecture dynamique pour récupérer sa valeur en fonction du namespace.

```php
  /**
   * Return constantName value from objectName and NS
   *
   * @param string $objectName Name of the object
   * @param string $constantName Name of the constant
   *
   * @return mixed constant value
   */
  public static function const($objectName, $constantName) {
    return constant(static::$_objectsNS . $objectName . '::' . $constantName);
  }
```

Puis ensuite de récupérer la valeur de la constante de classe.

```php
$value = \myclass::const('User', 'RIGHT_SEND')
```

Permet de récupérer la valeur de la constante `User::RIGHT_SEND` avec User qui dépend du namespace configuré.

## B/ User

### 1 - Chargement d'un utilisateur

Pour récupérer les informations d'un utilisateur, il faut utiliser une clé, qui est en général son uid, puis le charger. 

Par exemple,

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';
$user->load();
```

`load()` retourne un booléen il est donc possible de tester si l'utilisateur existe dans son annuaire en faisant

```php
if ($user->load()) {

}
```

En principe, il est également possible de charger un utilisateur depuis son email

```php
$user = new LibMelanie\Api\Mce\User();
$user->email = 'thomas.test1@example.com';
$user->load();
```

Ou depuis son DN

```php
$user = new LibMelanie\Api\Mce\User();
$user->dn = 'uid=thomas.test1,ou=example,ou=com';
$user->load();
```

_A noter :_ ces possibilités peuvent dépendre des environnements des ministères.

### 2 - Lire les informations d'un utilisateur

Une fois qu'un utilisateur est chargé, il est possible de lire ses informations.

Comme son nom,

```php
$name = $user->name;
```

ou son email,

```php
$mail = $user->email;
```

Voici la liste des attributs de base pour un utilisateur

```
 * @property string $dn DN de l'utilisateur dans l'annuaire            
 * @property string $uid Identifiant unique de l'utilisateur
 * @property string $fullname Nom complet de l'utilisateur
 * @property string $name Nom de l'utilisateur
 * @property string $type Type de boite (voir Mce\Users\Type::*)
 * @property string $email Adresse email principale de l'utilisateur
 * @property array $email_list Liste de toutes les adresses email de l'utilisateur
 * @property string $email_send Adresse email d'émission principale de l'utilisateur
 * @property array $email_send_list Liste de toutes les adresses email d'émission de l'utilisateur
 * @property Share[] $shares Liste des partages de la boite
 * @property string $street Adresse - Rue de l'utilisateur
 * @property string $postalcode Adresse - Code postal de l'utilisateur
 * @property string $locality Adresse - Ville de l'utilisateur
 * @property string $title Titre de l'utilisateur
```

En fonction de l'environnement certains attributs supplémentaire peuvent être disponibles, il faut se référer à la [documentation d'API de l'ORM](https://messagerie-melanie2.github.io/ORM-M2/namespaces/LibMelanie.Api.html) pour avoir plus d'informations par namespace.

### 3 - Récupération des boites partagées

Depuis un objet User il est possible de récupérer les boites partagées auxquelles il a accès. Pour cela plusieurs méthodes existent :

- Pour récupérer toutes les boites partagées auxquelles un utilisateur accède : 

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';
$objects = $user->getObjectsShared();
```

- Pour récupérer les boites partagées auxquelles un utilisateur accède avec les droits d'émission : 

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';
$objects = $user->getObjectsSharedEmission();
```

- Pour récupérer les boites partagées auxquelles un utilisateur accède avec les droits de gestionnaire : 

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';
$objects = $user->getObjectsSharedGestionnaire();
```

_A noter :_ Ce qui est récupéré ce sont des objets de partage (ObjectShare) et non pas les boites (User), il faut donc un léger traitement si on souhaite récupérer les informations de la boite et non pas de l'objet de partage (voir ci-dessous).

_A noter 2 :_ La boite individuelle de l'utilisateur ne fait pas parti de la liste des boites récupérées par ces trois méthodes, car elle n'est pas partagé. Pour ajouter la boite individuelle de l'utilisateur il suffit d'utiliser l'objet User chargé (`load()`).

Pour récupérer ensuite les informations depuis ces objets :

```php
  foreach ($objects as $object) {
    $object_uid = $object->uid;
    $mailbox_uid = $object->mailbox->uid;
  }
```

_A noter :_ en faisant `$object->uid` on récupère l'uid de l'objet de partage, alors qu'en faisant `$object->mailbox->uid` on récupère l'uid de la boite mail.

### 4 - Récupération des calendriers

Depuis l'objet User, il est possible de récupérer le calendrier par défaut de l'utilisateur

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';
$calendar = $user->getDefaultCalendar();
```

Il est également possible de récupérer la liste des calendriers appartenants à l'utilisateur

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';
$calendars = $user->getUserCalendars();
```

Et enfin, il est possible de récupérer tous les calendriers sur lesquels l'utilisateur à un accès (partagés ou non)

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';
$calendars = $user->getSharedCalendars();
```

### 5 - Récupération des carnets d'adresses

Depuis l'objet User, il est possible de récupérer le carnet par défaut de l'utilisateur

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';
$addressbook = $user->getDefaultAddressbook();
```

Il est également possible de récupérer la liste des carnets appartenants à l'utilisateur

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';
$addressbooks = $user->getUserAddressbooks();
```

Et enfin, il est possible de récupérer tous les carnets sur lesquels l'utilisateur à un accès (partagés ou non)

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';
$addressbooks = $user->getSharedAddressbooks();
```

### 6 - Récupération des listes de tâches

Depuis l'objet User, il est possible de récupérer la liste de tâches par defaut de l'utilisateur

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';
$taskslist = $user->getDefaultTaskslist();
```

Il est également possible de récupérer la liste des listes de tâches appartenants à l'utilisateur

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';
$taskslists = $user->getUserTaskslists();
```

Et enfin, il est possible de récupérer toutes les listes de tâches sur lesquels l'utilisateur à un accès (partagés ou non)

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';
$taskslists = $user->getSharedTaskslists();
```

## C/ Calendar

### 1 - Chargement d'un calendrier

Un calendrier individuel peut se charger depuis son identifiant

```php
$calendar = new LibMelanie\Api\Mce\Calendar();
$calendar->id = 'thomas.test1';
$calendar->load();
```

ou depuis son identifiant et son propriétaire via un objet User

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';

$calendar = new LibMelanie\Api\Mce\Calendar($user);
$calendar->id = 'thomas.test1';
$calendar->load();
```

### 2 - Récupération des événements depuis un calendrier

Depuis un calendrier il est possible de récupérer tous les événements associés

```php
$events = $calendar->getAllEvents();
```

Ou bien seulement les événements contenus entre des dates

```php
$events = $calendar->getRangeEvents('2022-09-01', '2022-09-07');
```

Plusieurs possibilités de filtres existent dans la méthode `getRangeEvents()`, voir la [définition](https://messagerie-melanie2.github.io/ORM-M2/classes/LibMelanie.Api.Defaut.Calendar.html#method_getRangeEvents) pour plus d'informations

## D/ Addressbook

### 1 - Chargement d'un carnet d'adresses

Un carnet individuel peut se charger depuis son identifiant

```php
$addressbook = new LibMelanie\Api\Mce\Addressbook();
$addressbook->id = 'thomas.test1';
$addressbook->load();
```

ou depuis son identifiant et son propriétaire via un objet User

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';

$addressbook = new LibMelanie\Api\Mce\Addressbook($user);
$addressbook->id = 'thomas.test1';
$addressbook->load();
```

### 2 - Récupération des contacts depuis un carnet d'adresses

Depuis un carnet il est possible de récupérer tous les contacts associés

```php
$contacts = $addressbook->getAllContacts();
```

## D/ Taskslist

### 1 - Chargement d'une liste de tâches

Une liste de tâches individuelle peut se charger depuis son identifiant

```php
$takslist = new LibMelanie\Api\Mce\Taskslist();
$takslist->id = 'thomas.test1';
$takslist->load();
```

ou depuis son identifiant et son propriétaire via un objet User

```php
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.test1';

$takslist = new LibMelanie\Api\Mce\Taskslist($user);
$takslist->id = 'thomas.test1';
$takslist->load();
```

### 2 - Récupération des tâches depuis une liste de tâches

Depuis une liste de tâches il est possible de récupérer toutes les tâches associées

```php
$tasks = $taskslist->getAllTasks();
```