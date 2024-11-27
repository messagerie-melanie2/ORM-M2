# contact

[Retour à la documentation API](../README.md#utilisation-de-lapi)

API permettant de récupérer les informations d'un contacts, créer, modifier ou supprimer un contact

## GET contact

Récupérer un contact à partir de son uid et de son carnet d'adresses

### Utilisation

```url
GET /api/api.php/contact?addressbook=<addressbook_id>&uid=<contact_uid>
```

### Résultat

```json
{
  "success": true,
  "data": {
    "addressbook": "<contact_addressbook>",
    "uid": "<contact_uid>",
    "modified": 1654786678,
    "name": "Nom d'affichage",
    "alias": "Surnom",
    "freebusyurl": "www.freebusy.fr",
    "firstname": "Prénom",
    "lastname": "Nom",
    "middlenames": "Second prénom",
    "nameprefix": "Préfixe",
    "namesuffix": "Suffixe",
    "birthday": "2022-06-08",
    "company": "Organisation",
    "notes": "Mes notes pour voir",
    "email": "domicile@exemple.fr",
    "email1": "travail@exemple.fr",
    "email2": "autre@exemple.fr",
    "cellphone": "06 67 87 76 65",
    "fax": "04 37 86 85 94",
    "category": "Catégories",
    "url": "www.siteweb.fr",
    "homephone": "04 78 67 64 65",
    "homestreet": "Rue du domicile",
    "homecity": "Lyon",
    "homeprovince": "Rhone",
    "homepostalcode": "69003",
    "homecountry": "FR",
    "workphone": "08 99 87 96 93",
    "workstreet": "Rue du travail",
    "workcity": "L'isle d'abeau",
    "workprovince": "Isere",
    "workpostalcode": "38080",
    "workcountry": "FR",
    "pager": "08 97 96 95 008",
    "role": "Appellation d'emmploi"
  }
}
```

### Paramètres

 - `addressbook_id` : [Obligatoire] identifiant du carnet d'adresse auquel appartient le contact
 - `contact_uid` : [Obligatoire] identifiant du contact à récupérer

## POST contact

Créer un nouveau contact ou en modifier un existant

### Utilisation

#### Url
```url
POST /api/api.php/contact
```

#### Body
```json
{
  "addressbook": "<contact_addressbook>",
  "uid": "<contact_uid>",
  "modified": 1654786678,
  "name": "Nom d'affichage",
  "alias": "Surnom",
  "freebusyurl": "www.freebusy.fr",
  "firstname": "Prénom",
  "lastname": "Nom",
  "middlenames": "Second prénom",
  "nameprefix": "Préfixe",
  "namesuffix": "Suffixe",
  "birthday": "2022-06-08",
  "company": "Organisation",
  "notes": "Mes notes pour voir",
  "email": "domicile@exemple.fr",
  "email1": "travail@exemple.fr",
  "email2": "autre@exemple.fr",
  "cellphone": "06 67 87 76 65",
  "fax": "04 37 86 85 94",
  "category": "Catégories",
  "url": "www.siteweb.fr",
  "homephone": "04 78 67 64 65",
  "homestreet": "Rue du domicile",
  "homecity": "Lyon",
  "homeprovince": "Rhone",
  "homepostalcode": "69003",
  "homecountry": "FR",
  "workphone": "08 99 87 96 93",
  "workstreet": "Rue du travail",
  "workcity": "L'isle d'abeau",
  "workprovince": "Isere",
  "workpostalcode": "38080",
  "workcountry": "FR",
  "pager": "08 97 96 95 008",
  "role": "Appellation d'emmploi"
}
```

### Résultat

```json
{
  "success": true
}
```

### Paramètres

 - `addressbook` : [Obligatoire] identifiant du carnet d'adresses auquel appartient le contact
 - `uid` : [Obligatoire] identifiant du contact à récupérer

## DELETE contact

Supprimer un contact existant

### Utilisation

```url
DELETE /api/api.php/contact?addressbook=<addressbook_id>&uid=<contact_uid>
```

### Résultat

```json
{
  "success": true,
}
```

### Paramètres

 - `addressbook_id` : [Obligatoire] identifiant du carnet d'adresses auquel appartient l'événement
 - `contact_uid` : [Obligatoire] identifiant du contact à supprimer