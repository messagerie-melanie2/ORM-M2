# calendar/events

[Retour à la documentation API](../../README.md#utilisation-de-lapi) | [Retour au endpoint calendar](../README.md#calendar)

API permettant de récupérer les événements associés à un calendrier

## GET calendar/events

### Utilisation

```url
GET /api/api.php/calendar/events?id=<calendar_id>
```

### Résultat

```json
{
  "success": true,
  "data": [
    {
      "uid": "<event_uid>",
      "realuid": "<event_realuid>",
      "calendar": "<event_calendar>",
      "owner": "<event_owner>",
      "title": "Mon événement de test",
      "status": "confirmed",
      "class": "public",
      "sequence": 1,
      "transparency": "OPAQUE",
      "start": "2022-04-26 16:30:00",
      "end": "2022-04-26 20:00:00",
      "created": 1650901534,
      "modified": 1650901534,
      "timezone": "Europe/Paris"
    }
  ]
}
```

### Paramètres

 - `id` : [Obligatoire] identifiant du calendrier à récupérer
