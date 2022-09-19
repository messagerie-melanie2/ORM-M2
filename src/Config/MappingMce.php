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
 * Configuration du mapping vers MCE
 *
 * @author GMCD/Apitech
 * @package Librairie Mélanie2
 * @subpackage Config
 */
class MappingMce {
	// Mapping SQL
	/**
	 * Tables associées aux objets
	 * @var array
	 */
	public static $Table_Name = [];

	/**
	 * Clés primaires des tables Melanie2
	 * @var array
	*/
	public static $Primary_Keys = [];

	/**
	 * Gestion du mapping entre les données et les champs de la base de données
	 * need name, type if != string, format for datetime (user constants)
	 * @var array
	*/
	public static $Data_Mapping = [];
	
	/**
	 * Initialisation du mapping
	 */
	public static function Init() {
	  // Init Tables Name
	  self::$Table_Name = [
	      "EventMelanie" 		=> "kronolith_events",
	      "HistoryMelanie" 		=> "horde_histories",
	      "TaskMelanie" 		=> "nag_tasks",
	      "ContactMelanie" 		=> "turba_objects",
	      "EventProperties" 	=> "lightning_attributes",
	      "TaskProperties" 		=> "lightning_attributes",
	      "AttachmentMelanie" 	=> "horde_vfs",
	      "CalendarMelanie" 	=> "horde_datatree",
	      "CalendarSync" 		=> "kronolith_sync",
	      "TaskslistSync" 		=> "nag_sync",
	      "TaskslistMelanie" 	=> "horde_datatree",
	      "AddressbookSync" 	=> "turba_sync",
	      "AddressbookMelanie" 	=> "horde_datatree",
	      "UserPrefs" 			=> "horde_prefs",
		  "Share" 				=> "horde_datatree_attributes",
		  "Workspace"			=> "dwp_workspaces",
		  "WorkspaceShare"		=> "dwp_shares",
		  "WorkspaceHashtag"	=> "dwp_hashtags",
		  "WorkspaceHashtagRef"	=> "dwp_hashtags_workspaces",
		  "News"				=> "dwp_news",
		  "Rss"					=> "dwp_rss",
		  "NewsShare"			=> "dwp_news_share",
		  "Notification"		=> "dwp_notifications",
	  ];
	  // Init Primary Keys
	  self::$Primary_Keys = [
	      "UserMelanie" 		=> ["uid", "email"],
	      "EventMelanie" 		=> ["uid", "calendar"],
	      "HistoryMelanie" 		=> ["uid", "action"],
	      "TaskMelanie" 		=> ["uid", "taskslist"],
	      "ContactMelanie" 		=> ["uid", "addressbook"],
	      "EventProperties" 	=> ["event", "calendar", "user", "key"],
	      "TaskProperties" 		=> ["task", "taskslist", "user", "key"],
	      "AttachmentMelanie" 	=> ["path", "name"],
	      "CalendarMelanie" 	=> ["id", "owner", "group"],
	      "CalendarSync" 		=> ["token", "calendar"],
	      "TaskslistSync" 		=> ["token", "taskslist"],
	      "TaskslistMelanie" 	=> ["id", "owner", "group"],
	      "AddressbookSync" 	=> ["token", "addressbook"],
	      "AddressbookMelanie" 	=> ["id", "owner", "group"],
	      "UserPrefs" 			=> ["user", "scope", "name"],
		  "Share" 				=> ["object_id", "name"],
		  "Workspace"			=> ['uid'],
		  "WorkspaceShare"		=> ['workspace', 'user'],
		  "WorkspaceHashtag"	=> ['label'],
		  "WorkspaceHashtagRef"	=> ['hashtag', 'workspace'],
		  "News"				=> ["uid"],
		  "Rss"					=> ["uid"],
		  "NewsShare"			=> ["user", "service"],
		  "Notification"		=> ["uid", "owner"],
	  ];
	  // Init Data Mapping
	  self::$Data_Mapping = [
	      // Gestion de l'utilisateur : objet UserMelanie
	      "UserMelanie" => [
	          "dn"                     => [self::name => "dn", 			self::type => self::stringLdap],
	          "uid"                    => [self::name => "uid", 		self::type => self::stringLdap],
	          "fullname"               => [self::name => "cn", 			self::type => self::stringLdap],
	          "name"                   => [self::name => "displayname", self::type => self::stringLdap],
	          "email"                  => [self::name => "mail", 		self::type => self::stringLdap],
	          "email_list"             => [self::name => "mail", 		self::type => self::arrayLdap],
	          "email_send"             => [self::name => "mail", 		self::type => self::stringLdap],
	          "email_send_list"        => [self::name => "mail", 		self::type => self::arrayLdap],
		  ],
		  // Gestion des groupes : objet GroupMelanie
	      "GroupMelanie" => [
			"dn"                     => [self::name => "dn", self::type => self::stringLdap],
			"fullname"               => [self::name => "cn", self::type => self::stringLdap],
			"name"                   => [self::name => "displayname", self::type => self::stringLdap],
			"email"                  => [self::name => "mail", 		self::type => self::stringLdap],
			"email_list"             => [self::name => "mail", 		self::type => self::arrayLdap],
			"email_send"             => [self::name => "mail", 		self::type => self::stringLdap],
			"email_send_list"        => [self::name => "mail", 		self::type => self::arrayLdap],
		  ],
	      // Gestion des préférences de l'utilisateur : objet UserPrefs
	      "UserPrefs" => [
	          "user" 	=> [self::name => "pref_uid", self::type => self::string, self::size => 255],
	          "scope" 	=> [self::name => "pref_scope", self::type => self::string, self::size => 16],
	          "name" 	=> [self::name => "pref_name", self::type => self::string, self::size => 32],
	          "value" 	=> [self::name => "pref_value"],
	      ],
	      // Gestion des partages de l'utilisateur : objet Share
	      "Share" => [
	          "object_id" 		=> [self::name => "datatree_id", self::type => self::integer],
	          "name" 			=> [self::name => "attribute_key", self::type => self::string, self::size => 255],
	          "type" 			=> [self::name => "attribute_name", self::type => self::string, self::size => 255],
	          "acl" 			=> [self::name => "attribute_value"],
	      ],
	      // Gestion du calendrier : objet CalendarMelanie
	      "CalendarMelanie" => [
	          "id" 				=> [self::name => "calendar_id"],
	          "owner" 			=> [self::name => "calendar_owner"],
	          "name" 			=> [self::name => "calendar_name"],
	          "ctag" 			=> [self::name => "calendar_ctag"],
	          "synctoken" 		=> [self::name => "calendar_synctoken"],
	          "perm" 			=> [self::name => "perm_calendar"],
	          "object_id" 		=> [self::name => "datatree_id"],
	          "group" 			=> [self::name => "group_uid", self::defaut => DefaultConfig::CALENDAR_GROUP_UID],
	      ],
	      // Gestion de la liste de tâches : objet TaskslistMelanie
	      "TaskslistMelanie" => [
	          "id" 				=> [self::name => "task_owner"],
	          "owner" 			=> [self::name => "taskslist_owner"],
	          "name" 			=> [self::name => "taskslist_name"],
	          "ctag" 			=> [self::name => "taskslist_ctag"],
	          "synctoken" 		=> [self::name => "taskslist_synctoken"],
	          "perm" 			=> [self::name => "perm_taskslist"],
	          "object_id" 		=> [self::name => "datatree_id"],
	          "group" 			=> [self::name => "group_uid", self::defaut => DefaultConfig::TASKSLIST_GROUP_UID],
	      ],
	      // Gestion de la liste de contacts : objet AddressbookMelanie
	      "AddressbookMelanie" => [
	          "id" 				=> [self::name => "owner_id"],
	          "owner" 			=> [self::name => "addressbook_owner"],
	          "name" 			=> [self::name => "addressbook_name"],
	          "ctag" 			=> [self::name => "addressbook_ctag"],
	          "synctoken" 		=> [self::name => "addressbook_synctoken"],
	          "perm" 			=> [self::name => "perm_addressbook"],
	          "object_id" 		=> [self::name => "datatree_id"],
	          "group" 			=> [self::name => "group_uid", self::defaut => DefaultConfig::ADDRESSBOOK_GROUP_UID],
	      ],
	      // Gestion de l'historique : objet HistoryMelanie
	      "HistoryMelanie" => [
	          "id" 					=> [self::name => "history_id", self::type => self::integer],
	          "uid" 				=> [self::name => "object_uid"],
	          "action" 				=> [self::name => "history_action"],
	          "timestamp" 			=> [self::name => "history_ts", self::type => self::timestamp, self::defaut => 0],
	          "description" 		=> [self::name => "history_desc"],
	          "who" 				=> [self::name => "history_who"],
	          "extra" 				=> [self::name => "history_extra"]
	      ],
	      // Gestion des évènements : objet EventMelanie
	      "EventMelanie" => [
	          "uid" 			=> [self::name => "event_uid", self::type => self::string, self::size => 255],
	          "realuid" 		=> [self::name => "event_realuid", self::type => self::string, self::size => 255],
	          "calendar" 		=> [self::name => "calendar_id", self::type => self::string, self::size => 255],
	          "id" 				=> [self::name => "event_id", self::type => self::string, self::size => 64],
	          "owner" 			=> [self::name => "event_creator_id", self::type => self::string, self::size => 255, self::defaut => ''],
	          "keywords" 		=> [self::name => "event_keywords"],
			  "version" 		=> [self::name => "event_version", self::type => self::integer, self::defaut => 1],
	          
	          // DATA
	          "title" 			=> [self::name => "event_title", self::type => self::string, self::size => 255, self::defaut => ''],
	          "description"   	=> [self::name => "event_description", self::defaut => ''],
	          "category" 		=> [self::name => "event_category", self::type => self::string, self::size => 80, self::defaut => ''],
	          "location" 		=> [self::name => "event_location", self::defaut => ''],
	          "status" 			=> [self::name => "event_status", self::type => self::integer, self::defaut => 2],
	          "class" 			=> [self::name => "event_private", self::type => self::integer, self::defaut => 0],
	          "sequence" 		=> [self::name => "event_sequence", self::type => self::integer, self::defaut => 0],
	          "priority" 		=> [self::name => "event_priority", self::type => self::integer, self::defaut => 0],
	          "alarm" 			=> [self::name => "event_alarm", self::type => self::integer, self::defaut => 0],
	          "is_deleted"    	=> [self::name => "event_is_deleted", self::type => self::integer, self::defaut => 0],
	          "is_exception"  	=> [self::name => "event_is_exception", self::type => self::integer, self::defaut => 0],
	          "transparency" 	=> [self::name => "event_transparency", self::type => self::string, self::size => 10, self::defaut => 'OPAQUE'],
	          "properties" 	  	=> [self::name => "event_properties_json"],
			  "attachments" 	=> [self::name => "event_attachments_json"],
	          
	          // ATTENDEES
	          "attendees" 	           => [self::name => "event_attendees"],
	          "organizer_json" 	       => [self::name => "event_organizer_json"],
	          "organizer_calendar_id"  => [self::name => "organizer_calendar_id"],
	          
	          // TIME
	          "all_day"  	    => [self::name => "event_all_day", self::type => self::integer, self::defaut => 0],
	          "start" 		    => [self::name => "event_start", self::type => self::date, self::format => "Y-m-d H:i:s"],
	          "end" 			=> [self::name => "event_end", self::type => self::date, self::format => "Y-m-d H:i:s"],
	          "created" 	    => [self::name => "event_created", self::type => self::timestamp, self::defaut => 0],
	          "modified" 	    => [self::name => "event_modified", self::type => self::timestamp, self::defaut => 0],
	          "modified_json" 	=> [self::name => "event_modified_json", self::type => self::timestamp, self::defaut => 0],
	          "timezone"      	=> [self::name => "event_timezone", self::type => self::string, self::defaut => 'Europe/Paris'],
	          
	          // RECURRENCE
	          "exceptions" 		=> [self::name => "event_exceptions"],
	          "enddate" 		=> [self::name => "event_recurenddate",self::type => self::date, self::format => "Y-m-d H:i:s"],
	          "count" 			=> [self::name => "event_recurcount", self::type => self::integer],
	          "interval" 		=> [self::name => "event_recurinterval", self::type => self::integer],
	          "type" 			=> [self::name => "event_recurtype", self::type => self::integer, self::defaut => 0],
	          "days" 			=> [self::name => "event_recurdays", self::type => self::integer],
	          "recurrence_id" 	=> [self::name => "event_recurrence_id", self::type => self::date],
	          "recurrence_json" => [self::name => "event_recurrence_json"],
	      ],
	      // Gestion des propriétés des évènements : objet EventProperties
	      "EventProperties" => [
	          "event" 		=> [self::name => "event_uid", self::type => self::string, self::size => 255],
	          "calendar" 	=> [self::name => "calendar_id", self::type => self::string, self::size => 255],
	          "user" 		=> [self::name => "user_uid", self::type => self::string, self::size => 255],
	          "key" 		=> [self::name => "attribute_key", self::type => self::string, self::size => 255],
	          "value" 		=> [self::name => "attribute_value"],
	      ],
	      // Gestion des propriétés des tâches : objet TaskProperties
	      "TaskProperties" => [
	          "task" 		=> [self::name => "event_uid", self::type => self::string, self::size => 255],
	          "taskslist" 	=> [self::name => "calendar_id", self::type => self::string, self::size => 255],
	          "user" 		=> [self::name => "user_uid", self::type => self::string, self::size => 255],
	          "key" 		=> [self::name => "attribute_key", self::type => self::string, self::size => 255],
	          "value" 		=> [self::name => "attribute_value"],
	      ],
	      // Gestion des pièces jointes dans les évènements : objet AttachmentMelanie
	      "AttachmentMelanie" => [
	          "id" => [self::name => "vfs_id", self::type => self::integer],
	          "type" 		=> [self::name => "vfs_type", self::type => self::integer],
	          "path" 		=> [self::name => "vfs_path", self::type => self::string, self::size => 255],
	          "name" 		=> [self::name => "vfs_name", self::type => self::string, self::size => 255],
	          "modified" 	=> [self::name => "vfs_modified", self::type => self::integer, self::defaut => 0],
	          "owner" 		=> [self::name => "vfs_owner", self::type => self::string, self::size => 255],
	          "data" 		=> [self::name => "vfs_data", self::type => self::string],
	      ],
	      // Gestion des SyncToken pour le calendrier : objet CalendarSync
	      "CalendarSync" => [
	          "token" 		=> [self::name => "token", self::type => self::integer],
	          "calendar" 	=> [self::name => "calendar_id", self::type => self::string, self::size => 255],
	          "uid" 		=> [self::name => "event_uid", self::type => self::string, self::size => 255],
	          "action" 		=> [self::name => "action", self::type => self::string, self::size => 3],
	      ],
	      // Gestion des SyncToken pour la liste de tâches : objet TaskslistSync
	      "TaskslistSync" => [
	          "token" 		=> [self::name => "token", self::type => self::integer],
	          "taskslist" 	=> [self::name => "taskslist_id", self::type => self::string, self::size => 255],
	          "uid" 		=> [self::name => "task_uid", self::type => self::string, self::size => 255],
	          "action" 		=> [self::name => "action", self::type => self::string, self::size => 3],
	      ],
	      // Gestion des SyncToken pour la liste de tâches : objet AddressbookSync
	      "AddressbookSync" => [
	          "token" 		  	=> [self::name => "token", self::type => self::integer],
              "addressbook"		=> [self::name => "addressbook_id", self::type => self::string, self::size => 255],
	          "uid" 			=> [self::name => "contact_uid", self::type => self::string, self::size => 255],
	          "action" 		   	=> [self::name => "action", self::type => self::string, self::size => 3],
	      ],
	      // Gestion des tâches : objet TaskMelanie
	      "TaskMelanie" => [
	          "id" 			=> [self::name => "task_id", self::type => self::string, self::size => 32],
	          "taskslist" 	=> [self::name => "task_owner", self::type => self::string, self::size => 255],
	          "uid" 		=> [self::name => "task_uid", self::type => self::string, self::size => 255],
	          "owner" 		=> [self::name => "task_creator", self::type => self::string, self::size => 255],
	          
	          // DATA
	          "name" 		=> [self::name => "task_name", self::type => self::string, self::size => 255],
	          "description" => [self::name => "task_desc"],
	          "priority" 	=> [self::name => "task_priority", self::type => self::integer],
	          "category" 	=> [self::name => "task_category", self::type => self::string, self::size => 80],
	          "completed" 	=> [self::name => "task_completed", self::type => self::integer],
	          "alarm" 		=> [self::name => "task_alarm"],
	          "class" 		=> [self::name => "task_private", self::type => self::integer],
	          "assignee" 	=> [self::name => "task_assignee", self::type => self::string, self::size => 255],
	          "estimate" 	=> [self::name => "task_estimate", self::type => self::double],
	          "parent" 		=> [self::name => "task_parent", self::type => self::string, self::size => 32],
	          
	          // TIME
	          "due" 			=> [self::name => "task_due", self::type => self::timestamp],
	          "completed_date" 	=> [self::name => "task_completed_date", self::type => self::timestamp],
	          "start" 			=> [self::name => "task_start", self::type => self::timestamp],
	          "modified" 		=> [self::name => "task_ts", self::type => self::timestamp, self::defaut => 0]
	      ],
	      // Gestion des contacts : objet ContactMelanie
	      "ContactMelanie" => [
	          "id" 				=> [self::name => "object_id", self::type => self::string, self::size => 32],
	          "addressbook" 	=> [self::name => "owner_id", self::type => self::string, self::size => 255],
	          "uid" 			=> [self::name => "object_uid", self::type => self::string, self::size => 255],
	          "type" 			=> [self::name => "object_type", self::type => self::string, self::size => 255],
	          "modified" 		=> [self::name => "object_ts", self::type => self::timestamp, self::defaut => 0],
	          
	          // DATA
	          "members" 		=> [self::name => "object_members"],
	          "name" 			=> [self::name => "object_name", self::type => self::string, self::size => 255],
	          "alias" 			=> [self::name => "object_alias", self::type => self::string, self::size => 32],
	          "freebusyurl" 	=> [self::name => "object_freebusyurl", self::type => self::string, self::size => 255],
	          "firstname" 		=> [self::name => "object_firstname", self::type => self::string, self::size => 255],
	          "lastname" 		=> [self::name => "object_lastname", self::type => self::string, self::size => 255],
	          "middlenames" 	=> [self::name => "object_middlenames", self::type => self::string, self::size => 255],
	          "nameprefix" 		=> [self::name => "object_nameprefix", self::type => self::string, self::size => 255],
	          "namesuffix" 		=> [self::name => "object_namesuffix", self::type => self::string, self::size => 32],
	          "birthday" 		=> [self::name => "object_bday", self::type => self::string, self::size => 10],
	          
	          "title" 	=> [self::name => "object_title", self::type => self::string, self::size => 255],
	          "company" => [self::name => "object_company", self::type => self::string, self::size => 255],
	          "notes" 	=> [self::name => "object_notes"],
	          
	          "email" 	=> [self::name => "object_email", self::type => self::string, self::size => 255],
	          "email1" 	=> [self::name => "object_email1", self::type => self::string, self::size => 255],
	          "email2" 	=> [self::name => "object_email2", self::type => self::string, self::size => 255],
	          
	          "cellphone" => [self::name => "object_cellphone", self::type => self::string, self::size => 25],
	          "fax" 	  => [self::name => "object_fax", self::type => self::string, self::size => 25],
	          
	          "category" 		=> [self::name => "object_category", self::type => self::string, self::size => 80],
	          "url" 			=> [self::name => "object_url", self::type => self::string, self::size => 255],
	          // HOME
	          "homeaddress" 	=> [self::name => "object_homeaddress", self::type => self::string, self::size => 255],
	          "homephone" 		=> [self::name => "object_homephone", self::type => self::string, self::size => 25],
	          "homestreet" 		=> [self::name => "object_homestreet", self::type => self::string, self::size => 255],
	          "homepob" 		=> [self::name => "object_homepob", self::type => self::string, self::size => 10],
	          "homecity" 		=> [self::name => "object_homecity", self::type => self::string, self::size => 255],
	          "homeprovince" 	=> [self::name => "object_homeprovince", self::type => self::string, self::size => 255],
	          "homepostalcode" 	=> [self::name => "object_homepostalcode", self::type => self::string, self::size => 255],
	          "homecountry" 	=> [self::name => "object_homecountry", self::type => self::string, self::size => 255],
	          // WORK
	          "workaddress" 	=> [self::name => "object_workaddress", self::type => self::string, self::size => 255],
	          "workphone" 		=> [self::name => "object_workphone", self::type => self::string, self::size => 25],
	          "workstreet" 		=> [self::name => "object_workstreet", self::type => self::string, self::size => 255],
	          "workpob" 		=> [self::name => "object_workpob", self::type => self::string, self::size => 10],
	          "workcity" 		=> [self::name => "object_workcity", self::type => self::string, self::size => 255],
	          "workprovince" 	=> [self::name => "object_workprovince", self::type => self::string, self::size => 255],
	          "workpostalcode" 	=> [self::name => "object_workpostalcode", self::type => self::string, self::size => 255],
	          "workcountry" 	=> [self::name => "object_workcountry", self::type => self::string, self::size => 255],
	          
	          "pgppublickey" 	=> [self::name => "object_pgppublickey"],
	          "smimepublickey" 	=> [self::name => "object_smimepublickey"],
	          
	          "photo" 		=> [self::name => "object_photo"],
	          "phototype" 	=> [self::name => "object_phototype", self::type => self::string, self::size => 10],
	          "logo" 		=> [self::name => "object_logo"],
	          "logotype" 	=> [self::name => "object_logotype", self::type => self::string, self::size => 10],
	          
	          "timezone" 	=> [self::name => "object_tz", self::type => self::string, self::size => 32],
	          "geo" 		=> [self::name => "object_geo", self::type => self::string, self::size => 255],
	          "pager" 		=> [self::name => "object_pager", self::type => self::string, self::size => 25],
	          "role" 		=> [self::name => "object_role", self::type => self::string, self::size => 255]
		  ],
		  // Gestion d'un workspace : objet Workspace
		  "Workspace" => [
				"id"			=> [self::name => "workspace_id", self::type => self::integer],
				"uid"			=> [self::name => "workspace_uid", self::type => self::string, self::size => 40],
				"created" 		=> [self::name => "created", self::type => self::date],
				"modified"		=> [self::name => "modified", self::type => self::date],
				"creator"		=> [self::name => "workspace_creator", self::type => self::string, self::size => 255],
				"title"			=> [self::name => "workspace_title", self::type => self::string, self::size => 255],
				"description"	=> [self::name => "workspace_description"],
				"logo"			=> [self::name => "workspace_logo"],
				"ispublic"		=> [self::name => "workspace_ispublic", self::type => self::integer],
				"isarchived"	=> [self::name => "workspace_isarchived", self::type => self::integer],
				"objects"		=> [self::name => "workspace_objects"],
				"links"			=> [self::name => "workspace_links"],
				"flux"			=> [self::name => "workspace_flux"],
				"settings"		=> [self::name => "workspace_settings"],
		  ],
		  // Gestion des partage de workspace : object WorkspaceShare
		  "WorkspaceShare" => [
				"workspace" 	=> [self::name => "workspace_id", self::type => self::integer],
				"user" 			=> [self::name => "user_uid", self::type => self::string, self::size => 255],
				"rights" 		=> [self::name => "rights", self::type => self::string, self::size => 1],
		  ],
		  // Gestion des hashtags de workspace : objet WorkspaceHashtag
		  "WorkspaceHashtag" => [
				"id"	=> [self::name => "hashtag_id", self::type => self::integer],
				"label"	=> [self::name => "hashtag", self::type => self::string, self::size => 255],
		  ],
		  // Gestion du lien entre les hashtags et les workspaces : objet WorkspaceHashtagRef
		  "WorkspaceHashtagRef" => [
				"hashtag"	=> [self::name => "hashtag_id", self::type => self::integer],
				"workspace"	=> [self::name => "workspace_id", self::type => self::integer],
		  ],
		  // Gestion des news dans le bureau numérique
		  "News" => [
				"id" 			=> [self::name => "news_id", self::type => self::integer],
				"uid" 			=> [self::name => "news_uid"],
				"title" 		=> [self::name => "news_title"],
				"description" 	=> [self::name => "news_description"],
				"created" 		=> [self::name => "news_created", self::type => self::date],
				"modified"		=> [self::name => "news_modified", self::type => self::date],
				"service" 		=> [self::name => "news_service"],
				"service_name" 	=> [self::name => "news_service_name"],
				"creator" 		=> [self::name => "news_creator_id"],
		  ],
		  // Gestion des flux rss dans le bureau numérique
		  "Rss"	=> [
				"id" 		=> [self::name => "rss_id", self::type => self::integer],
				"uid" 		=> [self::name => "rss_uid"],
				"title" 	=> [self::name => "rss_title"],
				"url" 		=> [self::name => "rss_url"],
				"source" 	=> [self::name => "rss_source", self::type => self::string, self::size => 20],
				"service" 	=> [self::name => "rss_service"],
				"creator" 	=> [self::name => "rss_creator_id"],
		  ],
		  // Gestion des droits sur les news dans le bureau numérique
		  "NewsShare" => [
				"id" 		=> [self::name => "news_share_id", self::type => self::integer],
				"service" 	=> [self::name => "news_share_service"],
				"user" 		=> [self::name => "news_share_user_id"],
				"right" 	=> [self::name => "news_share_right", self::type => self::string, self::size => 1], // 'a' or 'p'
		  ],
		  // Gestion des notifications dans le bureau numérique
		  "Notification" => [
				"id" 			=> [self::name => "notification_id", self::type => self::integer],
				"uid" 			=> [self::name => "notification_uid"],
				"owner" 		=> [self::name => "notification_owner"],
				"from" 			=> [self::name => "notification_from"],
				"title" 		=> [self::name => "notification_title"],
				"content" 		=> [self::name => "notification_content"],
				"category" 		=> [self::name => "notification_category"],
				"action" 		=> [self::name => "notification_action"],
				"created" 	    => [self::name => "notification_created", self::type => self::timestamp],
				"modified" 	    => [self::name => "notification_modified", self::type => self::timestamp],
				"isread"		=> [self::name => "notification_isread", self::type => self::integer],
				"isdeleted"		=> [self::name => "notification_isdeleted", self::type => self::integer],
		  ],
	  ];
	}

