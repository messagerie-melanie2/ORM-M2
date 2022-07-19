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
 * Configuration de l'ORM
 * Permet de récupérer les valeurs de configuration possible 
 * soit par défaut soit donné par le serveur
 * 
 * Se référer aux contantes listées pour avoir les valeurs disponibles
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Config
 */
class Config {
  /**
   * Nom de la configuration serveur a utiliser
   * 
   * @var string
   */
  const CONFIG_NAME = "LibMelanie\Config\ConfigMelanie";
  
  /**
   * Function permettant de récuper une valeur de configuration
   * Commencer par tester si la valeur est définie par le serveur, 
   * et si non retourne celle par défaut
   * 
   * @param string $name Nom de constante à utiliser
   * @return mixed Valeur de configuration
   */
  public static function get($name) {
    $classname = self::CONFIG_NAME;
    if (defined($classname . '::' . $name)) {
      // Valeur constante de serveur
      return constant($classname . '::' . $name);
    }
    else if (isset($classname::$name)) {
      // Valeur statique de serveur
      return $classname::$name;
    }
    else if (defined('LibMelanie\Config\DefaultConfig::' . $name)) {
      // Valeur constante  par défaut
      return constant('LibMelanie\Config\DefaultConfig::' . $name);
    }
    else if (isset(DefaultConfig::$name)) {
      // Valeur statique par défaut
      return DefaultConfig::$name;
    }
    else {
      // Valeur n'existe pas
      return null;
    }
  }
  /**
   * Function permettant de déterminer si une valeur de configuration
   * est positionnée
   * Commencer par tester si la valeur est définie par le serveur, 
   * et si non retourne celle par défaut
   * 
   * @param string $name Nom de constante à utiliser
   * @return boolean true si la valeur est postionnée, false sinon
   */
  public static function is_set($name) {
    $classname = self::CONFIG_NAME;
    if (defined($classname . '::' . $name)) {
      // Valeur constante de serveur
      return true;
    }
    else if (isset($classname::$name)) {
      // Valeur statique de serveur
      return true;
    }
    else if (defined('DefaultConfig::' . $name)) {
      // Valeur constante  par défaut
      return true;
    }
    else if (isset(DefaultConfig::$name)) {
      // Valeur statique par défaut
      return true;
    }
    else {
      // Valeur n'existe pas
      return false;
    }
  }
  
	/*
	 * Liste des constantes disponibles
	 */
  	const USE_NEW_MODE = 'USE_NEW_MODE';
  
	const CACHE_ENABLED = 'CACHE_ENABLED';
	const CACHE_DELAY = 'CACHE_DELAY';
	const CACHE_TYPE = 'CACHE_TYPE';
	const CACHE_MEMCACHE_HOST = 'CACHE_MEMCACHE_HOST';

	const ATTRIBUTE_OWNER = 'ATTRIBUTE_OWNER';
	const ATTRIBUTE_NAME_NAME = 'ATTRIBUTE_NAME_NAME';
	const ATTRIBUTE_NAME_PERM = 'ATTRIBUTE_NAME_PERM';
	const ATTRIBUTE_NAME_PERMGROUP = 'ATTRIBUTE_NAME_PERMGROUP';
	const PREF_SCOPE = 'PREF_SCOPE';
	const TZ_PREF_NAME = 'TZ_PREF_NAME';

	const APP_NAME = 'APP_NAME';

	const GENERAL_PREF_SCOPE = "GENERAL_PREF_SCOPE";

	const CALENDAR_PREF_SCOPE = "CALENDAR_PREF_SCOPE";
	const CALENDAR_GROUP_UID = 'CALENDAR_GROUP_UID';
	const CALENDAR_PREF_DEFAULT_NAME = "CALENDAR_PREF_DEFAULT_NAME";
	const CALENDAR_DEFAULT_TIMEZONE = "CALENDAR_DEFAULT_TIMEZONE";
	const CALENDAR_CALDAV_URL = 'CALENDAR_CALDAV_URL';

	const TASKSLIST_PREF_SCOPE = "TASKSLIST_PREF_SCOPE";
	const TASKSLIST_GROUP_UID = 'TASKSLIST_GROUP_UID';
	const TASKSLIST_PREF_DEFAULT_NAME = "TASKSLIST_PREF_DEFAULT_NAME";
	const TASKSLIST_CALDAV_URL = 'TASKSLIST_CALDAV_URL';

	const ADDRESSBOOK_PREF_SCOPE = "ADDRESSBOOK_PREF_SCOPE";
	const ADDRESSBOOK_GROUP_UID = 'ADDRESSBOOK_GROUP_UID';
	const ADDRESSBOOK_PREF_DEFAULT_NAME = "ADDRESSBOOK_PREF_DEFAULT_NAME";
	const ADDRESSBOOK_CARDDAV_URL = 'ADDRESSBOOK_CARDDAV_URL';

	const HISTORY_ADD = "HISTORY_ADD";
	const HISTORY_MODIFY = "HISTORY_MODIFY";
	const HISTORY_DELETE = "HISTORY_DELETE";

	const PRIV = "PRIV";
	const FREEBUSY = "FREEBUSY";
	const READ = "READ";
	const DELETE = "DELETE";
	const WRITE = "WRITE";

