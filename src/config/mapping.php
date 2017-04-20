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
 * Configuration du mapping vers Melanie2
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Config
 */
class MappingMelanie {
	// Mapping SQL
	/**
	 * Tables associées aux objets
	 * @var array
	 */
	public static $Table_Name = array(
		"EventMelanie" 				=> "kronolith_events",
		"HistoryMelanie" 			=> "horde_histories",
		"TaskMelanie" 				=> "nag_tasks",
		"ContactMelanie" 			=> "turba_objects",
		"EventProperties" 		=> "lightning_attributes",
    "TaskProperties" 			=> "lightning_attributes",
		"AttachmentMelanie" 	=> "horde_vfs",
		"CalendarMelanie" 		=> "horde_datatree",
		"CalendarSync" 				=> "kronolith_sync",
		"TaskslistSync" 			=> "nag_sync",
		"TaskslistMelanie" 		=> "horde_datatree",
		"AddressbookMelanie" 	=> "horde_datatree",
		"UserPrefs" 					=> "horde_prefs",
		"Share" 							=> "horde_datatree_attributes",
	);

	/**
	 * Clés primaires des tables Melanie2
	 * @var array
	*/
	public static $Primary_Keys = array(
		"EventMelanie" 				=> array("uid", "calendar"),
		"HistoryMelanie" 			=> array("uid", "action"),
		"TaskMelanie" 				=> array("uid", "taskslist"),
		"ContactMelanie" 			=> array("uid", "addressbook"),
		"EventProperties" 		=> array("event", "calendar", "user", "key"),
    "TaskProperties" 			=> array("task", "taskslist", "user", "key"),
		"AttachmentMelanie" 	=> array("path", "name"),
		"CalendarMelanie" 		=> array("id", "owner", "group"),
	  "CalendarSync" 				=> array("token", "calendar"),
		"TaskslistSync" 			=> array("token", "taskslist"),
		"TaskslistMelanie" 		=> array("id", "owner", "group"),
		"AddressbookMelanie" 	=> array("id", "owner", "group"),
		"UserPrefs" 					=> array("user", "scope", "name"),
		"Share" 							=> array("object_id", "name"),
	);

