# user/calendars

[Retour à la documentation API](../../README.md#utilisation-de-lapi) | [Retour au endpoint user](../README.md#user)

API permettant de récupérer les calendriers d'un utilisateur

Il propose également des endpoints supplémentaires associés aux calendriers de l'utilisateur

## Endpoints

Liste des accès utilisables par l'API calendars

### user/calendars/default

```url
/api/api.php/user/calendars/default
```

Récupérer le calendrier par défaut de l'utilisateur

Voir [user/calendars/default](default/README.md#usercalendarsdefault)

### user/calendars/shared

```url
/api/api.php/user/calendars/shared
```

Récupérer la liste de tous les calendriers accessibles à l'utilisateur

Voir [user/calendars/shared](shared/README.md#usercalendarsshared)

## GET user/calendars

### Utilisation

```url
GET /api/api.php/user/calendars?uid=<user_uid>
```

### Résultat

```json
{
  "success": true,
  "data": [
    {
      "id": "<calendar_id>",
      "name": "<calendar_name>",
      "owner": "<calendar_owner>",
      "perm": "<perm_value>"
    }
  ]
}
```

### Paramètres

 - `uid` : [Obligatoire] identifiant de l'utilisateur