# user

[Retour à la documentation API](../README.md#utilisation-de-lapi)

API permettant de récupérer les informations d'un utilisateur

Il propose également des endpoints supplémentaires associés à l'utilisateur

## Endpoints

Liste des accès utilisables par l'API user

### user/calendars

```url
/api/api.php/user/calendars
```

Lister les calendriers associés à un utilisateur

Voir [user/calendars](calendars/README.md#usercalendars)

### user/calendars/default

```url
/api/api.php/user/calendars/default
```

Récupérer le calendrier par défaut de l'utilisateur

Voir [user/calendars/default](calendars/default/README.md#usercalendarsdefault)

### user/calendars/shared

```url
/api/api.php/user/calendars/shared
```

Récupérer la liste de tous les calendriers accessibles à l'utilisateur

Voir [user/calendars/shared](calendars/shared/README.md#usercalendarsshared)

### user/addressbooks

```url
/api/api.php/user/addressbooks
```

Lister les carnets d'adresses associés à un utilisateur

Voir [user/addressbooks](addressbooks/README.md#useraddressbooks)

### user/addressbooks/default

```url
/api/api.php/user/addressbooks/default
```

Récupérer le carnet d'adresses par défaut de l'utilisateur

Voir [user/addressbooks/default](addressbooks/default/README.md#useraddressbooksdefault)

### user/addressbooks/shared

```url
/api/api.php/user/addressbooks/shared
```

Récupérer la liste de tous les carnets d'adresses accessibles à l'utilisateur

Voir [user/addressbooks/shared](addressbooks/shared/README.md#useraddressbooksshared)

## GET user

### Utilisation

```url
GET /api/api.php/user?uid=<user_uid>
```

### Résultat

```json
{
  "success": true,
  "data": {
    "dn": "<user_dn>",
    "uid": "<user_uid>",
    "fullname": "<user_fullname>",
    "name": "<user_name>",
    "email": "<user_email>",
    "email_list": [
      "<user_email>",
      "<user_email1>",
      "<user_email2>"
    ],
    "email_send": "<user_email_send>",
    "email_send_list": [
      "<user_email_send>"
    ],
    "type": "<user_type>"
  }
}
```

### Paramètres

 - `uid` : [Obligatoire] identifiant de l'utilisateur
