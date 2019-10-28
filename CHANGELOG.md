ORM M2 - 0.5.0.16
------
- 0005437: Gérer des les différents format de date pour le recurrence UNTIL

ORM M2 - 0.5.0.15
------
- Support du champ de mapping user_uid pour les Attendees

ORM M2 - 0.5.0.14
------
- HOTFIX pour les EXDATE de récurrences journée entière

ORM M2 - 0.5.0.13
------
- 0005407: Le save ne met pas correctement à jour le champ modified
- 0005418: HOTFIX: Ne pas utiliser le dtstart_utc pour les allday event

ORM M2 - 0.5.0.12
------
- 0005282: Problème de format du RDATE
- 0005401: Nouveaux champs pour la classe Event : dtstart, dtend, dtstart_utc, dtend_utc
- 0005400: Passer les EXDATE en GMT

ORM M2 - 0.5.0.11
------
- 0005261: Ajouter l'operateur BETWEEN/NOT BETWEEN

ORM M2 - 0.5.0.10
------
- 0005238: [ICS] Enregistrer les attributs X-MOZ-SNOOZE-TIME-*
- 0005234: Supprimer les attributs non nécessaire

ORM M2 - 0.5.0.9
------
- 0005209: Problème de gestion des enregistrements entre le mode en attente et le s'inviter
- 0005221: [Config] Créer une configuration par défaut
- 0005220: [Config] nouveau champ SELF_INVITE
- 0005219: Ajouter une information dans le participant qui s'est inviter
- 0005218: [En attente] Si le participant s'est invité ne pas passer son événement en annulé

ORM M2 - 0.5.0.8
------
- 0005142: [Recurrence] Si le format json n'est pas présent forcer l'ancien format
- 0005143: [ICS] Valider la recurrence avec de la passer au VObject

ORM M2 - 0.5.0.7
------
- 0005125: Bloquer les répétitions "récursives"
- 0005111: [ICS] Ne pas écraser la réponse d'un participant confirmée par un "need action"
- 0005126: Ne pas supprimer l'attribut X_MOZ_SEND_INVITATIONS
- 0005127: Problème de sync des contacts quand le token est null

ORM M2 - 0.5.0.6
------
- 0005102: [En attente] La suppression d'un événement ne supprime pas chez les participants

ORM M2 - 0.5.0.5
------
- 0005101: [En attente] La recherche des participants supprimés est sensible au champ ORM.enattente dans l'annuaire

ORM M2 - 0.5.0.4
------
- 0005098: Mise en place du sync token pour les carnets d'adresses

ORM M2 - 0.5.0.3
------
- 0005096: Le champ X-M2-ORG-MAIL n'est pas alimenté pour une modification d'événement
- 0005097: [En attente] Vérifier que le participant n'est pas aussi l'organisateur

ORM M2 - 0.5.0.2
------
- 0005089: Erreur de génération d'un ICS
- 0005086: Impossible de vider la liste des participants
- 0005088: Prévoir une requête optimisé de liste des événéments pour SabreDAV
- 0005095: Mauvaise gestion du timezone pour les exceptions

ORM M2 - 0.5.0.1
------
- 0005080: HOTFIX: Problème de suppression des occurrences
- 0005074: [ICS] Si l'événement n'a pas de date générer une date standard

ORM M2 - 0.5
------
- 0005035: Créer un événement en attente lorsque l'on est invité
- 0005038: Lorsque le participant accepte, si l'événement est en provisoire dans son agenda le passer en confirmé
- 0005037: [MagicObject] Rendre le champ haschanged accessible publiquement
- 0005046: [MagicObject] permettre la récupération des données depuis data
- 0005040: La création d'un événement sans participant puis ajout de participants ne fonctionne pas correctement
- 0005049: La récupération du calendrier de l'organisateur pour les occurrences ne fonctionne pas
- 0005055: [En attente] Mieux gérer les participants qui sont dans une occurrence mais pas dans la récurrence maitre
- 0005066: Marquage LDAP du mode "En attente"

ORM M2 - 0.4.0.17
------
- 0005080: HOTFIX: Problème de suppression des occurrences
- 0005074: [ICS] Si l'événement n'a pas de date générer une date standard

ORM M2 - 0.4.0.16
------
- 0005064: [ICS] si l'organisateur existe, ne pas le modifier depuis l'ICS
- 0005065: [Recurrence] Gérer les jours en DAILY

ORM M2 - 0.4.0.15
------
- 0005047: [SyncToken] le nettoyage de l'uid des occurrences n'est pas
- 0005040: La création d'un événement sans participant puis ajout de participants ne fonctionne pas correctement
- 0005049: La récupération du calendrier de l'organisateur pour les occurrences ne fonctionne pas

ORM M2 - 0.4.0.14
------
- FIX 0005028: L'enregistrement de la réponse d'un participant ne se base pas sur la bonne valeur
- Problème dans le mapping des attributs LDAP

ORM M2 - 0.4.0.13
------
- FIX 0005028: L'enregistrement de la réponse d'un participant ne se base pas sur la bonne valeur

ORM M2 - 0.4.0.12
------
- 0005028: L'enregistrement de la réponse d'un participant ne se base pas sur la bonne valeur
- 0005033: [ICS] Gérer les objets de partage pour les événements privés
- 0005029: Le nom de l'organisateur n'est pas conservé après acceptation de l'invitation

ORM M2 - 0.4.0.11
------
- 0005022: Gestion du champ owner email dans la classe organizer
- 0005023: [ICS] ICS ajout du champ X-M2-ORG-MAIL dans ORGANIZER

ORM M2 - 0.4.0.10
------
- 0004986: [ICS] un non participant doit être en accepted

ORM M2 - 0.4.0.9
------
- 0004973: Modifier le CN de l'organisateur dans le cas assistante/directeur
- 0004972: Une invitation créé depuis une boite partagée n'est pas reconnu comme interne
- 0004974: [LDAP] Nouvelles méthodes getMapValue et getMapValues

ORM M2 - 0.4.0.8
------
- 0004945: Positionner le exist à true sur un Faked Master
- 0004970: [ICS] Le owner n'est pas correctement récupéré pour les exceptions
- 0004971: La définition d'un organisateur pour une exception ne fonctionne pas bien

ORM M2 - 0.4.0.7
------
- 0004944: Problème dans la gestion du enddate

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
