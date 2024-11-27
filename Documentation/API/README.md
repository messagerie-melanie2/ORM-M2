# Utilisation de l'API

L'API ORM MCE est une API Restful se basant sur l'URL, les méthodes HTTP et les paramètres pour retourner ou modifier des données. Le point d'entrée est le fichier `api.php`.

## Endpoints

Liste des accès utilisables par l'API

### user

```url
/api/api.php/user
```

Actions autour d'un utilisateur et des objets associés à un utilisateur (partages, calendriers, ...).

Voir [user](user/README.md#user)

### user/calendars

```url
/api/api.php/user/calendars
```

Lister les calendriers associés à un utilisateur

Voir [user/calendars](user/calendars/README.md#usercalendars)

### user/calendars/default

```url
/api/api.php/user/calendars/default
```

Récupérer le calendrier par défaut de l'utilisateur

Voir [user/calendars/default](user/calendars/default/README.md#usercalendarsdefault)

### user/calendars/shared

```url
/api/api.php/user/calendars/shared
```

Récupérer la liste de tous les calendriers accessibles à l'utilisateur

Voir [user/calendars/shared](user/calendars/shared/README.md#usercalendarsshared)

### user/addressbooks

```url
/api/api.php/user/addressbooks
```

Lister les carnets d'adresses associés à un utilisateur

Voir [user/addressbooks](user/addressbooks/README.md#useraddressbooks)

### user/addressbooks/default

```url
/api/api.php/user/addressbooks/default
```

Récupérer le carnet d'adresses par défaut de l'utilisateur

Voir [user/addressbooks/default](user/addressbooks/default/README.md#useraddressbooksdefault)

### user/addressbooks/shared

```url
/api/api.php/user/addressbooks/shared
```

Récupérer la liste de tous les carnets d'adresses accessibles à l'utilisateur

Voir [user/addressbooks/shared](user/addressbooks/shared/README.md#useraddressbooksshared)

### calendar

```url
/api/api.php/calendar
```

Actions autour d'un calendrier et des objets associés à un calendrier (partages, événements, ...).

Voir [calendar](calendar/README.md#calendar)

### calendar/events

```url
/api/api.php/calendar/events
```

Lister les événements associés à un calendrier

Voir [calendar/events](calendar/events/README.md#calendarevents)

### calendar/shares

```url
/api/api.php/calendar/shares
```

Lister les partages associés à un calendrier

Voir [calendar/shares](calendar/shares/README.md#calendarshares)

### calendar/share

```url
/api/api.php/calendar/share
```

Actions sur un partage associé à un calendrier

Voir [calendar/share](calendar/share/README.md#calendarshare)

### event

```url
/api/api.php/event
```

Actions autour d'un événement et des objets associés à un événement (pièces jointes, participants, ...).

Voir [event](event/README.md#event)

### attachment

```url
/api/api.php/attachment
```

Actions autour d'une pièce jointe d'événements.

Voir [attachment](attachment/README.md#attachment)

### addressbook

```url
/api/api.php/addressbook
```

Actions autour d'un carnet d'adresses et des objets associés à un carnet d'adresse (partages, contacts, ...).

Voir [addressbook](addressbook/README.md#addressbook)

### addressbook/contacts

```url
/api/api.php/addressbook/contacts
```

Lister les contacts associés à un carnet d'adresses

Voir [addressbook/contacts](addressbook/contacts/README.md#addressbookcontacts)

### addressbook/groups

```url
/api/api.php/addressbook/groups
```

Lister les groupes de contacts associés à un carnet d'adresses

Voir [addressbook/groups](addressbook/groups/README.md#addressbookgroups)

### addressbook/shares

```url
/api/api.php/addressbook/shares
```

Lister les partages associés à un carnet d'adresses

Voir [addressbook/shares](addressbook/shares/README.md#addressbookshares)

### addressbook/share

```url
/api/api.php/addressbook/share
```

Actions sur un partage associé à un carnet d'adresses

Voir [addressbook/share](addressbook/share/README.md#addressbookshare)

### contact

```url
/api/api.php/contact
```

Actions autour d'un contact

Voir [contact](contact/README.md#contact)

## Authentification

L'authentification se fait sur le header `Authorization` ou `authorization` (Bug fetch sur Firefox). Plusieurs types d'authentification sont possibles.

### Basic

L'authentification se fait avec l'identifiant et le mot de passe de l'utilisateur.

```header
Authorization: Basic YWxhZGRpbjpvcGVuc2VzYW1l
```

Les identifiants `Basic` sont construits de la manière suivante :
 - L'identifiant de l'utilisateur et le mot de passe sont combinés avec deux-points : (aladdin:sesameOuvreToi).
 - Cette chaîne de caractères est ensuite encodée en base64 (YWxhZGRpbjpzZXNhbWVPdXZyZVRvaQ==).

### Apikey

L'authentification se fait avec une clé d'API fournie par l'API.

```header
Authorization: Apikey <Clé d'API>
```

### Bearer

Non implémenté à l'heure actuelle