ORM M2 - 0.6.4.3
------
- 0007056: Gestion des itemName sur les objectshares
- Fix shares pour Api/Gn/User

ORM M2 - 0.6.4.2
------
- 0007050: Logguer les commandes ldapsearch, ldapadd, ... dans l'objet Ldap
- 0007051: Problème dans l'enregistrement d'un utilisateur

ORM M2 - 0.6.4.1
------
- Ajout d'un gitattributes pour nettoyer les releases

ORM M2 - 0.6.4
------
- 0007042: Pouvoir gérer des absences hebdo internes ou externes
- 0007045: Problème dans le calcul des itemName
- MR #5  Correctif Gn
- Gestion de paramètres dans read_version.php

ORM M2 - 0.6.3.6
------
- Nouveau mapping pour le User de Api/Mi
- Permettre le set des champs other ldap dans le User de Api/Mce

ORM M2 - 0.6.3.5
------
- 0006995: [User] Permettre un load() différent sur un objet de partage

ORM M2 - 0.6.3.4
------
- Type individuel pour l'ENS
- Fix sur bind4lookup() dans les deux méthodes LDAP GetUsersList() et GetGroups()

ORM M2 - 0.6.3.3
------
- Adaptations API ENS
- Fix sur l'objet User pour l'API MI
- Prise en compte (partielle) de la PR #3

ORM M2 - 0.6.3.2
------
- 0006984: Ajouter une méthode asRight dans l'objet User
- 0006971: [ICS] pour les tâches, une alarme de 2 jours est enregistrée sur 1 min
- Mise à jour du mapping pour le MI

ORM M2 - 0.6.3.1
------
- 0006964: Supporter un statut "Télétravail" dans les événements

ORM M2 - 0.6.3
------
- 0006920: Utiliser le champs event_attachments_json pour stocker les informations sur les pieces jointes
- 0005093: Ne plus utiliser la table lightning_attributes
- 0006850: Supporter la syntaxe MCE/DGFIP pour le gestionnaire d'absence
- Ajout d'un Api/Ens

ORM M2 - 0.6.2.7
------
- 0006899: [Event] pourvoir filtrer par catégorie dans getRangeEvent()

ORM M2 - 0.6.2.6
------
- 0006889: [Recurrence] Problème de BYDAY vide retourné en ICS
- 0006890: Amélioration du mapping des champs de type énumération

ORM M2 - 0.6.2.5
------
- 0006864: [Attendee] Problème de valeur de retour en cas d'erreur

ORM M2 - 0.6.2.4
------
- 0006800: Pour une invitation interne, un participant ne peut pas modifier l'horaire
- 0006759: [Event] Ajouter un champ version pour les évolutions de schéma
- 0006803: [Attendee] Support du CUTYPE
- 0006802: [Attendee] Ajoute une méthode is_ressource
- 0006801: [En attente] Gestion des boites partagées

ORM M2 - 0.6.2.3
------
- Fix sur la fonctionnalité de s'inviter pour un participant

ORM M2 - 0.6.2.2
------
- 0006295: [En attente] Identifier des changements de lieu non majeur
- 0006752: Lors du saveAttendees, forcer la date de l'événement de l'organisateur
- 0006751: MR MCE 0000007 Allow ldap servers that forbid anonymous binds

ORM M2 - 0.6.2.1
------
- 0006686: Problème de sequence en ICS
- 0006698: Incrémenter la séquence des participants dans le cas d'une suppression par l'organisateur
- 0006730: [VCard] Gestion des membres par uid
- 0006750: [VCard] Problème lorsque la propriété TYPE n'est pas présente
- 0006729: Ajouter des méthodes getAllGroups et getAllGroupsAndContacts dans Addressbook
- 0006721: Lors de la création de l'occurence chez l'organisateur, ne pas récupérer les dates du participants

ORM M2 - 0.6.2
------
- 0006191: Mode en attente lorsque le participant est une liste

ORM M2 - 0.6.1.37
------
- Fix sur les caractères non supportés par Lightning

ORM M2 - 0.6.1.36
------
- API DGFIP dédiée

ORM M2 - 0.6.1.35
------
- 0006578: Problème d'optimisation du load() des pièces jointes

ORM M2 - 0.6.1.34
------
- Correctifs sur les index pour les notifications
- Correctifs dans le Driver MI pour les groupes
- Pour les notifications se baser sur created

ORM M2 - 0.6.1.33
------
- Mise au propre des configurations défaut
- Ajout du script de traitement des droits sur les groupes

