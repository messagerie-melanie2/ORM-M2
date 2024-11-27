# calendar

[Retour à la documentation API](../README.md#utilisation-de-lapi)

API permettant de récupérer les informations d'un calendrier, créer, modifier ou supprimer un calendrier

Il propose également des endpoints supplémentaires associés au calendrier

## Endpoints

Liste des accès utilisables par l'API calendar

### calendar/events

```url
/api/api.php/calendar/events
```

Lister les événements associés à un calendrier

Voir [calendar/events](events/README.md#calendarevents)

### calendar/shares

```url
/api/api.php/calendar/shares
```

Lister les partages associés à un calendrier

Voir [calendar/shares](shares/README.md#calendarshares)

### calendar/share

```url
/api/api.php/calendar/share
```

Actions sur un partage associé à un calendrier

Voir [calendar/share](share/README.md#calendarshare)

## GET calendar

### Utilisation

```url
GET /api/api.php/calendar?id=<calendar_id>
```

### Résultat

```json
{
  "success": true,
  "data": {
    "id": "<calendar_id>",
    "name": "<calendar_name>",
    "owner": "<calendar_owner>",
    "perm": "<perm_value>"
  }
}
```

### Paramètres

 - `id` : [Obligatoire] identifiant du calendrier à récupérer
 - `user` : [Optionnel] identifiant de l'utilisateur à associer au calendrier (change la valeur de perm voir)

## POST calendar

### Utilisation

#### Url
```url
POST /api/api.php/calendar
```

#### Body
```json
{
  "id": "<calendar_id>",
  "name": "<calendar_name>",
  "owner": "<calendar_owner>"
}
```

#### Informations
 - `id` : Par convention l'id du calendrier principal de l'utilisateur est son identifiant
 - `name` : Par convention le name du calendrier principal de l'utilisateur est son fullname

### Résultat

```json
{
  "success": true
}
```

### Paramètres

 - `id` : [Obligatoire] identifiant du calendrier à créer ou modifier
 - `name` : [Obligatoire] nom du calendrier à créer ou modifier
 - `owner` : [Obligatoire en création] identifiant du propriétaire du calendrier à créer

## DELETE calendar

### Utilisation

```url
DELETE /api/api.php/calendar?id=<calendar_id>
```

### Résultat

```json
{
  "success": true,
}
```

### Paramètres

 - `id` : [Obligatoire] identifiant du calendrier à récupérer
 - `user` : [Optionnel] identifiant de l'utilisateur à associer au calendrier (change la valeur de perm voir)