	/**
	 * Gestion du mapping entre les données et les champs de la base de données
	 * need name, type if != string, format for datetime (user constants)
	 * @var array
	*/
	public static $Data_Mapping = array(
			// Gestion de l'utilisateur : objet UserMelanie
			"UserMelanie" => array(
					"uid" => array(self::name => "user_uid")
			),
			// Gestion des préférences de l'utilisateur : objet UserPrefs
			"UserPrefs" => array(
					"user" 	=> array(self::name => "pref_uid", self::type => self::string, self::size => 255),
					"scope" => array(self::name => "pref_scope", self::type => self::string, self::size => 16),
					"name" 	=> array(self::name => "pref_name", self::type => self::string, self::size => 32),
					"value" => array(self::name => "pref_value"),
			),
			// Gestion des partages de l'utilisateur : objet Share
			"Share" => array(
					"object_id" => array(self::name => "datatree_id", self::type => self::integer),
					"name" 			=> array(self::name => "attribute_key", self::type => self::string, self::size => 255),
					"type" 			=> array(self::name => "attribute_name", self::type => self::string, self::size => 255),
					"acl" 			=> array(self::name => "attribute_value"),
			),
			// Gestion du calendrier : objet CalendarMelanie
			"CalendarMelanie" => array(
					"id" 				=> array(self::name => "calendar_id"),
					"owner" 		=> array(self::name => "calendar_owner", self::defaut => ''),
					"name" 			=> array(self::name => "calendar_name", self::defaut => ''),
					"ctag" 			=> array(self::name => "calendar_ctag"),
					"synctoken" => array(self::name => "calendar_synctoken"),
					"perm" 			=> array(self::name => "perm_calendar"),
					"object_id" => array(self::name => "datatree_id"),
					"group" 		=> array(self::name => "group_uid", self::defaut => ConfigMelanie::CALENDAR_GROUP_UID),
			),
			// Gestion de la liste de tâches : objet TaskslistMelanie
			"TaskslistMelanie" => array(
					"id" 				=> array(self::name => "task_owner"),
					"owner" 		=> array(self::name => "taskslist_owner", self::defaut => ''),
					"name" 			=> array(self::name => "taskslist_name", self::defaut => ''),
					"ctag" 			=> array(self::name => "taskslist_ctag"),
					"synctoken" => array(self::name => "taskslist_synctoken"),
					"perm" 			=> array(self::name => "perm_taskslist"),
					"object_id" => array(self::name => "datatree_id"),
					"group" 		=> array(self::name => "group_uid", self::defaut => ConfigMelanie::TASKSLIST_GROUP_UID),
			),
			// Gestion de la liste de contacts : objet AddressbookMelanie
			"AddressbookMelanie" => array(
					"id" 				=> array(self::name => "owner_id"),
					"owner" 		=> array(self::name => "addressbook_owner", self::defaut => ''),
					"name" 			=> array(self::name => "addressbook_name", self::defaut => ''),
					"ctag" 			=> array(self::name => "addressbook_ctag"),
					"synctoken" => array(self::name => "addressbook_synctoken"),
					"perm" 			=> array(self::name => "perm_addressbook"),
					"object_id" => array(self::name => "datatree_id"),
					"group" 		=> array(self::name => "group_uid", self::defaut => ConfigMelanie::ADDRESSBOOK_GROUP_UID),
			),
			// Gestion de l'historique : objet HistoryMelanie
			"HistoryMelanie" => array(
					"id" 					=> array(self::name => "history_id", self::type => self::integer),
					"uid" 				=> array(self::name => "object_uid"),
					"action" 			=> array(self::name => "history_action"),
					"timestamp" 	=> array(self::name => "history_ts", self::type => self::timestamp, self::defaut => 0),
					"description" => array(self::name => "history_desc"),
					"who" 				=> array(self::name => "history_who"),
					"extra" 			=> array(self::name => "history_extra")
			),
			// Gestion des évènements : objet EventMelanie
			"EventMelanie" => array(
					"uid" 			=> array(self::name => "event_uid", self::type => self::string, self::size => 255),
					"calendar" 	=> array(self::name => "calendar_id", self::type => self::string, self::size => 255),
					"id" 				=> array(self::name => "event_id", self::type => self::string, self::size => 32),
					"owner" 		=> array(self::name => "event_creator_id", self::type => self::string, self::size => 255, self::defaut => ''),
					"keywords" 	=> array(self::name => "event_keywords"),

					// DATA
					"title" 			=> array(self::name => "event_title", self::type => self::string, self::size => 255, self::defaut => ''),
					"description" => array(self::name => "event_description", self::defaut => ''),
					"category" 		=> array(self::name => "event_category", self::type => self::string, self::size => 80, self::defaut => ''),
					"location" 		=> array(self::name => "event_location", self::defaut => ''),
					"status" 			=> array(self::name => "event_status", self::type => self::integer, self::defaut => 2),
					"class" 			=> array(self::name => "event_private", self::type => self::integer, self::defaut => 0),
					"alarm" 			=> array(self::name => "event_alarm", self::type => self::integer, self::defaut => 0),
					"attendees" 	=> array(self::name => "event_attendees"),

					// TIME
					"start" 		=> array(self::name => "event_start", self::type => self::date, self::format => "Y-m-d H:i:s"),
					"end" 			=> array(self::name => "event_end", self::type => self::date, self::format => "Y-m-d H:i:s"),
					"modified" 	=> array(self::name => "event_modified", self::type => self::timestamp, self::defaut => 0),

					// RECURRENCE
					"exceptions" 			=> array(self::name => "event_exceptions"),
					"exceptionsdate" 	=> array(self::name => "event_exceptions"),
					"enddate" 				=> array(self::name => "event_recurenddate",self::type => self::date, self::format => "Y-m-d H:i:s"),
					"count" 					=> array(self::name => "event_recurcount", self::type => self::integer),
					"interval" 				=> array(self::name => "event_recurinterval", self::type => self::integer),
					"type" 						=> array(self::name => "event_recurtype", self::type => self::integer, self::defaut => 0),
					"days" 						=> array(self::name => "event_recurdays", self::type => self::integer)
			),
			// Gestion des propriétés des évènements : objet EventProperties
			"EventProperties" => array(
					"event" 		=> array(self::name => "event_uid", self::type => self::string, self::size => 255),
					"calendar" 	=> array(self::name => "calendar_id", self::type => self::string, self::size => 255),
					"user" 			=> array(self::name => "user_uid", self::type => self::string, self::size => 255),
					"key" 			=> array(self::name => "attribute_key", self::type => self::string, self::size => 255),
					"value" 		=> array(self::name => "attribute_value"),
			),
			// Gestion des propriétés des tâches : objet TaskProperties
			"TaskProperties" => array(
			    "task" 			=> array(self::name => "event_uid", self::type => self::string, self::size => 255),
			    "taskslist" => array(self::name => "calendar_id", self::type => self::string, self::size => 255),
			    "user" 			=> array(self::name => "user_uid", self::type => self::string, self::size => 255),
			    "key" 			=> array(self::name => "attribute_key", self::type => self::string, self::size => 255),
			    "value" 		=> array(self::name => "attribute_value"),
			),
			// Gestion des pièces jointes dans les évènements : objet AttachmentMelanie
			"AttachmentMelanie" => array(
					"id" => array(self::name => "vfs_id", self::type => self::integer),
					"type" 			=> array(self::name => "vfs_type", self::type => self::integer),
					"path" 			=> array(self::name => "vfs_path", self::type => self::string, self::size => 255),
					"name" 			=> array(self::name => "vfs_name", self::type => self::string, self::size => 255),
					"modified" 	=> array(self::name => "vfs_modified", self::type => self::integer, self::defaut => 0),
					"owner" 		=> array(self::name => "vfs_owner", self::type => self::string, self::size => 255),
					"data" 			=> array(self::name => "vfs_data", self::type => self::string),
			),
			// Gestion des SyncToken pour le calendrier : objet CalendarSync
			"CalendarSync" => array(
					"token" 		=> array(self::name => "token", self::type => self::integer),
					"calendar" 	=> array(self::name => "calendar_id", self::type => self::string, self::size => 255),
					"uid" 			=> array(self::name => "event_uid", self::type => self::string, self::size => 255),
					"action" 		=> array(self::name => "action", self::type => self::string, self::size => 3),
			),
			// Gestion des SyncToken pour la liste de tâches : objet TaskslistSync
			"TaskslistSync" => array(
					"token" 		=> array(self::name => "token", self::type => self::integer),
					"taskslist" 	=> array(self::name => "taskslist_id", self::type => self::string, self::size => 255),
					"uid" 			=> array(self::name => "task_uid", self::type => self::string, self::size => 255),
					"action" 		=> array(self::name => "action", self::type => self::string, self::size => 3),
			),
			// Gestion des tâches : objet TaskMelanie
			"TaskMelanie" => array(
					"id" 				=> array(self::name => "task_id", self::type => self::string, self::size => 32),
					"taskslist" => array(self::name => "task_owner", self::type => self::string, self::size => 255),
					"uid" 			=> array(self::name => "task_uid", self::type => self::string, self::size => 255),
					"owner" 		=> array(self::name => "task_creator", self::type => self::string, self::size => 255),

					// DATA
					"name" 				=> array(self::name => "task_name", self::type => self::string, self::size => 255),
					"description" => array(self::name => "task_desc"),
					"priority" 		=> array(self::name => "task_priority", self::type => self::integer),
					"category" 		=> array(self::name => "task_category", self::type => self::string, self::size => 80),
					"completed" 	=> array(self::name => "task_completed", self::type => self::integer),
					"alarm" 			=> array(self::name => "task_alarm"),
					"class" 			=> array(self::name => "task_private", self::type => self::integer),
					"assignee" 		=> array(self::name => "task_assignee", self::type => self::string, self::size => 255),
					"estimate" 		=> array(self::name => "task_estimate", self::type => self::double),
					"parent" 			=> array(self::name => "task_parent", self::type => self::string, self::size => 32),

					// TIME
					"due" 						=> array(self::name => "task_due", self::type => self::timestamp),
					"completed_date" 	=> array(self::name => "task_completed_date", self::type => self::timestamp),
					"start" 					=> array(self::name => "task_start", self::type => self::timestamp),
					"modified" 				=> array(self::name => "task_ts", self::type => self::timestamp, self::defaut => 0)
			),
			// Gestion des contacts : objet ContactMelanie
			"ContactMelanie" => array(
					"id" 					=> array(self::name => "object_id", self::type => self::string, self::size => 32),
					"addressbook" => array(self::name => "owner_id", self::type => self::string, self::size => 255),
					"uid" 				=> array(self::name => "object_uid", self::type => self::string, self::size => 255),
					"type" 				=> array(self::name => "object_type", self::type => self::string, self::size => 255),
					"modified" 		=> array(self::name => "object_ts", self::type => self::timestamp, self::defaut => 0),

					// DATA
					"members" 		=> array(self::name => "object_members"),
					"name" 				=> array(self::name => "object_name", self::type => self::string, self::size => 255),
					"alias" 			=> array(self::name => "object_alias", self::type => self::string, self::size => 32),
					"freebusyurl" => array(self::name => "object_freebusyurl", self::type => self::string, self::size => 255),
					"firstname" 	=> array(self::name => "object_firstname", self::type => self::string, self::size => 255),
					"lastname" 		=> array(self::name => "object_lastname", self::type => self::string, self::size => 255),
					"middlenames" => array(self::name => "object_middlenames", self::type => self::string, self::size => 255),
					"nameprefix" 	=> array(self::name => "object_nameprefix", self::type => self::string, self::size => 255),
					"namesuffix" 	=> array(self::name => "object_namesuffix", self::type => self::string, self::size => 32),
					"birthday" 		=> array(self::name => "object_bday", self::type => self::string, self::size => 10),

					"title" 	=> array(self::name => "object_title", self::type => self::string, self::size => 255),
					"company" => array(self::name => "object_company", self::type => self::string, self::size => 255),
					"notes" 	=> array(self::name => "object_notes"),

					"email" 	=> array(self::name => "object_email", self::type => self::string, self::size => 255),
					"email1" 	=> array(self::name => "object_email1", self::type => self::string, self::size => 255),
					"email2" 	=> array(self::name => "object_email2", self::type => self::string, self::size => 255),

					"cellphone" => array(self::name => "object_cellphone", self::type => self::string, self::size => 25),
					"fax" 			=> array(self::name => "object_fax", self::type => self::string, self::size => 25),

					"category" 	=> array(self::name => "object_category", self::type => self::string, self::size => 80),
					"url" 			=> array(self::name => "object_url", self::type => self::string, self::size => 255),
					// HOME
					"homeaddress" 		=> array(self::name => "object_homeaddress", self::type => self::string, self::size => 255),
					"homephone" 			=> array(self::name => "object_homephone", self::type => self::string, self::size => 25),
					"homestreet" 			=> array(self::name => "object_homestreet", self::type => self::string, self::size => 255),
					"homepob" 				=> array(self::name => "object_homepob", self::type => self::string, self::size => 10),
					"homecity" 				=> array(self::name => "object_homecity", self::type => self::string, self::size => 255),
					"homeprovince" 		=> array(self::name => "object_homeprovince", self::type => self::string, self::size => 255),
					"homepostalcode" 	=> array(self::name => "object_homepostalcode", self::type => self::string, self::size => 255),
					"homecountry" 		=> array(self::name => "object_homecountry", self::type => self::string, self::size => 255),
					// WORK
					"workaddress" 		=> array(self::name => "object_workaddress", self::type => self::string, self::size => 255),
					"workphone" 			=> array(self::name => "object_workphone", self::type => self::string, self::size => 25),
					"workstreet" 			=> array(self::name => "object_workstreet", self::type => self::string, self::size => 255),
					"workpob" 				=> array(self::name => "object_workpob", self::type => self::string, self::size => 10),
					"workcity" 				=> array(self::name => "object_workcity", self::type => self::string, self::size => 255),
					"workprovince" 		=> array(self::name => "object_workprovince", self::type => self::string, self::size => 255),
					"workpostalcode" 	=> array(self::name => "object_workpostalcode", self::type => self::string, self::size => 255),
					"workcountry" 		=> array(self::name => "object_workcountry", self::type => self::string, self::size => 255),

					"pgppublickey" 		=> array(self::name => "object_pgppublickey"),
					"smimepublickey" 	=> array(self::name => "object_smimepublickey"),

					"photo" 		=> array(self::name => "object_photo"),
					"phototype" => array(self::name => "object_phototype", self::type => self::string, self::size => 10),
					"logo" 			=> array(self::name => "object_logo"),
					"logotype" 	=> array(self::name => "object_logotype", self::type => self::string, self::size => 10),

					"timezone" 	=> array(self::name => "object_tz", self::type => self::string, self::size => 32),
					"geo" 			=> array(self::name => "object_geo", self::type => self::string, self::size => 255),
					"pager" 		=> array(self::name => "object_pager", self::type => self::string, self::size => 25),
					"role" 			=> array(self::name => "object_role", self::type => self::string, self::size => 255)
			)
	);

