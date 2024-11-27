# Documentation technique

Cette documentation détaille les points techniques sur le développement du projet API ORM MCE

## Architecture du projet

Le point d'entrée du projet est le fichier [api.php](../src/api.php).

Les librairies utilisées pour enrichir le projet se situe dans le dossier [lib](../src/lib/).

Les controleurs sont les classes permettant de faire appel aux méthodes de l'ORM en fonction du routing, ils se situent dans le dossier [controller](../src/controller/).

Un outil de tests des API est utilisable depuis le dossier [tests](../src/tests/), il permet notamment de configurer une clé d'API et d'avoir l'historique des commandes utilisées.

### api.php

Point d'entrée des API, le fichier en lui même ne fait qu'initiliser la configuration, les logs, gérer l'authentification, le routing puis la réponse. Il est peu probable d'avoir besoin de modifier ce fichier pour faire fonctionner ou enrichir les API.

### Librairies (lib)

Il s'agit de librairies internes au projet, les librairies externes étant définie dans le fichier [composer.json](../src/composer.json) et chargées dans le dossier [vendor](../src/vendor/).

#### auth.php

Gestion de l'authentification pour l'accès aux API. Les authentification supportées actuellement sont Basic ou Apikey. Pour ajouter le support de l'authentification Bearer il faut prévoir une modification de la méthode `private static function bearer($token)`.

#### config.php

Gestion de la configuration, charge les différents fichiers de configuration et charge les valeurs avec un support de la valeur par défaut si celle-ci n'est trouvée. Une évolution possible serait une gestion multi-instance en gérant plusieurs fichier de configuration en fonction de l'instance qui serait données dans l'url.

#### log.php

Gestion des logs.

#### mapping.php

Gestion du mapping.

#### objects.php

Permet de générer les objets de l'ORM en fonction du namespace.

#### request.php

Traitement de la requête en entrée.

#### response.php

Traitement de la réponse en sortie

#### routing.php

Gestion du routing.

#### utils.php

Méthodes supplémentaires pour éviter les duplications de code.

### Controleurs (controller)

Les controleurs sont chargés directement à partir du routing. Pour bloquer l'accès à certaines méthodes du controleur ou certains controleurs, voir la configuration du routing dans la doc de Configuration.

## Ajouter un nouveau routing

Pour ajouter le support d'un nouveau routing dans les API il faut : 
* Ajouter le routing dans le fichier routing.inc.php ou le surcharger dans la configuration
* Ajouter un controleur associé ou enrichir un controleur existant avec de nouvelles méthodes.

Dans le fichier de routing il utilise toujours le controleur par défaut associé au nom de routing. Dans le cas ou l'on souhaite utiliser un controleur différent il faut le préciser dans le routing en modifiant la valeur de `'class'`. Le nom de la méthode va également être générée automatiquement en fonction de la requête, pour un nom de méthode personnalisé il faut donc le définir dans `'methods'`.