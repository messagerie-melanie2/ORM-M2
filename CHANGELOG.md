ORM M2 - 0.4.0.6
------
- Mise en place d'un timeout LDAP
- Gestion du last request dans le driver LDAP
- Passage du event_id en 64 caractères
- 0004929: [ICS] Si l'organisateur est vide, ne pas considérer que c'est une réunion
- Correction PHP Notice:  Undefined index: uid in ORM-M2/src/Ldap/LDAPMelanie.php on line 94
- 0004911: Boucle de fonctionnement lorsque qu'on arrive pas à déterminer l'organisateur proprement
- Si pas de CREATED dans l'ICS en calculer un

ORM M2 - 0.4.0.5
------
- 0003624: Ajouter des attributs LDAP dans l'objet Melanie2\User
- Ajout des URL CalDAV et CardDAV dans les conteneurs
- Mise à jour du docs

ORM M2 - 0.4.0.4
------
- Ajout du getList dans la classe UserPrefs
- Ajout des docs générés par ApiGen

ORM M2 - 0.4.0.3
------
- 0004869: Support du ROLE CHAIR pour les participants
- 0004870: Gestion du SENT BY dans l'ICS
- 0004871: [Recurrence] Le décodage json du UNTIL ne fourni pas de DateTime
- 0004873: Convertir la recurrence au bon timezone avant de l'enregistrer

ORM M2 - 0.4.0.2
------
- Problème dans la récupération du type de récurrence
- Problème quand le UNTIL est un objet

ORM M2 - 0.4.0.1
------
- 0004777: [Sync] encoder les uid retournés 
- 0004789: [Nouveau schéma] Gérer les éléments de récurrence qui retourne un tableau
- 0004814: [Nouveau schéma] Gérer les éléments de récurrence indépendemment
- 0004817: Les data d'une pièce jointe chargée via getList() ne sont pas récupéré par le load()

ORM M2 - 0.4
------
- 0004004: Evolution du schéma de base de données

ORM M2 - 0.3.0.24
------
- 0004740: Considérer l'événement supprimé si start = 1970-01-01
- 0004744: Une exception doit récupérer l'organisateur de l'événement maitre s'il existe
- 0004746: Potentiel problème dans la recherche de l'organisateur

ORM M2 - 0.3.0.23
------
- 0004730: [ICS] Problème de génération du recurrence id
- 0004731: [ICS] Ne pas retourner les occurrences sans date
- 0004721: [ICS] La conversion d'une alarme en semaine ne doit se faire que si elle tombe juste

ORM M2 - 0.3.0.22
------
- 0004708: Lors d'un "s'inviter" utiliser les informations de l'ICS
- 0004706: L'enregistrement d'une pièce jointe depuis l'ICS ne se fait pas dans le bon dossier vfs

ORM M2 - 0.3.0.21
------
- 0004694: Forcer le Timezone quand il est différent de celui enregistré
- 0004695: Enregistrer le timezone lors de la conversion depuis l'ICS

ORM M2 - 0.3.0.20
------
- Problème de typage pour l'Exception
- 0004692: Enregistrer la derniere requete SQL + ses parametres

ORM M2 - 0.3.0.19
------
- 0004689: Mauvaise optimisation du chargement des pièces jointes

ORM M2 - 0.3.0.18
------
- Suppression des appels au garbage collector suite à un problème de charge sur SabreDAV

ORM M2 - 0.3.0.17
------
- Ajouter l'email dans les logs de l'organisateur
- 0004675: Mauvaise détermination du calendrier de l'organisateur lors d'un FAKED MASTER
- 0004677: Enregistrer le récurrence id d'une exception

ORM M2 - 0.3.0.16
------
- 0004669: Modifier la class "Event" pour que la methode "exists" retourne true dans le cas d'un Faked Master
- 0004668: [ICS] Conversion d'un événement sans DTSTART
- 0004667: [CalendarSync] Nettoyer les uid en @RECURRENCE-ID

ORM M2 - 0.3.0.15
------
- 0004618: La classe CalendarSync doit gérer les récurrences

