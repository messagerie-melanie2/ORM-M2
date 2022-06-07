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
namespace LibMelanie\Api\Dgfip;

use LibMelanie\Api\Mce;

/**
 * Classe groupe LDAP pour DGFIP
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/DGFIP
 * @api
 * 
 * @property string $dn DN du groupe l'annuaire 
 * @property string $fullname Nom complet du groupe LDAP
 * @property string $type Type de groupe (voir Mce\Users\Type::*)
 * @property string $email Adresse email principale de l'utilisateur
 * @property array $email_list Liste de toutes les adresses email de l'utilisateur
 * @property User[] $members Liste des membres appartenant au groupe
 * @property array $members_email Liste des adresses e-mail de la liste
 * @property array $owners Liste des propriétaires du groupe LDAP
 * @property string $service Service du groupe dans l'annuaire
 * @property-read boolean $is_dynamic Est-ce qu'il s'agit d'une liste dynamique ?
 */
class Group extends Mce\Group {}