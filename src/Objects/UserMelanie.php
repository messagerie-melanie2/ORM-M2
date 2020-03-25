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
use LibMelanie\Config;
use LibMelanie\Config\ConfigSQL;
use LibMelanie\Config\MappingMce;
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
   * Quel serveur LDAP utiliser pour la lecture des données
   * 
   * @var boolean
   */
  private $server;
  /**
   * Liste des propriétés qui ne peuvent pas être modifiées
   * 
   * @var array Valeur static
   */
  private static $unchangeableProperties = [
      'dn',
      'uid',
  ];
  /**
   * Liste des propriétés qui ne peuvent pas être modifiées
   * 
   * @var array Valeur non static
   */
  private $_unchangeableProperties;

   /**
   * Est-ce que l'objet a déjà été initialisé
   * 
   * @var boolean
   */
  private static $isInit = false;
  
  /**
   * Constructeur de la class
   * 
   * @param string $server Serveur d'annuaire a utiliser en fonction de la configuration
   * @param array $unchangeableProperties Liste des propriétés qui ne peut pas être modifiées
   * @param array $mapping Données de mapping
   */
  public function __construct($server = null, $unchangeableProperties = null, $mapping = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");
    
    // Récupération du type d'objet en fonction de la class
    $this->objectType = explode('\\', $this->get_class);
    $this->objectType = $this->objectType[count($this->objectType) - 1];

    // Init du serveur
    $this->server = $server;

    // Gestion des proprietes non modifiables
    if (isset($unchangeableProperties)) {
      $this->_unchangeableProperties = $unchangeableProperties;
    }
    else {
      $this->_unchangeableProperties = self::$unchangeableProperties;
    }

    // Gestion du mapping global
    self::Init($mapping, $server);
    
    // Gestion du mapping des clés primaires
    if (isset(MappingMce::$Primary_Keys[$this->objectType])) {
      if (is_array(MappingMce::$Primary_Keys[$this->objectType]))
        $this->primaryKeys = MappingMce::$Primary_Keys[$this->objectType];
      else
        $this->primaryKeys = [
            MappingMce::$Primary_Keys[$this->objectType]
        ];
    }
  }

  /**
   * Appel l'initialisation du mapping
   * 
   * @param array $mapping Données de mapping
   * @return boolean
   */
  private static function Init($mapping, $server) {
    if (!self::$isInit) {
      if (isset($server) && isset(Config\Ldap::$SERVERS[$server]['mapping'])) {
        $mapping = array_merge($mapping, Config\Ldap::$SERVERS[$server]['mapping']);
      }
      else if (isset(Config\Ldap::$SERVERS[Config\Ldap::$SEARCH_LDAP]['mapping'])) {
        $mapping = array_merge($mapping, Config\Ldap::$SERVERS[Config\Ldap::$SEARCH_LDAP]['mapping']);
      }
      // Traitement du mapping
      foreach ($mapping as $key => $map) {
        if (is_array($map)) {
          if (!isset($map[MappingMce::type])) {
            $mapping[$key][MappingMce::type] = MappingMce::stringLdap;
          }
        }
        else {
          $mapping[$key] = [MappingMce::name => $map, MappingMce::type => MappingMce::stringLdap];
        }
      }
      self::$isInit = MappingMce::UpdateDataMapping('UserMelanie', $mapping);
    }
    return self::$isInit;
  }
  
  /**
   * Chargement de l'objet UserMelanie
   * need: $this->uid
   * need: $this->email
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   * @param string $filter [Optionnal] Filter to load data
   * @param string $filterFromEmail [Optionnal] Filter to load data
   * 
   * @see IObjectMelanie::load()
   *
   * @return boolean isExist
   */
  public function load($attributes = null, $filter = null, $filterFromEmail = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->load()");
    // Si les clés primaires et la table ne sont pas définies, impossible de charger l'objet
    if (!isset($this->uid) && !isset($this->email)) {
      return false;
    }
    // Test si l'objet existe, pas besoin de load
    if (is_bool($this->isExist) && $this->isLoaded) {
      return $this->isExist;
    }
    // Mapping pour les champs
		$attributesmapping = [];
		if (is_array($attributes)) {
    		// Recherche l'attribut dans la conf de mapping
    		foreach ($attributes as $key) {
    			// Récupèration des données de mapping
    			if (isset(MappingMce::$Data_Mapping[$this->objectType])
    					&& isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
    				$key = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
    			}
    			$attributesmapping[] = $key;
    		}
		}
    // Récupération des données depuis le LDAP avec l'uid ou l'email
    if (isset($this->uid)) {
      $data = Ldap::GetUserInfos($this->uid, $filter, $attributesmapping, $this->server);
    } else if (isset($this->email)) {
      $data = Ldap::GetUserInfosFromEmail($this->email, $filterFromEmail, $attributesmapping, $this->server);
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
   * @param array $attributes [Optionnal] List of attributes to load
   * @param string $filter [Optionnal] Filter to load data
   * @param string $filterFromEmail [Optionnal] Filter to load data
   * 
   * @see IObjectMelanie::exists()
   */
  public function exists($attributes = null, $filter = null, $filterFromEmail = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->exists()");
    // Si les clés primaires et la table ne sont pas définies, impossible de charger l'objet
    if (!isset($this->uid) && !isset($this->email))
      return false;
    // Test si l'objet existe, pas besoin de load
    if (is_bool($this->isExist)) {
      return $this->isExist;
    }
    // Mapping pour les champs
		$attributesmapping = [];
		if (is_array($attributes)) {
    		// Recherche l'attribut dans la conf de mapping
    		foreach ($attributes as $key) {
    			// Récupèration des données de mapping
    			if (isset(MappingMce::$Data_Mapping[$this->objectType])
    					&& isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
    				$key = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
    			}
    			$attributesmapping[] = $key;
    		}
		}
    // Récupération des données depuis le LDAP avec l'uid ou l'email
    if (isset($this->uid)) {
      $data = Ldap::GetUserInfos($this->uid, $filter, $attributesmapping, $this->server);
    } else if (isset($this->email)) {
      $data = Ldap::GetUserInfosFromEmail($this->email, $filterFromEmail, $attributesmapping, $this->server);
    }
    $this->isExist = isset($data);
    return $this->isExist;
  }
  
  /**
   * Enregistrement de l'utilisateur modifié dans l'annuaire
   * 
   * @see IObjectMelanie::save()
   * @ignore
   */
  public function save() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->save()");
    $entry = $this->getEntry();
    if (!empty($entry) && isset($this->dn)) {
      $ldap = Ldap::GetInstance(\LibMelanie\Config\Ldap::$MASTER_LDAP);
      return $ldap->modify($this->dn, $entry);
    }
    return false;
  }
  
  /**
   * Not implemented
   * 
   * @see IObjectMelanie::delete()
   * @ignore
   */
  public function delete() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->delete()");
    throw new \Exception('Not implemented');
  }
  
  /**
   * Positionne les data pour le UserMelanie
   * 
   * @param array $data
   */
  public function setData($data) {
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
        if (!in_array($key, $this->unchangeableProperties)) {
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
  public function authentification($password, $master = false) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->authentification()");
    if ($master) {
      $this->server = \LibMelanie\Config\Ldap::$MASTER_LDAP;
    }
    if (isset($this->dn)) {
      return Ldap::AuthentificationDirect($this->dn, $password, $this->server);
    }
    else {
      return Ldap::Authentification($this->uid, $password, $this->server, true);
    }
  }

  /**
   * Récupère la liste des BALP accessibles à l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   * @param string $filter [Optionnal] Filter to load data
   * 
   * @return UserMelanie[] Liste d'objet UserMelanie
   */
  public function getBalp($attributes = null, $filter = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getBalpList()");
    if (!isset($this->uid)) {
      return [];
    }
    // Mapping pour les champs
		$attributesmapping = [];
		if (is_array($attributes)) {
    		// Recherche l'attribut dans la conf de mapping
    		foreach ($attributes as $key) {
    			// Récupèration des données de mapping
    			if (isset(MappingMce::$Data_Mapping[$this->objectType])
    					&& isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
    				$key = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
    			}
    			$attributesmapping[] = $key;
    		}
		}
    // Récupération des Balp depuis le LDAP
    $list = Ldap::GetUserBalPartagees($this->uid, $filter, $attributesmapping, $this->server);
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
   * @param array $attributes [Optionnal] List of attributes to load
   * @param string $filter [Optionnal] Filter to load data
   *
   * @return UserMelanie[] Liste d'objet UserMelanie
   */
  public function getBalpEmission($attributes = null, $filter = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getBalpListEmission()");
    if (!isset($this->uid)) {
      return [];
    }
    // Mapping pour les champs
		$attributesmapping = [];
		if (is_array($attributes)) {
    		// Recherche l'attribut dans la conf de mapping
    		foreach ($attributes as $key) {
    			// Récupèration des données de mapping
    			if (isset(MappingMce::$Data_Mapping[$this->objectType])
    					&& isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
    				$key = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
    			}
    			$attributesmapping[] = $key;
    		}
		}
    // Récupération des Balp depuis le LDAP
    $list = Ldap::GetUserBalEmission($this->uid, $filter, $attributesmapping, $this->server);
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
   * @param array $attributes [Optionnal] List of attributes to load
   * @param string $filter [Optionnal] Filter to load data
   *
   * @return UserMelanie[] Liste d'objet UserMelanie
   */
  public function getBalpGestionnaire($attributes = null, $filter = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getBalpListGestionnaire()");
    if (!isset($this->uid)) {
      return false;
    }
    // Mapping pour les champs
		$attributesmapping = [];
		if (is_array($attributes)) {
    		// Recherche l'attribut dans la conf de mapping
    		foreach ($attributes as $key) {
    			// Récupèration des données de mapping
    			if (isset(MappingMce::$Data_Mapping[$this->objectType])
    					&& isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
    				$key = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
    			}
    			$attributesmapping[] = $key;
    		}
		}
    // Récupération des Balp depuis le LDAP
    $list = Ldap::GetUserBalGestionnaire($this->uid, $filter, $attributesmapping, $this->server);
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
  public function getDefaultCalendar() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDefaultCalendar()");
    if (!isset($this->uid))
      return false;
    
    $query = Sql\SqlMelanieRequests::getDefaultObject;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['CalendarMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['CalendarMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['CalendarMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['CalendarMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['CalendarMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['CalendarMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    
    // Params
    $params = [
        "user_uid" => $this->uid,
        "datatree_name" => $this->uid,
        "group_uid" => DefaultConfig::CALENDAR_GROUP_UID,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    
    // Calendrier par défaut de l'utilisateur
    $calendars = Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\CalendarMelanie');
    if (isset($calendars) && is_array($calendars) && count($calendars)) {
      $calendars[0]->pdoConstruct(true);
      return $calendars[0];
    }
    return null;
  }
  
  /**
   * Récupère la liste des calendriers appartenant à l'utilisateur
   * 
   * @return CalendarMelanie[]
   */
  public function getUserCalendars() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserCalendars()");
    if (!isset($this->uid))
      return false;
    
    $query = Sql\SqlMelanieRequests::listUserObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['CalendarMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['CalendarMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['CalendarMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['CalendarMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['CalendarMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['CalendarMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    
    // Params
    $params = [
        "group_uid" => DefaultConfig::CALENDAR_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME
    ];
    
    // Liste les calendriers de l'utilisateur
    return Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\CalendarMelanie');
  }
  
  /**
   * Récupère la liste des calendriers appartenant à l'utilisateur
   * ainsi que ceux qui lui sont partagés
   * 
   * @return CalendarMelanie[]
   */
  public function getSharedCalendars() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedCalendars()");
    if (!isset($this->uid))
      return false;
    
    $query = Sql\SqlMelanieRequests::listSharedObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['CalendarMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['CalendarMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['CalendarMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['CalendarMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['CalendarMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['CalendarMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    
    // Params
    $params = [
        "group_uid" => DefaultConfig::CALENDAR_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    
    // Liste les calendriers de l'utilisateur
    return Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\CalendarMelanie');
  }
  
  // -- TASKSLIST
  /**
   * Retour la liste de tâches par défaut
   * 
   * @return TaskslistMelanie
   */
  public function getDefaultTaskslist() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDefaultTaskslist()");
    if (!isset($this->uid))
      return false;
    
    $query = Sql\SqlMelanieRequests::getDefaultObject;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['TaskslistMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['TaskslistMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['TaskslistMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['TaskslistMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['TaskslistMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['TaskslistMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    
    // Params
    $params = [
        "user_uid" => $this->uid,
        "datatree_name" => $this->uid,
        "group_uid" => DefaultConfig::TASKSLIST_GROUP_UID,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    
    // Liste de tâches par défaut de l'utilisateur
    $taskslists = Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\TaskslistMelanie');
    if (isset($taskslists) && is_array($taskslists) && count($taskslists)) {
      $taskslists[0]->pdoConstruct(true);
      return $taskslists[0];
    }
    return null;
  }
  
  /**
   * Récupère la liste des listes de tâches appartenant à l'utilisateur
   * 
   * @return TaskslistMelanie[]
   */
  public function getUserTaskslists() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserTaskslists()");
    if (!isset($this->uid))
      return false;
    
    $query = Sql\SqlMelanieRequests::listUserObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['TaskslistMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['TaskslistMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['TaskslistMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['TaskslistMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['TaskslistMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['TaskslistMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    
    // Params
    $params = [
        "group_uid" => DefaultConfig::TASKSLIST_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME
    ];
    // Liste les listes de tâches de l'utilisateur
    return Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\TaskslistMelanie');
  }
  
  /**
   * Récupère la liste des listes de tâches appartenant à l'utilisateur
   * ainsi que ceux qui lui sont partagés
   * 
   * @return TaskslistMelanie[]
   */
  public function getSharedTaskslists() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedTaskslists()");
    if (!isset($this->uid))
      return false;
    
    $query = Sql\SqlMelanieRequests::listSharedObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['TaskslistMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['TaskslistMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['TaskslistMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['TaskslistMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['TaskslistMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['TaskslistMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    
    // Params
    $params = [
        "group_uid" => DefaultConfig::TASKSLIST_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    // Liste les listes de tâches de l'utilisateur
    return Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\TaskslistMelanie');
  }
  
  // -- ADDRESSBOOK
  /**
   * Retour la liste de contacts par défaut
   * 
   * @return AddressbookMelanie
   */
  public function getDefaultAddressbook() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDefaultAddressbook()");
    if (!isset($this->uid))
      return false;
    
    $query = Sql\SqlMelanieRequests::getDefaultObject;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['AddressbookMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['AddressbookMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['AddressbookMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['AddressbookMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['AddressbookMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['AddressbookMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    
    // Params
    $params = [
        "user_uid" => $this->uid,
        "datatree_name" => $this->uid,
        "group_uid" => DefaultConfig::ADDRESSBOOK_GROUP_UID,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    
    // Liste de tâches par défaut de l'utilisateur
    $addressbooks = Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\AddressbookMelanie');
    if (isset($addressbooks) && is_array($addressbooks) && count($addressbooks)) {
      $addressbooks[0]->pdoConstruct(true);
      return $addressbooks[0];
    }
    return null;
  }
  
  /**
   * Récupère la liste des listes de contacts appartenant à l'utilisateur
   * 
   * @return AddressbookMelanie[]
   */
  public function getUserAddressbooks() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserAddressbooks()");
    if (!isset($this->uid))
      return false;
    
    $query = Sql\SqlMelanieRequests::listUserObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['AddressbookMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['AddressbookMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['AddressbookMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['AddressbookMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['AddressbookMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['AddressbookMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    
    // Params
    $params = [
        "group_uid" => DefaultConfig::ADDRESSBOOK_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME
    ];
    // Liste les listes de contacts de l'utilisateur
    return Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\AddressbookMelanie');
  }
  
  /**
   * Récupère la liste des listes de contacts appartenant à l'utilisateur
   * ainsi que ceux qui lui sont partagés
   * 
   * @return AddressbookMelanie[]
   */
  public function getSharedAddressbooks() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedAddressbooks()");
    if (!isset($this->uid))
      return false;
    
    $query = Sql\SqlMelanieRequests::listSharedObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['AddressbookMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['AddressbookMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['AddressbookMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['AddressbookMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['AddressbookMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['AddressbookMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    
    // Params
    $params = [
        "group_uid" => DefaultConfig::ADDRESSBOOK_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    // Liste les listes de contacts de l'utilisateur
    return Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\AddressbookMelanie');
  }
  
  /**
   * Recupère le timezone par défaut pour le
   * need: $this->uid
   * 
   * @deprecated
   */
  public function getTimezone() {
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
      $res = Sql\Sql::GetInstance()->executeQueryToObject($query, $params, $this);
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