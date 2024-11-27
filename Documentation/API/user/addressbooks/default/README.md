# user/addressbooks/default

[Retour à la documentation API](../../../README.md#utilisation-de-lapi) | [Retour au endpoint user](../../README.md#user) | [Retour au endpoint user/addressbooks](../README.md#useraddressbooks)

API permettant de récupérer le carnet d'adresses par défaut d'un utilisateur

## GET user/addressbooks/default

### Utilisation

```url
GET /api/api.php/user/addressbooks/default?uid=<user_uid>
```

### Résultat

```json
{
  "success": true,
  "data": {
    "id": "<addressbook_id>",
    "name": "<addressbook_name>",
    "owner": "<addressbook_owner>",
    "perm": "<perm_value>"
  }
}
```

### Paramètres

 - `uid` : [Obligatoire] identifiant de l'utilisateur