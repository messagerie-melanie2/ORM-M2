# user/addressbooks

[Retour à la documentation API](../../README.md#utilisation-de-lapi) | [Retour au endpoint user](../README.md#user)

API permettant de récupérer les carnets d'adresses d'un utilisateur

Il propose également des endpoints supplémentaires associés aux carnets d'adresses de l'utilisateur

## Endpoints

Liste des accès utilisables par l'API addressbooks

### user/addressbooks/default

```url
/api/api.php/user/addressbooks/default
```

Récupérer le carnet d'adresses par défaut de l'utilisateur

Voir [user/addressbooks/default](default/README.md#useraddressbooksdefault)

### user/addressbooks/shared

```url
/api/api.php/user/addressbooks/shared
```

Récupérer la liste de tous les carnets d'adresses accessibles à l'utilisateur

Voir [user/addressbooks/shared](shared/README.md#useraddressbooksshared)

## GET user/addressbooks

### Utilisation

```url
GET /api/api.php/user/addressbooks?uid=<user_uid>
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
    }
  ]
}
```

### Paramètres

 - `uid` : [Obligatoire] identifiant de l'utilisateur