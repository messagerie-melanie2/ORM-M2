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
namespace LibMelanie\Api\Ens\News;

use LibMelanie\Api\Defaut;

/**
 * Classe de gestion des partages pour les News
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Ens
 * @api
 * 
 * @property string $uid Identifiant unique du flux rss
 * @property string $title Titre donné au flux rss
 * @property string $url URL vers le flux rss
 * @property string $source Nom de la source
 * @property string $service Service associé au flux rss
 * @property string $creator Identifiant du créateur
 * @property boolean $publisher Est-ce que l'utilisateur est un publisher de cette news ?
 * 
 * @method bool load() Charge les données du hashtag depuis la base de données
 * @method bool exists() Est-ce que le hashtag existe dans la base de données ?
 * @method bool save() Enregistre le hashtag dans la base de données
 * @method bool delete() Supprime le hashtag de la base de données
 */
class Rss extends Defaut\News\Rss {}