	// Mapping constants
	const name = "name";
	const type = "type";
	const size = "size";
	const format = "format";
	const string = "string";
	const integer = "integer";
	const double = "double";
	const date = "date";
	const timestamp = "timestamp";
	const defaut = "defaut";
	const sup = ">";
	const supeq = ">=";
	const inf = "<";
	const infeq = "<=";
	const diff = "<>";
	const like = "LIKE";
	const eq = "=";
	const in = "IN";

	// DATA MAPPING
	// Class
	const PRIV = 1;
	const PUB = 0;
	/**
	 * Class mapping
	 */
	public static $MapClassObjectMelanie = array(
		ConfigMelanie::PRIV => self::PRIV,
		ConfigMelanie::PUB => self::PUB,
		ConfigMelanie::CONFIDENTIAL => self::PRIV,
		self::PRIV => ConfigMelanie::PRIV,
		self::PUB => ConfigMelanie::PUB
	);

	// Status
	const NONE = 4;
	const TENTATIVE = 1;
	const CONFIRMED = 2;
	const CANCELLED = 3;
	/**
	 * Status mapping
	 */
	public static $MapStatusObjectMelanie = array(
			ConfigMelanie::TENTATIVE => self::TENTATIVE,
			ConfigMelanie::NONE => self::NONE,
			ConfigMelanie::CONFIRMED => self::CONFIRMED,
			ConfigMelanie::CANCELLED => self::CANCELLED,
			self::TENTATIVE => ConfigMelanie::TENTATIVE,
			self::CONFIRMED => ConfigMelanie::CONFIRMED,
			self::NONE => ConfigMelanie::NONE,
			self::CANCELLED => ConfigMelanie::CANCELLED
	);

