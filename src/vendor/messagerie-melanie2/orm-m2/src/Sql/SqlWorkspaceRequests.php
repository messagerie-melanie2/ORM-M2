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
 * Liste des requêtes SQL vers les workspaces
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage SQL
 *
 */
class SqlWorkspaceRequests {
	/**
	 * @var string SELECT
	 * @param REPLACE {order_by}, {limit}
	 */
	const listPublicsWorkspaces = "SELECT dwp_workspaces.* FROM dwp_workspaces WHERE workspace_ispublic = 1{order_by}{limit};";

	/**
	 * @var string SELECT
	 * @param REPLACE {order_by}, {limit}
	 * @param PDO :user_id
	 */
	const listUserWorkspaces = "SELECT dwp_workspaces.* FROM dwp_workspaces INNER JOIN dwp_shares USING (workspace_id) WHERE user_uid = :user_uid AND rights = 'o'{order_by}{limit};";

	/**
	 * @var string SELECT
	 * @param REPLACE {order_by}, {limit}
	 * @param PDO :user_id
	 */
	const listSharedWorkspaces = "SELECT dwp_workspaces.* FROM dwp_workspaces INNER JOIN dwp_shares USING (workspace_id) WHERE user_uid = :user_uid{order_by}{limit};";

	/**
	 * @var string SELECT
	 * @param REPLACE {order_by}, {limit}
	 * @param PDO :hashtag
	 */
	const listWorkspacesByHashtag = "SELECT dwp_workspaces.* FROM dwp_workspaces INNER JOIN dwp_hashtags_workspaces USING (workspace_id) INNER JOIN dwp_hashtags USING (hashtag_id) WHERE hashtag = :hashtag{order_by}{limit};";

	/**
	 * @var string SELECT
	 * @param REPLACE {order_by}, {limit}
	 * @param PDO :workspace_id
	 */
	const listWorkspaceHashtags = "SELECT dwp_hashtags.* FROM dwp_hashtags INNER JOIN dwp_hashtags_workspaces USING (hashtag_id) WHERE workspace_id = :workspace_id{order_by}{limit};";

	/**
	 * @var string SELECT
	 * @param REPLACE {order_by}, {limit}
	 * @param PDO :workspace_id
	 */
	const listWorkspaceShares = "SELECT dwp_shares.* FROM dwp_shares WHERE workspace_id = :workspace_id{order_by}{limit};";
}
