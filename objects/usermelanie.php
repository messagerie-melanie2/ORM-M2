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
namespace LibMelanie\Objects;

use LibMelanie\Sql;
use LibMelanie\Ldap\LDAPMelanie;
use LibMelanie\Config\ConfigMelanie;
use LibMelanie\Config\ConfigSQL;
use LibMelanie\Config\MappingMelanie;
use LibMelanie\Log\M2Log;

/**
 * Gestion de l'utilisateur Melanie2
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage ORM
 *
 */
class UserMelanie {
	/**
	 * UID de l'utilisateur
	 * @var String $uid
	 */
	private $uid;
	/**
	 * Email de l'utilisateur
	 * @var String $email
	 */
	private $email;
	/**
	 * Timezone de l'utilisateur
	 * @var string
	 */
	public $timezone;

	/**
	 * Constructeur de la class
	 * @param string $uid
	 */
	function __construct($uid = null) {
	    // Défini la classe courante
	    $this->get_class = get_class($this);

		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->__construct($uid)");
		if (!is_null($uid)) $this->uid = $uid;
	}

	/**
	 * Met à jour l'uid
	 * @param string $uid
	 */
	function setUid($uid) {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->setUid($uid)");
		$this->uid = $uid;
	}

	/**
	 * Recupère l'uid
	 */
	function getUid() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getUid(): " . $this->uid);
		return $this->uid;
	}

	/**
	 * Met à jour l'email
	 * @param string $email
	 */
	function setEmail($email) {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->setEmail($email)");
		$this->email = $email;
	}

	/**
	 * Recupère l'email
	 */
	function getEmail() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getEmail(): " . $this->email);
		return $this->email;
	}

	/**
	 * Authentification sur le serveur LDAP
	 *
	 * @param string $password
	 * @return boolean
	 */
	function authentification($password) {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->authentification()");
		return LDAPMelanie::Authentification($this->uid, $password);
	}

	// -- CALENDAR
	/**
	 * Retour le calendrier par défaut
	 * @return CalendarMelanie
	 */
	function getDefaultCalendar() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getDefaultCalendar()");
		if (!isset($this->uid)) return false;

		// Initialisation du backend SQL
		Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);

		$query = Sql\SqlMelanieRequests::getDefaultObject;
		// Replace name
		$query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['CalendarMelanie']['owner'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['CalendarMelanie']['id'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['CalendarMelanie']['ctag'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['CalendarMelanie']['synctoken'][MappingMelanie::name], $query);
		$query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['CalendarMelanie']['name'][MappingMelanie::name], $query);
		$query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['CalendarMelanie']['perm'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);

		// Params
		$params = [
				"group_uid" => ConfigMelanie::CALENDAR_GROUP_UID,
				"user_uid" => $this->uid,
				"attribute_name" => ConfigMelanie::ATTRIBUTE_NAME_NAME,
				"attribute_perm" => ConfigMelanie::ATTRIBUTE_NAME_PERM,
				"pref_scope" => ConfigMelanie::CALENDAR_PREF_SCOPE,
				"pref_name" => ConfigMelanie::CALENDAR_PREF_DEFAULT_NAME,
				"attribute_permfg" => ConfigMelanie::ATTRIBUTE_NAME_PERMGROUP,
		];

		// Calendrier par défaut de l'utilisateur
		$calendar = Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\CalendarMelanie');
		if (isset($calendar)
				&& is_array($calendar)
				&& count($calendar)) {
			$calendar[0]->pdoConstruct(true);
			return $calendar[0];
		} else {
		    $calendar = $this->getUserCalendars();
		    return isset($calendar[0]) ? $calendar[0] : null;
		}
		return false;
	}

	/**
	 * Récupère la liste des calendriers appartenant à l'utilisateur
	 * @return CalendarMelanie[]
	 */
	function getUserCalendars() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getUserCalendars()");
		if (!isset($this->uid)) return false;

		// Initialisation du backend SQL
		Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);

		$query = Sql\SqlMelanieRequests::listUserObjects;
		// Replace name
		$query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['CalendarMelanie']['owner'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['CalendarMelanie']['id'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['CalendarMelanie']['ctag'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['CalendarMelanie']['synctoken'][MappingMelanie::name], $query);
		$query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['CalendarMelanie']['name'][MappingMelanie::name], $query);
		$query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['CalendarMelanie']['perm'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);

		// Params
		$params = [
				"group_uid" => ConfigMelanie::CALENDAR_GROUP_UID,
				"user_uid" => $this->uid,
				"attribute_name" => ConfigMelanie::ATTRIBUTE_NAME_NAME
		];

		// Liste les calendriers de l'utilisateur
		return Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\CalendarMelanie');
	}

	/**
	 * Récupère la liste des calendriers appartenant à l'utilisateur
	 * ainsi que ceux qui lui sont partagés
	 * @return CalendarMelanie[]
	 */
	function getSharedCalendars() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getSharedCalendars()");
		if (!isset($this->uid)) return false;

		// Initialisation du backend SQL
		Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);

		$query = Sql\SqlMelanieRequests::listSharedObjects;
		// Replace name
		$query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['CalendarMelanie']['owner'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['CalendarMelanie']['id'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['CalendarMelanie']['ctag'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['CalendarMelanie']['synctoken'][MappingMelanie::name], $query);
		$query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['CalendarMelanie']['name'][MappingMelanie::name], $query);
		$query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['CalendarMelanie']['perm'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);

		// Params
		$params = [
				"group_uid" => ConfigMelanie::CALENDAR_GROUP_UID,
				"user_uid" => $this->uid,
				"attribute_name" => ConfigMelanie::ATTRIBUTE_NAME_NAME,
				"attribute_perm" => ConfigMelanie::ATTRIBUTE_NAME_PERM,
				"attribute_permfg" => ConfigMelanie::ATTRIBUTE_NAME_PERMGROUP,
		];

		// Liste les calendriers de l'utilisateur
		return Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\CalendarMelanie');
	}

	// -- TASKSLIST
	/**
	 * Retour la liste de tâches par défaut
	 * @return TaskslistMelanie
	 */
	function getDefaultTaskslist() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getDefaultTaskslist()");
		if (!isset($this->uid)) return false;

		// Initialisation du backend SQL
		Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);

		$query = Sql\SqlMelanieRequests::getDefaultObject;
		// Replace name
		$query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['owner'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['id'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['ctag'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['synctoken'][MappingMelanie::name], $query);
 		$query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['name'][MappingMelanie::name], $query);
 		$query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['perm'][MappingMelanie::name], $query);
 		$query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);

 		// Params
		$params = [
			"group_uid" => ConfigMelanie::TASKSLIST_GROUP_UID,
			"user_uid" => $this->uid,
			"attribute_name" => ConfigMelanie::ATTRIBUTE_NAME_NAME,
			"attribute_perm" => ConfigMelanie::ATTRIBUTE_NAME_PERM,
			"pref_scope" => ConfigMelanie::TASKSLIST_PREF_SCOPE,
			"pref_name" => ConfigMelanie::TASKSLIST_PREF_DEFAULT_NAME,
			"attribute_permfg" => ConfigMelanie::ATTRIBUTE_NAME_PERMGROUP,
		];

		// Liste de tâches par défaut de l'utilisateur
		$tasklist = Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\TaskslistMelanie');
		if (isset($tasklist)
				&& is_array($tasklist)
				&& count($tasklist)) {
			$tasklist[0]->pdoConstruct(true);
			return $tasklist[0];
		} else {
		    $tasklist = $this->getUserTaskslists();
		    return isset($tasklist[0]) ? $tasklist[0] : null;
		}
		return false;
	}

    /**
 	 * Récupère la liste des listes de tâches appartenant à l'utilisateur
 	 * @return TaskslistMelanie[]
 	 */
 	function getUserTaskslists() {
 		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getUserTaskslists()");
	 	if (!isset($this->uid)) return false;

	 	// Initialisation du backend SQL
	 	Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);

	 	$query = Sql\SqlMelanieRequests::listUserObjects;
	 	// Replace name
	 	$query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['owner'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['id'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['ctag'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['synctoken'][MappingMelanie::name], $query);
		$query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['name'][MappingMelanie::name], $query);
		$query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['perm'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);

		// Params
		$params = [
				"group_uid" => ConfigMelanie::TASKSLIST_GROUP_UID,
				"user_uid" => $this->uid,
				"attribute_name" => ConfigMelanie::ATTRIBUTE_NAME_NAME
		];
		// Liste les listes de tâches de l'utilisateur
		return Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\TaskslistMelanie');
 	}

	/**
	 * Récupère la liste des listes de tâches appartenant à l'utilisateur
 	 * ainsi que ceux qui lui sont partagés
 	 * @return TaskslistMelanie[]
 	 */
 	function getSharedTaskslists() {
 		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getSharedTaskslists()");
		if (!isset($this->uid)) return false;

		// Initialisation du backend SQL
		Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);

		$query = Sql\SqlMelanieRequests::listSharedObjects;
		// Replace name
		$query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['owner'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['id'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['ctag'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['synctoken'][MappingMelanie::name], $query);
		$query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['name'][MappingMelanie::name], $query);
		$query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['perm'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);

		// Params
		$params = [
			"group_uid" => ConfigMelanie::TASKSLIST_GROUP_UID,
			"user_uid" => $this->uid,
			"attribute_name" => ConfigMelanie::ATTRIBUTE_NAME_NAME,
			"attribute_perm" => ConfigMelanie::ATTRIBUTE_NAME_PERM,
			"attribute_permfg" => ConfigMelanie::ATTRIBUTE_NAME_PERMGROUP,
		];
		// Liste les listes de tâches de l'utilisateur
		return Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\TaskslistMelanie');
	}

	// -- ADDRESSBOOK
	/**
	 * Retour la liste de contacts par défaut
	 * @return AddressbookMelanie
	 */
	function getDefaultAddressbook() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getDefaultAddressbook()");
		if (!isset($this->uid)) return false;

		// Initialisation du backend SQL
		Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);

		$query = Sql\SqlMelanieRequests::getDefaultObject;
		// Replace name
		$query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['owner'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['id'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['ctag'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['synctoken'][MappingMelanie::name], $query);
		$query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['name'][MappingMelanie::name], $query);
		$query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['perm'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);

		// Params
		$params = [
			"group_uid" => ConfigMelanie::ADDRESSBOOK_GROUP_UID,
			"user_uid" => $this->uid,
			"attribute_name" => ConfigMelanie::ATTRIBUTE_NAME_NAME,
			"attribute_perm" => ConfigMelanie::ATTRIBUTE_NAME_PERM,
			"pref_scope" => ConfigMelanie::ADDRESSBOOK_PREF_SCOPE,
			"pref_name" => ConfigMelanie::ADDRESSBOOK_PREF_DEFAULT_NAME,
			"attribute_permfg" => ConfigMelanie::ATTRIBUTE_NAME_PERMGROUP,
		];

		// Liste de tâches par défaut de l'utilisateur
		$addressbook = Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\AddressbookMelanie');
		if (isset($addressbook)
				&& is_array($addressbook)
				&& count($addressbook)) {
			$addressbook[0]->pdoConstruct(true);
			return $addressbook[0];
	    } else {
		    $addressbook = $this->getUserAddressbooks();
		    return isset($addressbook[0]) ? $addressbook[0] : null;
		}
		return false;
	}

	/**
	 * Récupère la liste des listes de contacts appartenant à l'utilisateur
	 * @return AddressbookMelanie[]
	 */
	function getUserAddressbooks() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getUserAddressbooks()");
		if (!isset($this->uid)) return false;

		// Initialisation du backend SQL
		Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);

		$query = Sql\SqlMelanieRequests::listUserObjects;
		// Replace name
		$query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['owner'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['id'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['ctag'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['synctoken'][MappingMelanie::name], $query);
		$query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['name'][MappingMelanie::name], $query);
		$query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['perm'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);

		// Params
		$params = [
			"group_uid" => ConfigMelanie::ADDRESSBOOK_GROUP_UID,
			"user_uid" => $this->uid,
			"attribute_name" => ConfigMelanie::ATTRIBUTE_NAME_NAME
		];
		// Liste les listes de contacts de l'utilisateur
		return Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\AddressbookMelanie');
	}

	/**
	 * Récupère la liste des listes de contacts appartenant à l'utilisateur
	 * ainsi que ceux qui lui sont partagés
	 * @return AddressbookMelanie[]
	 */
	function getSharedAddressbooks() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getSharedAddressbooks()");
		if (!isset($this->uid)) return false;

		// Initialisation du backend SQL
		Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);

		$query = Sql\SqlMelanieRequests::listSharedObjects;
		// Replace name
		$query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['owner'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['id'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['ctag'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['synctoken'][MappingMelanie::name], $query);
		$query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['name'][MappingMelanie::name], $query);
		$query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['perm'][MappingMelanie::name], $query);
		$query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);

		// Params
		$params = [
	 		"group_uid" => ConfigMelanie::ADDRESSBOOK_GROUP_UID,
		 	"user_uid" => $this->uid,
		 	"attribute_name" => ConfigMelanie::ATTRIBUTE_NAME_NAME,
		 	"attribute_perm" => ConfigMelanie::ATTRIBUTE_NAME_PERM,
			"attribute_permfg" => ConfigMelanie::ATTRIBUTE_NAME_PERMGROUP,
		];
		// Liste les listes de contacts de l'utilisateur
		return Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\AddressbookMelanie');
	}

	/**
	 * Recupère le timezone par défaut pour le
	 * need: $this->uid
	 */
	function getTimezone() {
	  M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getTimezone()");
	  if (!isset($this->uid)) return ConfigMelanie::CALENDAR_DEFAULT_TIMEZONE;

	  if (!isset($this->timezone)) {
	    // Replace name
	    $query = str_replace('{pref_name}', 'timezone', Sql\SqlMelanieRequests::getUserPref);

	    // Params
	    $params = [
	        "user_uid" => $this->uid,
	        "pref_scope" => ConfigMelanie::PREF_SCOPE,
	        "pref_name" => ConfigMelanie::TZ_PREF_NAME
	    ];

	    // Récupération du timezone
	    $res = Sql\DBMelanie::ExecuteQueryToObject($query, $params, $this);
	    // Test si le timezone est valide en PHP
	    try {
	      $tz = new \DateTimeZone($this->timezone);
	    } catch (\Exception $ex) {
	      $this->timezone = ConfigMelanie::CALENDAR_DEFAULT_TIMEZONE;
	    }
	    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getTimezone() this->timezone: " . $this->timezone);
	  }
	  return $this->timezone;
	}
}