	// Recurrence days
	const NODAY = 0;
	const SUNDAY = 1;
	const MONDAY = 2;
	const TUESDAY = 4;
	const WEDNESDAY = 8;
	const THURSDAY = 16;
	const FRIDAY = 32;
	const SATURDAY = 64;
	/**
	 * Recurdays mapping
	 */
	public static $MapRecurdaysObjectMelanie = array(
			ConfigMelanie::NODAY => self::NODAY,
			ConfigMelanie::SUNDAY => self::SUNDAY,
			ConfigMelanie::MONDAY => self::MONDAY,
			ConfigMelanie::TUESDAY => self::TUESDAY,
			ConfigMelanie::WEDNESDAY => self::WEDNESDAY,
			ConfigMelanie::THURSDAY => self::THURSDAY,
			ConfigMelanie::FRIDAY => self::FRIDAY,
			ConfigMelanie::SATURDAY => self::SATURDAY,
			self::NODAY => ConfigMelanie::NODAY,
			self::SUNDAY => ConfigMelanie::SUNDAY,
			self::MONDAY => ConfigMelanie::MONDAY,
			self::TUESDAY => ConfigMelanie::TUESDAY,
			self::WEDNESDAY => ConfigMelanie::WEDNESDAY,
			self::THURSDAY => ConfigMelanie::THURSDAY,
			self::FRIDAY => ConfigMelanie::FRIDAY,
			self::SATURDAY => ConfigMelanie::SATURDAY
	);

