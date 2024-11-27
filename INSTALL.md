# Installation de l'API ORM MCE

## Pré-requis

L'API ORM MCE est un site web écrit en PHP, il nécessite donc un serveur web (Apache2/NGinx) et PHP (module ou FastCGI) pour fonctionner. La version de PHP recommandée pour utiliser l'API est 7.3.

L'API en elle-même ne nécessite pas de module complémentaire. Par contre certains modules sont nécessaires pour le bon fonctionnement de l'ORM MCE (l'API étant basée dessus), il faut donc consulter la documentation de l'ORM pour voir les modules PHP à installer.

## Installation

Le serveur web doit pointer sur le dossier [src/](src/), le fichier d'entrée de l'API étant le fichier [src/api.php](src/api.php). Une fois le serveur web configuré l'API est prête à être utilisée, il faut ensuite la configurer, voir [Documentation/Configuration](Documentation/Configuration/README.md).