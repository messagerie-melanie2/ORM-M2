<?php
/**
 * Ce fichier est développé pour la gestion de la librairie MCE
 * 
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * 
 * ORM Mél Copyright © 2020 Groupe Messagerie/MTES
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
namespace LibMelanie\Api\Mel;

use LibMelanie\Api\Defaut;
use LibMelanie\Api\Mce\Users\Outofoffice;
use LibMelanie\Api\Mce\Users\Share;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\MappingMce;

/**
 * Classe utilisateur pour Mel
 * 
 * @author Groupe Messagerie/MTES - Apitech
 * @package Librairie MCE
 * @subpackage API MCE
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
 * @property Share[] $shares Liste des partages de la boite
 * @property-read array $supported_shares Liste des droits supportés par cette boite
 * @property string $away_response Message d'absence de l'utilisateur (TODO: Objet pour traiter la syntaxe)
 * @property integer $internet_access_admin Accés Internet positionné par l'administrateur
 * @property integer $internet_access_user Accés Internet positionné par l'utilisateur
 * @property-read boolean $internet_access_enable Est-ce que l'accès Internet de l'utilisateur est activé
 * @property string $use_photo_ader Photo utilisable sur le réseau ADER (RIE)
 * @property string $use_photo_intranet Photo utilisable sur le réseau Intranet
 * @property string $service Service de l'utilisateur dans l'annuaire Mélanie2
 * @property string $employee_number Champ RH
 * @property string $zone Zone de diffusion de l'utilisateur
 * @property-read string $ministere Nom de ministère de l'utilisateur
 * @property string $street Adresse - Rue de l'utilisateur
 * @property string $postalcode Adresse - Code postal de l'utilisateur
 * @property string $locality Adresse - Ville de l'utilisateur
 * @property array $info Champ d'information de l'utilisateur
 * @property-read string $observation Information d'observation sur l'utilisateur
 * @property-read string $access_internet_profil Profil d'accés internet pour l'utilisateur
 * @property-read string $access_internet_timestamp Timestamp de mise en place du profil d'accés internet
 * @property string $description Description de l'utilisateur
 * @property string $phonenumber Numéro de téléphone de l'utilisateur
 * @property string $faxnumber Numéro de fax de l'utilisateur
 * @property string $mobilephone Numéro de mobile de l'utilisateur
 * @property string $roomnumber Numéro de bureau de l'utilisateur
 * @property string $title Titre de l'utilisateur
 * @property array $business_category Catégories professionnelles de l'utilisateur
 * @property string $vpn_profile Profil VPN de l'utilisateur
 * @property-read string $vpn_profile_name Nom du profil VPN de l'utilisateur
 * @property string $update_personnal_info Est-ce que l'utilisateur a le droit de mettre à jour ses informations personnelles
 * @property array $server_routage Champ de routage pour le serveur de message de l'utilisateur
 * @property-read string $server_host Host du serveur de messagerie de l'utilisateur
 * @property-read string $server_user User du serveur de messagerie de l'utilisateur
 * @property string $synchro_access_admin Accés synchronisation mobile positionné par l'administrateur
 * @property string $synchro_access_user Accés synchronisation mobile positionné par l'utilisateur
 * @property array $mission Missions de l'utilisateur
 * @property string $photo Photo de l'utilisateur
 * @property string $gender Genre de l'utilisateur
 * @property string $liens_import Lien d'import dans l'annuaire
 * @property Outofoffice[] $outofoffices Tableau de gestionnaire d'absence pour l'utilisateur
 * @property-read boolean $is_objectshare Est-ce que cet utilisateur est en fait un objet de partage
 * @property-read ObjectShare $objectshare Retourne l'objet de partage lié à cet utilisateur si s'en est un
 * 
 * @method string getTimezone() [OSOLETE] Chargement du timezone de l'utilisateur
 * @method bool authentification($password, $master = false) Authentification de l'utilisateur sur l'annuaire Mélanie2
 * @method bool save() Enregistrement de l'utilisateur dans l'annuaire
 * @method bool load() Charge les données de l'utilisateur depuis l'annuaire Mélanie2 (en fonction de l'uid ou l'email)
 * @method bool exists() Est-ce que l'utilisateur existe dans l'annuaire Mélanie2 (en fonction de l'uid ou l'email)
 */
