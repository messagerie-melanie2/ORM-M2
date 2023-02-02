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
namespace LibMelanie\Api\Defaut;

use LibMelanie\Lib\MceObject;
use LibMelanie\Objects\GroupMelanie;
use LibMelanie\Log\M2Log;

/**
 * Classe groupe LDAP par defaut
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $dn DN du groupe l'annuaire 
 * @property string $fullname Nom complet du groupe LDAP
 * @property string $type Type de groupe (voir Mce\Users\Type::*)
 * @property string $email Adresse email principale de l'utilisateur
 * @property array $email_list Liste de toutes les adresses email de l'utilisateur
 * @property User[] $members Liste des membres appartenant au groupe
 * @property array $owners Liste des propriétaires du groupe LDAP
 * 
 * @method bool save() Enregistrement du groupe dans l'annuaire
 */
abstract class Group extends MceObject {

  /**
   * Nom de la conf serveur utilisé pour le LDAP
   * 
   * @var string
   * @ignore
   */
  protected $_server;

  /**
   * Est-ce que l'objet est déjà chargé depuis l'annuaire ?
   * 
   * @var boolean
   * @ignore
   */
  protected $_isLoaded;

  /**
   * Est-ce que l'objet existe dans l'annuaire ?
   * 
   * @var boolean
   * @ignore
   */
  protected $_isExist;

  /**
   * Liste des membres du groupe
   * 
   * @var User[]
   * @ignore
   */
  protected $_members;

  /**
   * Liste des propriétés à sérialiser pour le cache
   */
  protected $serializedProperties = [
    '_server',
    '_isLoaded',
    '_isExist',
  ];

  /**
   * Configuration de l'item name associé à l'objet courant
   * 
   * @var string
   * @ignore
   */
  protected $_itemName;

  // **** Configuration des filtres et des attributs par défaut
  /**
   * Filtre pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_FILTER = null;

  /**
   * Filtre pour la méthode load() avec un email
   * 
   * @ignore
   */
  const LOAD_FROM_EMAIL_FILTER = null;

  /**
   * Attributs par défauts pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_ATTRIBUTES = ['dn', 'fullname', 'email', 'members', 'owners'];

  /**
   * Configuration du mapping qui surcharge la conf
   */
  const MAPPING = [];

  /**
   * Constructeur de l'objet
   * 
   * @param string $server Serveur d'annuaire a utiliser en fonction de la configuration
   * @param string $itemName Nom de l'objet associé dans la configuration LDAP
   */
  public function __construct($server = null, $itemName = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");

    // Récupération de l'itemName
    $this->_itemName = $itemName;

    // Définition de l'utilisateur
    $this->objectmelanie = new GroupMelanie($server, null, static::MAPPING, $this->_itemName);

    $this->_server = $server;
  }

  /**
   * Est-ce que l'utilisateur fait parti des propriétaires du groupes ?
   * 
   * @param User $user
   * 
   * @return boolean
   */
  public function isOwner($user) {
    return in_array($user->dn, $this->objectmelanie->owner);
  }

  /**
   * ***************************************************
   * METHOD MAPPING
   */

  /**
   * Authentification sur le serveur LDAP
   *
   * @param string $password
   * @param boolean $master Utiliser le serveur maitre (nécessaire pour faire des modifications)
   * @param string $user_dn DN de l'utilisateur si ce n'est pas le courant a utiliser
   * @param boolean $gssapi Utiliser une authentification GSSAPI sans mot de passe
   * @param string $itemName Nom de l'objet associé dans la configuration LDAP
   * 
   * @return boolean
   */
  public function authentification($password, $master = false, $user_dn = null, $gssapi = false, $itemName = null) {
    if ($master) {
      $this->_server = \LibMelanie\Config\Ldap::$MASTER_LDAP;
    }

    // Récupération de l'itemName
    if (isset($itemName)) {
      $this->_itemName = $itemName;
    }
    
    return $this->objectmelanie->authentification($password, $master, $user_dn, $gssapi, $this->_itemName);
  }

  /**
   * Charge les données du groupe depuis l'annuaire (en fonction du dn)
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   * 
   * @return boolean true si l'objet existe dans l'annuaire false sinon
   */
  public function load($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->load() [" . $this->_server . "]");
    if (isset($attributes) && is_string($attributes)) {
      $attributes = [$attributes];
    }
    if (isset($this->_isLoaded) && !isset($attributes)) {
      return $this->_isLoaded;
    }
    $useIsLoaded = !isset($attributes);
    if (!isset($attributes)) {
      $attributes = static::LOAD_ATTRIBUTES;
    }
    $filter = static::LOAD_FILTER;
    $filterFromEmail = static::LOAD_FROM_EMAIL_FILTER;
    if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server])) {
      if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_list_filter'])) {
        $filter = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_list_filter'];
      }
      if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_list_from_email_filter'])) {
        $filterFromEmail = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_list_from_email_filter'];
      }
    }
    $ret = $this->objectmelanie->load($attributes, $filter, $filterFromEmail);
    if ($useIsLoaded) {
      $this->_isLoaded = $ret;
    }
    return $ret;
  }
  /**
   * Charge les données du groupe depuis l'annuaire (en fonction du dn)
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   * 
   * @return boolean true si l'objet existe dans l'annuaire false sinon
   */
  public function exists($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->exists() [" . $this->_server . "]");
    if (!isset($this->_isExist)) {
      if (!isset($attributes)) {
        $attributes = static::LOAD_ATTRIBUTES;
      }
      $filter = static::LOAD_FILTER;
      if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server])) {
        if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_list_filter'])) {
          $filter = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_list_filter'];
        }
      }
      $this->_isExist = $this->objectmelanie->exists($attributes, $filter);
    }
    return $this->_isExist;
  }

  /**
   * Mapping members field
   * 
   * @return User[] Liste d'objets User
   */
  protected function getMapMembers() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapMembers()");
    if (!isset($this->_members)) {
      $this->_members = [];
      $classUser = $this->__getNamespace() . '\\User';
      $members = $this->objectmelanie->members;
      if (is_array($members)) {
        foreach ($members as $member) {
          $_member = new $classUser();
          $_member->uid = $member;
          $this->_members[$member] = $_member;
        }
      }
    }
    return $this->_members;
  }

  /**
   * Mapping members field
   * 
   * @param User[] Liste d'objets User  
   */
  function setMapMembers($members) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapMembers()");
    $this->_members = $members;
    $_members = [];
    foreach ($this->_members as $member) {
      $_members[] = $member->uid;
    }
    $this->objectmelanie->members = $_members;
  }
}