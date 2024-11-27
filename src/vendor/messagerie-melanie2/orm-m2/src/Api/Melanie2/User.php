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
use LibMelanie\Api\Mel\Users\Outofoffice;
use LibMelanie\Api\Mel\Users\Share;

/**
 * Classe utilisateur pour Melanie2
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Melanie2
 * @api
 * 
 * @property string $dn DN de l'utilisateur dans l'annuaire            
 * @property string $uid Identifiant unique de l'utilisateur
 * @property string $fullname Nom complet de l'utilisateur
 * @property string $name Nom de l'utilisateur
 * @property string $email Adresse email principale de l'utilisateur
 * @property array $email_list Liste de toutes les adresses email de l'utilisateur
 * @property string $email_send Adresse email d'émission principale de l'utilisateur
 * @property array $email_send_list Liste de toutes les adresses email d'émission de l'utilisateur
 * @property string $password_need_change Est-ce que le mot de passe doit changer et pour quelle raison ? (Si la chaine n'est pas vide, le mot de passe doit changer)
 * @property Share[] $shares Liste des partages de la boite
 * @property string $away_response Message d'absence de l'utilisateur (TODO: Objet pour traiter la syntaxe)
 * @property string $internet_access_admin Accés Internet positionné par l'administrateur
 * @property string $internet_access_user Accés Internet positionné par l'utilisateur
 * @property string $use_photo_ader Photo utilisable sur le réseau ADER (RIE)
 * @property string $use_photo_intranet Photo utilisable sur le réseau Intranet
 * @property string $service Service de l'utilisateur dans l'annuaire Mélanie2
 * @property string $employee_number Champ RH
 * @property string $zone Zone de diffusion de l'utilisateur
 * @property string $street Adresse - Rue de l'utilisateur
 * @property string $postalcode Adresse - Code postal de l'utilisateur
 * @property string $locality Adresse - Ville de l'utilisateur
 * @property array $info Champ d'information de l'utilisateur
 * @property string $description Description de l'utilisateur
 * @property string $phonenumber Numéro de téléphone de l'utilisateur
 * @property string $faxnumber Numéro de fax de l'utilisateur
 * @property string $mobilephone Numéro de mobile de l'utilisateur
 * @property string $roomnumber Numéro de bureau de l'utilisateur
 * @property string $title Titre de l'utilisateur
 * @property string $business_category Catégorie professionnelle de l'utilisateur
 * @property string $vpn_profile Profil VPN de l'utilisateur
 * @property string $update_personnal_info Est-ce que l'utilisateur a le droit de mettre à jour ses informations personnelles
 * @property array $server_routage Champ de routage pour le serveur de message de l'utilisateur
 * @property-read string $server_host Host du serveur de messagerie de l'utilisateur
 * @property-read string $server_user User du serveur de messagerie de l'utilisateur
 * @property string $synchro_access_admin Accés synchronisation mobile positionné par l'administrateur
 * @property string $synchro_access_user Accés synchronisation mobile positionné par l'utilisateur
 * @property string $mission Mission de l'utilisateur
 * @property string $photo Photo de l'utilisateur
 * @property string $gender Genre de l'utilisateur
 * @property Outofoffice[] $outofoffices Tableau de gestionnaire d'absence pour l'utilisateur
 * 
 * @method string getTimezone() [OSOLETE] Chargement du timezone de l'utilisateur
 * @method bool authentification($password, $master = false) Authentification de l'utilisateur sur l'annuaire Mélanie2
 * @method bool save() Enregistrement de l'utilisateur dans l'annuaire
 * @method bool load() Charge les données de l'utilisateur depuis l'annuaire Mélanie2 (en fonction de l'uid ou l'email)
 * @method bool exists() Est-ce que l'utilisateur existe dans l'annuaire Mélanie2 (en fonction de l'uid ou l'email)
 */
class User extends Mel\User {}
