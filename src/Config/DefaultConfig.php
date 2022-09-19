<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 *
 * ORM M2 Copyright © 2017  PNE Annuaire et Messagerie/MEDDE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace LibMelanie\Config;

/**
 * Configuration par défaut à surcharger dans 
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Config
 */
class DefaultConfig {
  /**
   * Configuraton du mode ORM
   * false = Ancien schéma Horde
   * true = Nouveau schéma Mélanie2
   * @var boolean
   */
  const USE_NEW_MODE = false;
  
  /** Configuration générale */
  /**
   * Configuration du nom de l'application pour la table Histories
   * @var string
   */
  const APP_NAME = 'App Name';
  /**
   * Authoriser le s'inviter dans l'agenda
   * @var boolean
   */
  const SELF_INVITE = true;
  /**
   * Calendar default timezone
   * @var string
   */
  const CALENDAR_DEFAULT_TIMEZONE = "Europe/Paris";
  /**
   * Configuration de l'URL CalDAV pour les calendriers
   * %o -> owner du calendrier
   * %u -> utilisateur courant
   * %i -> identifiant du calendrier
   * @var string
   */
  const CALENDAR_CALDAV_URL = "http://monserveur.com/calendars.php/calendars/%o/%i/";
  /**
   * Configuration de l'URL CalDAV pour les listes de tâches
   * %o -> owner de la liste de tâches
   * %u -> utilisateur courant
   * %i -> identifiant de la liste de tâches
   * @var string
   */
  const TASKSLIST_CALDAV_URL = "http://monserveur.com/taskslist.php/taskslist/%o/%i/";
  /**
   * Configuration de l'URL CardDAV pour les carnets d'adresses
   * %o -> owner du carnet d'adresses
   * %u -> utilisateur courant
   * %i -> identifiant du carnets d'adresses
   * @var string
   */
  const ADDRESSBOOK_CARDDAV_URL = "http://monserveur.com/contacts.php/contacts/%u/%i/";
  /**
   * Chemin par défaut du stockage des pièces jointes
   * @var string
   */
  const DEFAULT_ATTACHMENTS_FOLDER = ".documents";
  /**
   * Chemin par défaut du stockage des pièces jointes pour les événements
   * @var string
   */
  const ATTACHMENTS_EVENT_FOLDER = self::DEFAULT_ATTACHMENTS_FOLDER;
  /**
   * Stockage des pièces jointes dans un événement
   * %e -> uid de l'évènement
   * %u -> uid de l'utilisateur owner de l'évènement
   * %c -> uid du calendrier contenant les pièces jointes
   * @var string
   */
  const ATTACHMENTS_CALENDAR_FOLDER = ".documents/%e";
  /**
   * Stockage des pièces jointes dans un événement pour un calendrier
   * %e -> uid de l'évènement
   * %u -> uid de l'utilisateur owner de l'évènement
   * %c -> uid du calendrier contenant les pièces jointes
   * @var string
   */
  const ATTACHMENTS_PATH = ".documents/%e/%c";
  /**
   * URL pour le téléchargement automatique d'une pièce jointe
   * %f -> nom du fichier/de la pièce jointe
   * %p -> chemin relatif vers la pièce jointe (sans le DEFAULT_ATTACHMENTS_FOLDER défini plus haut)
   * @var string
   */
  const ATTACHMENT_DOWNLOAD_URL = "http://monserveur.com/download/?action=download_file&file=%f&vfsKey=%p&fn=%f";
  
  /** Configuration du cache */
  /**
   * Activer le cache
   * @var boolean
   */
  const CACHE_ENABLED = false;
  /**
   * Durée de stockage en cache (en sec)
   * @var integer
   */
  const CACHE_DELAY = 180;
  /**
   * Type de Cache 'php' ou 'memcache'
   * @var string
   */
  const CACHE_TYPE = 'memcache';
  /**
   * Host vers le/les serveur(s) memcache
   * @var string
   */
  const CACHE_MEMCACHE_HOST = 'localhost:11211';
  