	/**
	 * Mise a jour du DataMapping depuis une application externe
	 * Permet de faire le mapping de façon dynamique
	 * 
	 * @param string $object Nom de l'objet (UserMelanie, CalendarMelanie, ...)
	 * @param array $dataMapping Données à mettre à jour, effectue un array_merge
	 * 
	 * @return boolean true si les valeurs sont OK, false sinon
	 */
	public static function UpdateDataMapping($object, $dataMapping) {
		if (isset(self::$Data_Mapping[$object])) {
			self::$Data_Mapping[$object] = array_merge(self::$Data_Mapping[$object], $dataMapping);
			return true;
		}
		return false;
	}

	// Mapping constants
	const name = "name";
	const type = "type";
	const size = "size";
	const format = "format";
	const string = "string";
	const integer = "integer";
	const double = "double";
	const date = "date";
	const prefixLdap = "prefixLdap";
	const arrayLdap = "arrayLdap";
	const stringLdap = "stringLdap";
	const booleanLdap = "booleanLdap";
	const trueLdapValue = "trueLdapValue";
	const falseLdapValue = "falseLdapValue";
	const emptyLdapValue = "emptyLdapValue";
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
	const notin = "NOT IN";
	const between = "BETWEEN";
	const notbetween = "NOT BETWEEN";