ORM M2 - 0.3.0.14
------
- HOTFIX journée entère 0004585: [ICS] Conversion DURATION en DTEND

ORM M2 - 0.3.0.13
------
- HOTFIX journée entère 0004585: [ICS] Conversion DURATION en DTEND

ORM M2 - 0.3.0.12
------
- 0004585: [ICS] Conversion DURATION en DTEND
- 0004591: [ICS] Gestion du GMT pour DTSTART et DTEND

ORM M2 - 0.3.0.11
------
- Problème de génération des ICS en mode vendor

ORM M2 - 0.3.0.10
------
- 0004567: La date de fin de récurrence ne devrait pas être calculé


ORM M2 - 0.3.0.9
------
- 0004566: La conversion d'un participant en ICS perd le CN


ORM M2 - 0.3.0.8
------
- 0004537: Le calcul de la date de fin de récurrence n'est pas correct


ORM M2 - 0.3.0.7
------
- Problème d'utilisation du default path pour l'env


ORM M2 - 0.3.0.6
------
- Mise en place de constante par défaut pour l'env.php


ORM M2 - 0.3.0.5
------
- Modification du env.php


ORM M2 - 0.3.0.4
------
- Inversion files dans le composer


ORM M2 - 0.3.0.3
------
- Ajout du includes_conf dans le composer


ORM M2 - 0.3.0.2
------
- Refactoring de l'arborescence pour la gestion du case sensitive de vendor


ORM M2 - 0.3.0.1
------
- Problème de composer


ORM M2 - 0.3
------
- 0004522: Passer en vendor avec un composer

ORM M2 - 0.2.5.2
------
- 0004530: [Agenda] [ICS] Passer le statut par défaut en libre
- 0004487: [Tâches] Gérer la table nag_sync
- 0004485: [Général] Prise en compte du SyncToken
- 0004486: [Agenda] Gérer la table kronolith_sync


ORM M2 - 0.2.5.1
------
- 0004519: [Agenda] Ne pas retourner les recurrence id dans le Sync
- 0004517: [Tâches] [ICS] Une tâche terminée ne passe pas completed à 1
- 0004516: [Tâches] La methode getList des tâches ne retourne toujours qu'une tâche
- 0004510: [Tâches] Problème de conversion ICS des tâches avec alarme
- 0004507: [Agenda] Problème de gération des EXDATE en journée entière
- 0004481: [Général] Modifier les méthodes getCTag pour récupérer directement le ctag
- 0004479: [Agenda] Le mode freebusy n'est pas correctement redescendu à l'événement
- 0004471: [Agenda] Problème lorsque la réponse du participant ne change pas
- 0004468: [Agenda] Problème d'utilisateur dans l'attribut


ORM M2 - 0.2.5
------
- 0004324: [Agenda] Le nom de la base de données pour les sauvegardes j-1 et j-2 a changé.
- 0004290: [Agenda] Lorsqu'une seule occurrence modifiée est supprimé le RECURRENCE-MASTER n'est pas nettoyé
- 0004286: [Agenda] L'enregistrement des attributs n'enregistre pas l'utilisateur


ORM M2 - 0.2.4
------
- 0004259: [Agenda] Problème dans la gestion des privés pour l'ICS
- 0004258: [Agenda] Alimenter un titre même pour un événement sans titre


ORM M2 - 0.2.3
------
- 0004232: [Agenda] Gestion des événements privés quand l'utilisateur n'existe pas


ORM M2 - 0.2.2
------
- 0004177: [Agenda] Problème de timezone pour la génération ICS
- 0004159: [Agenda] Gérer un vcalendar externe au moment de l'export ICS
- 0004157: [Agenda] La méthode getRangeEvent ne retourne pas tous les événements


ORM M2 - 0.2.1
------
- 0004005: [Général] Stockage du ctag en base de données
- 0004130: [Agenda] Mise en place d'un trigger pour le calcul du ctag
- 0003628: [Agenda] Génération des disponibilités via les freebusy
- 0004132: [Contacts] Utiliser le nouveau calcul de ctag