  /* Informations générales (normalement des changements ne sont pas nécessaire */
  const ATTRIBUTE_OWNER = 'owner';
  const ATTRIBUTE_NAME_NAME = 'name';
  const ATTRIBUTE_NAME_PERM = 'perm_users';
  const ATTRIBUTE_NAME_PERMGROUP = 'perm_usersfg';
  const PREF_SCOPE = 'horde';
  const TZ_PREF_NAME = 'timezone';
  const GENERAL_PREF_SCOPE = "horde";
  
  /* Calendar config */
  const CALENDAR_PREF_SCOPE = "kronolith";
  const CALENDAR_GROUP_UID = 'horde.shares.kronolith';
  const CALENDAR_PREF_DEFAULT_NAME = "default_share";
  
  /* Tasklist config */
  const TASKSLIST_PREF_SCOPE = "nag";
  const TASKSLIST_GROUP_UID = 'horde.shares.nag';
  const TASKSLIST_PREF_DEFAULT_NAME = "default_tasklist";
  
  /* Addressbook config */
  const ADDRESSBOOK_PREF_SCOPE = "turba";
  const ADDRESSBOOK_GROUP_UID = 'horde.shares.turba';
  const ADDRESSBOOK_PREF_DEFAULT_NAME = "default_dir";
  
  /* History config */
  const HISTORY_ADD = "add";
  const HISTORY_MODIFY = "modify";
  const HISTORY_DELETE = "delete";
  
  /* ACL config */
  const PRIV = "private";
  const FREEBUSY = "freebusy";
  const READ = "read";
  const DELETE = "delete";
  const WRITE = "write";
  
  /* Class configuration */
  // const PRIV = "private";
  const PUB = "public";
  const CONFIDENTIAL = "confidential";
  
  /* Status configuration */
  const TENTATIVE = "tentative";
  const CONFIRMED = "confirmed";
  const CANCELLED = "cancelled";
  const NONE = "none";
  const TELEWORK = "telework";
  
  /* Recurrence days */
  const NODAY = "";
  const SUNDAY = "SU";
  const MONDAY = "MO";
  const TUESDAY = "TU";
  const WEDNESDAY = "WE";
  const THURSDAY = "TH";
  const FRIDAY = "FR";
  const SATURDAY = "SA";
  
  /* Recurrence type */
  const NORECUR = "";
  const DAILY = "daily";
  const WEEKLY = "weekly";
  const MONTHLY = "monthly";
  const MONTHLY_BYDAY = "monthly_by_day";
  const YEARLY = "yearly";
  const YEARLY_BYDAY = "yearly_by_day";

  /* Cutypes */
  const CUTYPE = "cutype";
  const INDIVIDUAL = 'individual';
	const GROUP = 'group';
	const RESOURCE = 'resource';
	const ROOM = 'room';
	const UNKNOWN = 'unknown';
  
  /* Attendee status */
  const RESPONSE = "response";
  const NEED_ACTION = "need_action";
  const ACCEPTED = "accepted";
  const DECLINED = "declined";
  const IN_PROCESS = "in_process";
  const SELF_INVITE_ATTENDEE = "self_invite";
  const IS_SAVED_ATTENDEE = "is_saved";
  // const TENTATIVE = "tentative"; // -> see Status
  
  /* Attendee role */
  const ROLE = "attendance";
  const CHAIR = "chair";
  const REQ_PARTICIPANT = "req_participant";
  const OPT_PARTICIPANT = "opt_participant";
  const NON_PARTICIPANT = "non_participant";
  const NAME = "name";
  
  /* Priority */
  const NO_PRIORITY = "no_priority";
  const VERY_HIGH = "very_high";
  const HIGH = "high";
  const NORMAL = "normal";
  const LOW = "low";
  const VERY_LOW = "very_low";
  
  /* VFS Folder */
  const TYPE_FILE = 1;
  const TYPE_FOLDER = 2;
  
  /* Completed */
  const COMPLETED = "completed";
  const NOTCOMPLETED = "notcompleted";
  
