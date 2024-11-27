# addressbook

[Retour à la documentation API](../README.md#utilisation-de-lapi)

API permettant de récupérer les informations d'un carnet d'adresses, créer, modifier ou supprimer un carnet d'adresses

Il propose également des endpoints supplémentaires associés au carnet d'adresses

## Endpoints

Liste des accès utilisables par l'API addressbook

### addressbook/contacts

```url
/api/api.php/addressbook/contacts
```

Lister les contacts associés à un carnet d'adresses

Voir [addressbook/contacts](contacts/README.md#addressbookcontacts)

### addressbook/groups

```url
/api/api.php/addressbook/groups
```

Lister les groupes de contacts associés à un carnet d'adresses

Voir [addressbook/groups](groups/README.md#addressbookgroups)

### addressbook/shares

```url
/api/api.php/addressbook/shares
```

Lister les partages associés à un carnet d'adresses

Voir [addressbook/shares](shares/README.md#addressbookshares)

### addressbook/share

```url
/api/api.php/addressbook/share
```

Actions sur un partage associé à un carnet d'adresses

Voir [addressbook/share](share/README.md#addressbookshare)

## GET addressbook

Récupérer un carnet d'adresses à partir de son identifiant

### Utilisation

```url
GET /api/api.php/addressbook?id=<addressbook_id>
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

 - `id` : [Obligatoire] identifiant du carnet d'adresses à récupérer
 - `user` : [Optionnel] identifiant de l'utilisateur à associer au carnet d'adresses (change la valeur de perm voir)

## POST addressbook

Créer un nouveau carnet d'adresses ou en modifier un existant

### Utilisation

#### Url
```url
POST /api/api.php/addressbook
```

#### Body
```json
{
  "id": "<addressbook_id>",
  "name": "<addressbook_name>",
  "owner": "<addressbook_owner>"
}
```

#### Informations
 - `id` : Par convention l'id du carnet d'adresses principal de l'utilisateur est son identifiant
 - `name` : Par convention le name du carnet d'adresses principal de l'utilisateur est son fullname

### Résultat

```json
{
  "success": true
}
```

### Paramètres

 - `id` : [Obligatoire] identifiant du carnet d'adresses à créer ou modifier
 - `name` : [Obligatoire] nom du carnet d'adresses à créer ou modifier
 - `owner` : [Obligatoire en création] identifiant du propriétaire du carnet d'adresses à créer

## DELETE addressbook

Supprimer un carnet d'adresses existant

### Utilisation

```url
DELETE /api/api.php/addressbook?id=<addressbook_id>
```

### Résultat

```json
{
  "success": true,
}
```

### Paramètres

 - `id` : [Obligatoire] identifiant du carnet d'adresses à récupérer
 - `user` : [Optionnel] identifiant de l'utilisateur à associer au carnet d'adresses (change la valeur de perm voir)