class User extends Defaut\User {
	/**
	 * Configuration du délimiteur pour le server host
	 * 
	 * @var string
	 */
  const SERVER_HOST_DELIMITER = '%';

  // **** Configuration des filtres et des attributs par défaut
  /**
   * Filtre pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_FILTER = "(&(uid=%%username%%)(mineqTypeentree=*))";
  /**
   * Filtre pour la méthode load() avec un email
   * 
   * @ignore
   */
  const LOAD_FROM_EMAIL_FILTER = "(&(mineqmelmailemission=%%email%%)(objectClass=mineqMelBoite))";
  /**
   * Attributs par défauts pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_ATTRIBUTES = ['cn', 'mail', 'mailpr', 'displayname', 'mineqmelmailemission', 'mineqmelmailemissionpr', 'uid', 'departmentnumber', 'info', 'mineqmelroutage', 'mineqmelaccesinterneta', 'mineqmelaccesinternetu', 'mineqmelpartages', 'mineqtypeentree', 'mineqliensimport'];
  /**
   * Filtre pour la méthode getBalp()
   * 
   * @ignore
   */
  const GET_BALP_FILTER = "(uid=%%username%%.-.*)";
  /**
   * Attributs par défauts pour la méthode getBalp()
   * 
   * @ignore
   */
  const GET_BALP_ATTRIBUTES = ['cn', 'mineqmelmailemission', 'mineqmelmailemissionpr', 'uid', 'mineqmelpartages', 'mineqmelaccesinterneta', 'mineqmelaccesinternetu'];
  /**
   * Filtre pour la méthode getBalpEmission()
   * 
   * @ignore
   */
  const GET_BALP_EMISSION_FILTER = "(|(mineqmelpartages=%%username%%:C)(mineqmelpartages=%%username%%:G))";
  /**
   * Attributs par défauts pour la méthode getBalpEmission()
   * 
   * @ignore
   */
  const GET_BALP_EMISSION_ATTRIBUTES = ['cn', 'mineqmelmailemission', 'mineqmelmailemissionpr', 'uid', 'mineqmelpartages'];
  /**
   * Filtre pour la méthode getBalpGestionnaire()
   * 
   * @ignore
   */
  const GET_BALP_GESTIONNAIRE_FILTER = "(mineqmelpartages=%%username%%:G)";
  /**
   * Attributs par défauts pour la méthode getBalpGestionnaire()
   * 
   * @ignore
   */
  const GET_BALP_GESTIONNAIRE_ATTRIBUTES = ['cn', 'mineqmelmailemission', 'mineqmelmailemissionpr', 'uid', 'mineqmelpartages'];

