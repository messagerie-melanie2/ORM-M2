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
 * Liste des requêtes SQL vers l'historique
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage SQL
 *
 */
class SqlHistoryRequests {
	/**
	 * @var string SELECT
	 * Recupère la nextval de la séquence 'horde_histories_seq'
	 */
	const getNextHistory = "SELECT nextval('horde_histories_seq') as history_id;";

	/**
	 * @var string SELECT
	 * @param :object_uid, :history_action
	 */
	const getHistory = "SELECT * FROM horde_histories WHERE object_uid = :object_uid AND history_action = :history_action;";

	/**
	 * @var string INSERT
	 * @param REPLACE {data_fields}, {data_values}
	 */
	const insertHistory = "INSERT INTO horde_histories ({data_fields}) 	VALUES ({data_values});";

	/**
	 * @var string UPDATE
	 * @param REPLACE {event_set}
	 * @param :object_uid, :history_action
	 */
	const updateHistory = "UPDATE horde_histories SET {history_set} WHERE object_uid = :object_uid AND history_action = :history_action;";
}