	// DATA MAPPING
	// Class
	const PRIV = 1;
	const PUB = 0;
	const CONF = 2;
	/**
	 * Class mapping object to MCE
	 */
	public static $MapClassObjectToMce = [
	    DefaultConfig::PRIV => self::PRIV,
	    DefaultConfig::PUB => self::PUB,
	    DefaultConfig::CONFIDENTIAL => self::PRIV,
	];
	/**
	 * Class mapping MCE to object
	 */
	public static $MapClassMceToObject = [
	    self::PRIV => DefaultConfig::PRIV,
	    self::PUB => DefaultConfig::PUB
	];

	// Status
	const NONE = 4;
	const TELEWORK = 5;
	const TENTATIVE = 1;
	const CONFIRMED = 2;
	const CANCELLED = 3;
	/**
	 * Status mapping object to MCE
	 */
	public static $MapStatusObjectToMce = [
	    DefaultConfig::TENTATIVE => self::TENTATIVE,
	    DefaultConfig::NONE => self::NONE,
		DefaultConfig::TELEWORK => self::TELEWORK,
	    DefaultConfig::CONFIRMED => self::CONFIRMED,
	    DefaultConfig::CANCELLED => self::CANCELLED,
	];
	/**
	 * Status mapping MCE to object
	 */
	public static $MapStatusMceToObject = [
	    self::TENTATIVE => DefaultConfig::TENTATIVE,
	    self::CONFIRMED => DefaultConfig::CONFIRMED,
	    self::NONE => DefaultConfig::NONE,
		self::TELEWORK => DefaultConfig::TELEWORK,
	    self::CANCELLED => DefaultConfig::CANCELLED
	];

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
	 * Recurdays mapping object to MCE
	 */
	public static $MapRecurdaysObjectToMce = [
	    DefaultConfig::NODAY => self::NODAY,
	    DefaultConfig::SUNDAY => self::SUNDAY,
	    DefaultConfig::MONDAY => self::MONDAY,
	    DefaultConfig::TUESDAY => self::TUESDAY,
	    DefaultConfig::WEDNESDAY => self::WEDNESDAY,
	    DefaultConfig::THURSDAY => self::THURSDAY,
	    DefaultConfig::FRIDAY => self::FRIDAY,
	    DefaultConfig::SATURDAY => self::SATURDAY,
	];
	/**
	 * Recurdays mapping MCE to object
	 */
	public static $MapRecurdaysMceToObject = [
	    self::NODAY => DefaultConfig::NODAY,
	    self::SUNDAY => DefaultConfig::SUNDAY,
	    self::MONDAY => DefaultConfig::MONDAY,
	    self::TUESDAY => DefaultConfig::TUESDAY,
	    self::WEDNESDAY => DefaultConfig::WEDNESDAY,
	    self::THURSDAY => DefaultConfig::THURSDAY,
	    self::FRIDAY => DefaultConfig::FRIDAY,
	    self::SATURDAY => DefaultConfig::SATURDAY
	];

