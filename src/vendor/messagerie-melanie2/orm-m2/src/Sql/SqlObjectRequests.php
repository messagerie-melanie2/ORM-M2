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
 * Requêtes SQL pour un objet de base de Melanie2
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage SQL
 *
 */
class SqlObjectRequests {
	/**
	 * Récupère un objet générique de Mélanie2
	 * @var string SELECT
	 * @param Replace {fields_list}, {table_name}, {where_clause}
	 */
	const getObject = "SELECT {fields_list} FROM {table_name}{where_clause};";

	/**
	 * Insertion d'un objet générique dans la table Mélanie2
	 * @var string INSERT
	 * @param REPLACE {table_name}, {data_fields}, {data_values}
	 */
	const insertObject = "INSERT INTO {table_name} ({data_fields}) VALUES ({data_values});";

	/**
	 * Mise à jour d'un objet Mélanie2
	 * @var string UPDATE
	 * @param REPLACE {table_name}, {object_set}, {where_clause}
	 */
	const updateObject = "UPDATE {table_name} SET {object_set} WHERE {where_clause};";

	/**
	 * Suppression d'un objet Mélanie2
	 * @var string DELETE
	 * @param REPLACE {table_name}, {where_clause}
	 */
	const deleteObject = "DELETE FROM {table_name} WHERE {where_clause};";
}