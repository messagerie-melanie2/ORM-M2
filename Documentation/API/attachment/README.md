# attachment

[Retour à la documentation API](../README.md#utilisation-de-lapi)

API permettant de récupérer une pièce jointe

## GET attachment

Récupérer une pièce jointe à partir de son name et son path

### Utilisation

```url
GET /api/api.php/attachment?name=<name>&path=<path>&_download=0
```

### Résultat

Si _download=0 ou absent, affichage de la pièce jointe dans le navigateur. Si _download=1, téléchargement de la pièce jointe.

### Paramètres

 - `name` : [Obligatoire] nom de la pièce jointe
 - `path` : [Obligatoire] chemin complet vers la pièce jointe
-  `_download` : `1` pour télécharger la pièce jointe, `0` pour l'afficher (valeur par défaut)