ORM M2 - 0.6.1.32
------
- 0006528: Mise à jour du schéma LDAP pour les groupes dans Api\Mce\Group
- 0006525: Création de la table pour le stockage des notifications du Bnum
- 0006526: Création d'un nouvel objet Notification
- 0006527: Création des méthodes pour gérer les notifications dans l'objet User

ORM M2 - 0.6.1.31
------
- Correctif sur le calcul du isPublisher d'une news s'il s'agit d'un sous service
- Mise à jour des init et update SQL pour mieux gérer les schémas

ORM M2 - 0.6.1.30
------
- 0006512: Ajouter le support de la sequence dans le en attente

ORM M2 - 0.6.1.29
------
- Fix getUserRss method pour les News/Rss
- Fixing queries comment header
- Ajout du support isNewsPublisher pendant un load

ORM M2 - 0.6.1.28
------
- 0006328: Ajouter une lib pour genérer des UUID
- 0006329: Indexes manquant sur les tables *_sync
- 0006319: Schéma de base de données pour s'informer dans le Bnum
- 0006320: Ajouter les objets News, Rss et NewsShare

ORM M2 - 0.6.1.27
------
- HOTFIX 0006314: Le en attente ne fonctionne pas lorsque l'invitation part d'une BALP

ORM M2 - 0.6.1.26
------
- 0006302: [Api/Gn] Pour les groupes les membres doivent se récupérer différemment
- 0006306: Problème de chargement d'une liste de tâches basé uniquement sur l'id
- 0006305: Après un getList le isLoaded n'est pas initialisé pour l'objet récupéré
- 0006303: Si le paramètre du constructeur de Calendar, Addressbook et Taskslist est un string, positionner l'id
- 0006304: Ajouter un __toString() dans MagicObject et MceObject
- 0006301: La méthode loadExceptions charge toutes les exceptions de tous les agendas

ORM M2 - 0.6.1.25
------
- 0006294: Ajouter l'information dans un participant quand il a été enregistré en attente

ORM M2 - 0.6.1.24
------
- 0006289: Dans le IF pour savoir si l'événement est l'événement de l'organisateur ajouter le test s'il est externe
- 0006291: Permettre un type booleanLdap sur une entrée multivaluée
- 0006292: Ajouter une propriété is_mailbox dans l'objet User
- 0006288: Lorsqu'on recherche si l'organisateur est externe, valider l'objectClass mineqMelBoite

ORM M2 - 0.6.1.23
------
- Mise à jour du schéma de base de données initial

ORM M2 - 0.6.1.22
------
- 0006244: Problème dans le calcul de la date de fin à partir du count pour une modification
- 0006245: Optimisation du calcul de la date de fin à partir du count de la recurrence
- 0006232: [En attente] Gérer les catégories des espaces de travail du BNum

ORM M2 - 0.6.1.21
------
- Mise à jour de l'API MI pour les shares et vacation

ORM M2 - 0.6.1.20
------
- Fix 0006229: [User Mél] Gérer le info=REPONSE:<adresse de reply-to> pour les listes

ORM M2 - 0.6.1.19
------
- 0006229: [User Mél] Gérer le info=REPONSE:<adresse de reply-to> pour les listes

ORM M2 - 0.6.1.18
------
- 0006225: [En attente] Un participant décliné ne doit pas avoir l'événement recréé

ORM M2 - 0.6.1.17
------
- Correctif double_authentification pour les Mel\User

ORM M2 - 0.6.1.16
------
- Ajout de l'attribut double_authentification pour les Mel\User

ORM M2 - 0.6.1.15
------
- 0006183: Plantage lors de la lecture d'un ICS mal formé
- 0006204: Supporter une configuration dédiée pour des objets particuliers
- 0006206: Permettre un load d'un Calendar/Addressbook/Taskslist sans user
- Modification du Api/Gn/Group 

ORM M2 - 0.6.1.14
------
 - 0006178: Quand un participant répond a une invitation, modifier automatiquement son statut
 - 0006177: [Api/Mel] Ajouter un mapping pour Cerbère
 - 0006176: Problème dans la recherche de l'évenement de l'organisateur pour une occurrence

ORM M2 - 0.6.1.13
------
- Fix 0006153: Permettre une déconnexion forcée de toutes les instances

ORM M2 - 0.6.1.12
------
- 0006151: Le champ event_organizer_json est vide pour l'organisateur lorsqu'un particpant modifie une occurrence
- 0006136: Gérer la création d'un objet LDAP
- 0006150: Support du driver MI
- 0006149: Support du driver GN
- 0006153: Permettre une déconnexion forcée de toutes les instances
- Suppression de la lib DBMelanie devenue inutile avec le multi-bases