	// Recurrence type
	const NORECUR = 0;
	const DAILY = 1;
	const WEEKLY = 2;
	const MONTHLY = 3;
	const MONTHLY_BYDAY = 4;
	const YEARLY = 5;
	const YEARLY_BYDAY = 6;
	/**
	 * Recurtype mapping object to MCE
	 */
	public static $MapRecurtypeObjectToMce = [
	    DefaultConfig::NORECUR => self::NORECUR,
	    DefaultConfig::DAILY => self::DAILY,
	    DefaultConfig::WEEKLY => self::WEEKLY,
	    DefaultConfig::MONTHLY => self::MONTHLY,
	    DefaultConfig::MONTHLY_BYDAY => self::MONTHLY_BYDAY,
	    DefaultConfig::YEARLY => self::YEARLY,
	    DefaultConfig::YEARLY_BYDAY => self::YEARLY_BYDAY,
	];
	/**
	 * Recurtype mapping MCE to object
	 */
	public static $MapRecurtypeMceToObject = [
	    self::NORECUR => DefaultConfig::NORECUR,
	    self::DAILY => DefaultConfig::DAILY,
	    self::WEEKLY => DefaultConfig::WEEKLY,
	    self::MONTHLY => DefaultConfig::MONTHLY,
	    self::MONTHLY_BYDAY => DefaultConfig::MONTHLY_BYDAY,
	    self::YEARLY => DefaultConfig::YEARLY,
	    self::YEARLY_BYDAY => DefaultConfig::YEARLY_BYDAY
	];

