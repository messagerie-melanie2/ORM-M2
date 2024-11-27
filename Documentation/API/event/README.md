# event

[Retour à la documentation API](../README.md#utilisation-de-lapi)

API permettant de récupérer les informations d'un événement, créer, modifier ou supprimer un événement

## GET event

Récupérer un événement à partir de son uid et son calendrier

### Utilisation

```url
GET /api/api.php/event?calendar=<calendar_id>&uid=<event_uid>
```

### Résultat

#### Événement simple
```json
{
  "success": true,
  "data": {
    "uid": "<event_uid>",
    "realuid": "<event_realuid>",
    "calendar": "<calendar_id>",
    "owner": "<event_owner>",
    "title": "Test",
    "status": "confirmed",
    "class": "public",
    "transparency": "OPAQUE",
    "start": "2021-05-13 12:30:00",
    "end": "2021-05-13 17:30:00",
    "created": 1620814548,
    "modified": 1620814548,
    "timezone": "Europe/Paris"
  }
}
```

### Événement avec pièce jointe
```json
{
  "success": true,
  "data": {
    "uid": "<event_uid>",
    "realuid": "<event_realuid>",
    "calendar": "<calendar_id>",
    "owner": "<event_owner>",
    "title": "Événement avec pièce jointe",
    "status": "confirmed",
    "class": "public",
    "sequence": 1,
    "transparency": "OPAQUE",
    "attachments": [
      {
        "name": "index.html",
        "path": ".horde/kronolith/documents/<event_uid>/<event_owner>",
        "contenttype": "text/html",
        "size": 783,
        "modified": 1697792052,
        "owner": "<event_owner>"
      }
    ],
    "start": "2023-10-20 11:30:00",
    "end": "2023-10-20 12:00:00",
    "created": 1697792051,
    "modified": 1697792052,
    "timezone": "Europe/Paris"
  }
}
```

#### Réunion récurrente avec exceptions
```json
{
  "success": true,
  "data": {
    "uid": "<event_uid>",
    "realuid": "<event_realuid>",
    "calendar": "<calendar_id>",
    "owner": "<event_owner>",
    "title": "Test invitation récurrente",
    "location": "Lyon",
    "status": "confirmed",
    "class": "public",
    "sequence": 6,
    "transparency": "OPAQUE",
    "attendees": [
      {
        "name": "Nom participant",
        "email": "<email_participant>",
        "role": "req_participant",
        "response": "need_action"
      }
    ],
    "organizer": {
      "name": "Nom organisateur",
      "email": "<email_organisateur>"
    },
    "start": "2022-04-04 11:00:00",
    "end": "2022-04-04 13:30:00",
    "created": 1649339702,
    "modified": 1650634623,
    "timezone": "Europe/Paris",
    "exceptions": {
      "20220408": {
        "uid": "<event_uid>",
        "realuid": "<exception_realuid>",
        "calendar": "<calendar_id>",
        "owner": "<event_owner>",
        "title": "Test invitation récurrente",
        "location": "Lyon",
        "status": "confirmed",
        "class": "public",
        "sequence": 6,
        "transparency": "OPAQUE",
        "attendees": [
          {
            "name": "Nom participant",
            "email": "<email_participant>",
            "role": "req_participant",
            "response": "need_action"
          }
        ],
        "organizer": {
          "name": "Nom organisateur",
          "email": "<email_organisateur>"
        },
        "start": "2022-04-08 14:00:00",
        "end": "2022-04-08 16:30:00",
        "created": 1649341475,
        "modified": 1649341475,
        "timezone": "Europe/Paris",
        "recurrence_id": "2022-04-08 11:00:00"
      },
      "20220420": {
        "uid": "<event_uid>",
        "realuid": "<exception_realuid>",
        "calendar": "<calendar_id>",
        "status": "confirmed",
        "class": "public",
        "deleted": true,
        "transparency": "OPAQUE",
        "organizer": {
          "name": "Nom organisateur",
          "email": "<email_organisateur>"
        },
        "timezone": "Europe/Paris",
        "recurrence_id": "2022-04-20 11:00:00"
      }
    },
    "recurrence": {
      "enddate": "2022-04-22 11:30:00",
      "interval": "1",
      "type": "daily"
    }
  }
}
```

### Paramètres

 - `calendar_id` : [Obligatoire] identifiant du calendrier auquel appartient l'événement
 - `event_uid` : [Obligatoire] identifiant de l'événement à récupérer
 - `_get_attachments_data` : `1` pour récupérer les données des pièces jointes encodées en base64, `0` pour ne pas récupérer les données (valeur par défaut)

## POST event

Créer un nouvel événement ou en modifier un existant

### Utilisation

#### Url
```url
POST /api/api.php/event
```

#### Body
```json
{
    "uid": "<event_uid>",
    "realuid": "<event_realuid>",
    "calendar": "<calendar_id>",
    "owner": "<event_owner>",
    "title": "Test invitation récurrente",
    "location": "Lyon",
    "status": "confirmed",
    "class": "public",
    "sequence": 6,
    "transparency": "OPAQUE",
    "attendees": [
      {
        "name": "Nom participant",
        "email": "<email_participant>",
        "role": "req_participant",
        "response": "need_action"
      }
    ],
    "organizer": {
      "name": "Nom organisateur",
      "email": "<email_organisateur>"
    },
    "start": "2022-04-04 11:00:00",
    "end": "2022-04-04 13:30:00",
    "created": 1649339702,
    "modified": 1650634623,
    "timezone": "Europe/Paris",
    "exceptions": {
      "20220408": {
        "uid": "<event_uid>",
        "realuid": "<exception_realuid>",
        "calendar": "<calendar_id>",
        "owner": "<event_owner>",
        "title": "Test invitation récurrente",
        "location": "Lyon",
        "status": "confirmed",
        "class": "public",
        "sequence": 6,
        "transparency": "OPAQUE",
        "attendees": [
          {
            "name": "Nom participant",
            "email": "<email_participant>",
            "role": "req_participant",
            "response": "need_action"
          }
        ],
        "organizer": {
          "name": "Nom organisateur",
          "email": "<email_organisateur>"
        },
        "start": "2022-04-08 14:00:00",
        "end": "2022-04-08 16:30:00",
        "created": 1649341475,
        "modified": 1649341475,
        "timezone": "Europe/Paris",
        "recurrence_id": "2022-04-08 11:00:00"
      },
      "20220420": {
        "uid": "<event_uid>",
        "realuid": "<exception_realuid>",
        "calendar": "<calendar_id>",
        "status": "confirmed",
        "class": "public",
        "deleted": true,
        "transparency": "OPAQUE",
        "organizer": {
          "name": "Nom organisateur",
          "email": "<email_organisateur>"
        },
        "timezone": "Europe/Paris",
        "recurrence_id": "2022-04-20 11:00:00"
      }
    },
    "recurrence": {
      "enddate": "2022-04-22 11:30:00",
      "interval": "1",
      "type": "daily"
    },
    "attachments": [
      {
        "name": "<nom_pièce_jointe>",
        "path": "<chemin_complet_vers_la_pièce_jointe>",
        "contenttype": "<content_type_de_la_pièce_jointe>",
        "size": 783,
        "modified": 1697792052,
        "owner": "<propriétaire_de_la_pièce_jointe>",
        "encoding": "base64",
        "data": "<données_de_la_pièce_jointe_encodées_en_base64>",
      }
    ]
}
```

#### Informations
 - `status` : Valeurs possibles : `confirmed`, `cancelled`, `tentative`, `none`
 - `class` : Valeurs possibles : `public`, `private`
 - `transparency` : Valeurs possibles : `OPAQUE`, `TRANSPARENT`
 - `attendees` :
   - `role` : Valeurs possibles : `chair`, `req_participant`, `opt_participant`, `non_participant`
   - `response` : Valeurs possibles : `need_action`, `accepted`, `declined`, `in_process`
 - `recurrence` :
   - `type` : Valeurs possibles : `daily`, `weekly`, `monthly`, `monthly_by_day`, `yearly`, `yearly_by_day`

### Résultat

```json
{
  "success": true
}
```

### Paramètres

 - `calendar` : [Obligatoire] identifiant du calendrier auquel appartient l'événement
 - `uid` : [Obligatoire] identifiant de l'événement à récupérer

## DELETE event

Supprimer un événement existant

### Utilisation

```url
DELETE /api/api.php/event?calendar=<calendar_id>&uid=<event_uid>
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