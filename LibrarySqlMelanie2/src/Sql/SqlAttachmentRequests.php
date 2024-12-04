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
 * Liste des requêtes SQL vers les pièces jointes
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage SQL
 *
 */
class SqlAttachmentRequests {
	/**
	 * @var string SELECT
	 * Recupère la nextval de la séquence 'horde_vfs_seq'
	 */
	const getNextAttachment = "SELECT nextval('horde_vfs_seq') as vfs_id;";

	/**
	 * @var string SELECT
	 * @param Replace {fields_list}, {where_clause}
	 */
	const getAttachmentsList = "SELECT {fields_list} FROM horde_vfs WHERE {where_clause};";

	/**
	 * @var string SELECT
	 * @param Replace {where_clause}
	 */
	const getAttachmentData = "SELECT vfs_id, vfs_name, vfs_type, vfs_modified, vfs_owner, vfs_path, vfs_data FROM horde_vfs WHERE {where_clause};";

	/**
	 * @var string INSERT
	 * @param Replace {data_fields}, {data_values}
	 */
	const insertAttachment = "INSERT INTO horde_vfs ({data_fields}) VALUES ({data_values});";

	/**
	 * @var string UPDATE
	 * @param Replace {attachment_set}, {where_clause}
	 */
	const updateAttachment = "UPDATE horde_vfs SET {attachment_set} WHERE {where_clause};";

	/**
	 * Suppression d'un objet Mélanie2
	 * 
	 * @var string DELETE
	 * @param Replace {where_clause}
	 */
	const deleteAttachment = "DELETE FROM horde_vfs WHERE {where_clause};";
}
