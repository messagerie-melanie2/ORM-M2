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
namespace LibMelanie\Api\Melanie2;

use LibMelanie\Api\Mel;

/**
 * Classe attendee pour les évènements pour Melanie2
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Melanie2
 * @api
 * 
 * @property string $email Email du participant
 * @property string $name Nom du participant
 * @property string $uid Uid du participant
 * @property boolean $self_invite Est-ce que ce participant s'est lui même invité
 * @property-read boolean $need_action Est-ce que le mode En attente est activé pour ce participant
 * @property Attendee::RESPONSE_* $response Réponse du participant
 * @property Attendee::ROLE_* $role Role du participant
 */
class Attendee extends Mel\Attendee {}