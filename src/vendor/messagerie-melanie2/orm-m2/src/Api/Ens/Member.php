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
namespace LibMelanie\Api\Ens;

/**
 * Classe member pour ENS
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Ens
 * @api
 * 
 * @property string $dn DN de l'utilisateur dans l'annuaire            
 * @property string $uid Identifiant unique de l'utilisateur
 * @property string $fullname Nom complet de l'utilisateur
 * @property string $name Nom de l'utilisateur
 * @property string $type Type de boite (voir Mce\Users\Type::*)
 * @property string $email Adresse email principale de l'utilisateur
 * @property array $email_list Liste de toutes les adresses email de l'utilisateur
 * @property string $street Adresse - Rue de l'utilisateur
 * @property string $postalcode Adresse - Code postal de l'utilisateur
 * @property string $locality Adresse - Ville de l'utilisateur
 * @property string $phonenumber Numéro de téléphone de l'utilisateur
 * @property string $faxnumber Numéro de fax de l'utilisateur
 * @property string $mobilephone Numéro de mobile de l'utilisateur
 * @property string $roomnumber Numéro de bureau de l'utilisateur
 * @property string $title Titre de l'utilisateur
 * @property array $business_category Catégories professionnelles de l'utilisateur
 * @property array $server_routage Champ de routage pour le serveur de message de l'utilisateur
 */
class Member extends User {}