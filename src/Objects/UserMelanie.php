<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * ORM Mél Copyright © 2020 Groupe Messagerie/MTES
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
namespace LibMelanie\Objects;

use LibMelanie\Sql;
use LibMelanie\Ldap\Ldap;
use LibMelanie\Config\ConfigSQL;
use LibMelanie\Config\MappingMelanie;
use LibMelanie\Log\M2Log;
use LibMelanie\Lib\MagicObject;
use LibMelanie\Interfaces\IObjectMelanie;
use LibMelanie\Config\DefaultConfig;

/**
 * Gestion de l'utilisateur Melanie2
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage ORM
 */
class UserMelanie extends MagicObject implements IObjectMelanie {
  /**
   * Timezone de l'utilisateur
   * 
   * @var string
   */
  public $timezone;
  /**
   * Est-ce que la connexion doit se faire sur le serveur maitre
   * 
   * @var boolean
   */
  private $master;
  /**
   * Liste des propriétés qui ne peuvent pas être modifiées
   * @var array
   */
  private static $unchangeableProperties = [
      'dn',
      'uid',
  ];
  
  /**
   * Constructeur de la class
   */
  function __construct() {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");
    
    // Récupération du type d'objet en fonction de la class
    $this->objectType = explode('\\', $this->get_class);
    $this->objectType = $this->objectType[count($this->objectType) - 1];
    
    // Init du master
    $this->master = false;
    
    if (isset(MappingMelanie::$Primary_Keys[$this->objectType])) {
      if (is_array(MappingMelanie::$Primary_Keys[$this->objectType]))
        $this->primaryKeys = MappingMelanie::$Primary_Keys[$this->objectType];
      else
        $this->primaryKeys = [
            MappingMelanie::$Primary_Keys[$this->objectType]
        ];
    }
  }
  
  /**
   * Chargement de l'objet UserMelanie
   * need: $this->uid
   * need: $this->email
   * 
   * @see IObjectMelanie::load()
   *
   * @return boolean isExist
   */
  function load() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->load()");
    // Si les clés primaires et la table ne sont pas définies, impossible de charger l'objet
    if (!isset($this->uid) && !isset($this->email))
      return false;
    // Test si l'objet existe, pas besoin de load
    if (is_bool($this->isExist) && $this->isLoaded) {
      return $this->isExist;
    }
    // Récupération du serveur
    $server = null;
    if ($this->master) {
      $server = \LibMelanie\Config\Ldap::$MASTER_LDAP;
    }
    // Récupération des données depuis le LDAP avec l'uid ou l'email
    if (isset($this->uid)) {
      $data = Ldap::GetUserInfos($this->uid, null, null, $server);
    } else if (isset($this->email)) {
      $data = Ldap::GetUserInfosFromEmail($this->email, null, null, $server);
    }
    
    if (isset($data)) {
      $this->setData($data);
    } else {
      $this->isExist = false;      
    }
    $this->isLoaded = true;
    return $this->isExist;
  }
  
  /**
   * Détermine si l'objet existe dans Melanie2
   * need: $this->uid
   * need: $this->email
   * 
   * @see IObjectMelanie::exists()
   */
  function exists() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->exists()");
    // Si les clés primaires et la table ne sont pas définies, impossible de charger l'objet
    if (!isset($this->uid) && !isset($this->email))
      return false;
    // Test si l'objet existe, pas besoin de load
    if (is_bool($this->isExist)) {
      return $this->isExist;
    }
    // Récupération du serveur
    $server = null;
    if ($this->master) {
      $server = \LibMelanie\Config\Ldap::$MASTER_LDAP;
    }
    // Récupération des données depuis le LDAP avec l'uid ou l'email
    if (isset($this->uid)) {
      $data = Ldap::GetUserInfos($this->uid, null, null, $server);
    } else if (isset($this->email)) {
      $data = Ldap::GetUserInfosFromEmail($this->email, null, null, $server);
    }
    $this->isExist = isset($data);
    return $this->isExist;
  }
  
  /**
   * Not implemented
   * 
   * @see IObjectMelanie::save()
   * @ignore
   */
  function save() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->save()");
    throw new \Exception('Not implemented');