  /**
   * Configuration du mapping qui surcharge la conf
   */
  const MAPPING = [
    "dn"                      => 'dn',                            // DN de l'utilisateur
    "uid"                     => 'uid',                           // Identifiant de l'utilisateur
    "fullname"                => 'cn',                            // Nom complet de l'utilisateur
    "name"                    => 'displayname',                   // Display name de l'utilisateur
    "email"                   => 'mailpr',                        // Adresse e-mail principale de l'utilisateur en reception
    "email_list"              => [MappingMce::name => 'mail', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en reception pour l'utilisateur
    "email_send"              => 'mineqmelmailemissionpr',        // Adresse e-mail principale de l'utilisateur en emission
    "email_send_list"         => [MappingMce::name => 'mineqmelmailemission', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en émission pour l'utilisateur
    "service"                 => 'departmentnumber',              // Department Number
    "password_need_change"    => 'mineqpassworddoitchanger',      // Message pour indiquer que le mot de passe de l'utilisateur doit changer
    "shares"                  => [MappingMce::name => 'mineqmelpartages', MappingMce::type => MappingMce::arrayLdap], // Liste des partages pour cette boite
    "server_routage"          => [MappingMce::name => 'mineqmelroutage', MappingMce::type => MappingMce::arrayLdap], // Champ utilisé pour le routage des messages
    "away_response"           => [MappingMce::name => 'mineqmelreponse', MappingMce::type => MappingMce::arrayLdap], // Affichage du message d'absence de l'utilisateur
    "type"                    => 'mineqtypeentree',               // Type d'entrée (boite individuelle, partagée, ressource, ...)
    "internet_access_admin"   => 'mineqmelaccesinterneta',        // Droit d'accès depuis internet donné par l'administrateur
    "internet_access_user"    => 'mineqmelaccesinternetu',        // Droit d'accès depuis internet accepté par l'utilisateur
    "use_photo_ader"          => 'mineqpublicationphotoader',     // Flag publier la photo de l'utilisateur sur Ader
    "use_photo_intranet"      => 'mineqpublicationphotointranet', // Flag publier la photo de l'utilisateur sur l'intranet
    "employee_number"         => 'employeenumber',                // Matricule de l'utilisateur
    "zone"                    => 'mineqzone',                     // Zone de l'utilisateur
    "street"                  => 'street',                        // Rue
    "postalcode"              => 'postalcode',                    // Code postal
    "locality"                => 'l',                             // Ville
    "info"                    => [MappingMce::name => 'info', MappingMce::type => MappingMce::arrayLdap], // Informations
    "description"             => 'description',                   // Description
    "phonenumber"             => 'telephonenumber',               // Numéro de téléphone
    "faxnumber"               => 'facsimiletelephonenumber',      // Numéro de fax
    "mobilephone"             => 'mobile',                        // Numéro de mobile
    "roomnumber"              => 'roomnumber',                    // Numéro de bureau
    "title"                   => 'title',                         // Titre
    "business_category"       => [MappingMce::name => 'businesscategory', MappingMce::type => MappingMce::arrayLdap], // Cetégorie
    "vpn_profile"             => 'mineqvpnprofil',                // Profil de droits VPN
    "samba_sid"               => 'sambasid',                      // SID Samba
    "update_personnal_info"   => 'mineqmajinfoperso',             // Droit pour l'utilisateur de modifier ses infos perso dans l'annuaire
    "remise"                  => 'mineqmelremise',                // Remise
    "synchro_access_admin"    => 'mineqmelaccessynchroa',         // Droit d'accès synchro mobile donné par admin
    "synchro_access_user"     => 'mineqmelaccessynchrou',         // Droit d'accès synchro mobile accepté par user
    "mission"                 => [MappingMce::name => 'mineqmission', MappingMce::type => MappingMce::arrayLdap], // Mission
    "photo"                   => 'jpegphoto',                     // Photo de l'utilisateur
    "gender"                  => 'gender',                        // Genre
    "liens_import"            => 'mineqliensimport'               // Lien d'import autres annuaires
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
    if (strpos($uid, '@') === false) {
      $this->objectmelanie->uid = $uid;
    }
    else {
      $this->objectmelanie->email = $uid;
    }
  }

  /**
   * Récupération du champ internet_access_enable
   * 
   * @return boolean true si l'access internet de l'utilisateur est activé, false sinon
   */
  protected function getMapInternet_access_enable() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapInternet_access_enable()");
    return isset($this->internet_access_admin) && $this->internet_access_admin == 1 
        && isset($this->internet_access_user) && $this->internet_access_user == 1;
  }

  /**
   * Récupération du champ server_host
   * 
   * @return mixed|NULL Valeur du serveur host, null si non trouvé
   */
  protected function getMapServer_host() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapServer_host()");
    foreach ($this->server_routage as $route) {
			if (strpos($route, self::SERVER_HOST_DELIMITER) !== false) {
				$route = explode('@', $route, 2);
				return $route[1];
			}
    }
    return null;
  }

