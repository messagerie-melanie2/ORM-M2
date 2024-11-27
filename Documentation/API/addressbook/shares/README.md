# addressbook/shares

[Retour à la documentation API](../../README.md#utilisation-de-lapi) | [Retour au endpoint addressbook](../README.md#addressbook)

API permettant de récupérer les partages associés à un carnet d'adresses

## GET addressbook/shares

Récupérer les partages associés à un carnet d'adresses à partir de l'identifiant du carnet

### Utilisation

```url
GET /api/api.php/addressbook/shares?id=<addressbook_id>&is_group=0
```

### Résultat

```json
{
  "success": true,
  "data": [
    {
      "name": "<user_uid>",
      "acl": "6"
    },
    {
      "name": "<user_uid1>",
      "acl": "30"
    },
    {
      "name": "<user_uid2>",
      "acl": "4"
    }
  ]
}
```

#### Informations
 - `name` : Identifiant de l'utilisateur ou du groupe
 - `acl` : Niveau de droit : `4` Disponibilités, `6` Lecture seule, `30` Lecture/écriture 

### Paramètres

 - `id` : [Obligatoire] identifiant du carnet d'adresses à récupérer
 - `name` : [Optionnel] identifiant de l'utilisateur à associer au carnet d'adresses
 - `is_group` : [Optionnel] Récupérer les droits sur les groupes ou sur les utilisateurs (valeurs `0` ou `1`)