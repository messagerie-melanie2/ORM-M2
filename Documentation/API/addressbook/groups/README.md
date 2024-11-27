# addressbook/groups

[Retour à la documentation API](../../README.md#utilisation-de-lapi) | [Retour au endpoint addressbook](../README.md#addressbook)

API permettant de récupérer les groupes de contacts associés à un carnet d'adresses

## GET addressbook/groups

Récupérer tous les groupes de contacts associés à un carnet d'adresses

### Utilisation

```url
GET /api/api.php/addressbook/groups?id=<addressbook_id>
```

### Résultat

```json
{
  "success": true,
  "data": [
    {
      "addressbook": "<contact_addressbook>",
      "uid": "<contact_uid>",
      "type": "Object",
      "modified": 1606313440,
      "name": "Thomas Payen",
      "firstname": "Thomas",
      "lastname": "Payen"
    }
  ]
}
```

### Paramètres

 - `id` : [Obligatoire] identifiant du carnet d'adresses à récupérer