//     $entry = $this->getEntry();
//     if (!empty($entry)) {
//       $ldap = Ldap::GetInstance(\LibMelanie\Config\Ldap::$MASTER_LDAP);
//       return $ldap->modify($this->dn, $entry);
//     }
//     return false;
  }
  
  /**
   * Not implemented
   * 
   * @see IObjectMelanie::delete()
   * @ignore
   */
  function delete() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->delete()");
    throw new \Exception('Not implemented');
  }
  
  /**
   * Positionne les data pour le UserMelanie
   * 
   * @param array $data
   */
  function setData($data) {
    foreach ($data as $key => $value) {
      if (!is_numeric($key)) {
        if (is_array($value)
            && isset($value['count'])) {
          unset($value['count']);
        }
        $this->data[$key] = $value;
      }
    }
    $this->isExist = true;
    $this->isLoaded = true;
    $this->initializeHasChanged();
  }
  /**
   * Récupère l'entrée générée suite au changement de valeur
   * 
   * @return array
   */
  private function getEntry() {
    $entry = [];
    foreach ($this->haschanged as $key => $changed) {
      if ($changed) {
        if (!in_array($key, self::$unchangeableProperties)) {
          $entry[$key] = $this->data[$key];
        }
      }
    }
    return $entry;
  }
  
  // -- LDAP
  /**
   * Authentification sur le serveur LDAP
   *
   * @param string $password
   * @param boolean $master Utiliser le serveur maitre (nécessaire pour faire des modifications)
   * @return boolean
   */
  function authentification($password, $master = false) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->authentification()");
    $this->master = $master;
    // Récupération du serveur
    $server = null;
    if ($this->master) {
      $server = \LibMelanie\Config\Ldap::$MASTER_LDAP;
    }
    return Ldap::Authentification($this->uid, $password, $server, true);
  }
  /**
   * Récupère la liste des BALP accessibles à l'utilisateur
   * 
   * @return UserMelanie[] Liste d'objet UserMelanie
   */
  function getBalp() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getBalpList()");
    if (!isset($this->uid))
      return [];
    
    // Récupération du serveur
    $server = null;
    if ($this->master) {
      $server = \LibMelanie\Config\Ldap::$MASTER_LDAP;
    }
    // Récupération des Balp depuis le LDAP
    $list = Ldap::GetUserBalPartagees($this->uid, null, null, $server);
    $balp = [];
    if (isset($list)) {
      // Parcours la list des balp pour générer les objet UserMelanie
      foreach ($list as $data) {
        if (is_array($data)) {
          $user = new UserMelanie();
          $user->setData($data);
          $balp[$user->uid] = $user;
        }        
      }
    }
   
    return $balp;
  }
  /**
   * Récupère la liste des BALP accessibles au moins en émission à l'utilisateur
   *
   * @return UserMelanie[] Liste d'objet UserMelanie
   */
  function getBalpEmission() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getBalpListEmission()");
    if (!isset($this->uid))
      return [];
    
    // Récupération du serveur
    $server = null;
    if ($this->master) {
      $server = \LibMelanie\Config\Ldap::$MASTER_LDAP;
    }
    // Récupération des Balp depuis le LDAP
    $list = Ldap::GetUserBalEmission($this->uid, null, null, $server);
    $balp = [];
    if (isset($list)) {
      // Parcours la list des balp pour générer les objet UserMelanie
      foreach ($list as $data) {
        if (is_array($data)) {
          $user = new UserMelanie();
          $user->setData($data);
          $balp[$user->uid] = $user;
        }
      }
    }
    
    return $balp;
  }
  /**
   * Récupère la liste des BALP accessibles en gestionnaire à l'utilisateur
   *
   * @return UserMelanie[] Liste d'objet UserMelanie
   */
  function getBalpGestionnaire() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getBalpListGestionnaire()");
    if (!isset($this->uid))
      return false;
    
    // Récupération du serveur
    $server = null;
    if ($this->master) {
      $server = \LibMelanie\Config\Ldap::$MASTER_LDAP;
    }
    // Récupération des Balp depuis le LDAP
    $list = Ldap::GetUserBalGestionnaire($this->uid, null, null, $server);
    $balp = [];
    if (isset($list)) {
      // Parcours la list des balp pour générer les objet UserMelanie
      foreach ($list as $data) {
        if (is_array($data)) {
          $user = new UserMelanie();
          $user->setData($data);
          $balp[$user->uid] = $user;
        }
      }
    }
    
    return $balp;
  }
  
  // -- CALENDAR
  /**
   * Retour le calendrier par défaut
   * 
   * @return CalendarMelanie
   */
  function getDefaultCalendar() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDefaultCalendar()");
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    if (!isset($this->uid))
      return false;
    
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    
    $query = Sql\SqlMelanieRequests::getDefaultObject;
    // Replace name
    $query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['CalendarMelanie']['owner'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['CalendarMelanie']['id'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['CalendarMelanie']['ctag'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['CalendarMelanie']['synctoken'][MappingMelanie::name], $query);
    $query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['CalendarMelanie']['name'][MappingMelanie::name], $query);
    $query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['CalendarMelanie']['perm'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);
    
    // Params
    $params = [
        "group_uid" => DefaultConfig::CALENDAR_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "pref_scope" => DefaultConfig::CALENDAR_PREF_SCOPE,
        "pref_name" => DefaultConfig::CALENDAR_PREF_DEFAULT_NAME,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    
    // Calendrier par défaut de l'utilisateur
    $calendar = Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\CalendarMelanie');
    if (isset($calendar) && is_array($calendar) && count($calendar)) {
      $calendar[0]->pdoConstruct(true);
      return $calendar[0];
    } else {
      $calendar = $this->getUserCalendars();
      return isset($calendar[0]) ? $calendar[0] : null;
    }
    return false;
  }
  
  /**
   * Récupère la liste des calendriers appartenant à l'utilisateur
   * 
   * @return CalendarMelanie[]
   */
  function getUserCalendars() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserCalendars()");
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    if (!isset($this->uid))
      return false;
    
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    
    $query = Sql\SqlMelanieRequests::listUserObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['CalendarMelanie']['owner'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['CalendarMelanie']['id'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['CalendarMelanie']['ctag'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['CalendarMelanie']['synctoken'][MappingMelanie::name], $query);
    $query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['CalendarMelanie']['name'][MappingMelanie::name], $query);
    $query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['CalendarMelanie']['perm'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);
    
    // Params
    $params = [
        "group_uid" => DefaultConfig::CALENDAR_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME
    ];
    
    // Liste les calendriers de l'utilisateur
    return Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\CalendarMelanie');
  }
  
  /**
   * Récupère la liste des calendriers appartenant à l'utilisateur
   * ainsi que ceux qui lui sont partagés
   * 
   * @return CalendarMelanie[]
   */
  function getSharedCalendars() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedCalendars()");
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    if (!isset($this->uid))
      return false;
    
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    
    $query = Sql\SqlMelanieRequests::listSharedObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['CalendarMelanie']['owner'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['CalendarMelanie']['id'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['CalendarMelanie']['ctag'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['CalendarMelanie']['synctoken'][MappingMelanie::name], $query);
    $query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['CalendarMelanie']['name'][MappingMelanie::name], $query);
    $query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['CalendarMelanie']['perm'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);
    
    // Params
    $params = [
        "group_uid" => DefaultConfig::CALENDAR_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    
    // Liste les calendriers de l'utilisateur
    return Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\CalendarMelanie');
  }
  
  // -- TASKSLIST
  /**
   * Retour la liste de tâches par défaut
   * 
   * @return TaskslistMelanie
   */
  function getDefaultTaskslist() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDefaultTaskslist()");
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    if (!isset($this->uid))
      return false;
    
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    
    $query = Sql\SqlMelanieRequests::getDefaultObject;
    // Replace name
    $query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['owner'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['id'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['ctag'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['synctoken'][MappingMelanie::name], $query);
    $query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['name'][MappingMelanie::name], $query);
    $query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['perm'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);
    
    // Params
    $params = [
        "group_uid" => DefaultConfig::TASKSLIST_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "pref_scope" => DefaultConfig::TASKSLIST_PREF_SCOPE,
        "pref_name" => DefaultConfig::TASKSLIST_PREF_DEFAULT_NAME,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    
    // Liste de tâches par défaut de l'utilisateur
    $tasklist = Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\TaskslistMelanie');
    if (isset($tasklist) && is_array($tasklist) && count($tasklist)) {
      $tasklist[0]->pdoConstruct(true);
      return $tasklist[0];
    } else {
      $tasklist = $this->getUserTaskslists();
      return isset($tasklist[0]) ? $tasklist[0] : null;
    }
    return false;
  }
  
  /**
   * Récupère la liste des listes de tâches appartenant à l'utilisateur
   * 
   * @return TaskslistMelanie[]
   */
  function getUserTaskslists() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserTaskslists()");
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    if (!isset($this->uid))
      return false;
    
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    
    $query = Sql\SqlMelanieRequests::listUserObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['owner'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['id'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['ctag'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['synctoken'][MappingMelanie::name], $query);
    $query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['name'][MappingMelanie::name], $query);
    $query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['perm'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);
    
    // Params
    $params = [
        "group_uid" => DefaultConfig::TASKSLIST_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME
    ];
    // Liste les listes de tâches de l'utilisateur
    return Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\TaskslistMelanie');
  }
  
  /**
   * Récupère la liste des listes de tâches appartenant à l'utilisateur
   * ainsi que ceux qui lui sont partagés
   * 
   * @return TaskslistMelanie[]
   */
  function getSharedTaskslists() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedTaskslists()");
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    if (!isset($this->uid))
      return false;
    
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    
    $query = Sql\SqlMelanieRequests::listSharedObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['owner'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['id'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['ctag'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['synctoken'][MappingMelanie::name], $query);
    $query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['name'][MappingMelanie::name], $query);
    $query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['TaskslistMelanie']['perm'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);
    
    // Params
    $params = [
        "group_uid" => DefaultConfig::TASKSLIST_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    // Liste les listes de tâches de l'utilisateur
    return Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\TaskslistMelanie');
  }
  
  // -- ADDRESSBOOK
  /**
   * Retour la liste de contacts par défaut
   * 
   * @return AddressbookMelanie
   */
  function getDefaultAddressbook() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDefaultAddressbook()");
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    if (!isset($this->uid))
      return false;
    
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    
    $query = Sql\SqlMelanieRequests::getDefaultObject;
    // Replace name
    $query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['owner'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['id'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['ctag'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['synctoken'][MappingMelanie::name], $query);
    $query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['name'][MappingMelanie::name], $query);
    $query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['perm'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);
    
    // Params
    $params = [
        "group_uid" => DefaultConfig::ADDRESSBOOK_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "pref_scope" => DefaultConfig::ADDRESSBOOK_PREF_SCOPE,
        "pref_name" => DefaultConfig::ADDRESSBOOK_PREF_DEFAULT_NAME,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    
    // Liste de tâches par défaut de l'utilisateur
    $addressbook = Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\AddressbookMelanie');
    if (isset($addressbook) && is_array($addressbook) && count($addressbook)) {
      $addressbook[0]->pdoConstruct(true);
      return $addressbook[0];
    } else {
      $addressbook = $this->getUserAddressbooks();
      return isset($addressbook[0]) ? $addressbook[0] : null;
    }
    return false;
  }
  
  /**
   * Récupère la liste des listes de contacts appartenant à l'utilisateur
   * 
   * @return AddressbookMelanie[]
   */
  function getUserAddressbooks() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserAddressbooks()");
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    if (!isset($this->uid))
      return false;
    
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    
    $query = Sql\SqlMelanieRequests::listUserObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['owner'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['id'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['ctag'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['synctoken'][MappingMelanie::name], $query);
    $query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['name'][MappingMelanie::name], $query);
    $query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['perm'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);
    
    // Params
    $params = [
        "group_uid" => DefaultConfig::ADDRESSBOOK_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME
    ];
    // Liste les listes de contacts de l'utilisateur
    return Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\AddressbookMelanie');
  }
  
  /**
   * Récupère la liste des listes de contacts appartenant à l'utilisateur
   * ainsi que ceux qui lui sont partagés
   * 
   * @return AddressbookMelanie[]
   */
  function getSharedAddressbooks() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedAddressbooks()");
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    if (!isset($this->uid))
      return false;
    
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    
    $query = Sql\SqlMelanieRequests::listSharedObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['owner'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_name}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['id'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['ctag'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['synctoken'][MappingMelanie::name], $query);
    $query = str_replace('{attribute_value}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['name'][MappingMelanie::name], $query);
    $query = str_replace('{perm_object}', MappingMelanie::$Data_Mapping['AddressbookMelanie']['perm'][MappingMelanie::name], $query);
    $query = str_replace('{datatree_id}', MappingMelanie::$Data_Mapping['CalendarMelanie']['object_id'][MappingMelanie::name], $query);
    
    // Params
    $params = [
        "group_uid" => DefaultConfig::ADDRESSBOOK_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    // Liste les listes de contacts de l'utilisateur
    return Sql\DBMelanie::ExecuteQuery($query, $params, 'LibMelanie\\Objects\\AddressbookMelanie');
  }
  
  /**
   * Recupère le timezone par défaut pour le
   * need: $this->uid
   * 
   * @deprecated
   */
  function getTimezone() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getTimezone()");
    // Initialisation du backend SQL
    Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);
    if (!isset($this->uid))
      return DefaultConfig::CALENDAR_DEFAULT_TIMEZONE;
    
    if (!isset($this->timezone)) {
      // Replace name
      $query = str_replace('{pref_name}', 'timezone', Sql\SqlMelanieRequests::getUserPref);
      
      // Params
      $params = [
          "user_uid" => $this->uid,
          "pref_scope" => DefaultConfig::PREF_SCOPE,
          "pref_name" => DefaultConfig::TZ_PREF_NAME
      ];
      
      // Récupération du timezone
      $res = Sql\DBMelanie::ExecuteQueryToObject($query, $params, $this);
      // Test si le timezone est valide en PHP
      try {
        $tz = new \DateTimeZone($this->timezone);
      } catch ( \Exception $ex ) {
        $this->timezone = DefaultConfig::CALENDAR_DEFAULT_TIMEZONE;
      }
      M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getTimezone() this->timezone: " . $this->timezone);
    }
    return $this->timezone;
  }
}