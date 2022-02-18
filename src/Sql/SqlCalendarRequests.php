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
namespace LibMelanie\Sql;

/**
 * Liste des requêtes SQL vers les calendriers
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage SQL
 *
 */
class SqlCalendarRequests {
	/**
	 * @var string SELECT
	 * @param REPLACE {event_range}
	 * @param PDO :calendar_id
	 */
	const listAllEvents = "SELECT k1.*, k2.event_creator_id as organizer_uid, k2.event_attendees as organizer_attendees, k2.calendar_id as organizer_calendar FROM kronolith_events k1 LEFT JOIN kronolith_events k2 ON k1.event_uid = k2.event_uid AND char_length(k2.event_attendees) > 10 WHERE k1.calendar_id = :calendar_id{event_range};";

	/**
	 * @var string SELECT
	 * @param REPLACE {event_range}
	 * @param PDO :calendar_id
	 */
	const listAllEventsFreebusy = "SELECT k1.* FROM kronolith_events k1 WHERE k1.calendar_id = :calendar_id{event_range};";

	/**
	 * @var string SELECT
	 * @param PDO :calendar_id, :event_uid
	 */
	const getEvent = "SELECT k1.*, k2.event_creator_id as organizer_uid, k2.event_attendees as organizer_attendees, k2.calendar_id as organizer_calendar FROM kronolith_events k1 LEFT JOIN kronolith_events k2	ON k1.event_uid = k2.event_uid AND char_length(k2.event_attendees) > 10 WHERE k1.calendar_id = :calendar_id AND k1.event_uid = :event_uid;";

	/**
	 * @var string SELECT
	 * @param REPLACE {fields_list}
	 * @param PDO :calendar_id, :event_uid
	 */
	const getListEvents = "SELECT {fields_list}, k2.event_creator_id as organizer_uid, k2.event_attendees as organizer_attendees, k2.calendar_id as organizer_calendar FROM kronolith_events k1 LEFT JOIN kronolith_events k2 ON k1.event_uid = k2.event_uid AND char_length(k2.event_attendees) > 10 WHERE {where_clause};";
	/**
	 * @var string SELECT
	 * @param REPLACE {fields_list}
	 * @param PDO :calendar_id, :event_uid
	 */
	const getOptiListEvents = "SELECT {fields_list} FROM kronolith_events k1 WHERE {where_clause};";

	/**
	 * @var string SELECT
	 * @param REPLACE {where_clause}
	 */
	const getCountEvents = "SELECT count(*) as events_count FROM kronolith_events k1 WHERE {where_clause};";

	/**
	 * @var string UPDATE
	 * @param REPLACE {event_set}
	 * @param PDO :calendar_id, :event_uid
	 */
	const updateEvent = "UPDATE kronolith_events SET {event_set} WHERE calendar_id = :calendar_id AND event_uid = :event_uid;";

	/**
	 * @var string UPDATE
	 * @param PDO :event_uid
	 */
	const updateMeetingEtag = "UPDATE kronolith_events SET event_modified = event_modified + 1,  event_modified_json = event_modified_json + 1 WHERE event_uid = :event_uid;";

	/**
	 * @var string INSERT
	 * @param REPLACE {data_fields}, {data_values}
	 */
	const insertEvent = "INSERT INTO kronolith_events ({data_fields}) VALUES ({data_values});";

	/**
	 * @var string DELETE
	 * @param PDO :calendar_id, :event_uid
	 */
	const deleteEvent = "DELETE FROM kronolith_events WHERE calendar_id = :calendar_id AND event_uid = :event_uid;";

	/**
	 * @var string SELECT
	 * @param :calendar_id
	 */
	const getCTag = "SELECT datatree_ctag as ctag FROM horde_datatree WHERE datatree_name = :calendar_id AND group_uid = 'horde.shares.kronolith'";
}