ORM M2 - 0.6.1.11
------
- 0006147: L'information journée entière est perdu pour l'organisateur

ORM M2 - 0.6.1.10
------
- 0006146: [Driver Mél] Filtre LDAP pour les listes de diffusion et les objets de partage

ORM M2 - 0.6.1.9
------
- 0006142: Problème de en attente avec une liste de diffusion
- 0006143: [En attente] Copier le organizer calendar, le organizer json et la liste des participants vide

ORM M2 - 0.6.1.8
------
- Fix problem with realuid

ORM M2 - 0.6.1.7
------
- 0006137: Problème de récupération de l'organisateur dans le s'inviter
- 0006138: Problème de realuid dans un Event

ORM M2 - 0.6.1.6
------
- 0006118: Nettoyer les erreurs dans les logs
- 0006119: Sécuriser les appels à DateTime
- 0006129: [ICS] Problème avec un RDATE enregistré par Roundcube
- 0006130: ldap_search ne doit pas avoir $attributes à null
- 0006133: Permettre le getList sans critère

ORM M2 - 0.6.1.5
------
- 0006117: [EventToICS] Cas particulier où l'event est deleted et l'exception aussi

ORM M2 - 0.6.1.4
------
- Utiliser le server_host MCE pour l'API GEN

ORM M2 - 0.6.1.3
------
- 0006102: [API/MCE] Problème de récupération du server_host

ORM M2 - 0.6.1.2
------
- 0006101: Si shared_base_dn n'est pas configuré utiliser base_dn
- 0006087: Lister les workspaces publics
- 0006088: Gerer la pagination des workspaces

ORM M2 - 0.6.1.1
------
- Nouvel index sur realuid
- Problème de schéma dans les workspaces
- Correctif: Ajoute l'organisateur json dans l'exception
- Correctif: Problème quand le recurrence_id est vide dans une exception

ORM M2 - 0.6.1.0
------
- 0006069: Optimisation de l'utilisation des récurrences et exceptions
- 0006065: Gestion des pièces jointes pour un participant
- 0006064: Nettoyer l'organisateur de la liste des participants
- 0006071: [ICS] supprimer le VALUE=DURATION du TRIGGER de la VALARM
- 0006066: Problème d'enregistrement des pièces jointes dans une occurrence
- 0006078: [Outofoffice] ne pas mettre les horaires si on est en journée entière
- 0006072: L'organisateur n'est pas enregistré dans les exceptions
- 0005643: Ouverture globale du en attente
- 0006077: Lister les pièces jointes de la récurrence maitre dans les occurrences

ORM M2 - 0.6.0.24
------
- 0006062: Rendre les types de boites configurables

ORM M2 - 0.6.0.23
------
- 0006052: [En attente] Problème avec les non participants
- 0006049: Schéma SQL pour les espaces de travail
- 0006048: Ajouter le support des espaces de travail
- 0006059: Ajouter un champ de configuration pour le "via"
- 0006060: Problème sur l'organisateur lorsqu'on ajoute des participants en modification et pas en création

ORM M2 - 0.6.0.22
------
- [OutofOffice] Forcer DSVT:1 pour tous les absences

ORM M2 - 0.6.0.21
------
- Supprimer les doublons dans les Outofoffices
- Problème avec le dimanche pour les absences récurrentes

ORM M2 - 0.6.0.20
------
- Fix problem with empty start and end in OutofOffice
- 0006042: Ajouter une authentification GSSAPI pour l'utilisateur


ORM M2 - 0.6.0.19
------
- Support lastname et firstname pour un User Mel
- 0006035: [Outofoffice] Ajouter le support des absences récurrentes

ORM M2 - 0.6.0.18
------
- 0005983: Problème avec la méthode getListsIsMember

ORM M2 - 0.6.0.17
------
- 0005944: Deux nouvelles méthodes pour z-push
- 0005943: Ajouter un mapping supported_shares pour Mél
- 0005915: Script de mise à jour du champ realuid
- 0005896: Nettoyage des enregistrements de sync
- Mise a jour du schéma "20200921.sql" pour les triggers de sync

ORM M2 - 0.6.0.16
------
- 0005899: Gérer une méthode hasChanged dans les objets
- 0005898: [Base de données] Fonction PL/SQL de nettoyage des sync

ORM M2 - 0.6.0.15
------
- Fix for Mel user filter by email

ORM M2 - 0.6.0.14
------
- 0005879: Object Member pour les listes de diffusion
- Update initial SQL schema

