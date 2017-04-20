<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * ORM M2 Copyright © 2017 PNE Annuaire et Messagerie/MEDDE
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace LibMelanie\Config;

/**
 * Configuration de l'application pour Melanie2
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Config
 */
class ConfigMelanie {
  /**
   * Configuration du cache
   */
  // Le cache est utilisé
  const CACHE_ENABLED = false;
  // Durée de stockage en cache (en sec)
  const CACHE_DELAY = 180;
  // Type de Cache 'php' ou 'memcache'
  const CACHE_TYPE = 'memcache';
  // Host vers memcache
  const CACHE_MEMCACHE_HOST = 'localhost:11211';
  
  // S'inviter autoriser
  const SELF_INVITE = true;
  
  // Generic config
  const ATTRIBUTE_OWNER = 'owner';
  const ATTRIBUTE_NAME_NAME = 'name';
  const ATTRIBUTE_NAME_PERM = 'perm_users';
  const ATTRIBUTE_NAME_PERMGROUP = 'perm_usersfg';
  const PREF_SCOPE = 'horde';
  const TZ_PREF_NAME = 'timezone';
  
  // Configuration du nom de l'application pour la table Histories
  const APP_NAME = 'Z-Push';
  
  // General config
  const GENERAL_PREF_SCOPE = "horde";
  
  // Calendar config
  const CALENDAR_PREF_SCOPE = "kronolith";
  const CALENDAR_GROUP_UID = 'horde.shares.kronolith';
  const CALENDAR_PREF_DEFAULT_NAME = "default_share";
  const CALENDAR_DEFAULT_TIMEZONE = "Europe/Paris";
  
  // Tasklist config
  const TASKSLIST_PREF_SCOPE = "nag";
  const TASKSLIST_GROUP_UID = 'horde.shares.nag';
  const TASKSLIST_PREF_DEFAULT_NAME = "default_tasklist";
  
  // Addressbook config
  const ADDRESSBOOK_PREF_SCOPE = "turba";
  const ADDRESSBOOK_GROUP_UID = 'horde.shares.turba';
  const ADDRESSBOOK_PREF_DEFAULT_NAME = "default_dir";
  
  // History config
  const HISTORY_ADD = "add";
  const HISTORY_MODIFY = "modify";
  const HISTORY_DELETE = "delete";
  
  // ACL config
  const PRIV = "private";
  const FREEBUSY = "freebusy";
  const READ = "read";
  const DELETE = "delete";
  const WRITE = "write";
  
  // Class configuration
  // const PRIV = "private"; // -> see ACL
  const PUB = "public";
  const CONFIDENTIAL = "confidential";
  
  // Status configuration
  const TENTATIVE = "tentative";
  const CONFIRMED = "confirmed";
  const CANCELLED = "cancelled";
  const NONE = "none";
  
  // Recurrence days
  const NODAY = "";
  const SUNDAY = "SU";
  const MONDAY = "MO";
  const TUESDAY = "TU";
  const WEDNESDAY = "WE";
  const THURSDAY = "TH";
  const FRIDAY = "FR";
  const SATURDAY = "SA";
  
  // Recurrence type
  const NORECUR = "";
  const DAILY = "daily";
  const WEEKLY = "weekly";
  const MONTHLY = "monthly";
  const MONTHLY_BYDAY = "monthly_by_day";
  const YEARLY = "yearly";
  const YEARLY_BYDAY = "yearly_by_day";
  
  // Attendee status
  const RESPONSE = "response";
  const NEED_ACTION = "need_action";
  const ACCEPTED = "accepted";
  const DECLINED = "declined";
  const IN_PROCESS = "in_process";
  // const TENTATIVE = "tentative"; // -> see Status
  
  // Attendee role
  const ROLE = "attendance";
  const CHAIR = "chair";
  const REQ_PARTICIPANT = "req_participant";
  const OPT_PARTICIPANT = "opt_participant";
  const NON_PARTICIPANT = "non_participant";
  const NAME = "name";
  
  // Priority
  const NO_PRIORITY = "no_priority";
  const VERY_HIGH = "very_high";
  const HIGH = "high";
  const NORMAL = "normal";
  const LOW = "low";
  const VERY_LOW = "very_low";
  
  // VFS Folder
  const TYPE_FILE = 1;
  const TYPE_FOLDER = 2;
  
  // Completed
  const COMPLETED = "completed";
  const NOTCOMPLETED = "notcompleted";
  
  // Chemin par défaut du stockage des pièces jointes
  // %e -> uid de l'évènement
  // %u -> uid de l'utilisateur owner de l'évènement
  // %c -> uid du calendrier contenant les pièces jointes
  const DEFAULT_ATTACHMENTS_FOLDER = ".horde/kronolith/documents";
  const ATTACHMENTS_EVENT_FOLDER = self::DEFAULT_ATTACHMENTS_FOLDER;
  const ATTACHMENTS_CALENDAR_FOLDER = ".horde/kronolith/documents/%e";
  const ATTACHMENTS_PATH = ".horde/kronolith/documents/%e/%c";
  
  // URL pour le téléchargement automatique d'une pièce jointe
  // %f -> nom du fichier/de la pièce jointe
  // %p -> chemin relatif vers la pièce jointe (sans le DEFAULT_ATTACHMENTS_FOLDER défini plus haut)
  const ATTACHMENT_DOWNLOAD_URL = "https://melanie2web.melanie2.i2/services/download/?module=kronolith&actionID=download_file&file=%f&vfsKey=%p&fn=%f";
  const DEFAULT_ATTACHMENT_CONTENTTYPE = "application/binary";
  
  // Défini si le timezone doit être ajouté à l'ICS
  const ICS_ADD_TIMEZONE = true;
  
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
  
  // Optimisation des prepares statements
  const REUSE_PREPARE_STATEMENT = true;
  
  // Gestion des selaformes
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
}