ORM M2 - 0.2
------
- 0004102: [Agenda] Optimiser les requêtes avec la mise en place d'index
- 0004103: [Agenda] Calculer une date de fin approximative pour un count
- 0004016: [Agenda] Gestion des COPY/MOVE
- 0003625: [Contacts] Conversion d'un contact en vcard et inversement
- 0004010: [Général] Problème dans l'enregistrement des pièces jointes URL
- 0003997: [Agenda] Ne pas supprimer une pièce jointe URL quand le binaire existe
- 0004002: [Agenda] Ajouter le creator dans la description lors de la génération de l'ICS
- 0003881: [Agenda] Rendre la librairie moins sensible au format de données pour les exceptions
- 0003875: [Agenda] Changement d'organisateur


ORM M2 - 0.1.9
------
- 0003830: [Agenda] Gestion des exceptions sans date
- 0003803: [Général] Modifier le isExists des objet pour déterminer le Creation/Modification


ORM M2 - 0.1.8
------
- 0003767: [Agenda] Intégrer les pièces jointes directement dans les évènements
- 0003626: [Tâches] Conversion d'une tâche en ICS et inversement
- 0002883: [Agenda] Conversion de l'évènement en ICS et inversement
- 0003706: [Général] Associer un timezone à un utilisateur plutot qu'un calendrier
- 0003680: [Agenda] Charger tous les attributs lors d'un getList


ORM M2 - 0.1.7
------
- 0003635: [Général] Externaliser la configuration de l'ORM
- 0003660: [Agenda] Le getList n'est pas optimum pour les exceptions
- 0003650: [Agenda] Problème lorsqu'un participant modifie sa réponse pour une occurrence
- 0003642: [Agenda] Impossible de remettre à zéro le champ "event_recurenddate"
- 0003615: [Agenda] Alimenter le champ recurrence master
- 0003613: [Général] Permetttre a l'application de gérer plusieurs connexions différentes aux bdd
- 0003604: [Tâches] Création d'un task propterty


ORM M2 - 0.1.6
------
- 0003547: [Général] Réutiliser les prepare statements pour les requêtes identiques
- 0003556: [Général] Générer une exception lorsque la base de données est inaccessible
- 0003548: [Général] Etudier la mise en place d'un Selaforme pour les connexions à la base de données
- 0003499: [Général] Le check sur les champs SQL ne se fait pas
- 0003369: [Général] Gérer le cluster SQL en lecture/écriture suivant les requêtes


ORM M2 - 0.1.5
------
- 0003298: [Général] Ajouter de nouveaux paramètres dans les recherches LDAP
- 0003000: [Général] Permettre de personnaliser les filtres du getList
- 0002994: [Général] Rendre paramètrable le starttls
- 0003147: [Général] Modifier le backend LDAP pour séparer les requêtes par serveur
- 0003098: [Général] Mettre en place un autoloader dynamique


ORM M2 - 0.1.4
------
- 0002998: [Général] Améliorer la consommation mémoire de la librairie
- 0002993: [Général] Charger la configuration en fonction de l'environnement
- 0002999: [Général] Pouvoir définir les champs à retourner par le getList


ORM M2 - 0.1.3
------
- 0002878: [Agenda] LibM2: Erreur dans la sauvegarde des attributs de l'évènement
- 0002877: [Agenda] Seuls les rdv dont la date de début est dans la plage de synchro sont synchronisés
- 0002882: [Agenda] Implémenter les pièces jointes Binaires et URL
- 0002858: [Général] LibM2: Gérer des valeurs par défaut dans la configuration du mapping
- 0002860: [Agenda] LibM2: Le etag des participants n'est pas mis à jour lors d'une réponse
- 0002887: [Agenda] Grouper le chargement des attributs supplémentaires
- 0002888: [Général] Pouvoir récupérer une liste d'objets à partir de champs multivalués
- 0002892: [Agenda] La méthode getList associe les évènements à un calendrier non chargé