  /* Content type par défaut */
  const DEFAULT_ATTACHMENT_CONTENTTYPE = "application/binary";
  
  /* Défini si le timezone doit être ajouté à l'ICS */
  const ICS_ADD_TIMEZONE = true;

  /* Défini si le mode invitation partagée (via) est activé */
  const USE_SHARED_INVITATION = true;

  /* Quel caractère est remplacé dans le nom utilisateur pour le via */
	const SHARED_INVITATION_REPLACE_CHAR = ' - ';

  /* Quel texte utilisé pour le via, mots clés : %%creator_name%%, %%creator_fullname%%, %%owner_name%%, %%owner_fullname%% */
	const SHARED_INVITATION_TEXT = ' (via %%creator_name%%) - ';
  
  /**
   * Droits sur les objets Melanie2
   *
   * @var array
   */
  public static $PERMS = array(
      self::PRIV => 1,
      self::FREEBUSY => 2,
      self::READ => 4,
      self::DELETE => 8,
      self::WRITE => 16
  );
  
  /**
   * Optimisation des prepares statements
   * true pour réutiliser au maximum les prepares statements
   * @var boolean
   */
  const REUSE_PREPARE_STATEMENT = true;
  
  /** Gestion des selaformes */
  /**
   * Utiliser les selaformes pour protéger les connexions SQL
   *
   * @var boolean
   */
  const SEL_ENABLED = false;
  /**
   * Nombre maximum de lock simultanés
   *
   * @var integer
   */
  const SEL_MAX_ACQUIRE = 40;
  /**
   * Nombre d'essais avant de retourner false
   *
   * @var integer
   */
  const SEL_NB_ESSAI = 8;
  /**
   * Durée en millisecondes entre chaque essai
   *
   * @var integer
   */
  const SEL_TEMPS_ATTENTE = 8000;
  /**
   * Nom du fichier et chemin pour les selaformes
   *
   * @var string
   */
  const SEL_FILE_NAME = '/tmp/_ORM_SQL_SeLaFoRmE_';

  /*** LDAP */
  /**
   * Boite individuelle
   */
  const LDAP_TYPE_INDIVIDUELLE = 'BALI';
  /**
   * Boite partagée
   */
  const LDAP_TYPE_PARTAGEE = 'BALP';
  /**
   * Boite fonctionnelle
   */
  const LDAP_TYPE_FONCTIONNELLE = 'BALF';
  /**
   * Boite de ressource
   */
  const LDAP_TYPE_RESSOURCE = 'BALR';
  /**
   * Boite d'unité
   */
  const LDAP_TYPE_UNITE = 'BALU';
  /**
   * Boite de service
   */
  const LDAP_TYPE_SERVICE = 'BALS';
  /**
   * Boite applicative
   */
  const LDAP_TYPE_APPLICATIVE = 'BALA';
  /**
   * Personne sans boite
   */
  const LDAP_TYPE_PERSONNE = 'PERS';
  /**
   * Liste serveur de DIStribution
   */
  const LDAP_TYPE_LIST = 'LDIS';
  /**
   * Liste serveur de Distribution à ABonnement
   */
  const LDAP_TYPE_LISTAB = 'LDAB';

  /**
   * Est-ce que le en attente est activé ou désactivé par défaut ?
   */
  const NEED_ACTION_ENABLE = true;
  /**
   * Si le en attente est désactivé, filtre les utilisateurs qui l'on activé
   * Format : ['<champ>' => '<valeur>'] 
   * Exemple : ['info' => 'en attente : oui'] 
   * si le le champ de l'utilisateur matche la valeur c'est activé
   */
  const NEED_ACTION_ENABLE_FILTER = null;
  /**
   * Si le en attente est activé, filtre les utilisateurs qui l'on désactivé
   * Format : ['<champ>' => '<valeur>'] 
   * Exemple : ['info' => 'en attente : oui'] 
   * si le le champ de l'utilisateur matche la valeur c'est désactivé
   */
  const NEED_ACTION_DISABLE_FILTER = null; 
}