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
namespace LibMelanie\Api\Mce;

use LibMelanie\Log\M2Log;
use LibMelanie\Api\Melanie2\Users\Outofoffice;
use LibMelanie\Api\Mce\Users\Share;
use LibMelanie\Api\Defaut;

/**
 * Classe utilisateur pour MCE
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
 * @property-read boolean $is_objectshare Est-ce que cet utilisateur est en fait un objet de partage
 * @property-read ObjectShare $objectshare Retourne l'objet de partage lié à cet utilisateur si s'en est un
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
  const LOAD_FILTER = "(uid=%%username%%)";
  /**
   * Filtre pour la méthode load() avec un email
   * 
   * @ignore
   */
  const LOAD_FROM_EMAIL_FILTER = "(mailalternateaddress=%%email%%)";
  /**
   * Attributs par défauts pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_ATTRIBUTES = ['mail','mailalternateaddress','uid','mailhost','mcedelegation','mcetypecompte'];
  /**
   * Filtre pour la méthode getBalp()
   * 
   * @ignore
   */
  const GET_BALP_FILTER = "(mcedelegation=%%username%%:*)";
  /**
   * Attributs par défauts pour la méthode getBalp()
   * 
   * @ignore
   */
  const GET_BALP_ATTRIBUTES = ['mail','mailalternateaddress','uid','mailhost','mcetypecompte'];
  /**
   * Filtre pour la méthode getBalpEmission()
   * 
   * @ignore
   */
  const GET_BALP_EMISSION_FILTER = self::GET_BALP_FILTER;
  /**
   * Attributs par défauts pour la méthode getBalpEmission()
   * 
   * @ignore
   */
  const GET_BALP_EMISSION_ATTRIBUTES = self::GET_BALP_ATTRIBUTES;
  /**
   * Filtre pour la méthode getBalpGestionnaire()
   * 
   * @ignore
   */
  const GET_BALP_GESTIONNAIRE_FILTER = self::GET_BALP_FILTER;
  /**
   * Attributs par défauts pour la méthode getBalpGestionnaire()
   * 
   * @ignore
   */
  const GET_BALP_GESTIONNAIRE_ATTRIBUTES = self::GET_BALP_ATTRIBUTES;

  /**
   * Configuration du mapping qui surcharge la conf
   */
  const MAPPING = [
    "user_cn"                       => 'cn',
    "user_displayname"              => 'cn',
    "user_mel_reception_principal"  => 'mail',
    "user_mel_reception"            => 'mail',
    "user_mel_emission_principal"   => 'mail',
    "user_mel_emission"             => 'mail',
    "user_mel_partages"             => 'mcedelegation',                 // Liste des partages pour cette boite
    "user_mel_routage"              => 'mailhost',                      // Champ utilisé pour le routage des messages
    "user_type_entree"              => 'mcetypecompte',
    "user_street"                   => 'street',                        // Rue
    "user_postalcode"               => 'postalcode',                    // Code postal
    "user_locality"                 => 'l',                             // Ville
    "user_title"                    => 'title',                         // Titre
  ];
  
  /**
   * ***************************************************
   * DATA MAPPING
   */
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
    return $this->server_routage;
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
    return null;
  }
  /**
   * Mapping fullname field
   */
  protected function getMapFullname() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapFullname()");
    if (!isset($this->otherldapobject)) {
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    }
    else if (!isset($this->otherldapobject->uid)) {
      $this->otherldapobject->uid = $this->uid;
      $this->otherldapobject->load();
    }
    return $this->otherldapobject->fullname;
  }

  /**
   * Mapping name field
   * 
   * @param string $name          
   */
  protected function setMapName($name) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapName($name)");
    return null;
  }
  /**
   * Mapping name field
   */
  protected function getMapName() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapName()");
    if (!isset($this->otherldapobject)) {
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    }
    else if (!isset($this->otherldapobject->uid)) {
      $this->otherldapobject->uid = $this->uid;
      $this->otherldapobject->load();
    }
    return $this->otherldapobject->name;
  }

  /**
   * Mapping street field
   * 
   * @param string $street          
   */
  protected function setMapStreet($street) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapStreet($street)");
    return null;
  }
  /**
   * Mapping street field
   */
  protected function getMapStreet() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapStreet()");
    if (!isset($this->otherldapobject)) {
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    }
    else if (!isset($this->otherldapobject->uid)) {
      $this->otherldapobject->uid = $this->uid;
      $this->otherldapobject->load();
    }
    return $this->otherldapobject->street;
  }

  /**
   * Mapping locality field
   * 
   * @param string $locality          
   */
  protected function setMapLocality($locality) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapLocality($locality)");
    return null;
  }

  /**
   * Mapping locality field
   */
  protected function getMapLocality() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapLocality()");
    if (!isset($this->otherldapobject)) {
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    }
    else if (!isset($this->otherldapobject->uid)) {
      $this->otherldapobject->uid = $this->uid;
      $this->otherldapobject->load();
    }
    return $this->otherldapobject->locality;
  }

  /**
   * Mapping title field
   * 
   * @param string $title          
   */
  protected function setMapTitle($title) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapTitle($title)");
    return null;
  }

  /**
   * Mapping locality field
   */
  protected function getMapTitle() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapTitle()");
    if (!isset($this->otherldapobject)) {
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    }
    else if (!isset($this->otherldapobject->uid)) {
      $this->otherldapobject->uid = $this->uid;
      $this->otherldapobject->load();
    }
    return $this->otherldapobject->title;
  }
}