	// Attendee type
	const ATT_TYPE_INDIVIDUAL = 1;
	const ATT_TYPE_GROUP = 2;
	const ATT_TYPE_RESOURCE = 3;
	const ATT_TYPE_ROOM = 4;
	const ATT_TYPE_UNKNOWN = 5;
	/**
	 * Attendee type mapping object to MCE
	 */
	public static $MapAttendeeTypeObjectToMce = [
	    DefaultConfig::INDIVIDUAL => self::ATT_TYPE_INDIVIDUAL,
	    DefaultConfig::GROUP => self::ATT_TYPE_GROUP,
	    DefaultConfig::RESOURCE => self::ATT_TYPE_RESOURCE,
	    DefaultConfig::ROOM => self::ATT_TYPE_ROOM,
	    DefaultConfig::UNKNOWN => self::ATT_TYPE_UNKNOWN,
	];
	/**
	 * Attendee type mapping MCE to object
	 */
	public static $MapAttendeeTypeMceToObject = [
	    self::ATT_TYPE_INDIVIDUAL => DefaultConfig::INDIVIDUAL,
	    self::ATT_TYPE_GROUP => DefaultConfig::GROUP,
	    self::ATT_TYPE_RESOURCE => DefaultConfig::RESOURCE,
	    self::ATT_TYPE_ROOM => DefaultConfig::ROOM,
		self::ATT_TYPE_UNKNOWN => DefaultConfig::UNKNOWN,
	];