	// Recurrence type
	const NORECUR = 0;
	const DAILY = 1;
	const WEEKLY = 2;
	const MONTHLY = 3;
	const MONTHLY_BYDAY = 4;
	const YEARLY = 5;
	const YEARLY_BYDAY = 6;
	/**
	 * Recurtype mapping
	 */
	public static $MapRecurtypeObjectMelanie = array(
			ConfigMelanie::NORECUR => self::NORECUR,
			ConfigMelanie::DAILY => self::DAILY,
			ConfigMelanie::WEEKLY => self::WEEKLY,
			ConfigMelanie::MONTHLY => self::MONTHLY,
			ConfigMelanie::MONTHLY_BYDAY => self::MONTHLY_BYDAY,
			ConfigMelanie::YEARLY => self::YEARLY,
			ConfigMelanie::YEARLY_BYDAY => self::YEARLY_BYDAY,
			self::NORECUR => ConfigMelanie::NORECUR,
			self::DAILY => ConfigMelanie::DAILY,
			self::WEEKLY => ConfigMelanie::WEEKLY,
			self::MONTHLY => ConfigMelanie::MONTHLY,
			self::MONTHLY_BYDAY => ConfigMelanie::MONTHLY_BYDAY,
			self::YEARLY => ConfigMelanie::YEARLY,
			self::YEARLY_BYDAY => ConfigMelanie::YEARLY_BYDAY
	);

