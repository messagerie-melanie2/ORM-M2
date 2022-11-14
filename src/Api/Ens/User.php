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

use LibMelanie\Api\Defaut;
use LibMelanie\Api\Ens\Users\Outofoffice;
use LibMelanie\Api\Ens\Users\Share;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\MappingMce;
use LibMelanie\Config\Config;

/**
 * Classe utilisateur pour ENS
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
 * @property string $email_send Adresse email d'émission principale de l'utilisateur
 * @property array $email_send_list Liste de toutes les adresses email d'émission de l'utilisateur
 * @property string $password_need_change Est-ce que le mot de passe doit changer et pour quelle raison ? (Si la chaine n'est pas vide, le mot de passe doit changer)
 * 
 * @property Share[] $shares Liste des partages de la boite
 * @property-read array $supported_shares Liste des droits supportés par cette boite
 * 
 * @property boolean $internet_access_admin Accés Internet positionné par l'administrateur
 * @property boolean $internet_access_user Accés Internet positionné par l'utilisateur
 * @property-read boolean $internet_access_enable Est-ce que l'accès Internet de l'utilisateur est activé
 * 
 * @property string $use_photo_ader Photo utilisable sur le réseau ADER (RIE)
 * @property string $use_photo_intranet Photo utilisable sur le réseau Intranet
 * @property string $service Service de l'utilisateur dans l'annuaire
 * @property string $employee_number Champ RH
 * @property-read string $ministere Nom de ministère de l'utilisateur
 * @property string $street Adresse - Rue de l'utilisateur
 * @property string $postalcode Adresse - Code postal de l'utilisateur
 * @property string $locality Adresse - Ville de l'utilisateur
 * @property-read string $observation Information d'observation sur l'utilisateur
 * @property-read string $acces_internet_profil Profil d'accés internet pour l'utilisateur
 * @property-read string $acces_internet_timestamp Timestamp de mise en place du profil d'accés internet
 * @property string $description Description de l'utilisateur
 * @property string $phonenumber Numéro de téléphone de l'utilisateur
 * @property string $faxnumber Numéro de fax de l'utilisateur
 * @property string $mobilephone Numéro de mobile de l'utilisateur
 * @property string $roomnumber Numéro de bureau de l'utilisateur
 * @property string $title Titre de l'utilisateur
 * @property array $business_category Catégories professionnelles de l'utilisateur
 * @property-read string $vpn_profile_name Nom du profil VPN de l'utilisateur
 * @property string $update_personnal_info Est-ce que l'utilisateur a le droit de mettre à jour ses informations personnelles
 * 
 * @property array $server_routage Champ de routage pour le serveur de message de l'utilisateur
 * @property-read string $server_host Host du serveur de messagerie de l'utilisateur
 * @property-read string $server_user User du serveur de messagerie de l'utilisateur
 * 
 * @property array $mission Missions de l'utilisateur
 * @property-read string $photo_src Photo de l'utilisateur
 * @property string $gender Genre de l'utilisateur
 * 
 * @property string $liens_import Lien d'import dans l'annuaire
 * @property-read boolean $is_agriculture Est-ce que l'utilisateur appartient au MAA (calcul sur le liens import)
 * 
 * @property Outofoffice[] $outofoffices Tableau de gestionnaire d'absence pour l'utilisateur
 * 
 * @property-read boolean $is_objectshare Est-ce que cet utilisateur est en fait un objet de partage ?
 * @property-read ObjectShare $objectshare Retourne l'objet de partage lié à cet utilisateur si s'en est un
 * 
 * @property string $acces_synchro_admin_profil Profil de synchronisation positionné par l'administrateur (STANDARD ou SENSIBLE)
 * @property string|DateTime $acces_synchro_admin_datetime Date de mise en place par l'administrateur de la synchronisation (format YmdHisZ)
 * @property string $acces_synchro_user_profil Profil de synchronisation positionné accepté par l'utilisateur (STANDARD ou SENSIBLE)
 * @property string|DateTime $acces_synchro_user_datetime Date d'acceptation de l'utilisateur pour la synchronisation (format YmdHisZ)
 * @property-read boolean $is_synchronisation_enable Est-ce que la synchronisation est activée pour l'utilisateur ?
 * @property-read string $synchronisation_profile Profil de synchronisation positionné pour l'utilisateur (STANDARD ou SENSIBLE)
 * 
 * @property-read boolean $has_bureautique Est-ce que cet utilisateur a un compte bureautique associé ?
 * 
 * @property-read boolean $is_individuelle Est-ce qu'il s'agit d'une boite individuelle ?
 * @property-read boolean $is_partagee Est-ce qu'il s'agit d'une boite partagée ?
 * @property-read boolean $is_fonctionnelle Est-ce qu'il s'agit d'une boite fonctionnelle ?
 * @property-read boolean $is_ressource Est-ce qu'il s'agit d'une boite de ressources ?
 * @property-read boolean $is_unite Est-ce qu'il s'agit d'une boite d'unité ?
 * @property-read boolean $is_service Est-ce qu'il s'agit d'une boite de service ?
 * @property-read boolean $is_personne Est-ce qu'il s'agit d'une boite personne ?
 * @property-read boolean $is_applicative Est-ce qu'il s'agit d'une boite applicative ?
 * @property-read boolean $is_list Est-ce qu'il s'agit d'une liste ?
 * @property-read boolean $is_listab Est-ce qu'il s'agit d'une list a abonnement ?
 * 
 * @method string getTimezone() [OSOLETE] Chargement du timezone de l'utilisateur
 * @method bool save() Enregistrement de l'utilisateur dans l'annuaire
 */
