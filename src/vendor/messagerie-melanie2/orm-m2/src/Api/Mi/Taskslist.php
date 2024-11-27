<?php
/**
 * Ce fichier est développé pour la gestion de la lib MCE
 * 
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * 
 * ORM Mél Copyright © 2021 Groupe Messagerie/MTE
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace LibMelanie\Api\Mi;

use LibMelanie\Api\Defaut;

/**
 * Classe liste de tâches pour MI
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/MI
 * @api
 * 
 * @property string $id Identifiant unique de la liste de tâche
 * @property string $owner Identifiant du propriétaire de la liste de tâche
 * @property string $name Nom complet de la liste de tâche
 * @property int $perm Permission associée, utiliser asRight()
 * @property string $ctag CTag de la liste de tâche
 * @property int $synctoken SyncToken de la liste de tâche
 * @property-read string $caldavurl URL CalDAV pour la liste de tâches
 * @method bool load() Charge les données de la liste de tâche depuis la base de données
 * @method bool exists() Non implémentée
 * @method bool save() Non implémentée
 * @method bool delete() Non implémentée
 * @method void getCTag() Charge la propriété ctag avec l'identifiant de modification de la liste de tâche
 * @method void getTimezone() Charge la propriété timezone avec le timezone de la liste de tâche
 * @method bool asRight($action) Retourne un boolean pour savoir si les droits sont présents
 */
class Taskslist extends Defaut\Taskslist {}