	// Attendee status
	const ATT_NEED_ACTION = 1;
	const ATT_ACCEPTED = 2;
	const ATT_DECLINED = 3;
	const ATT_TENTATIVE = 4;
	/**
	 * Attendee response mapping
	 */
	public static $MapAttendeeResponseObjectMelanie = array(
			ConfigMelanie::NEED_ACTION => self::ATT_NEED_ACTION,
			ConfigMelanie::ACCEPTED => self::ATT_ACCEPTED,
			ConfigMelanie::DECLINED => self::ATT_DECLINED,
			ConfigMelanie::IN_PROCESS => self::ATT_NEED_ACTION,
			ConfigMelanie::TENTATIVE => self::ATT_TENTATIVE,
			self::ATT_NEED_ACTION => ConfigMelanie::NEED_ACTION,
			self::ATT_ACCEPTED => ConfigMelanie::ACCEPTED,
			self::ATT_DECLINED => ConfigMelanie::DECLINED,
			self::ATT_TENTATIVE => ConfigMelanie::TENTATIVE
	);

	// Attendee role
	const CHAIR = 1;
	const REQ_PARTICIPANT = 1;
	const OPT_PARTICIPANT = 2;
	const NON_PARTICIPANT = 3;
	/**
	 * Attendee role mapping
	 */
	public static $MapAttendeeRoleObjectMelanie = array(
			ConfigMelanie::CHAIR => self::CHAIR,
			ConfigMelanie::REQ_PARTICIPANT => self::REQ_PARTICIPANT,
			ConfigMelanie::OPT_PARTICIPANT => self::OPT_PARTICIPANT,
			ConfigMelanie::NON_PARTICIPANT => self::NON_PARTICIPANT,
			self::CHAIR => ConfigMelanie::CHAIR,
			self::REQ_PARTICIPANT => ConfigMelanie::REQ_PARTICIPANT,
			self::OPT_PARTICIPANT => ConfigMelanie::OPT_PARTICIPANT,
			self::NON_PARTICIPANT => ConfigMelanie::NON_PARTICIPANT
	);

	// Task priority
	const NO_PRIORITY = 0;
	const VERY_HIGH = 1;
	const HIGH = 2;
	const NORMAL = 3;
	const LOW = 4;
	const VERY_LOW = 5;
	/**
	 * Priority Mapping
	 */
	public static $MapPriorityObjectMelanie = array(
	    ConfigMelanie::NO_PRIORITY => self::NO_PRIORITY,
			ConfigMelanie::VERY_HIGH => self::VERY_HIGH,
			ConfigMelanie::HIGH => self::HIGH,
			ConfigMelanie::NORMAL => self::NORMAL,
			ConfigMelanie::LOW => self::LOW,
			ConfigMelanie::VERY_LOW => self::VERY_LOW,
	    self::NO_PRIORITY => ConfigMelanie::NO_PRIORITY,
			self::VERY_HIGH => ConfigMelanie::VERY_HIGH,
			self::HIGH => ConfigMelanie::HIGH,
			self::NORMAL => ConfigMelanie::NORMAL,
			self::LOW => ConfigMelanie::LOW,
			self::VERY_LOW => ConfigMelanie::VERY_LOW
	);

	// Task completed
	const COMPLETED = 1;
	const NOTCOMPLETED = 0;
	/**
	 * Completed mapping
	 */
	public static $MapCompletedObjectMelanie = array(
			ConfigMelanie::COMPLETED => self::COMPLETED,
			ConfigMelanie::NOTCOMPLETED => self::NOTCOMPLETED,
			self::COMPLETED => ConfigMelanie::COMPLETED,
			self::NOTCOMPLETED => ConfigMelanie::NOTCOMPLETED
	);
}