class User extends Defaut\User {

  // **** Configuration des filtres et des attributs par défaut
  /**
   * Filtre pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_FILTER = "(uid=%%uid%%)";
  /**
   * Filtre pour la méthode load() avec un email
   * 
   * @ignore
   */
  const LOAD_FROM_EMAIL_FILTER = "(mailroutingaddress=%%email%%)";
  /**
   * Attributs par défauts pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_ATTRIBUTES = ['fullname', 'uid', 'name', 'email', 'email_list', 'email_send', 'email_send_list', 'type'];

  /**
   * Configuration du mapping qui surcharge la conf
   */
  const MAPPING = [
    "dn"                      => 'dn',                            // DN de l'utilisateur
    "uid"                     => 'uid',                           // Identifiant de l'utilisateur
    "fullname"                => 'cn',                            // Nom complet de l'utilisateur
    "name"                    => 'displayname',                   // Display name de l'utilisateur
    "lastname"                => 'sn',                            // Last name de l'utilisateur
    "firstname"               => 'givenname',                     // First name de l'utilisateur
    "email"                   => 'mailroutingaddress',                        // Adresse e-mail principale de l'utilisateur en reception
    "email_list"              => [MappingMce::name => 'mailroutingaddress', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en reception pour l'utilisateur
    "email_send"              => 'mailroutingaddress',        // Adresse e-mail principale de l'utilisateur en emission
    "email_send_list"         => [MappingMce::name => 'mailroutingaddress', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en émission pour l'utilisateur
    "service"                 => 'supannaffectation',              // Department Number
    "type"                    => 'supannentiteaffectationprincipale',               // Type d'entrée (boite individuelle, partagée, ressource, ...)
    "outofoffices"            => [MappingMce::name => 'enslpersonm2absence', MappingMce::type => MappingMce::arrayLdap], // Affichage du message d'absence de l'utilisateur
  ];

  /**
   * ***************************************************
   * DATA MAPPING
   */
  /**
   * Mapping uid field
   *
   * @param string $uid
   */
  protected function setMapUid($uid) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapUid(" . (is_string($uid) ? $uid : "") . ")");
    if (!isset($this->objectmelanie)) {
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    }
    if (strpos($uid, 'uid=') === 0) {
      // C'est un dn utilisateur
      $this->objectmelanie->dn = $uid;
    }
    else if (strpos($uid, '@') !== false) {
      // C'est une adresse e-mail
      $this->objectmelanie->email = $uid;
    }
    else {
      $this->objectmelanie->uid = $uid;
    }
  }

  /**
   * Mapping shares field
   * 
   * @return array Liste des partages supportés par cette boite ([Share::TYPE_*])
   */
  protected function getMapSupported_shares() {
    return [Share::TYPE_ADMIN];
  }

  /**
   * Mapping shares field
   * 
   * @param array $supported_shares Liste des partages supportés par cette boite ([Share::TYPE_*])
   * 
   * @return boolean false non supporté
   */
  protected function setMapSupported_shares($supported_shares) {
    return false;
  }

  /**
   * Récupération du champ out of offices
   * 
   * @return Outofoffice[] Tableau de d'objets Outofoffice
   */
  protected function getMapOutofoffices() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapOutofoffices()");
    $objects = [];
    if (is_array($this->objectmelanie->outofoffices)) {
      $i = 0;
      foreach ($this->objectmelanie->outofoffices as $oof) {
        $object = new Outofoffice($oof);
        if (isset($object->days)) {
          $key = Outofoffice::HEBDO.$i++;
        }
        else {
          $key = $object->type;
        }
        $objects[$key] = $object;
      }
    }
    return $objects;
	}

  /**
   * Positionnement du champ out of offices
   * 
   * @param Outofoffice[] $OofObjects
   */
  protected function setMapOutofoffices($OofObjects) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapOutofoffices()");
    $reponses = [];
    if (is_array($OofObjects)) {
      foreach ($OofObjects as $OofObject) {
        $reponses[] = $OofObject->render();
      }
    }
    $this->objectmelanie->outofoffices = array_unique($reponses);
	}
}