	// Attendee status
	const ATT_NEED_ACTION = 1;
	const ATT_ACCEPTED = 2;
	const ATT_DECLINED = 3;
	const ATT_TENTATIVE = 4;
	/**
	 * Attendee response mapping object to MCE
	 */
	public static $MapAttendeeResponseObjectToMce = [
	    DefaultConfig::NEED_ACTION => self::ATT_NEED_ACTION,
	    DefaultConfig::ACCEPTED => self::ATT_ACCEPTED,
	    DefaultConfig::DECLINED => self::ATT_DECLINED,
	    DefaultConfig::IN_PROCESS => self::ATT_NEED_ACTION,
	    DefaultConfig::TENTATIVE => self::ATT_TENTATIVE,
	];
	/**
	 * Attendee response mapping MCE to object
	 */
	public static $MapAttendeeResponseMceToObject = [
	    self::ATT_NEED_ACTION => DefaultConfig::NEED_ACTION,
	    self::ATT_ACCEPTED => DefaultConfig::ACCEPTED,
	    self::ATT_DECLINED => DefaultConfig::DECLINED,
	    self::ATT_TENTATIVE => DefaultConfig::TENTATIVE
	];

	// Attendee role	
	const REQ_PARTICIPANT = 1;
	const OPT_PARTICIPANT = 2;
	const NON_PARTICIPANT = 3;
	const CHAIR = 4;
	/**
	 * Attendee role mapping object to MCE
	 */
	public static $MapAttendeeRoleObjectToMce = [
	    DefaultConfig::CHAIR => self::CHAIR,
	    DefaultConfig::REQ_PARTICIPANT => self::REQ_PARTICIPANT,
	    DefaultConfig::OPT_PARTICIPANT => self::OPT_PARTICIPANT,
	    DefaultConfig::NON_PARTICIPANT => self::NON_PARTICIPANT,
	];
	/**
	 * Attendee role mapping MCE to object
	 */
	public static $MapAttendeeRoleMceToObject = [
	    self::CHAIR => DefaultConfig::CHAIR,
	    self::REQ_PARTICIPANT => DefaultConfig::REQ_PARTICIPANT,
	    self::OPT_PARTICIPANT => DefaultConfig::OPT_PARTICIPANT,
	    self::NON_PARTICIPANT => DefaultConfig::NON_PARTICIPANT
	];

