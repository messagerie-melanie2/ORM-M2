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
 * Liste des requêtes SQL vers les tâches
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage SQL
 *
 */
class SqlTaskRequests {
	/**
	 * @var string SELECT
	 * @param :task_owner
	 */
	const listAllTasks = "SELECT * FROM nag_tasks WHERE task_owner = :task_owner;";

	/**
	 * @var string SELECT
	 * @param :task_owner
	 */
	const getCTag = "SELECT datatree_ctag as ctag FROM horde_datatree WHERE datatree_name = :task_owner AND group_uid = 'horde.shares.nag'";
}
