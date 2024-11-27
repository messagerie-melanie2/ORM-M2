# user/calendars/shared

[Retour à la documentation API](../../../README.md#utilisation-de-lapi) | [Retour au endpoint user](../../README.md#user) | [Retour au endpoint user/calendars](../README.md#usercalendars)

API permettant de récupérer tous les calendriers accessibles à un utilisateur

## GET user/calendars/shared

### Utilisation

```url
GET /api/api.php/user/calendars/shared?uid=<user_uid>
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
    },
    {
      "id": "<calendar_id1>",
      "name": "<calendar_name1>",
      "owner": "<calendar_owner1>",
      "perm": "<perm_value1>"
    }
  ]
}
```

### Paramètres

 - `uid` : [Obligatoire] identifiant de l'utilisateur