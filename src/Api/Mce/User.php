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

use LibMelanie\Log\M2Log;
use LibMelanie\Api\Mce\Users\Outofoffice;
use LibMelanie\Api\Mce\Users\Share;
use LibMelanie\Api\Defaut;
use LibMelanie\Config\MappingMce;
use LibMelanie\Objects\UserMelanie;

/**
 * Classe utilisateur pour MCE
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/MCE
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
 * @property Share[] $shares Liste des partages de la boite
 * @property-read array $supported_shares Liste des droits supportés par cette boite
 * @property-read boolean $internet_access_enable Est-ce que l'accès Internet de l'utilisateur est activé
 * @property string $street Adresse - Rue de l'utilisateur
 * @property string $postalcode Adresse - Code postal de l'utilisateur
 * @property string $locality Adresse - Ville de l'utilisateur
 * @property string $title Titre de l'utilisateur
 * @property array $server_routage Champ de routage pour le serveur de message de l'utilisateur
 * @property-read string $server_host Host du serveur de messagerie de l'utilisateur
 * @property-read string $server_user User du serveur de messagerie de l'utilisateur
 * @property Outofoffice[] $outofoffices Tableau de gestionnaire d'absence pour l'utilisateur
 * 
 * @property-read boolean $is_objectshare Est-ce que cet utilisateur est en fait un objet de partage
 * @property-read ObjectShare $objectshare Retourne l'objet de partage lié à cet utilisateur si s'en est un
 * 
 * @property-read boolean $is_synchronisation_enable Est-ce que la synchronisation est activée pour l'utilisateur ?
 * @property-read string $synchronisation_profile Profil de synchronisation positionné pour l'utilisateur (STANDARD ou SENSIBLE)
 * 
 * @method bool authentification($password, $master = false) Authentification de l'utilisateur sur l'annuaire MCE
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
  const LOAD_FROM_EMAIL_FILTER = "(mail=%%email%%)";
  /**
   * Filtre pour la méthode load() si c'est un objet de partage
   * 
   * @ignore
   */
  const LOAD_OBJECTSHARE_FILTER = self::LOAD_FILTER;
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
  const GET_BALP_FILTER = "(mcedelegation=%%uid%%:*)";
  /**
   * Filtre pour la méthode getBalpEmission()
   * 
   * @ignore
   */
  const GET_BALP_EMISSION_FILTER = self::GET_BALP_FILTER;
  /**
   * Filtre pour la méthode getBalpGestionnaire()
   * 
   * @ignore
   */
  const GET_BALP_GESTIONNAIRE_FILTER = self::GET_BALP_FILTER;
  /**
   * Filtre pour la méthode getGroups()
   * 
   * @ignore
   */
  const GET_GROUPS_FILTER = null;
  /**
   * Filtre pour la méthode getGroupsIsMember()
   * 
   * @ignore
   */
  const GET_GROUPS_IS_MEMBER_FILTER = null;
  /**
   * Filtre pour la méthode getListsIsMember()
   * 
   * @ignore
   */
  const GET_LISTS_IS_MEMBER_FILTER = null;

  /**
   * Liste des attributs à récupérer depuis l'autre annuaire
   * 
   * @ignore
   */
  const OTHER_LDAP_ATTRIBUTES = ['fullname', 'name', 'street', 'postalcode', 'locality', 'title'];

  /**
   * Configuration du mapping qui surcharge la conf
   */
  const MAPPING = [
    "dn"                      => 'dn',                            // DN de l'utilisateur
    "uid"                     => 'uid',                           // Identifiant de l'utilisateur
    "fullname"                => 'cn',                            // Nom complet de l'utilisateur
    "name"                    => 'cn',                            // Nom court de l'utilisateur
    "email"                   => 'mail',                          // Adresse e-mail principale de l'utilisateur en reception
    "email_list"              => [MappingMce::name => 'mailalternateaddress', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en reception pour l'utilisateur
    "email_send"              => 'mail',                          // Adresse e-mail principale de l'utilisateur en emission
    "email_send_list"         => [MappingMce::name => 'mailalternateaddress', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en émission pour l'utilisateur
    "shares"                  => [MappingMce::name => 'mcedelegation', MappingMce::type => MappingMce::arrayLdap], // Liste des partages pour cette boite
    "server_routage"          => [MappingMce::name => 'mailhost', MappingMce::type => MappingMce::arrayLdap], // Champ utilisé pour le routage des messages
    "type"                    => 'mcetypecompte',                 // Type d'entrée (boite individuelle, partagée, ressource, ...)
    "street"                  => 'street',                        // Rue
    "postalcode"              => 'postalcode',                    // Code postal
    "locality"                => 'l',                             // Ville
    "title"                   => 'title',                         // Titre
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
   * Récupération du champ internet_access_enable
   * 
   * @return boolean true si l'access internet de l'utilisateur est activé, false sinon
   */
  protected function getMapInternet_access_enable() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapInternet_access_enable()");
    // Toujours true pour la MCE
    return true;
  }

  /**
   * Récupération du champ server_host
   * 
   * @return mixed|NULL Valeur du serveur host, null si non trouvé
   */
  protected function getMapServer_host() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapServer_host()");
    if (is_array($this->server_routage) && isset($this->server_routage[0])) {
      return $this->server_routage[0];
    }
    else if (is_string($this->server_routage)) {
      return $this->server_routage;
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
    return null;
  }

  /**
   * Récupération du champ out of offices
   * 
   * @return Outofoffice[] Tableau de d'objets Outofoffice
   */
  protected function getMapOutofoffices() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapOutofoffices()");
    return [];
  }

  /**
   * Positionnement du champ out of offices
   * 
   */
  protected function setMapOutofoffices($outofoffices) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapOutofoffices()");
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
      $right = '';
      switch ($share->type) {
        case Share::TYPE_ADMIN:
          $right = 'G';
          break;
      }
      $_shares[] = $share->user . ':' . $right;
    }
    $this->objectmelanie->shares = $_shares;
  }

  /**
   * Mapping shares field
   * 
   * @return Share[] Liste des partages de l'objet
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
    return [Share::TYPE_ADMIN];
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
   * Mapping fullname field
   * 
   * @param string $fullname          
   */
  protected function setMapFullname($fullname) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapFullname($fullname)");
    if (!isset(\LibMelanie\Config\Ldap::$OTHER_LDAP)) {
      $this->objectmelanie->fullname = $fullname;
    }
  }
  /**
   * Mapping fullname field
   */
  protected function getMapFullname() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapFullname()");
    if (isset(\LibMelanie\Config\Ldap::$OTHER_LDAP)) {
      if (!isset($this->otherldapobject)) {
        $this->otherldapobject = new UserMelanie(\LibMelanie\Config\Ldap::$OTHER_LDAP, null, static::MAPPING);
      }
      if (!isset($this->otherldapobject->uid) || !isset($this->otherldapobject->fullname)) {
        $this->otherldapobject->uid = $this->uid;
        $this->otherldapobject->load(self::OTHER_LDAP_ATTRIBUTES);
      }
      $fullname = $this->otherldapobject->fullname;
    }
    else {
      $fullname = $this->objectmelanie->fullname;
    }
    return $fullname;
  }

  /**
   * Mapping name field
   * 
   * @param string $name          
   */
  protected function setMapName($name) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapName($name)");
    if (!isset(\LibMelanie\Config\Ldap::$OTHER_LDAP)) {
      $this->objectmelanie->name = $name;
    }
  }
  /**
   * Mapping name field
   */
  protected function getMapName() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapName()");
    if (isset(\LibMelanie\Config\Ldap::$OTHER_LDAP)) {
      if (!isset($this->otherldapobject)) {
        $this->otherldapobject = new UserMelanie(\LibMelanie\Config\Ldap::$OTHER_LDAP, null, static::MAPPING);
      }
      if (!isset($this->otherldapobject->uid)) {
        $this->otherldapobject->uid = $this->uid;
        $this->otherldapobject->load(self::OTHER_LDAP_ATTRIBUTES);
      }
      $name = $this->otherldapobject->name;
    }
    else {
      $name = $this->objectmelanie->name;
    }
    if (strpos($name, ' - ') !== false) {
      $name = explode(' - ', $name, 2);
      $name = $name[0];
    }
    return $name;
  }

  /**
   * Mapping street field
   * 
   * @param string $street          
   */
  protected function setMapStreet($street) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapStreet($street)");
    if (!isset(\LibMelanie\Config\Ldap::$OTHER_LDAP)) {
      $this->objectmelanie->street = $street;
    }
  }
  /**
   * Mapping street field
   */
  protected function getMapStreet() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapStreet()");
    if (isset(\LibMelanie\Config\Ldap::$OTHER_LDAP)) {
      if (!isset($this->otherldapobject)) {
        $this->otherldapobject = new UserMelanie(\LibMelanie\Config\Ldap::$OTHER_LDAP, null, static::MAPPING);
      }
      if (!isset($this->otherldapobject->uid)) {
        $this->otherldapobject->uid = $this->uid;
        $this->otherldapobject->load(self::OTHER_LDAP_ATTRIBUTES);
      }
      $street = $this->otherldapobject->street;
    }
    else {
      $street = $this->objectmelanie->street;
    }
    
    return $street;
  }

  /**
   * Mapping locality field
   * 
   * @param string $locality          
   */
  protected function setMapLocality($locality) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapLocality($locality)");
    if (!isset(\LibMelanie\Config\Ldap::$OTHER_LDAP)) {
      $this->objectmelanie->locality = $locality;
    }
  }

  /**
   * Mapping locality field
   */
  protected function getMapLocality() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapLocality()");
    if (isset(\LibMelanie\Config\Ldap::$OTHER_LDAP)) {
      if (!isset($this->otherldapobject)) {
        $this->otherldapobject = new UserMelanie(\LibMelanie\Config\Ldap::$OTHER_LDAP, null, static::MAPPING);
      }
      if (!isset($this->otherldapobject->uid)) {
        $this->otherldapobject->uid = $this->uid;
        $this->otherldapobject->load(self::OTHER_LDAP_ATTRIBUTES);
      }
      $locality = $this->otherldapobject->locality;
    }
    else {
      $locality = $this->objectmelanie->locality;
    }
    return $locality;
  }

  /**
   * Mapping title field
   * 
   * @param string $title          
   */
  protected function setMapTitle($title) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapTitle($title)");
    if (!isset(\LibMelanie\Config\Ldap::$OTHER_LDAP)) {
      $this->objectmelanie->title = $title;
    }
  }

  /**
   * Mapping locality field
   */
  protected function getMapTitle() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapTitle()");
    if (isset(\LibMelanie\Config\Ldap::$OTHER_LDAP)) {
      if (!isset($this->otherldapobject)) {
        $this->otherldapobject = new UserMelanie(\LibMelanie\Config\Ldap::$OTHER_LDAP, null, static::MAPPING);
      }
      if (!isset($this->otherldapobject->uid)) {
        $this->otherldapobject->uid = $this->uid;
        $this->otherldapobject->load(self::OTHER_LDAP_ATTRIBUTES);
      }
      $title = $this->otherldapobject->title;
    }
    else {
      $title = $this->objectmelanie->title;
    }
    return $title;
  }
}