ORM M2 - 0.6.0.13
------
- 0005840: [User/MCE] Reconnaitre automatiquement une adresse e-mail

ORM M2 - 0.6.0.12
------
- 0005837: Dans Api/Mce gérer le OTHER_LDAP qui n'est pas défini

ORM M2 - 0.6.0.11
------
- 0005805: Ne pas utiliser "static" quand on peut soit être un Event soit une Exception

ORM M2 - 0.6.0.10
------
- Add server host conf in User Mél
- Fix error in foreach for shares values


ORM M2 - 0.6.0.9
------
- 0005795: [Bug] Problème dans l'objet Outofoffice


ORM M2 - 0.6.0.8
------
- 0005793: Gérer le cache lors du save de User
- 0005792: Problème dans les partages de User
- 0005790: Permettre d'associer un champ User à plusieurs champs LDAP

ORM M2 - 0.6.0.7
------
- Correctifs pour les User MCE
- Correctif pour la gestion du other ldap
- Correctif pour l'agri pour les User Mel

ORM M2 - 0.6.0.6
------
- Correctif sur la generation de l'entree LDAP
- Mise en place des objets pour la MCE
- Suppression des index non nécessaire


ORM M2 - 0.6.0.5
------
- Correctif sur le gestionnaire d'absence Mel
- Correctifs sur des problèmes de cache
- Suppression d'un ancien accés à la base de donnés


ORM M2 - 0.6.0.4
------
- Amélioration de la serialization pour la mise en cache des objets

ORM M2 - 0.6.0.3
------
- 0005761: Serialization des objets pour la mise en cache
- 0005760: Méthodes clean pour nettoyer les données en cache
- 0005762: Mettre en place des events pour la gestion du cache

ORM M2 - 0.6.0.2
------
- Correctifs
- 0005744: [LDAP] Prévoir de vider le cache après une modification
- 0005734: Avoir tous les objets dans tous les namespaces
- 0005735: Ajouter deux méthodes pour lister les groupes LDAP auquel un utilisateur appartient

ORM M2 - 0.6.0.1
------
- Correctifs mineurs
- Améliorer le support pour le multi-instance de base de données

ORM M2 - 0.6
------
- 0005647: [Utilisateur] Méthodes pour créer les objets par défaut (principals)
- 0005646: [Utilisateur] Modifier les requêtes pour sélectionner les objets par défaut
- 0005639: [Agenda] Problème avec le recurrence_id
- 0005642: [Général] Gestion de multiple d'entrées et de serveurs d'annuaire pour un seul objet
- 0005641: [Général] Intégrer la configuration LDAP directement dans l'objet User
- 0005614: [Général] Modifier les objets Mélanie2 en objet MCE
- 0005615: [Général] Créer une branche spécifique Api/Mel
- 0005616: [Général] Garder la compatibilité avec Api/Melanie2
- 0005601: [Général] Mettre en place un driver LDAP

ORM M2 - 0.5.0.24
------
- Gérer la désactivation des pièces jointes lors de la lecture d'un ICS


ORM M2 - 0.5.0.24
------
- Gérer la désactivation des pièces jointes lors de la lecture d'un ICS


ORM M2 - 0.5.0.23
------
- Suppression des ORDER BY dans les requêtes listUserObjects et listSharedObjects pour améliorer les performances de la base de données

ORM M2 - 0.5.0.22
------
- Problème de realuid écrasé pour les exceptions
- Ajout l'initialisation de la base dans les méthodes des objets 

ORM M2 - 0.5.0.21
------
- 0005589: Problème de gestion des recurrences sur des champs vides
- 0005591: Le chargement des attributs ne renseigne pas toujours correctement le owner
- 0005590: La valeur par défaut ne devrait pas impacter le isset
- 0005598: Nettoyage UTF8 des noms de participants et organisateurs

ORM M2 - 0.5.0.20
------
- 0005462: Problème sur l'objet Attendee
- 0005461: Améliorer les logs ldap
- Fix deref sur 0005444: [LDAP] Ajouter la gestion du dref dans les ldap_search

ORM M2 - 0.5.0.19
------
- 0005454: Problème de lecture des recurrences en PHP 7

ORM M2 - 0.5.0.18
------
- 0005444: [LDAP] Ajouter la gestion du dref dans les ldap_search

ORM M2 - 0.5.0.17
------
- 0005439: HOTFIX: un timezone type 1 pose problème dans les dates
- 0005442: Problème de date dans les fin de récurrence journée entière

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