	// Task priority
	const NO_PRIORITY = 0;
	const VERY_HIGH = 1;
	const HIGH = 2;
	const NORMAL = 3;
	const LOW = 4;
	const VERY_LOW = 5;
	/**
	 * Priority Mapping object to MCE
	 */
	public static $MapPriorityObjectToMce = [
	    DefaultConfig::NO_PRIORITY => self::NO_PRIORITY,
	    DefaultConfig::VERY_HIGH => self::VERY_HIGH,
	    DefaultConfig::HIGH => self::HIGH,
	    DefaultConfig::NORMAL => self::NORMAL,
	    DefaultConfig::LOW => self::LOW,
	    DefaultConfig::VERY_LOW => self::VERY_LOW,
	];
	/**
	 * Priority Mapping MCE to object
	 */
	public static $MapPriorityMceToObject = [
	    self::NO_PRIORITY => DefaultConfig::NO_PRIORITY,
	    self::VERY_HIGH => DefaultConfig::VERY_HIGH,
	    self::HIGH => DefaultConfig::HIGH,
	    self::NORMAL => DefaultConfig::NORMAL,
	    self::LOW => DefaultConfig::LOW,
	    self::VERY_LOW => DefaultConfig::VERY_LOW
	];

	// Task completed
	const COMPLETED = 1;
	const NOTCOMPLETED = 0;
	/**
	 * Completed mapping object to MCE
	 */
	public static $MapCompletedObjectToMce = [
	    DefaultConfig::COMPLETED => self::COMPLETED,
	    DefaultConfig::NOTCOMPLETED => self::NOTCOMPLETED,
	];
	/**
	 * Completed mapping MCE to object
	 */
	public static $MapCompletedMceToObject = [
	    self::COMPLETED => DefaultConfig::COMPLETED,
	    self::NOTCOMPLETED => DefaultConfig::NOTCOMPLETED
	];
}

// Initialisation du mapping
MappingMce::Init();
