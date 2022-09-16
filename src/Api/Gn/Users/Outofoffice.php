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
namespace LibMelanie\Api\Gn\Users;

use LibMelanie\Api\Mce;

/**
 * Classe utilisateur pour GN
 * utilisation de la syntaxe Mél
 * pour la gestion du gestionnaire d'absence
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/GN
 * @api
 * 
 * @property \Datetime $start Date de début de l'absence
 * @property \Datetime $end Date de fin de l'absence
 * @property boolean $enable Est-ce que l'absence est active
 * @property string $message Message d'absence a afficher
 * @property int $order Ordre de tri du message d'absence
 * @property Outofoffice::TYPE_* $type Type d'absence (Interne, Externe)
 */
class Outofoffice extends Mce\Users\Outofoffice {}