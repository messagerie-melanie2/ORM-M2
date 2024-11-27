# calendar/share

[Retour à la documentation API](../../README.md#utilisation-de-lapi) | [Retour au endpoint calendar](../README.md#calendar)

API permettant de récupérer les informations d'un partage, créer, modifier ou supprimer un partage associé à un calendrier

## GET calendar/share

Récupérer un partage de calendrier depuis le carnet et l'identifiant du partage

### Utilisation

```url
GET /api/api.php/calendar/share?id=<calendar_id>&name=<user_uid>
```

### Résultat

```json
{
  "success": true,
  "data": {
    "acl": "30"
  }
}
```

#### Informations
 - `acl` : Niveau de droit : `4` Disponibilités, `6` Lecture seule, `30` Lecture/écriture 

### Paramètres

 - `id` : [Obligatoire] identifiant du calendrier à récupérer
 - `name` : [Obligatoire] identifiant de l'utilisateur ou du groupe pour lequel le partage est positionné

## POST calendar/share

Créer un nouveau partage de calendrier ou en modifier un existant

### Utilisation

#### Url
```url
POST /api/api.php/calendar/share
```

#### Body
```json
{
  "id": "<calendar_id>",
  "name": "<user_uid>",
  "acl": "6"
}
```

### Résultat

```json
{
  "success": true
}
```

### Paramètres

 - `id` : [Obligatoire] identifiant du calendrier sur lequel doit être positionné le partage
 - `name` : [Obligatoire] identifiant de l'utilisateur ou du groupe pour lequel le partage doit être positionné
 - `acl` : [Obligatoire] Niveau de droit : `4` Disponibilités, `6` Lecture seule, `30` Lecture/écriture 

## DELETE calendar/share

Supprimer un partage de calendrier existant

### Utilisation

```url
DELETE /api/api.php/calendar/share?id=<calendar_id>&name=<user_uid>
```

### Résultat

```json
{
  "success": true,
}
```

### Paramètres

 - `calendar_id` : [Obligatoire] identifiant du calendrier auquel appartient l'événement
 - `event_uid` : [Obligatoire] identifiant de l'événement à supprimer