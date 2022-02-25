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
 * Liste des requêtes SQL Generique Melanie2
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage SQL
 *
 */
class SqlMelanieRequests {
	/**
	 * @var string SELECT
	 * @param Replace {datatree_id}, {user_uid}, {datatree_name}, {attribute_value}, {perm_object}
	 * @param PDO :group_uid, :user_uid, :attribute_name
	 */
	const listUserObjects = "SELECT hd.datatree_id as {datatree_id}, user_uid as {user_uid}, datatree_name as {datatree_name}, datatree_ctag as {datatree_ctag}, datatree_synctoken as {datatree_synctoken}, attribute_value as {attribute_value}, '30' as {perm_object} FROM horde_datatree hd INNER JOIN horde_datatree_attributes USING (datatree_id) WHERE group_uid = :group_uid AND user_uid = :user_uid AND attribute_name = :attribute_name;";

	/**
	 * @var string SELECT
	 * @param Replace {datatree_id}, {user_uid}, {datatree_name}, {attribute_value}, {perm_object}
	 * @param :user_uid, :pref_scope, :pref_name, :group_uid, :attribute_name, :attribute_perm, :attribute_permfg
	 */
	const getDefaultObject = "SELECT hd.datatree_id as {datatree_id}, hd.user_uid as {user_uid}, hd.datatree_name as {datatree_name}, hd.datatree_ctag as {datatree_ctag}, hd.datatree_synctoken as {datatree_synctoken}, hda2.attribute_value as {attribute_value}, hda1.attribute_value as {perm_object} FROM horde_prefs hp INNER JOIN horde_datatree hd ON hp.pref_value = hd.datatree_name INNER JOIN horde_datatree_attributes hda1 ON hd.datatree_id = hda1.datatree_id INNER JOIN horde_datatree_attributes hda2 ON (hd.datatree_id = hda2.datatree_id) WHERE (hda1.attribute_name = :attribute_perm OR hda1.attribute_name = :attribute_permfg) AND hda1.attribute_key = :user_uid AND hd.group_uid = :group_uid AND hda2.attribute_name = :attribute_name AND hp.pref_scope = :pref_scope AND hp.pref_name = :pref_name AND hp.pref_uid = :user_uid LIMIT 1;";

	/**
	 * @var string SELECT
	 * @param Replace {datatree_id}, {user_uid}, {datatree_name}, {attribute_value}, {perm_object}
	 * @param PDO :group_uid, :user_uid, :attribute_name, :attribute_perm, :attribute_permfg
	 */
	const listSharedObjects = "SELECT hd1.datatree_id as {datatree_id}, hd1.user_uid as {user_uid}, hd1.datatree_name as {datatree_name}, hd1.datatree_ctag as {datatree_ctag}, hd1.datatree_synctoken as {datatree_synctoken}, hda2.attribute_value as {attribute_value}, hda1.attribute_value as {perm_object} FROM horde_datatree hd1 INNER JOIN horde_datatree_attributes hda1 ON hd1.datatree_id = hda1.datatree_id INNER JOIN horde_datatree_attributes hda2 ON (hd1.datatree_id = hda2.datatree_id) WHERE (hda1.attribute_name = :attribute_perm OR hda1.attribute_name = :attribute_permfg) AND hda1.attribute_key = :user_uid AND hd1.group_uid = :group_uid AND hda2.attribute_name = :attribute_name;";

	/**
	 * @var string SELECT
	 * @param Replace {datatree_id}, {user_uid}, {datatree_name}, {attribute_value}, {perm_object}
	 * @param PDO :group_uid, :datatree_name, :attribute_name, :attribute_perm, :attribute_permfg
	 */
	const listObjectsByUid = "SELECT hd1.datatree_id as {datatree_id}, hd1.user_uid as {user_uid}, hd1.datatree_name as {datatree_name}, hd1.datatree_ctag as {datatree_ctag}, hd1.datatree_synctoken as {datatree_synctoken}, hda2.attribute_value as {attribute_value}, hda1.attribute_value as {perm_object} FROM horde_datatree hd1 INNER JOIN horde_datatree_attributes hda1 ON hd1.datatree_id = hda1.datatree_id INNER JOIN horde_datatree_attributes hda2 ON (hd1.datatree_id = hda2.datatree_id) WHERE (hda1.attribute_name = :attribute_perm OR hda1.attribute_name = :attribute_permfg) AND hd1.group_uid = :group_uid AND hda2.attribute_name = :attribute_name AND hd1.datatree_name = :datatree_name";

	/**
	 * @var string SELECT
	 * @param Replace {datatree_id}, {user_uid}, {datatree_name}, {attribute_value}, {perm_object}
	 * @param PDO :group_uid, :user_uid, :datatree_name, :attribute_name, :attribute_perm, :attribute_permfg
	 */
	const listObjectsByUidAndUser = "SELECT hd1.datatree_id as {datatree_id}, hd1.user_uid as {user_uid}, hd1.datatree_name as {datatree_name}, hd1.datatree_ctag as {datatree_ctag}, hd1.datatree_synctoken as {datatree_synctoken}, hda2.attribute_value as {attribute_value}, hda1.attribute_value as {perm_object} FROM horde_datatree hd1 INNER JOIN horde_datatree_attributes hda1 ON hd1.datatree_id = hda1.datatree_id INNER JOIN horde_datatree_attributes hda2 ON (hd1.datatree_id = hda2.datatree_id) WHERE (hda1.attribute_name = :attribute_perm OR hda1.attribute_name = :attribute_permfg) AND hda1.attribute_key = :user_uid AND hd1.group_uid = :group_uid AND hda2.attribute_name = :attribute_name AND hd1.datatree_name = :datatree_name";

	/**
	 * @var string SELECT
	 * @param Replace {pref_name}
	 * @param PDO :user_uid, :pref_scope, :pref_name
	 */
	const getUserPref = "SELECT pref_value as {pref_name} FROM horde_prefs WHERE pref_uid = :user_uid AND pref_scope = :pref_scope AND pref_name = :pref_name LIMIT 1;";

	/**
	 * @var string SELECT
	 * Recupère la nextval de la séquence 'horde_datatree_seq'
	 */
	const getNextObject = "SELECT nextval('horde_datatree_seq') as datatree_id;";

	/**
	 * @var string INSERT
	 * @param PDO :datatree_id, :group_uid, :user_uid, :datatree_name
	 */
	const insertObject = "INSERT INTO horde_datatree (datatree_id, group_uid, user_uid, datatree_name, datatree_ctag, datatree_parents) VALUES (:datatree_id, :group_uid, :user_uid, :datatree_name, :datatree_ctag, '');";

	/**
	 * Suppression d'un objet Mélanie2
	 * @var string DELETE
	 * @param Replace {objects_table}, {object_id}
	 * @param PDO :datatree_id
	 */
	const deleteObject1 = "DELETE FROM horde_datatree_attributes WHERE datatree_id = :datatree_id;";
	const deleteObject2 = "DELETE FROM horde_datatree WHERE datatree_id = :datatree_id;";
	const deleteObject3 = "DELETE FROM {objects_table} WHERE {datatree_name} = :datatree_name;";
	const deleteObject4 = "DELETE FROM horde_histories WHERE object_uid LIKE :object_uid;";
}
