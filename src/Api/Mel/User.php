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
namespace LibMelanie\Api\Mel;

use LibMelanie\Api\Defaut;
use LibMelanie\Api\Mel\Users\Outofoffice;
use LibMelanie\Api\Mel\Users\Share;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\MappingMce;
use LibMelanie\Config\Config;

/**
 * Classe utilisateur pour Mel
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Mel
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
  const LOAD_FILTER = "(&(uid=%%uid%%)(mineqTypeentree=*))";
  /**
   * Filtre pour la méthode load() avec un email
   * 
   * @ignore
   */
  const LOAD_FROM_EMAIL_FILTER = "(|(mail=%%email%%)(mineqmelmailemission=%%email%%))";
  /**
   * Attributs par défauts pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_ATTRIBUTES = ['fullname', 'uid', 'name', 'email', 'email_list', 'email_send', 'email_send_list', 'server_routage', 'internet_access_user', 'shares', 'type'];
  /**
   * Filtre pour la méthode load() si c'est un objet de partage
   * 
   * @ignore
   */
  const LOAD_OBJECTSHARE_FILTER = "(uid=%%uid%%)";
  /**
   * Filtre pour la méthode load() avec un email si c'est un object de partage
   * 
   * @ignore
   */
  const LOAD_OBJECTSHARE_FROM_EMAIL_FILTER = self::LOAD_FROM_EMAIL_FILTER;
  /**
   * Filtre pour la méthode getBalp()
   * 
   * @ignore
   */
  const GET_BALP_FILTER = "(uid=%%uid%%.-.*)";
  /**
   * Attributs par défauts pour la méthode getBalp()
   * 
   * @ignore
   */
  const GET_BALP_ATTRIBUTES = ['fullname', 'email_send', 'email_send_list', 'uid', 'shares', 'internet_access_user'];
  /**
   * Filtre pour la méthode getBalpEmission()
   * 
   * @ignore
   */
  const GET_BALP_EMISSION_FILTER = "(|(mineqmelpartages=%%uid%%:C)(mineqmelpartages=%%uid%%:G))";
  /**
   * Attributs par défauts pour la méthode getBalpEmission()
   * 
   * @ignore
   */
  const GET_BALP_EMISSION_ATTRIBUTES = ['fullname', 'email_send', 'email_send_list', 'uid', 'shares', 'internet_access_user'];
  /**
   * Filtre pour la méthode getBalpGestionnaire()
   * 
   * @ignore
   */
  const GET_BALP_GESTIONNAIRE_FILTER = "(mineqmelpartages=%%uid%%:G)";
  /**
   * Attributs par défauts pour la méthode getBalpGestionnaire()
   * 
   * @ignore
   */
  const GET_BALP_GESTIONNAIRE_ATTRIBUTES = ['fullname', 'email_send', 'email_send_list', 'uid', 'shares', 'internet_access_user'];
  /**
   * Filtre pour la méthode getGroups()
   * 
   * @ignore
   */
  const GET_GROUPS_FILTER = "(&(objectclass=mineqMelListe)(owner=%%dn%%))";
  /**
   * Filtre pour la méthode getGroupsIsMember()
   * 
   * @ignore
   */
  const GET_GROUPS_IS_MEMBER_FILTER = "(&(objectclass=mineqMelListe)(member=%%uid%%))";
  /**
   * Filtre pour la méthode getListsIsMember()
   * 
   * @ignore
   */
  const GET_LISTS_IS_MEMBER_FILTER = "(&(objectclass=mineqMelListe)(mineqMelMembres=%%email%%))";

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
    "email"                   => 'mailpr',                        // Adresse e-mail principale de l'utilisateur en reception
    "email_list"              => [MappingMce::name => 'mail', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en reception pour l'utilisateur
    "email_send"              => 'mineqmelmailemissionpr',        // Adresse e-mail principale de l'utilisateur en emission
    "email_send_list"         => [MappingMce::name => 'mineqmelmailemission', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en émission pour l'utilisateur
    "service"                 => 'departmentnumber',              // Department Number
    "password_need_change"    => 'mineqpassworddoitchanger',      // Message pour indiquer que le mot de passe de l'utilisateur doit changer
    "shares"                  => [MappingMce::name => 'mineqmelpartages', MappingMce::type => MappingMce::arrayLdap], // Liste des partages pour cette boite
    "server_routage"          => [MappingMce::name => 'mineqmelroutage', MappingMce::type => MappingMce::arrayLdap], // Champ utilisé pour le routage des messages
    "server_host"             => [MappingMce::name => 'mineqmelroutage', MappingMce::type => MappingMce::arrayLdap], // Champ utilisé pour le routage des messages
    "type"                    => 'mineqtypeentree',               // Type d'entrée (boite individuelle, partagée, ressource, ...)
    "internet_access_admin"   => [MappingMce::name => 'mineqmelaccesinterneta', MappingMce::defaut => false, MappingMce::type => MappingMce::booleanLdap],        // Droit d'accès depuis internet donné par l'administrateur
    "internet_access_user"    => [MappingMce::name => 'mineqmelaccesinternetu', MappingMce::defaut => false, MappingMce::type => MappingMce::booleanLdap],        // Droit d'accès depuis internet accepté par l'utilisateur
    "internet_access_enable"  => ['mineqmelaccesinterneta', 'mineqmelaccesinternetu'],
    "use_photo_ader"          => 'mineqpublicationphotoader',     // Flag publier la photo de l'utilisateur sur Ader
    "use_photo_intranet"      => 'mineqpublicationphotointranet', // Flag publier la photo de l'utilisateur sur l'intranet
    "employee_number"         => [MappingMce::name => 'employeenumber', MappingMce::defaut => 'non renseigné'],                // Matricule de l'utilisateur
    "ministere"               => 'mineqzone',                     // Zone de l'utilisateur
    "street"                  => 'street',                        // Rue
    "postalcode"              => 'postalcode',                    // Code postal
    "locality"                => 'l',                             // Ville
    "description"             => 'description',                   // Description
    "phonenumber"             => 'telephonenumber',               // Numéro de téléphone
    "faxnumber"               => 'facsimiletelephonenumber',      // Numéro de fax
    "mobilephone"             => 'mobile',                        // Numéro de mobile
    "roomnumber"              => 'roomnumber',                    // Numéro de bureau
    "title"                   => 'title',                         // Titre
    "business_category"       => [MappingMce::name => 'businesscategory', MappingMce::type => MappingMce::arrayLdap], // Cetégorie
    "vpn_profile_name"        => 'mineqvpnprofil',                // Nom du profil VPN associé
    "has_bureautique"         => 'sambasid',                      // SID Samba
    "update_personnal_info"   => [MappingMce::name => 'mineqmajinfoperso', MappingMce::defaut => true, MappingMce::trueLdapValue => '1', MappingMce::falseLdapValue => '0', MappingMce::type => MappingMce::booleanLdap],             // Droit pour l'utilisateur de modifier ses infos perso dans l'annuaire
    "remise"                  => 'mineqmelremise',                // Remise
    "mission"                 => [MappingMce::name => 'mineqmission', MappingMce::type => MappingMce::arrayLdap], // Mission
    "photo_src"               => 'jpegphoto',                     // Photo de l'utilisateur
    "gender"                  => 'gender',                        // Genre
    "liens_import"            => 'mineqliensimport',              // Lien d'import autres annuaires
    "is_agriculture"          => 'mineqliensimport',              // Calcul si l'utilisateur appartient à l'agriculture
    "supported_shares"        => 'mineqliensimport',              // Liste des droits supportés en fonction du lien import
    "observation"             => [MappingMce::name => 'info', MappingMce::prefixLdap => 'OBSERVATION:', MappingMce::type => MappingMce::stringLdap],
    "acces_internet_profil"   => [MappingMce::name => 'info', MappingMce::prefixLdap => 'AccesInternet.Profil: ', MappingMce::type => MappingMce::stringLdap],
    "acces_internet_ts"       => [MappingMce::name => 'info', MappingMce::prefixLdap => 'AccesInternet.AcceptationCGUts: ', MappingMce::type => MappingMce::stringLdap],
    "mdrive"                  => [MappingMce::name => 'info', MappingMce::prefixLdap => 'MDRIVE: ', MappingMce::type => MappingMce::booleanLdap, MappingMce::trueLdapValue => 'oui', MappingMce::falseLdapValue => 'non'],
    "outofoffices"            => [MappingMce::name => 'mineqmelreponse', MappingMce::type => MappingMce::arrayLdap], // Affichage du message d'absence de l'utilisateur
    "acces_synchro_admin_profil"    => 'mineqmelaccessynchroa',       // Profil de synchro administrateur
    "acces_synchro_admin_datetime"  => 'mineqmelaccessynchroa',       // Date de synchro administrateur
    "acces_synchro_user_profil"     => 'mineqmelaccessynchrou',       // Profil de synchro administrateur
    "acces_synchro_user_datetime"   => 'mineqmelaccessynchrou',       // Profil de synchro administrateur
    "is_synchronisation_enable"     => 'mineqmelaccessynchrou',       // Est-ce que la synchronisation de l'utilisateur est activée ?
    "synchronisation_profile"       => 'mineqmelaccessynchrou',       // Retourne le profil de synchronisation
    "cerbere"                   => [MappingMce::name => 'info', MappingMce::prefixLdap => 'AUTH.Cerbere:', MappingMce::type => MappingMce::stringLdap],
    "double_authentification"   => [MappingMce::name => 'mineqinfosec', MappingMce::prefixLdap => 'DoubleAuth.Obligatoire: ', MappingMce::type => MappingMce::booleanLdap, MappingMce::trueLdapValue => 'oui', MappingMce::falseLdapValue => 'non'],
    "is_mailbox"                => [MappingMce::name => 'objectclass', MappingMce::trueLdapValue => 'mineqMelBoite', MappingMce::type => MappingMce::booleanLdap],             // Est-ce qu'il s'agit bien d'une boite Mél ?
  ];

  /**
   * ***************************************************
   * PRIVATE VAR
   */
  /**
   * Nom du ministere associe a l'utilisateur
   * 
   * @var string
   */
  private $ministere;
  /**
   * Nom du profil VPN de l'utilisateur
   * 
   * @var string
   */
  private $vpn_profile_name;
  /**
   * Profil d'acces internet pour l'utilisateur
   * 
   * @var string
   */
  private $acces_internet_profil;
  /**
   * Profil de synchronisation positionné par l'administrateur
   * 
   * @var string
   */
  private $acces_synchro_admin_profil;
  /**
   * Date de mise en place par l'administrateur de la synchronisation
   * 
   * @var string
   */
  private $acces_synchro_admin_datetime;
  /**
   * Profil de synchronisation positionné accepté par l'utilisateur
   * 
   * @var string
   */
  private $acces_synchro_user_profil;
  /**
   * Date d'acceptation de l'utilisateur pour la synchronisation
   * 
   * @var string
   */
  private $acces_synchro_user_datetime;

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
   * Récupération du champ internet_access_enable
   * 
   * @return boolean true si l'access internet de l'utilisateur est activé, false sinon
   */
  protected function getMapInternet_access_enable() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapInternet_access_enable()");
    return $this->internet_access_admin && $this->internet_access_user;
  }

  /**
   * Récupération du champ server_host
   * 
   * @return mixed|NULL Valeur du serveur host, null si non trouvé
   */
  protected function getMapServer_host() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapServer_host()");
    if (is_array($this->server_routage)) {
      foreach ($this->server_routage as $route) {
        if (strpos($route, self::SERVER_HOST_DELIMITER) !== false) {
          $route = explode('@', $route, 2);
          return $route[1];
        }
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
    if (is_array($this->server_routage)) {
      foreach ($this->server_routage as $route) {
        if (strpos($route, self::SERVER_HOST_DELIMITER) !== false) {
          $route = explode('@', $route, 2);
          return $route[0];
        }
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
    if (is_array($shares)) {
      foreach ($shares as $share) {
        if ($this->getMapIs_agriculture() && $share->type == Share::TYPE_ADMIN) {
          // Pas de droit gestionnaire pour les imports Agri
          continue;
        }
        if (empty($share->user)) {
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
      if (is_array($_shares)) {
        foreach ($_shares as $_share) {
          $share = new Share();
          list($share->user, $right) = explode(':', $_share, 2);
          if (empty($share->user)) {
            continue;
          }
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
    }
    return $this->_shares;
  }

  /**
   * Mapping is_agriculture field
   * 
   * @return boolean $is_agriculture Est-ce que l'utilisateur appartient au MAA (calcul sur le liens import)
   */
  protected function getMapIs_agriculture() {
    return strpos($this->liens_import, 'AGRI.Lien: ') === 0;
  }

  /**
   * Mapping is_agriculture field
   * 
   * @param boolean $is_agriculture Est-ce que l'utilisateur appartient au MAA (calcul sur le liens import)
   * 
   * @return boolean false non supporté
   */
  protected function setMapIs_agriculture($is_agriculture) {
    return false;
  }

  /**
   * Mapping has_bureautique field
   * 
   * @return boolean $has_bureautique Est-ce que l'utilisateur a un compte bureautique ?
   */
  protected function getMapHas_bureautique() {
    // Si un samba sid est positionné, le compte bureautique existe
    return isset($this->objectmelanie->sambasid) && !empty($this->objectmelanie->sambasid);
  }

  /**
   * Mapping has_bureautique field
   * 
   * @param boolean $has_bureautique Est-ce que l'utilisateur a un compte bureautique ?
   * 
   * @return boolean false non supporté
   */
  protected function setMapHas_bureautique($has_bureautique) {
    return false;
  }

  /**
   * Mapping photo_src field
   * 
   * @return string $photo_src Source pour afficher la photo de l'utilisateur
   */
  protected function getMapPhoto_src() {
    if (isset($this->objectmelanie->jpegphoto)
        && isset($this->objectmelanie->jpegphoto[0])) {
      return "data:image/jpeg;base64," . base64_encode($this->objectmelanie->jpegphoto[0]);
    }
    return null;
  }

  /**
   * Mapping photo_src field
   * 
   * @param boolean $photo_src Source pour afficher la photo de l'utilisateur
   * 
   * @return boolean false non supporté
   */
  protected function setMapPhoto_src($photo_src) {
    return false;
  }

  /**
   * Mapping shares field
   * 
   * @return array Liste des partages supportés par cette boite ([Share::TYPE_*])
   */
  protected function getMapSupported_shares() {
    if ($this->getMapIs_agriculture()) {
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
   * @param array $supported_shares Liste des partages supportés par cette boite ([Share::TYPE_*])
   * 
   * @return boolean false non supporté
   */
  protected function setMapSupported_shares($supported_shares) {
    return false;
  }

  /**
   * Mapping ministere field
   * 
   * @return string Nom du ministère de l'utilisateur
   */
  protected function getMapMinistere() {
    if (!isset($this->ministere)) {
      if (isset($this->objectmelanie->mineqzone)
          && isset($this->objectmelanie->mineqzone[0])) {
        $baseDNZone = 'ou=mineqZone,ou=nomenclatures,ou=ressources,dc=equipement,dc=gouv,dc=fr';
        $filtreZone = 'mineqzone=' . $this->objectmelanie->mineqzone[0];
        $searchZone = \LibMelanie\Ldap\Ldap::GetInstance($this->_server)->search($baseDNZone, $filtreZone, array('description'));
        $entries = \LibMelanie\Ldap\Ldap::GetInstance($this->_server)->get_entries($searchZone);
        if (is_array($entries) && isset($entries[0])) {
          $this->ministere = $entries[0]['description'][0];
        }
      }
      else {
        $this->ministere = 'non renseigné';
      }
      
    }
    return $this->ministere;
  }

  /**
   * Mapping ministere field
   * 
   * @param string $ministere Nom du ministère de l'utilisateur
   * 
   * @return boolean false non supporté
   */
  protected function setMapMinistere($ministere) {
    return false;
  }

  /**
   * Mapping vpn_profile_name field
   * 
   * @return string Nom du profil VPN de l'utilisateur
   */
  protected function getMapVpn_profile_name() {
    if (!isset($this->vpn_profile_name)) {
      if (isset($this->objectmelanie->mineqvpnprofil)
          && isset($this->objectmelanie->mineqvpnprofil[0])) {
        $baseDNVPN = 'ou=mineqVpnProfil,ou=nomenclatures,ou=ressources,dc=equipement,dc=gouv,dc=fr';
        $filtreVpn = 'mineqvpnprofil=' . $this->objectmelanie->mineqvpnprofil[0];
        $searchVpn = \LibMelanie\Ldap\Ldap::GetInstance($this->_server)->search($baseDNVPN, $filtreVpn, array('description'));
        $entries = \LibMelanie\Ldap\Ldap::GetInstance($this->_server)->get_entries($searchVpn);
        if (is_array($entries) && isset($entries[0])) {
          $this->vpn_profile_name = $entries[0]['description'][0];
        }
      }
      else {
        $this->vpn_profile_name = 'aucun';
      }
    }
    return $this->vpn_profile_name;
  }

  /**
   * Mapping vpn_profile_name field
   * 
   * @param string $vpn_profile_name Nom du profil VPN de l'utilisateur
   * 
   * @return boolean false non supporté
   */
  protected function setMapVpn_profile_name($vpn_profile_name) {
    return false;
  }

  /**
   * Mapping acces_internet_profil field
   * 
   * @return string Profil d'accés internet de l'utilisateur
   */
  protected function getMapAcces_internet_profil() {
    if (!isset($this->acces_internet_profil)) {
      // Initialisation du profil
      if (strpos($this->objectmelanie->dn, 'ou=departements,ou=organisation,dc=equipement,dc=gouv,dc=fr') !== false
          || strpos($this->objectmelanie->dn, 'ou=DDEA,ou=melanie,ou=organisation,dc=equipement,dc=gouv,dc=fr') !== false) {
        $this->acces_internet_profil = 'DDI-INTERNET-STANDARD';
      } else {
        $this->acces_internet_profil = 'ACCESINTERNET';
      }
      $acces_internet_profil = $this->objectmelanie->acces_internet_profil;
      if (isset($acces_internet_profil)) {
        $this->acces_internet_profil = $acces_internet_profil;
      }
    }
    return $this->acces_internet_profil;
  }

  /**
   * Mapping acces_synchro_admin_profil field
   * 
   * @return string Profil de synchronisation de l'utilisateur (enregistré par l'admin)
   */
  protected function getMapAcces_synchro_admin_profil() {
    if (!isset($this->acces_synchro_admin_profil)
        && isset($this->objectmelanie->mineqmelaccessynchroa)
        && isset($this->objectmelanie->mineqmelaccessynchroa[0])) {
      $_var = explode('--', $this->objectmelanie->mineqmelaccessynchroa[0], 2);
      if (isset($_var[1])) {
        $this->acces_synchro_admin_profil = $_var[1];
        $this->acces_synchro_admin_datetime = $_var[0];
      }
    }
    return $this->acces_synchro_admin_profil;
  }

  /**
   * Mapping acces_synchro_admin_profil field
   * 
   * @param string $acces_synchro_admin_profil Profil de synchronisation de l'utilisateur (enregistré par l'admin)
   * 
   * @return boolean
   */
  protected function setMapAcces_synchro_admin_profil($acces_synchro_admin_profil) {
    $date = '';
    if (isset($this->objectmelanie->mineqmelaccessynchroa)
        && isset($this->objectmelanie->mineqmelaccessynchroa[0])) {
      $_var = explode('--', $this->objectmelanie->mineqmelaccessynchroa[0], 2);
      if (isset($_var[0])) {
        $date = $_var[0];
      }
    }
    $this->acces_synchro_admin_profil = $acces_synchro_admin_profil;
    $this->objectmelanie->mineqmelaccessynchroa = [$date . '--' . strtoupper($acces_synchro_admin_profil)];
  }

  /**
   * Mapping acces_synchro_admin_datetime field
   * 
   * @return string Datetime de l'activation de la synchro de l'utilisateur
   */
  protected function getMapAcces_synchro_admin_datetime() {
    if (!isset($this->acces_synchro_admin_datetime)
        && isset($this->objectmelanie->mineqmelaccessynchroa)
        && isset($this->objectmelanie->mineqmelaccessynchroa[0])) {
      $_var = explode('--', $this->objectmelanie->mineqmelaccessynchroa[0], 2);
      if (isset($_var[0])) {
        $this->acces_synchro_admin_datetime = $_var[0];
        $this->acces_synchro_admin_profil = isset($_var[1]) ? $_var[1] : null;
      }
    }
    return $this->acces_synchro_admin_datetime;
  }

  /**
   * Mapping acces_synchro_admin_datetime field
   * 
   * @param string|DateTime $acces_synchro_admin_datetime Datetime de l'activation de la synchro de l'utilisateur
   * 
   * @return boolean
   */
  protected function setMapAcces_synchro_admin_datetime($acces_synchro_admin_datetime) {
    $profil = '';
    if ($acces_synchro_admin_datetime instanceof \DateTime) {
      $acces_synchro_admin_datetime = $acces_synchro_admin_datetime->format('YmdHis') . $acces_synchro_admin_datetime->format('Z') === 0 ? 'Z' : '';
    }
    if (isset($this->objectmelanie->mineqmelaccessynchroa) 
        && isset($this->objectmelanie->mineqmelaccessynchroa[0])) {
      $_var = explode('--', $this->objectmelanie->mineqmelaccessynchroa[0], 2);
      if (isset($_var[1])) {
        $profil = $_var[1];
      }
    }
    $this->acces_synchro_admin_datetime = $acces_synchro_admin_datetime;
    $this->objectmelanie->mineqmelaccessynchroa = [$acces_synchro_admin_datetime . '--' . $profil];
  }

  /**
   * Mapping acces_synchro_user_profil field
   * 
   * @return string Profil de synchronisation de l'utilisateur
   */
  protected function getMapAcces_synchro_user_profil() {
    if (!isset($this->acces_synchro_user_profil)
        && isset($this->objectmelanie->mineqmelaccessynchrou)
        && isset($this->objectmelanie->mineqmelaccessynchrou[0])) {
      $_var = explode('--', $this->objectmelanie->mineqmelaccessynchrou[0], 2);
      if (isset($_var[1])) {
        $this->acces_synchro_user_profil = $_var[1];
        $this->acces_synchro_user_datetime = $_var[0];
      }
    }
    return $this->acces_synchro_user_profil;
  }

  /**
   * Mapping acces_synchro_user_profil field
   * 
   * @param string $acces_synchro_user_profil Profil de synchronisation de l'utilisateur
   * 
   * @return boolean
   */
  protected function setMapAcces_synchro_user_profil($acces_synchro_user_profil) {
    $date = '';
    if (isset($this->objectmelanie->mineqmelaccessynchrou)
        && isset($this->objectmelanie->mineqmelaccessynchrou[0])) {
      $_var = explode('--', $this->objectmelanie->mineqmelaccessynchrou[0], 2);
      if (isset($_var[0])) {
        $date = $_var[0];
      }
    }
    $this->acces_synchro_user_profil = $acces_synchro_user_profil;
    $this->objectmelanie->mineqmelaccessynchrou = [$date . '--' . strtoupper($acces_synchro_user_profil)];
  }

  /**
   * Mapping acces_synchro_user_datetime field
   * 
   * @return string Datetime de l'activation de la synchro de l'utilisateur
   */
  protected function getMapAcces_synchro_user_datetime() {
    if (!isset($this->acces_synchro_user_datetime)
        && isset($this->objectmelanie->mineqmelaccessynchrou)
        && isset($this->objectmelanie->mineqmelaccessynchrou[0])) {
      $_var = explode('--', $this->objectmelanie->mineqmelaccessynchrou[0], 2);
      if (isset($_var[0])) {
        $this->acces_synchro_user_datetime = $_var[0];
        $this->acces_synchro_user_profil = $_var[1];
      }
    }
    return $this->acces_synchro_user_datetime;
  }

  /**
   * Mapping acces_synchro_user_datetime field
   * 
   * @param string $acces_synchro_user_datetime Datetime de l'activation de la synchro de l'utilisateur
   * 
   * @return boolean
   */
  protected function setMapAcces_synchro_user_datetime($acces_synchro_user_datetime) {
    $profil = '';
    if ($acces_synchro_user_datetime instanceof \DateTime) {
      $acces_synchro_user_datetime = $acces_synchro_user_datetime->format('YmdHis') . $acces_synchro_user_datetime->format('Z') === 0 ? 'Z' : '';
    }
    if (isset($this->objectmelanie->mineqmelaccessynchrou)
        && isset($this->objectmelanie->mineqmelaccessynchrou[0])) {
      $_var = explode('--', $this->objectmelanie->mineqmelaccessynchrou[0], 2);
      if (isset($_var[1])) {
        $profil = $_var[1];
      }
    }
    $this->acces_synchro_user_datetime = $acces_synchro_user_datetime;
    $this->objectmelanie->mineqmelaccessynchrou = [$acces_synchro_user_datetime . '--' . $profil];
  }

  /**
   * Mapping is_synchronisation_enable field
   * 
   * @return boolean true si la synchronisation est activée pour l'utilisateur
   */
  protected function getMapIs_synchronisation_enable() {
    if (isset($this->objectmelanie->mineqmelaccessynchrou)
        && isset($this->objectmelanie->mineqmelaccessynchrou[0])) {
      return strpos($this->objectmelanie->mineqmelaccessynchrou[0], '--') !== false;
    }
    return false;
  }

  /**
   * Mapping is_synchronisation_enable field
   * 
   * @param string $is_synchronisation_enable Si la synchronisation de l'utilisateur est activée
   * 
   * @return boolean false non supporté
   */
  protected function setMapIs_synchronisation_enable($is_synchronisation_enable) {
    return false;
  }

  /**
   * Mapping synchronisation_profile field
   * 
   * @return string Profil de synchronisation de l'utilisateur
   */
  protected function getMapSynchronisation_profile() {
    if (!isset($this->acces_synchro_user_profil)
        && isset($this->objectmelanie->mineqmelaccessynchrou)
        && isset($this->objectmelanie->mineqmelaccessynchrou[0])) {
      $_var = explode('--', $this->objectmelanie->mineqmelaccessynchrou[0], 2);
      if (isset($_var[1])) {
        $this->acces_synchro_user_profil = $_var[1];
        $this->acces_synchro_user_datetime = $_var[0];
      }
    }
    return $this->acces_synchro_user_profil;
  }

  /**
   * Mapping synchronisation_profile field
   * 
   * @param string $synchronisation_profile Profil de synchronisation de l'utilisateur
   * 
   * @return boolean false non supporté
   */
  protected function setMapSynchronisation_profile($synchronisation_profile) {
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

  /**
   * Mapping is_partagee field
   * 
   * @return boolean true si la boite est partagée
   */
  protected function getMapIs_partagee() {
    return $this->objectmelanie->type == Config::get(Config::LDAP_TYPE_FONCTIONNELLE) 
        || $this->objectmelanie->type == Config::get(Config::LDAP_TYPE_RESSOURCE)
        || $this->objectmelanie->type == Config::get(Config::LDAP_TYPE_UNITE)
        || $this->objectmelanie->type == Config::get(Config::LDAP_TYPE_SERVICE);
  }

  /**
   * Mapping is_mailbox field
   * 
   * @return boolean true s'il s'agit bien d'une boite (valeur par défaut pour la MCE)
   */
  protected function getMapIs_mailbox() {
    return $this->objectmelanie->is_mailbox;
  }
}
