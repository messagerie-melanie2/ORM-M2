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
namespace LibMelanie\Api\Mce;

use LibMelanie\Api\Defaut;

/**
 * Classe workspace par defaut
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Mce
 * @api
 * 
 * @property string $id Identifiant numérique du workspace
 * @property string $uid Identifiant unique du workspace
 * @property int $created Timestamp de creation du workspace
 * @property int $modified Timestamp de modification du workspace
 * @property string $creator Uid utilisateur du createur
 * @property string $title Titre du workspace
 * @property string $description Description du workspace
 * @property string $logo Logo du workspace
 * @property boolean $ispublic Est-ce que le workspace est public ?
 * @property boolean $isarchived Est-ce que le workspace est archivé ?
 * @property string $objects JSON des objets du workspace
 * @property string $links JSON des liens utiles du workspace
 * @property string $flux JSON des flux rss du workspace
 * @property string $settings JSON des paramètres du workspace
 * @property Workspaces\Share[] $shares Liste des partages du workspaces
 * @property string[] $hashtags Liste des hashtags du workspaces
 * 
 * @method bool load() Charge les données du workspace depuis la base de données
 * @method bool exists() Recherche si le workspace existe dans la base de données
 * @method bool save() Enregistre le workspace dans la base de données
 * @method bool delete() Supprime le workspace de la base de données
 */
class Workspace extends Defaut\Workspace {}