	const PUB = "PUB";
	const CONFIDENTIAL = "CONFIDENTIAL";

	const TENTATIVE = "TENTATIVE";
	const CONFIRMED = "CONFIRMED";
	const CANCELLED = "CANCELLED";
	const NONE = "NONE";

	const NODAY = "NODAY";
	const SUNDAY = "SUNDAY";
	const MONDAY = "MONDAY";
	const TUESDAY = "TUESDAY";
	const WEDNESDAY = "WEDNESDAY";
	const THURSDAY = "THURSDAY";
	const FRIDAY = "FRIDAY";
	const SATURDAY = "SATURDAY";

	const NORECUR = "NORECUR";
	const DAILY = "DAILY";
	const WEEKLY = "WEEKLY";
	const MONTHLY = "MONTHLY";
	const MONTHLY_BYDAY = "MONTHLY_BYDAY";
	const YEARLY = "YEARLY";
	const YEARLY_BYDAY = "YEARLY_BYDAY";

	const RESPONSE = "RESPONSE";
	const NEED_ACTION = "NEED_ACTION";
	const ACCEPTED = "ACCEPTED";
	const DECLINED = "DECLINED";
	const IN_PROCESS = "IN_PROCESS";
	const SELF_INVITE = "SELF_INVITE";
	const SELF_INVITE_ATTENDEE = 'SELF_INVITE_ATTENDEE';
	const IS_SAVED_ATTENDEE = 'IS_SAVED_ATTENDEE';

	const ROLE = "ROLE";
	const CHAIR = "CHAIR";
	const REQ_PARTICIPANT = "REQ_PARTICIPANT";
	const OPT_PARTICIPANT = "OPT_PARTICIPANT";
	const NON_PARTICIPANT = "NON_PARTICIPANT";
	const NAME = "NAME";

	const CUTYPE = "CUTYPE";

	const NO_PRIORITY = "NO_PRIORITY";
	const VERY_HIGH = "VERY_HIGH";
	const HIGH = "HIGH";
	const NORMAL = "NORMAL";
	const LOW = "LOW";
	const VERY_LOW = "VERY_LOW";

	const TYPE_FILE = 'TYPE_FILE';
	const TYPE_FOLDER = 'TYPE_FOLDER';

	const COMPLETED = "COMPLETED";
	const NOTCOMPLETED = "NOTCOMPLETED";

	const DEFAULT_ATTACHMENTS_FOLDER = "DEFAULT_ATTACHMENTS_FOLDER";
	const ATTACHMENTS_EVENT_FOLDER = "ATTACHMENTS_EVENT_FOLDER";
	const ATTACHMENTS_CALENDAR_FOLDER = "ATTACHMENTS_CALENDAR_FOLDER";
	const ATTACHMENTS_PATH = "ATTACHMENTS_PATH";

	const ATTACHMENT_DOWNLOAD_URL = "ATTACHMENT_DOWNLOAD_URL";
	const DEFAULT_ATTACHMENT_CONTENTTYPE = "DEFAULT_ATTACHMENT_CONTENTTYPE";

	const ICS_ADD_TIMEZONE = 'ICS_ADD_TIMEZONE';

	const REUSE_PREPARE_STATEMENT = 'REUSE_PREPARE_STATEMENT';

	const SEL_ENABLED = 'SEL_ENABLED';
	const SEL_MAX_ACQUIRE = 'SEL_MAX_ACQUIRE';
	const SEL_NB_ESSAI = 'SEL_NB_ESSAI';
	const SEL_TEMPS_ATTENTE = 'SEL_TEMPS_ATTENTE';
	const SEL_FILE_NAME = 'SEL_FILE_NAME';

	const USE_SHARED_INVITATION = 'USE_SHARED_INVITATION';
	const SHARED_INVITATION_REPLACE_CHAR = 'SHARED_INVITATION_REPLACE_CHAR';
	const SHARED_INVITATION_TEXT = 'SHARED_INVITATION_TEXT';

	const LDAP_TYPE_INDIVIDUELLE = 'LDAP_TYPE_INDIVIDUELLE';
	const LDAP_TYPE_PARTAGEE = 'LDAP_TYPE_PARTAGEE';
	const LDAP_TYPE_FONCTIONNELLE = 'LDAP_TYPE_FONCTIONNELLE';
	const LDAP_TYPE_RESSOURCE = 'LDAP_TYPE_RESSOURCE';
	const LDAP_TYPE_UNITE = 'LDAP_TYPE_UNITE';
	const LDAP_TYPE_SERVICE = 'LDAP_TYPE_SERVICE';
	const LDAP_TYPE_APPLICATIVE = 'LDAP_TYPE_APPLICATIVE';
	const LDAP_TYPE_PERSONNE = 'LDAP_TYPE_PERSONNE';
	const LDAP_TYPE_LIST = 'LDAP_TYPE_LIST';
	const LDAP_TYPE_LISTAB = 'LDAP_TYPE_LISTAB';

	const NEED_ACTION_ENABLE = 'NEED_ACTION_ENABLE';
	const NEED_ACTION_ENABLE_FILTER = 'NEED_ACTION_ENABLE_FILTER';
	const NEED_ACTION_DISABLE_FILTER = 'NEED_ACTION_DISABLE_FILTER'; 
}