  /**
   * Récupération du champ server_user
   * 
   * @return mixed|NULL Valeur du serveur user, null si non trouvé
   */
  protected function getMapServer_user() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapServer_user()");
    foreach ($this->server_routage as $route) {
			if (strpos($route, self::SERVER_HOST_DELIMITER) !== false) {
				$route = explode('@', $route, 2);
				return $route[0];
			}
    }
    return null;
  }

  /**
   * Mapping shares field
   *
   * @param Share[] $shares
   */
  protected function setMapShares($shares) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapShares()");
    if (!isset($this->objectmelanie)) {
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    }
    $this->_shares = $shares;
    $_shares = [];
    foreach ($shares as $share) {
      if (strpos($this->liens_import, 'AGRI.Lien:') !== false && $share->type == Share::TYPE_ADMIN) {
        // Pas de droit gestionnaire pour les imports Agri
        continue;
      }
      $right = '';
      switch ($share->type) {
        case Share::TYPE_ADMIN:
          $right = 'G';
          break;
        case Share::TYPE_SEND:
          $right = 'C';
          break;
        case Share::TYPE_WRITE:
          $right = 'E';
          break;
        case Share::TYPE_READ:
          $right = 'L';
          break;
      }
      $_shares[] = $share->user . ':' . $right;
    }
    $this->objectmelanie->shares = $_shares;
  }

  /**
   * Mapping shares field
   * 
   * @return boolean true si l'access internet de l'utilisateur est activé, false sinon
   */
  protected function getMapShares() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapShares()");
    if (!isset($this->_shares)) {
      $_shares = $this->objectmelanie->shares;
      $this->_shares = [];
      foreach ($_shares as $_share) {
        $share = new Share();
        list($share->user, $right) = \explode(':', $_share, 2);
        switch (\strtoupper($right)) {
          case 'G':
            $share->type = Share::TYPE_ADMIN;
            break;
          case 'C':
            $share->type = Share::TYPE_SEND;
            break;
          case 'E':
            $share->type = Share::TYPE_WRITE;
            break;
          case 'L':
            $share->type = Share::TYPE_READ;
            break;
        }
        $this->_shares[$share->user] = $share;
      }
    }
    return $this->_shares;
  }

  /**
   * Mapping shares field
   * 
   * @return array Liste des partages supportés par cette boite ([Share::TYPE_*])
   */
  protected function getMapSupported_shares() {
    if (strpos($this->objectmelanie->liens_import, 'AGRI.Lien:')) {
      $supported_shares = [Share::TYPE_SEND, Share::TYPE_WRITE, Share::TYPE_READ];
    }
    else {
      $supported_shares = [Share::TYPE_ADMIN, Share::TYPE_SEND, Share::TYPE_WRITE, Share::TYPE_READ];
    }
    return $supported_shares;
  }

  /**
   * Mapping shares field
   * 
   * @return boolean false non supporté
   */
  protected function setMapSupported_shares() {
    return false;
  }

  /**
   * Mapping ministere field
   * 
   * @return string Nom du ministère de l'utilisateur
   */
  protected function getMapMinistere() {
    if (!isset($this->ministere)) {
      $baseDNZone = 'ou=mineqZone,ou=nomenclatures,ou=ressources,dc=equipement,dc=gouv,dc=fr';
      $filtreZone = 'mineqzone=' . $this->objectmelanie->zone;
			$searchZone = \LibMelanie\Ldap\Ldap::GetInstance($this->_server)->search($baseDNZone, $filtreZone, array('description'));
      $entries = \LibMelanie\Ldap\Ldap::GetInstance($this->_server)->get_entries($searchZone);
      if (is_array($entries) && isset($entries[0])) {
        $this->ministere = $entries[0]['description'];
      }
    }
    return $this->ministere;
  }

  /**
   * Mapping ministere field
   * 
   * @return boolean false non supporté
   */
  protected function setMapMinistere() {
    return false;
  }

  /**
   * Mapping vpn_profile_name field
   * 
   * @return string Nom du profil VPN de l'utilisateur
   */
  protected function getMapVpn_profile_name() {
    if (!isset($this->vpn_profile_name)) {
      $baseDNVPN = 'ou=mineqVpnProfil,ou=nomenclatures,ou=ressources,dc=equipement,dc=gouv,dc=fr';
			$filtreVpn = 'mineqvpnprofil=' . $this->objectmelanie->vpn_profile;
			$searchVpn = \LibMelanie\Ldap\Ldap::GetInstance($this->_server)->search($baseDNVPN, $filtreVpn, array('description'));
      $entries = \LibMelanie\Ldap\Ldap::GetInstance($this->_server)->get_entries($searchVpn);
      if (is_array($entries) && isset($entries[0])) {
        $this->vpn_profile_name = $entries[0]['description'];
      }
    }
    return $this->vpn_profile_name;
  }

  /**
   * Mapping vpn_profile_name field
   * 
   * @return boolean false non supporté
   */
  protected function setMapVpn_profile_name() {
    return false;
  }

  /**
   * Mapping observation field
   * 
   * @return string Champ observation de l'utilisateur
   */
  protected function getMapObservation() {
    if (!isset($this->observation)) {
      if (is_array($this->objectmelanie->info)) {
        foreach ($this->objectmelanie->info as $info) {
          if (strpos($info, 'OBSERVATION') === 0) {
            $this->observation = substr($info, strpos($info, ':') + 2);
            break;
          }
        }
      }
    }
    return $this->observation;
  }

  /**
   * Mapping observation field
   * 
   * @return boolean false non supporté
   */
  protected function setMapObservation() {
    return false;
  }

  /**
   * Mapping access_internet_profil field
   * 
   * @return string Profil d'accés internet de l'utilisateur
   */
  protected function getMapAccess_internet_profil() {
    if (!isset($this->access_internet_profil)) {
      // Initialisation du profil
      if (strpos($this->objectmelanie->dn, 'ou=departements,ou=organisation,dc=equipement,dc=gouv,dc=fr') !== false
          || strpos($this->objectmelanie->dn, 'ou=DDEA,ou=melanie,ou=organisation,dc=equipement,dc=gouv,dc=fr') !== false) {
        $this->access_internet_profil = 'DDI-INTERNET-STANDARD';
      } else {
        $this->access_internet_profil = 'ACCESINTERNET';
      }
      if (is_array($this->objectmelanie->info)) {
        foreach ($this->objectmelanie->info as $info) {
          if (strpos($info, 'AccesInternet.Profil') === 0) {
            $this->access_internet_profil = substr($info, strpos($info, ':') + 2);
            break;
          }
        }
      }
    }
    return $this->access_internet_profil;
  }

  /**
   * Mapping access_internet_profil field
   * 
   * @return boolean false non supporté
   */
  protected function setMapAccess_internet_profil() {
    return false;
  }

  /**
   * Mapping access_internet_timestamp field
   * 
   * @return string Timestamp d'activation de l'accés internet de l'utilisateur
   */
  protected function getMapAccess_internet_timestamp() {
    if (!isset($this->access_internet_timestamp)) {
      if (is_array($this->objectmelanie->info)) {
        foreach ($this->objectmelanie->info as $info) {
          if (strpos($info, 'AccesInternet.AcceptationCGUts') === 0) {
            $this->access_internet_timestamp = intval(substr($info, strpos($info, ':') + 2));
            break;
          }
        }
      }
    }
    return $this->access_internet_timestamp;
  }

  /**
   * Mapping access_internet_timestamp field
   * 
   * @return boolean false non supporté
   */
  protected function setMapAccess_internet_timestamp() {
    return false;
  }

  /**
   * Mapping access_synchro_profil field
   * 
   * @return string Profil de synchronisation de l'utilisateur
   */
  protected function getMapAccess_synchro_profil() {
    if (!isset($this->access_internet_profil)) {
      
    }
    return $this->access_internet_profil;
  }

  /**
   * Mapping access_synchro_profil field
   * 
   * @return boolean false non supporté
   */
  protected function setMapAccess_synchro_profil() {
    return false;
  }

  /**
   * Mapping access_synchro_datetime field
   * 
   * @return string Datetime de l'activation de la synchro de l'utilisateur
   */
  protected function getMapAccess_synchro_datetime() {
    if (!isset($this->access_synchro_datetime)) {
      
    }
    return $this->access_synchro_datetime;
  }

  /**
   * Mapping access_synchro_datetime field
   * 
   * @return boolean false non supporté
   */
  protected function setMapAccess_synchro_datetime() {
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
    $length = isset($this->objectmelanie->away_response['count']) ? $this->objectmelanie->away_response['count'] : count($this->objectmelanie->away_response);
    for ($i = 0; $i < $length; $i++) {
      $objects[] = $this->createObjectFromData($this->objectmelanie->away_response[$i], 
          strpos($this->objectmelanie->away_response[$i], "RAIN") !== false ? Outofoffice::TYPE_INTERNAL : Outofoffice::TYPE_EXTERNAL);
    }
    return $objects;
	}
	
	/**
   * Créer un objet Outofoffice à partir des données de l'annuaire
   * 
   * @param array $data Données de l'annuaire pour le message d'absence
   * @return Outofoffice
   */
  private function createObjectFromData($data, $type) {
    // Positionnement de TEXTE qui doit être en dernier
    $pos = strpos($data, "TEXTE:") + 6;
    // On explode toutes les propriétés avant TEXTE
    $tab = explode(" ", substr($data, 0, $pos));
    $object = new Outofoffice();
    $object->type = $type;
    foreach ($tab as $entry) {
      if (strpos($entry, ':') !== false) {
        list($key, $val) = explode(":", $entry, 2);
        // Ajout des properties dans l'objet
        switch ($key) {
          case 'DDEB':
            // Date de début
            $object->start = strlen($val) ? new \DateTime($val) : null;
            break;
          case 'DFIN':
            // Si la date de fin commence par 0, le message d'absence est désactivé
            $object->enable = strpos($val, '0') !== 0;
            // Date de fin
            if (strpos($val, '/') !== false) {
              $val = substr($val, 2);
            }
            $object->end = strlen($val) ? new \DateTime($val) : null;
            break;
        }
      }
      else if (strpos($entry, '~') !== false) {
        // Gestion du tri
        $object->order = intval(str_replace('~', '', $entry));
      }
    }
    $object->message = substr($data, $pos);
    return $object;
  }
  /**
   * Positionnement du champ out of offices
   * 
   */
  protected function setMapOutofoffices($OofObjects) {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapOutofoffices()");
		$this->objectmelanie->away_response = [];
    foreach ($OofObjects as $OofObject) {
      $this->objectmelanie->away_response[] = $this->createDataFromObject($OofObject);
    }
	}
	
	/**
   * Génère les data pour l'annuaire en fonction de l'objet
   * 
   * @param Outofoffice $object
   * @return string Données a enregistrer dans l'annuaire
   */
  private function createDataFromObject(Outofoffice $object) {
    $data = [];
    // Gestion du classement
    if (isset($object->order)) {
      $data[] = $object->order . '~';
    }
    else {
      $data[] = '50~';
    }
    // Type de message d'absence
    $data[] = $object->type == Outofoffice::TYPE_INTERNAL ? 'RAIN' : 'RAEX';
    // Date de debut
    $data[] = 'DDEB:' . isset($object->start) ? $object->start->format('Ymd') : '';
    // Date de fin
    $data[] = 'DFIN:' . ($object->enable ? '' : (isset($object->end) ? '0/' : '0')) . (isset($object->end) ? $object->end->format('Ymd') : '');
    // Texte
    $data[] = 'TEXTE:' . $object->message;

    return implode(' ', $data);
  }

}
