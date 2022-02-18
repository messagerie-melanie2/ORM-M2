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
 * Liste des requêtes SQL pour les propriétés des objets Melanie2
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage SQL
 *
 */
class SqlObjectPropertyRequests {
	/**
	 * @var string INSERT
	 * @param PDO :datatree_id, :attribute_name, :attribute_key, :attribute_value
	 */
	const insertProperty = "INSERT INTO horde_datatree_attributes (datatree_id, attribute_name, attribute_key, attribute_value) VALUES (:datatree_id, :attribute_name, :attribute_key, :attribute_value);";

	/**
	 * Mise à jour d'une propriété Mélanie2
	 * 
	 * @var string UPDATE
	 * @param PDO :datatree_id, :attribute_value, :attribute_name
	 */
	const updateProperty = "UPDATE horde_datatree_attributes SET attribute_value = :attribute_value WHERE datatree_id = :datatree_id AND attribute_name = :attribute_name;";

	/**
	 * Suppression d'une propriété Mélanie2
	 * 
	 * @var string DELETE
	 * @param Replace {where_clause}
	 */
	const deleteProperty = "DELETE FROM horde_datatree_attributes WHERE {where_clause};";
}
