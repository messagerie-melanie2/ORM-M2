# user/addressbooks/shared

[Retour à la documentation API](../../../README.md#utilisation-de-lapi) | [Retour au endpoint user](../../README.md#user) | [Retour au endpoint user/addressbooks](../README.md#useraddressbooks)

API permettant de récupérer tous les carnets d'adresses accessibles à un utilisateur

## GET user/addressbooks/shared

### Utilisation

```url
GET /api/api.php/user/addressbooks/shared?uid=<user_uid>
```

### Résultat

```json
{
  "success": true,
  "data": [
    {
      "id": "<addressbook_id>",
      "name": "<addressbook_name>",
      "owner": "<addressbook_owner>",
      "perm": "<perm_value>"
    },
    {
      "id": "<addressbook_id1>",
      "name": "<addressbook_name1>",
      "owner": "<addressbook_owner1>",
      "perm": "<perm_value1>"
    }
  ]
}
```

### Paramètres

 - `uid` : [Obligatoire] identifiant de l'utilisateur