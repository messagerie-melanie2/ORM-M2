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
use LibMelanie\Objects\UserMelanie;
use LibMelanie\Log\M2Log;
use LibMelanie\Api\Defaut\UserPrefs;
use LibMelanie\Api\Defaut\Addressbook;
use LibMelanie\Api\Defaut\Calendar;
use LibMelanie\Api\Defaut\Taskslist;
use LibMelanie\Api\Defaut\Users\Share;
use LibMelanie\Config\Config;
use LibMelanie\Config\MappingMce;

/**
 * Classe utilisateur par defaut
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
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
 * 
 * @property Share[] $shares Liste des partages de la boite
 * @property-read array $supported_shares Liste des droits supportés par cette boite
 * 
 * @property-read boolean $is_synchronisation_enable Est-ce que la synchronisation est activée pour l'utilisateur ?
 * @property-read string $synchronisation_profile Profil de synchronisation positionné pour l'utilisateur (STANDARD ou SENSIBLE)
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
 * @method bool save() Enregistrement de l'utilisateur dans l'annuaire
 */
abstract class User extends MceObject {
  /**
   * Objet de partage associé a l'utilisateur courant si nécessaire
   * 
   * @var ObjectShare
   */
  protected $objectshare;

  /**
   * UserMelanie provenant d'un autre annuaire
   * 
   * @var UserMelanie
   */
  protected $otherldapobject;

  /**
   * Liste des workspaces de l'utilisateur
   * 
   * @var Workspace[]
   * @ignore
   */
  protected $_userWorkspaces;
  /**
   * Liste de tous les workspaces auquel l'utilisateur a accés
   * 
   * @var Workspace[]
   * @ignore
   */
  protected $_sharedWorkspaces;

  /**
   * Liste des news que l'utilisateur peut consulter
   * 
   * @var News\News[]
   * @ignore
   */
  protected $_userNews;
  /**
   * Liste des rss que l'utilisateur peut consulter
   * 
   * @var News\Rss[]
   * @ignore
   */
  protected $_userRss;
  /**
   * Liste des droits de l'utilisateur sur les news et rss
   * 
   * @var News\NewsShare[]
   * @ignore
   */
  protected $_userNewsShares;

  /**
   * Calendrier par défaut de l'utilisateur
   * 
   * @var Calendar
   * @ignore
   */
  protected $_defaultCalendar;
  /**
   * Liste des calendriers de l'utilisateur
   * 
   * @var Calendar[]
   * @ignore
   */
  protected $_userCalendars;
  /**
   * Liste de tous les calendriers auquel l'utilisateur a accés
   * 
   * @var Calendar[]
   * @ignore
   */
  protected $_sharedCalendars;

  /**
   * Carnet d'adresses par défaut de l'utilisateur
   * 
   * @var Addressbook
   */
  protected $_defaultAddressbook;
  /**
   * Liste des carnets d'adresses de l'utilisateur
   * 
   * @var Addressbook
   * @ignore
   */
  protected $_userAddressbooks;
  /**
   * Liste de tous les carnets d'adresses auquel l'utilisateur a accés
   * 
   * @var Addressbook
   * @ignore
   */
  protected $_sharedAddressbooks;

  /**
   * Liste de tâches par défaut de l'utilisateur
   * 
   * @var Taskslist
   * @ignore
   */
  protected $_defaultTaskslist;
  /**
   * Liste des listes de tâches de l'utilisateur
   * 
   * @var Taskslist
   * @ignore
   */
  protected $_userTaskslists;
  /**
   * Liste de toutes les listes de tâches auquel l'utilisateur a accés
   * 
   * @var Taskslist
   * @ignore
   */
  protected $_sharedTaskslists;

  /**
   * Liste des objets partagés accessibles à l'utilisateur
   * 
   * @var ObjectShare[]
   * @ignore
   */
  protected $_objectsShared;
  /**
   * Liste des objets partagés accessibles en emission à l'utilisateur
   * 
   * @var ObjectShare[]
   * @ignore
   */
  protected $_objectsSharedEmission;
  /**
   * Liste des objets partagés accessibles en gestionnaire à l'utilisateur
   * 
   * @var ObjectShare[]
   * @ignore
   */
  protected $_objectsSharedGestionnaire;

  /**
   * Liste des boites partagées accessibles à l'utilisateur
   * 
   * @var User[]
   * @ignore
   */
  protected $_shared;
  /**
   * Liste des boites partagées accessibles en emission à l'utilisateur
   * 
   * @var User[]
   * @ignore
   */
  protected $_sharedEmission;
  /**
   * Liste des boites partagées accessibles en gestionnaire à l'utilisateur
   * 
   * @var User[]
   * @ignore
   */
  protected $_sharedGestionnaire;

  /**
   * Liste des partages pour l'objet courant
   * 
   * @var Share[]
   * @ignore
   */
  protected $_shares;
  /**
   * Liste des groupes pour l'objet courant
   * 
   * @var Group[]
   * @ignore
   */
  protected $_lists;

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
   * Liste des preferences de l'utilisateur
   * 
   * @var UserPrefs[]
   * @ignore
   */
  protected $_preferences;

  /**
   * Liste des propriétés à sérialiser pour le cache
   */
  protected $serializedProperties = [
    'objectshare',
    'otherldapobject',
    '_userWorkspaces',
    '_sharedWorkspaces',
    '_defaultCalendar',
    '_userCalendars',
    '_sharedCalendars',
    '_defaultAddressbook',
    '_userAddressbooks',
    '_sharedAddressbooks',
    '_defaultTaskslist',
    '_userTaskslists',
    '_sharedTaskslists',
    '_objectsShared',
    '_objectsSharedEmission',
    '_objectsSharedGestionnaire',
    '_shares',
    '_lists',
    '_server',
    '_isLoaded',
    '_isExist',
    '_preferences',
  ];

  /**
   * Droits de lecture sur la boite
   */
  protected static $_sharesRead = ['G', 'C', 'E', 'L'];

  /**
   * Droits d'écriture sur la boite
   */
  protected static $_sharesWrite = ['G', 'C', 'E'];

  /**
   * Droits d'envoi de messages sur la boite
   */
  protected static $_sharesSend = ['G', 'C']; 

  /**
   * Droits de niveau administrateur ou gestionnaire sur la boite
   */
  protected static $_sharesAdmin = ['G'];

  /**
   * Configuration de l'item name associé à l'objet courant
   * 
   * @var string
   * @ignore
   */
  protected $_itemName;

  // **** Constantes pour les preferences
  /**
   * Scope de preference par defaut pour l'utilisateur
   */
  const PREF_SCOPE_DEFAULT = \LibMelanie\Config\ConfigMelanie::GENERAL_PREF_SCOPE;
  /**
   * Scope de preference pour les calendriers de l'utilisateur
   */
  const PREF_SCOPE_CALENDAR = \LibMelanie\Config\ConfigMelanie::CALENDAR_PREF_SCOPE;
  /**
   * Scope de preference pour les carnets d'adresses de l'utilisateur
   */
  const PREF_SCOPE_ADDRESSBOOK = \LibMelanie\Config\ConfigMelanie::ADDRESSBOOK_PREF_SCOPE;
  /**
   * Scope de preference pour les listes de taches de l'utilisateur
   */
  const PREF_SCOPE_TASKSLIST = \LibMelanie\Config\ConfigMelanie::TASKSLIST_PREF_SCOPE;

  /**
   * Droit de lecture
   */
  const RIGHT_READ = 'read';
  /**
   * Droit d'écriture
   */
  const RIGHT_WRITE = 'write';
  /**
   * Droit d'émission
   */
  const RIGHT_SEND = 'send';
  /**
   * Droit de gestion
   */
  const RIGHT_ADMIN = 'admin';

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
  const LOAD_ATTRIBUTES = ['fullname', 'uid', 'name', 'email', 'email_list', 'email_send', 'email_send_list', 'server_routage', 'shares', 'type'];
  /**
   * Filtre pour la méthode load() si c'est un objet de partage
   * 
   * @ignore
   */
  const LOAD_OBJECTSHARE_FILTER = null;
  /**
   * Filtre pour la méthode load() avec un email si c'est un object de partage
   * 
   * @ignore
   */
  const LOAD_OBJECTSHARE_FROM_EMAIL_FILTER = null;
  /**
   * Attributs par défauts pour la méthode load() si c'est un objet de partage
   * 
   * @ignore
   */
  const LOAD_OBJECTSHARE_ATTRIBUTES = ['fullname', 'uid', 'email_send', 'email_send_list', 'shares'];
  /**
   * Filtre pour la méthode getBalp()
   * 
   * @ignore
   */
  const GET_BALP_FILTER = null;
  /**
   * Attributs par défauts pour la méthode getBalp()
   * 
   * @ignore
   */
  const GET_BALP_ATTRIBUTES = ['fullname', 'email_send', 'email_send_list', 'uid', 'shares'];
  /**
   * Filtre pour la méthode getBalpEmission()
   * 
   * @ignore
   */
  const GET_BALP_EMISSION_FILTER = null;
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
  const GET_BALP_GESTIONNAIRE_FILTER = null;
  /**
   * Attributs par défauts pour la méthode getBalpGestionnaire()
   * 
   * @ignore
   */
  const GET_BALP_GESTIONNAIRE_ATTRIBUTES = self::GET_BALP_ATTRIBUTES;
  /**
   * Filtre pour la méthode getGroups()
   * 
   * @ignore
   */
  const GET_GROUPS_FILTER = null;
  /**
   * Attributs par défauts pour la méthode getGroups()
   * 
   * @ignore
   */
  const GET_GROUPS_ATTRIBUTES = ['dn','fullname','type','email','members'];
  /**
   * Filtre pour la méthode getGroupsIsMember()
   * 
   * @ignore
   */
  const GET_GROUPS_IS_MEMBER_FILTER = null;
  /**
   * Attributs par défauts pour la méthode getGroupsIsMember()
   * 
   * @ignore
   */
  const GET_GROUPS_IS_MEMBER_ATTRIBUTES = self::GET_GROUPS_ATTRIBUTES;
  /**
   * Filtre pour la méthode getListsIsMember()
   * 
   * @ignore
   */
  const GET_LISTS_IS_MEMBER_FILTER = null;
  /**
   * Attributs par défauts pour la méthode getListsIsMember()
   * 
   * @ignore
   */
  const GET_LISTS_IS_MEMBER_ATTRIBUTES = self::GET_GROUPS_ATTRIBUTES;

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
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct($server)");

    // Récupération de l'itemName
    $this->_itemName = $itemName;

    // Définition de l'utilisateur
    $this->objectmelanie = new UserMelanie($server, null, static::MAPPING, $this->_itemName);
    // Gestion d'un second serveur d'annuaire dans le cas ou les informations sont répartis
    if (isset(\LibMelanie\Config\Ldap::$OTHER_LDAP)) {
      $this->otherldapobject = new UserMelanie(\LibMelanie\Config\Ldap::$OTHER_LDAP, null, static::MAPPING, $this->_itemName);
    }
    $this->_server = $server ?: \LibMelanie\Config\Ldap::$SEARCH_LDAP;
  }

  /**
	 * Récupère le délimiteur d'un objet de partage
	 * 
	 * @return string ObjectShare::DELIMITER
	 */
	protected function getObjectShareDelimiter() {
    $class = $this->__getNamespace() . '\\ObjectShare';
		return $class::DELIMITER;
	}
   
  /**
   * ***************************************************
   * METHOD MAPPING
   */
  /**
   * Enregistrement de l'objet
   * Nettoie le cache du user
   * 
   * @return null si erreur, boolean sinon (true insert, false update)
   */
  public function save() {
    $ret = $this->objectmelanie->save();
    $this->executeCache();
    return $ret;
  }

  /**
   * Suppression de l'objet
   * Nettoie le cache du user
   * 
   * @return boolean
   */
  public function delete() {
    $ret = $this->objectmelanie->delete();
    $this->executeCache();
    return $ret;
  }

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
  public function authentification($password = null, $master = false, $user_dn = null, $gssapi = false, $itemName = null) {
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
   * Charge les données de l'utilisateur depuis l'annuaire (en fonction de l'uid ou l'email)
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
    // MANTIS 0006995: [User] Permettre un load() différent sur un objet de partage
    if ($this->getMapIs_objectshare()) {
      if (!isset($attributes)) {
        $attributes = static::LOAD_OBJECTSHARE_ATTRIBUTES;
      }
      $filter = static::LOAD_OBJECTSHARE_FILTER;
      $filterFromEmail = static::LOAD_OBJECTSHARE_FROM_EMAIL_FILTER;
    }
    else {
      if (!isset($attributes)) {
        $attributes = static::LOAD_ATTRIBUTES;
      }
      $filter = static::LOAD_FILTER;
      $filterFromEmail = static::LOAD_FROM_EMAIL_FILTER;
    }
    if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server])) {
      if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_infos_filter'])) {
        $filter = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_infos_filter'];
      }
      if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_infos_from_email_filter'])) {
        $filterFromEmail = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_infos_from_email_filter'];
      }
    }
    $ret = $this->objectmelanie->load($attributes, $filter, $filterFromEmail);
    if ($useIsLoaded) {
      $this->_isLoaded = $ret;
    }
    $this->executeCache();
    return $ret;
  }
  /**
   * Est-ce que l'utilisateur existe dans l'annuaire (en fonction de l'uid ou l'email)
   * Effectue un load cette méthode a donc peu d'intéret dans cet objet
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
      $filterFromEmail = static::LOAD_FROM_EMAIL_FILTER;
      if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server])) {
        if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_infos_filter'])) {
          $filter = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_infos_filter'];
        }
        if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_infos_from_email_filter'])) {
          $filterFromEmail = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_infos_from_email_filter'];
        }
      }
      $this->_isExist = $this->objectmelanie->exists($attributes, $filter, $filterFromEmail);
    }
    return $this->_isExist;
  }

  /**
   * Est-ce que l'utilisateur a les droits demandés sur cette boite
   * 
   * @param string $username
   * @param string $right Voir User::RIGHT_*
   * 
   * @return boolean
   */
  public function asRight($username, $right) {
    foreach ($this->getMapShares() as $share) {
      if ($share->user == $username) {
        switch ($right) {
          case self::RIGHT_ADMIN:
            return in_array($share->type, static::$_sharesAdmin);
            break;
          case self::RIGHT_SEND:
            return in_array($share->type, static::$_sharesSend);
            break;
          case self::RIGHT_WRITE:
            return in_array($share->type, static::$_sharesWrite);
            break;
          case self::RIGHT_READ:
            return in_array($share->type, static::$_sharesRead);
            break;
        }
      }
    }
    return false;
  }

  /**
   * Récupère la liste des objets de partage accessibles à l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return ObjectShare[] Liste d'objets
   */
  public function getObjectsShared($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getObjectsShared() [" . $this->_server . "]");
    if (!isset($this->_objectsShared)) {
      if (isset($attributes) && is_string($attributes)) {
        $attributes = [$attributes];
      }
      if (!isset($attributes)) {
        $attributes = static::GET_BALP_ATTRIBUTES;
      }
      $filter = static::GET_BALP_FILTER;
      if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]) 
          && isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_bal_partagees_filter'])) {
        $filter = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_bal_partagees_filter'];
      }
      $list = $this->objectmelanie->getBalp($attributes, $filter);
      $this->_objectsShared = [];
      $class = $this->__getNamespace() . '\\ObjectShare';
      foreach ($list as $key => $object) {
        $this->_objectsShared[$key] = new $class($this->_server, $this->_itemName);
        $this->_objectsShared[$key]->setObjectMelanie($object);
      }
      $this->executeCache();
    }
    return $this->_objectsShared;
  }

  /**
   * Récupère la liste des boites partagées à l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return User[] Liste de boites
   */
  public function getShared($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getShared() [" . $this->_server . "]");
    if (!isset($this->_shared)) {
      if (isset($attributes) && is_string($attributes)) {
        $attributes = [$attributes];
      }
      if (!isset($attributes)) {
        $attributes = static::GET_BALP_ATTRIBUTES;
      }
      $filter = static::GET_BALP_FILTER;
      if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]) 
          && isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_bal_partagees_filter'])) {
        $filter = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_bal_partagees_filter'];
      }
      $list = $this->objectmelanie->getBalp($attributes, $filter);
      $this->_shared = [];
      foreach ($list as $key => $object) {
        $this->_shared[$key] = new static($this->_server, $this->_itemName);
        $this->_shared[$key]->setObjectMelanie($object);
      }
      $this->executeCache();
    }
    return $this->_shared;
  }

  /**
   * Récupère la liste des objets de partage accessibles au moins en émission à l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return ObjectShare[] Liste d'objets
   */
  public function getObjectsSharedEmission($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getObjectsSharedEmission() [" . $this->_server . "]");
    if (!isset($this->_objectsSharedEmission)) {
      if (isset($attributes) && is_string($attributes)) {
        $attributes = [$attributes];
      }
      if (!isset($attributes)) {
        $attributes = static::GET_BALP_EMISSION_ATTRIBUTES;
      }
      $filter = static::GET_BALP_EMISSION_FILTER;
      if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]) 
          && isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_bal_emission_filter'])) {
        $filter = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_bal_emission_filter'];
      }
      $list = $this->objectmelanie->getBalpEmission($attributes, $filter);
      $this->_objectsSharedEmission = [];
      $class = $this->__getNamespace() . '\\ObjectShare';
      foreach ($list as $key => $object) {
        $this->_objectsSharedEmission[$key] = new $class($this->_server, $this->_itemName);
        $this->_objectsSharedEmission[$key]->setObjectMelanie($object);
      }
      $this->executeCache();
    }
    return $this->_objectsSharedEmission;
  }

  /**
   * Récupère la liste des boites accessibles au moins en émission à l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return User[] Liste d'objets
   */
  public function getSharedEmission($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedEmission() [" . $this->_server . "]");
    if (!isset($this->_sharedEmission)) {
      if (isset($attributes) && is_string($attributes)) {
        $attributes = [$attributes];
      }
      if (!isset($attributes)) {
        $attributes = static::GET_BALP_EMISSION_ATTRIBUTES;
      }
      $filter = static::GET_BALP_EMISSION_FILTER;
      if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]) 
          && isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_bal_emission_filter'])) {
        $filter = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_bal_emission_filter'];
      }
      $list = $this->objectmelanie->getBalpEmission($attributes, $filter);
      $this->_sharedEmission = [];
      foreach ($list as $key => $object) {
        $this->_sharedEmission[$key] = new static($this->_server, $this->_itemName);
        $this->_sharedEmission[$key]->setObjectMelanie($object);
      }
      $this->executeCache();
    }
    return $this->_sharedEmission;
  }

  /**
   * Récupère la liste des objets de partage accessibles en tant que gestionnaire pour l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return ObjectShare[] Liste d'objets
   */
  public function getObjectsSharedGestionnaire($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getObjectsSharedGestionnaire() [" . $this->_server . "]");
    if (!isset($this->_objectsSharedGestionnaire)) {
      if (isset($attributes) && is_string($attributes)) {
        $attributes = [$attributes];
      }
      if (!isset($attributes)) {
        $attributes = static::GET_BALP_GESTIONNAIRE_ATTRIBUTES;
      }
      $filter = static::GET_BALP_GESTIONNAIRE_FILTER;
      if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]) 
          && isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_bal_gestionnaire_filter'])) {
        $filter = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_bal_gestionnaire_filter'];
      }
      $list = $this->objectmelanie->getBalpGestionnaire($attributes, $filter);
      $this->_objectsSharedGestionnaire = [];
      $class = $this->__getNamespace() . '\\ObjectShare';
      foreach ($list as $key => $object) {
        $this->_objectsSharedGestionnaire[$key] = new $class($this->_server, $this->_itemName);
        $this->_objectsSharedGestionnaire[$key]->setObjectMelanie($object);
      }
      $this->executeCache();
    }
    return $this->_objectsSharedGestionnaire;
  }

  /**
   * Récupère la liste des boites accessibles en tant que gestionnaire pour l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return User[] Liste d'objets
   */
  public function getSharedGestionnaire($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedGestionnaire() [" . $this->_server . "]");
    if (!isset($this->_sharedGestionnaire)) {
      if (isset($attributes) && is_string($attributes)) {
        $attributes = [$attributes];
      }
      if (!isset($attributes)) {
        $attributes = static::GET_BALP_GESTIONNAIRE_ATTRIBUTES;
      }
      $filter = static::GET_BALP_GESTIONNAIRE_FILTER;
      if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]) 
          && isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_bal_gestionnaire_filter'])) {
        $filter = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_bal_gestionnaire_filter'];
      }
      $list = $this->objectmelanie->getBalpGestionnaire($attributes, $filter);
      $this->_sharedGestionnaire = [];
      foreach ($list as $key => $object) {
        $this->_sharedGestionnaire[$key] = new static($this->_server, $this->_itemName);
        $this->_sharedGestionnaire[$key]->setObjectMelanie($object);
      }
      $this->executeCache();
    }
    return $this->_sharedGestionnaire;
  }

  /**
   * Récupère la liste des groupes dont l'utilisateur est propriétaire
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return Group[] Liste d'objets
   */
  public function getGroups($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getGroups() [" . $this->_server . "]");
    if (!isset($this->_lists)) {
      if (isset($attributes) && is_string($attributes)) {
        $attributes = [$attributes];
      }
      if (!isset($attributes)) {
        $attributes = static::GET_GROUPS_ATTRIBUTES;
      }
      $filter = static::GET_GROUPS_FILTER;
      if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]) 
          && isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_groups_filter'])) {
        $filter = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_user_groups_filter'];
      }
      $list = $this->objectmelanie->getGroups($attributes, $filter);
      $this->_lists = [];
      $class = $this->__getNamespace() . '\\Group';
      foreach ($list as $key => $object) {
        $this->_lists[$key] = new $class($this->_server, $this->_itemName);
        $this->_lists[$key]->setObjectMelanie($object);
      }
      $this->executeCache();
    }
    return $this->_lists;
  }

  /**
   * Récupère la liste des groupes dont l'utilisateur est membre
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return Group[] Liste d'objets
   */
  public function getGroupsIsMember($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getGroupsIsMember() [" . $this->_server . "]");
    if (!isset($this->_lists)) {
      if (isset($attributes) && is_string($attributes)) {
        $attributes = [$attributes];
      }
      if (!isset($attributes)) {
        $attributes = static::GET_GROUPS_IS_MEMBER_ATTRIBUTES;
      }
      $filter = static::GET_GROUPS_IS_MEMBER_FILTER;
      if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]) 
          && isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_groups_user_member_filter'])) {
        $filter = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_groups_user_member_filter'];
      }
      $list = $this->objectmelanie->getGroupsIsMember($attributes, $filter);
      $this->_lists = [];
      $class = $this->__getNamespace() . '\\Group';
      foreach ($list as $key => $object) {
        $this->_lists[$key] = new $class($this->_server, $this->_itemName);
        $this->_lists[$key]->setObjectMelanie($object);
      }
      $this->executeCache();
    }
    return $this->_lists;
  }

  /**
   * Récupère la liste des listes de diffusion dont l'utilisateur est membre
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return Group[] Liste d'objets
   */
  public function getListsIsMember($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getListsIsMember() [" . $this->_server . "]");
    if (!isset($this->_lists)) {
      if (isset($attributes) && is_string($attributes)) {
        $attributes = [$attributes];
      }
      if (!isset($attributes)) {
        $attributes = static::GET_LISTS_IS_MEMBER_ATTRIBUTES;
      }
      $filter = static::GET_LISTS_IS_MEMBER_FILTER;
      if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]) 
          && isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_lists_user_member_filter'])) {
        $filter = \LibMelanie\Config\Ldap::$SERVERS[$this->_server]['get_lists_user_member_filter'];
      }
      $list = $this->objectmelanie->getListsIsMember($attributes, $filter);
      $this->_lists = [];
      $class = $this->__getNamespace() . '\\Group';
      foreach ($list as $key => $object) {
        $this->_lists[$key] = new $class($this->_server, $this->_itemName);
        $this->_lists[$key]->setObjectMelanie($object);
      }
      $this->executeCache();
    }
    return $this->_lists;
  }

  /**
   * Récupère la préférence de l'utilisateur
   * 
   * @param string $scope Scope de la préférence, voir User::PREF_SCOPE*
   * @param string $name Nom de la préférence
   * 
   * @return string La valeur de la préférence si elle existe, null sinon
   */
  public function getPreference($scope, $name) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getPreference($scope, $name)");
    if (!isset($this->_preferences)) {
      $this->_get_preferences();
      $this->executeCache();
    }
    if (isset($this->_preferences["$scope:$name"])) {
      return $this->_preferences["$scope:$name"]->value;
    }
    return null;
  }
  /**
   * Récupération de la préférence avec un scope Default
   * 
   * @param string $name Nom de la préférence
   * 
   * @return string La valeur de la préférence si elle existe, null sinon
   */
  public function getDefaultPreference($name) {
    return $this->getPreference(self::PREF_SCOPE_DEFAULT, $name);
  }
  /**
   * Récupération de la préférence avec un scope Calendar
   * 
   * @param string $name Nom de la préférence
   * 
   * @return string La valeur de la préférence si elle existe, null sinon
   */
  public function getCalendarPreference($name) {
    return $this->getPreference(self::PREF_SCOPE_CALENDAR, $name);
  }
  /**
   * Récupération de la préférence avec un scope Taskslist
   * 
   * @param string $name Nom de la préférence
   * 
   * @return string La valeur de la préférence si elle existe, null sinon
   */
  public function getTaskslistPreference($name) {
    return $this->getPreference(self::PREF_SCOPE_TASKSLIST, $name);
  }
  /**
   * Récupération de la préférence avec un scope Addressbook
   * 
   * @param string $name Nom de la préférence
   * 
   * @return string La valeur de la préférence si elle existe, null sinon
   */
  public function getAddressbookPreference($name) {
    return $this->getPreference(self::PREF_SCOPE_ADDRESSBOOK, $name);
  }

  /**
   * Liste les préférences de l'utilisateur et les conserves en mémoire
   */
  protected function _get_preferences() {
    if (isset($this->_preferences)) {
      return;
    }
    $this->_preferences = [];
    $UserPrefs = $this->__getNamespace() . '\\UserPrefs';
    $preferences = (new $UserPrefs($this))->getList();
    if (is_array($preferences)) {
      foreach ($preferences as $pref) {
        $this->_preferences[$pref->scope.":".$pref->name] = $pref;
      }
    }
  }

  /**
   * Enregistre la préférence de l'utilisateur
   * 
   * @param string $scope Scope de la préférence, voir User::PREF_SCOPE*
   * @param string $name Nom de la préférence
   * @param string $value Valeur de la préférence a enregistrer
   * 
   * @return boolean
   */
  public function savePreference($scope, $name, $value) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->savePreference($scope, $name)");
    if (!isset($this->_preferences)) {
      $this->_get_preferences();
    }
    if (!isset($this->_preferences["$scope:$name"])) {
      $UserPrefs = $this->__getNamespace() . '\\UserPrefs';
      $this->_preferences["$scope:$name"] = new $UserPrefs($this);
      $this->_preferences["$scope:$name"]->scope = $scope;
      $this->_preferences["$scope:$name"]->name = $name;
    }
    $this->_preferences["$scope:$name"]->value = $value;
    $ret = $this->_preferences["$scope:$name"]->save();
    $this->executeCache();
    return !is_null($ret);  
  }
  /**
   * Enregistre la préférence de l'utilisateur avec le scope Default
   * 
   * @param string $name Nom de la préférence
   * @param string $value Valeur de la préférence a enregistrer
   * 
   * @return boolean
   */
  public function saveDefaultPreference($name, $value) {
    return $this->savePreference(self::PREF_SCOPE_DEFAULT, $name, $value);
  }
  /**
   * Enregistre la préférence de l'utilisateur avec le scope Calendar
   * 
   * @param string $name Nom de la préférence
   * @param string $value Valeur de la préférence a enregistrer
   * 
   * @return boolean
   */
  public function saveCalendarPreference($name, $value) {
    return $this->savePreference(self::PREF_SCOPE_CALENDAR, $name, $value);
  }
  /**
   * Enregistre la préférence de l'utilisateur avec le scope Taskslist
   * 
   * @param string $name Nom de la préférence
   * @param string $value Valeur de la préférence a enregistrer
   * 
   * @return boolean
   */
  public function saveTaskslistPreference($name, $value) {
    return $this->savePreference(self::PREF_SCOPE_TASKSLIST, $name, $value);
  }
  /**
   * Enregistre la préférence de l'utilisateur avec le scope Addressbook
   * 
   * @param string $name Nom de la préférence
   * @param string $value Valeur de la préférence a enregistrer
   * 
   * @return boolean
   */
  public function saveAddressbookPreference($name, $value) {
    return $this->savePreference(self::PREF_SCOPE_ADDRESSBOOK, $name, $value);
  }

  /**
   * Supprime la préférence de l'utilisateur
   * 
   * @param string $scope Scope de la préférence, voir User::PREF_SCOPE*
   * @param string $name Nom de la préférence
   * 
   * @return boolean
   */
  public function deletePreference($scope, $name) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->savePreference($scope, $name)");
    if (!isset($this->_preferences)) {
      $this->_get_preferences();
    }
    if (!isset($this->_preferences["$scope:$name"])) {
      $UserPrefs = $this->__getNamespace() . '\\UserPrefs';
      $this->_preferences["$scope:$name"] = new $UserPrefs($this);
      $this->_preferences["$scope:$name"]->scope = $scope;
      $this->_preferences["$scope:$name"]->name = $name;
    }
    $ret = $this->_preferences["$scope:$name"]->delete();
    unset($this->_preferences["$scope:$name"]);
    $this->executeCache();
    return !is_null($ret);  
  }

  /**
   * Retourne la liste des workspaces de l'utilisateur
   * 
   * @param string $orderby [Optionnel] nom du champ a trier
   * @param boolean $asc [Optionnel] tri ascendant ?
   * @param integer $limit [Optionnel] limite du nombre de résultats à retourner
   * @param integer $offset [Optionnel] offset pour la pagination
   * 
   * @return Workspace[]
   */
  public function getUserWorkspaces($orderby = null, $asc = true, $limit = null, $offset = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserWorkspaces()");
    // Si on charge par orderby, limit ou offset
    if (isset($orderby) || isset($limit) || isset($offset)) {
      $_workspaces = $this->objectmelanie->getUserWorkspaces($orderby, $asc, $limit, $offset);
      if (!isset($_workspaces)) {
        return null;
      }
      $Workspace = $this->__getNamespace() . '\\Workspace';
      $workspaces = [];
      foreach ($_workspaces as $_workspace) {
        $workspace = new $Workspace($this);
        $workspace->setObjectMelanie($_workspace);
        $workspaces[$_workspace->id] = $workspace;
      }
      return $workspaces;
    }
    // Si la liste des workspaces n'est pas encore chargée
    if (!isset($this->_userWorkspaces)) {
      $this->_userWorkspaces = [];
      // Si les workspaces partagés sont chargés on utilise les données
      if (isset($this->_sharedWorkspaces)) {
        foreach ($this->_sharedWorkspaces as $_key => $_work) {
          if (!is_object($this->_sharedWorkspaces[$_key])) {
            $this->_sharedWorkspaces = null;
            $this->_userWorkspaces = null;
            return $this->getUserWorkspaces();
          }
          $this->_sharedWorkspaces[$_key]->setUserMelanie($this);
          if ($_work->owner == $this->uid) {
            $this->_userWorkspaces[$_key] = $_work;
          }
        }
      }
      // Sinon on charge depuis la base de données
      else {
        $_workspaces = $this->objectmelanie->getUserWorkspaces();
        if (!isset($_workspaces)) {
          return null;
        }
        $Workspace = $this->__getNamespace() . '\\Workspace';
        foreach ($_workspaces as $_workspace) {
          $workspace = new $Workspace($this);
          $workspace->setObjectMelanie($_workspace);
          $this->_userWorkspaces[$_workspace->id] = $workspace;
        }
      }
      $this->executeCache();
    }
    else {
      foreach ($this->_userWorkspaces as $_key => $_work) {
        if (!is_object($this->_userWorkspaces[$_key])) {
          $this->_userWorkspaces = null;
          return $this->getUserWorkspaces();
        }
        $this->_userWorkspaces[$_key]->setUserMelanie($this);
      }
    }
    return $this->_userWorkspaces;
  }

  /**
   * Retourne la liste des workspaces de l'utilisateur et ceux qui lui sont partagés
   * 
   * @param string $orderby [Optionnel] nom du champ a trier
   * @param boolean $asc [Optionnel] tri ascendant ?
   * @param integer $limit [Optionnel] limite du nombre de résultats à retourner
   * @param integer $offset [Optionnel] offset pour la pagination
   * 
   * @return Workspace[]
   */
  public function getSharedWorkspaces($orderby = null, $asc = true, $limit = null, $offset = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedWorkspaces()");
    // Si on charge par orderby, limit ou offset
    if (isset($orderby) || isset($limit) || isset($offset)) {
      $_workspaces = $this->objectmelanie->getSharedWorkspaces($orderby, $asc, $limit, $offset);
      if (!isset($_workspaces)) {
        return null;
      }
      $Workspace = $this->__getNamespace() . '\\Workspace';
      $workspaces = [];
      foreach ($_workspaces as $_workspace) {
        $workspace = new $Workspace($this);
        $workspace->setObjectMelanie($_workspace);
        $workspaces[$_workspace->id] = $workspace;
      }
      return $workspaces;
    }
    // Si la liste des calendriers n'est pas encore chargée on liste depuis la base
    if (!isset($this->_sharedWorkspaces)) {
      $_workspaces = $this->objectmelanie->getSharedWorkspaces();
      if (!isset($_workspaces)) {
        return null;
      }
      $this->_sharedWorkspaces = [];
      $Workspace = $this->__getNamespace() . '\\Workspace';
      foreach ($_workspaces as $_workspace) {
        $workspace = new $Workspace($this);
        $workspace->setObjectMelanie($_workspace);
        $this->_sharedWorkspaces[$_workspace->id] = $workspace;
      }
      $this->executeCache();
    }
    else {
      foreach ($this->_sharedWorkspaces as $_key => $_work) {
        if (!is_object($this->_sharedWorkspaces[$_key])) {
          $this->_sharedWorkspaces = null;
          return $this->getSharedWorkspaces();
        }
        $this->_sharedWorkspaces[$_key]->setUserMelanie($this);
      }
    }
    return $this->_sharedWorkspaces;
  }

  /**
   * Nettoyer les donnés en cache 
   * (appelé lors de la modification d'un workspace)
   */
  public function cleanWorkspaces() {
    $this->_userWorkspaces = null;
    $this->_sharedWorkspaces = null;
    $this->executeCache();
  }

  /**
   * Permet de lister les droits de l'utilisateur sur les news et les flux rss
   * 
   * @return News\NewsShare[]
   */
  public function getUserNewsShares() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserNews()");
    // Si le DN de l'utilisateur n'est pas positionné
    if (!isset($this->uid)) {
      return null;
    }
    if (!isset($this->_userNewsShares)) {
      $newsShare = new News\NewsShare($this);
      $this->_userNewsShares = $newsShare->getList();
    }
    return $this->_userNewsShares;
  }

  /**
   * Nettoyer les donnés en cache 
   * (appelé lors de la modification d'un NewsShare)
   */
  public function cleanNewsShare() {
    $this->_userNewsShares = null;
    $this->executeCache();
  }

  /**
   * Permet de lister les services associés à l'utilisateur
   * 
   * @param boolean $sharedServices [Optionnal] Permet de lister les services associés aux droits de l'utilisateurs
   * 
   * @return array Liste des services de l'utilisateur
   */
  protected function _get_user_services($sharedServices = true) {
    // Supprimer la base dn du dn pour limiter les services
    if (isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]) 
        && isset(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['base_dn'])) {
      $userDn = trim(str_replace(\LibMelanie\Config\Ldap::$SERVERS[$this->_server]['base_dn'], '', $this->dn), ',');
    }
    else {
      $userDn = $this->dn;
    }

    $services = [];

    // Parcourir les services pour construire l'arbre
    foreach (explode(',', $userDn) as $_s) {
      $services[] = substr($userDn, strrpos($userDn, $_s));
    }

    if ($sharedServices) {
      // Récupérer les droits de l'utilisateur pour avoir la liste des services visibles
      foreach ($this->getUserNewsShares() as $share) {
        // Ajouter les services sur lesquels l'utilisateur a des droits de publication
        if (in_array($share->right, [News\NewsShare::RIGHT_ADMIN_PUBLISHER, News\NewsShare::RIGHT_PUBLISHER]) && !in_array($share->service, $services)) {
          $services[] = $share->service;
        }
      }
    }

    return $services;
  }

  /**
   * Permet de parcourir les news et définir si l'utilisateur courant en est un publisher
   * 
   * @param News\News[]|News\Rss[] [in/out]
   */
  protected function _set_news_is_publisher(&$news) {
    // Ajouter une informations pour les news publisher
    $shares = $this->getUserNewsShares();
    $publisherServices = [];
    foreach ($shares as $share) {
      if (in_array($share->right, [News\NewsShare::RIGHT_ADMIN_PUBLISHER, News\NewsShare::RIGHT_PUBLISHER])) {
        $publisherServices[] = $share->service;
      }
    }

    // Si l'utilisateur est publisher d'une news on ajoute une info
    foreach ($news as $k => $n) {
      if ($this->_is_in_services($n->service, $publisherServices)) {
        $news[$k]->publisher = true;
      }
      else {
        $news[$k]->publisher = false;
      }
    }
  }

  /**
   * Permet de définir si l'utilisateur courant en est un publisher de la news
   * 
   * @param News\News|News\Rss [in/out]
   */
  public function isNewsPublisher(&$news) {
    // Ajouter une informations pour les news publisher
    $shares = $this->getUserNewsShares();
    $publisherServices = [];
    foreach ($shares as $share) {
      if (in_array($share->right, [News\NewsShare::RIGHT_ADMIN_PUBLISHER, News\NewsShare::RIGHT_PUBLISHER])) {
        $publisherServices[] = $share->service;
      }
    }

    // Si le service de la news fait parti des services publisher du User
    if ($this->_is_in_services($news->service, $publisherServices)) {
      $news->publisher = true;
    }
    else {
      $news->publisher = false;
    }
  }

  /**
   * Parcours la liste des services pour déterminer si le service en fait parti
   * Il peut également être un sous service d'un service de la liste et donc en faire partie
   * 
   * @param string $service
   * @param array $servicesList
   * 
   * @return boolean
   */
  protected function _is_in_services($service, $servicesList) {
    foreach ($servicesList as $s) {
      if (strpos($service, $s) !== false) {
        return true;
      }
    }
    return false;
  }

  /**
   * Retourne toutes les news de l'utilisateur liées à son service ou à ses droits
   * 
   * @return News\News
   */
  public function getUserNews() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserNews()");
    // Si le DN de l'utilisateur n'est pas positionné
    if (!isset($this->dn)) {
      return null;
    }
    if (!isset($this->_userNews)) {
      $news = new News\News();
      $news->service = $this->_get_user_services();
      $this->_userNews = $news->getList([], "", [], "modified", false);

      // Ajouter une informations pour les news publisher
      $this->_set_news_is_publisher($this->_userNews);
    }
    return $this->_userNews;
  }

  /**
   * Récupère les deux dernières news associées à l'utilisateur
   * Retourne la news la plus récente du service le plus éloigné de l'utilisateur (service national)
   * et la news la plus récente du service le plus proche de l'utilisateur
   * 
   * @return array 2 news au maximum, la plus proche et la plus loin
   */
  public function getUserLastTwoNews() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserLastTwoNews()");

    // Récupère les services associés à l'utilisateur
    $_services = $this->_get_user_services(false);

    // Récupérer les services comme clé du tableau
    $newsByService = array_fill_keys($_services, false);

    // Parcourir les news pour alimenter le tableau $newsByService
    foreach ($this->getUserNews() as $news) {
      if ($newsByService[$news->service] === false) {
        $newsByService[$news->service] = $news;
      }
    }

    // Récupérer la première et la dernière news
    $first = null; 
    $last = null;

    foreach ($newsByService as $news) {
      if ($news === false) {
        continue;
      }
      if (!isset($first)) {
        $first = $news;
      }
      else {
        $last = $news;
      }
    }
    return [$first, $last];
  }

  /**
   * Nettoyer les donnés en cache 
   * (appelé lors de la modification d'une news)
   */
  public function cleanNews() {
    $this->_userNews = null;
    $this->executeCache();
  }

  /**
   * Retourne tous les rss de l'utilisateur liées à son service ou à ses droits
   * 
   * @return News\Rss
   */
  public function getUserRss() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserRss()");
    // Si le DN de l'utilisateur n'est pas positionné
    if (!isset($this->dn)) {
      return null;
    }
    if (!isset($this->_userRss)) {
      $rss = new News\Rss();
      $rss->service = $this->_get_user_services();
      $this->_userRss = $rss->getList();

      // Ajouter une informations pour les news publisher
      $this->_set_news_is_publisher($this->_userRss);
    }
    return $this->_userRss;
  }

  /**
   * Nettoyer les donnés en cache 
   * (appelé lors de la modification d'un rss)
   */
  public function cleanRss() {
    $this->_userRss = null;
    $this->executeCache();
  }

  /**
   * Récupération des notifications de l'utilisateur
   * Si $last est positionné, récupère les notifications depuis le dernier timestamp
   * 
   * @param integer $last [Optionnel] Dernier timestamp de récupération des notifications
   * 
   * @return Notification[] Liste des notifications de l'utilisateur
   */
  public function getNotifications($last = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getNotifications($last)");

    // Initialisation de l'objet pour récupérer les notifications
    $_notif = new Notification($this);
    $_operators = [];

    // Un last est positionné
    if (isset($last)) {
      $_notif->modified = $last;
      $_operators['modified'] = MappingMce::supeq;
    }
    
    return $_notif->getList(null, null, $_operators, 'created', false);
  }

  /**
   * Passe la notification de l'utilisateur en read (ou non si $read = false)
   * 
   * @param string|Notification $notification Notification ou uid de la notification
   * @param boolean $read Passer en lu ?
   * 
   * @return boolean
   */
  public function readNotification($notification, $read = true) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->readNotification()");

    // La notification est un uid
    if (is_string($notification)) {
      $uid = $notification;
      $notification = new Notification($this);
      $notification->uid = $uid;
    }

    // On load puis on modifie
    if ($notification->load()) {
      $notification->isread = $read;
      $notification->modified = time();

      $ret = $notification->save();

      return !is_null($ret);
    }
    return false;
  }

  /**
   * Supprime la notification de l'utilisateur
   * 
   * @param string|Notification $notification Notification ou uid de la notification
   * 
   * @return boolean
   */
  public function deleteNotification($notification) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->deleteNotification()");

    // La notification est un uid
    if (is_string($notification)) {
      $uid = $notification;
      $notification = new Notification($this);
      $notification->uid = $uid;
    }

    return $notification->delete();
  }

  /**
   * Ajoute la notification pour l'utilisateur
   * 
   * @param Notification $notification Notification
   * 
   * @return string|boolean Uid de la nouvelle notification si Ok, false sinon
   */
  public function addNotification($notification) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->addNotification()");

    // Positionne le owner de la notification
    if (!isset($notification->owner)) {
      $notification->owner = $this->uid;
    }

    // Positionne l'uid de la notification
    if (!isset($notification->uid)) {
      $notification->uid = \LibMelanie\Lib\UUID::v4();
    }

    // Position le modified de la notification
    $notification->created = time();
    $notification->modified = $notification->created;
    $notification->isdeleted = false;
    $notification->isread = false;

    // Sauvegarde la notification
    $ret = $notification->save();

    // Gestion du retour
    if (!is_null($ret)) {
      return $notification->uid;
    }
    return false;
  }

  /**
   * Retourne le calendrier par défaut
   * 
   * @return Calendar Calendrier par défaut de l'utilisateur, null s'il n'existe pas
   */
  public function getDefaultCalendar() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDefaultCalendar()");
    // Si le calendrier n'est pas déjà chargé
    if (!isset($this->_defaultCalendar) || !is_object($this->_defaultCalendar)) {
      // Charge depuis la base
      $_calendar = $this->objectmelanie->getDefaultCalendar();
      if (!$_calendar || !isset($_calendar)) {
        $this->_defaultCalendar = null;
      }
      else {
        $Calendar = $this->__getNamespace() . '\\Calendar';
        $this->_defaultCalendar = new $Calendar($this);
        $this->_defaultCalendar->setObjectMelanie($_calendar);
      }
      if (!isset($this->_defaultCalendar)) {
        // Si pas de default calendar le récupérer dans userCalendars
        if (!isset($this->_userCalendars)) {
          $this->getUserCalendars();
        }
        $this->_defaultCalendar = $this->_userCalendars[$this->uid] ?: null;
        if (is_object($this->_defaultCalendar)) {
          $this->_defaultCalendar->setUserMelanie($this);
        }
      }
      $this->executeCache();
    }
    else {
      $this->_defaultCalendar->setUserMelanie($this);
    }
    return $this->_defaultCalendar;
  }

  /**
   * Modifie le calendrier par défaut de l'utilisateur
   * 
   * @param string|Calendar $calendar Calendrier à mettre par défaut pour l'utilisateur
   * 
   * @return boolean
   */
  public function setDefaultCalendar($calendar) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setDefaultCalendar()");
    if (is_object($calendar)) {
      $calendar_id = $calendar->id;
    }
    else if (is_string($calendar)) {
      $calendar_id = $calendar;
    }
    else {
      return false;
    }
    if ($this->savePreference(self::PREF_SCOPE_CALENDAR, \LibMelanie\Config\ConfigMelanie::CALENDAR_PREF_DEFAULT_NAME, $calendar_id)) {
      if (is_object($calendar)) {
        $this->_defaultCalendar = $calendar;
      }
      else {
        $this->_defaultCalendar = null;
      }
      $this->executeCache();
      return true;
    }
    return false;
  }

  /**
   * Création du calendrier par défaut pour l'utilisateur courant
   * 
   * @param string $calendarName [Optionnel] Nom du calendrier
   * 
   * @return true si la création est OK, false sinon
   */
  public function createDefaultCalendar($calendarName = null) {
    // Gestion du nom du calendrier
    if (isset($calendarName)) {
      $calendarName = str_replace('%%fullname%%', $this->fullname, $calendarName);
      $calendarName = str_replace('%%name%%', $this->name, $calendarName);
      $calendarName = str_replace('%%email%%', $this->email, $calendarName);
      $calendarName = str_replace('%%uid%%', $this->uid, $calendarName);
    }
    // Création du calendrier
    $Calendar = $this->__getNamespace() . '\\Calendar';
    $calendar = new $Calendar($this);
    $calendar->name = $calendarName ?: $this->fullname;
    $calendar->id = $this->uid;
    $calendar->owner = $this->uid;
    if ($calendar->save()) {
      // Création du default calendar
      $this->setDefaultCalendar($calendar->id);
      // Création du display_cals (utile pour que pacome fonctionne)
      $this->savePreference(self::PREF_SCOPE_CALENDAR, 'display_cals', 'a:0:{}');
      $this->executeCache();
      return true;
    }
    return false;
  }

  /**
   * Retourne la liste des calendriers de l'utilisateur
   * 
   * @return Calendar[]
   */
  public function getUserCalendars() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserCalendars()");
    // Si la liste des calendriers n'est pas encore chargée
    if (!isset($this->_userCalendars)) {
      $this->_userCalendars = [];
      // Si les calendriers partagés sont chargés on utilise les données
      if (isset($this->_sharedCalendars)) {
        foreach ($this->_sharedCalendars as $_key => $_cal) {
          if (!is_object($this->_sharedCalendars[$_key])) {
            $this->_sharedCalendars = null;
            $this->_userCalendars = null;
            return $this->getUserCalendars();
          }
          $this->_sharedCalendars[$_key]->setUserMelanie($this);
          if ($_cal->owner == $this->uid) {
            $this->_userCalendars[$_key] = $_cal;
          }
        }
      }
      // Sinon on charge depuis la base de données
      else {
        $_calendars = $this->objectmelanie->getUserCalendars();
        if (!isset($_calendars)) {
          return null;
        }
        $Calendar = $this->__getNamespace() . '\\Calendar';
        foreach ($_calendars as $_calendar) {
          $calendar = new $Calendar($this);
          $calendar->setObjectMelanie($_calendar);
          $this->_userCalendars[$_calendar->id] = $calendar;
        }
      }
      $this->executeCache();
    }
    else {
      foreach ($this->_userCalendars as $_key => $_cal) {
        if (!is_object($this->_userCalendars[$_key])) {
          $this->_userCalendars = null;
          return $this->getUserCalendars();
        }
        $this->_userCalendars[$_key]->setUserMelanie($this);
      }
    }
    return $this->_userCalendars;
  }

  /**
   * Retourne la liste des calendriers de l'utilisateur et ceux qui lui sont partagés
   * 
   * @return Calendar[]
   */
  public function getSharedCalendars() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedCalendars()");
    // Si la liste des calendriers n'est pas encore chargée on liste depuis la base
    if (!isset($this->_sharedCalendars)) {
      $_calendars = $this->objectmelanie->getSharedCalendars();
      if (!isset($_calendars)) {
        return null;
      }
      $this->_sharedCalendars = [];
      $Calendar = $this->__getNamespace() . '\\Calendar';
      foreach ($_calendars as $_calendar) {
        $calendar = new $Calendar($this);
        $calendar->setObjectMelanie($_calendar);
        $this->_sharedCalendars[$_calendar->id] = $calendar;
      }
      $this->executeCache();
    }
    else {
      foreach ($this->_sharedCalendars as $_key => $_cal) {
        if (!is_object($this->_sharedCalendars[$_key])) {
          $this->_sharedCalendars = null;
          return $this->getSharedCalendars();
        }
        $this->_sharedCalendars[$_key]->setUserMelanie($this);
      }
    }
    return $this->_sharedCalendars;
  }

  /**
   * Nettoyer les donnés en cache 
   * (appelé lors de la modification d'un calendrier)
   */
  public function cleanCalendars() {
    $this->_defaultCalendar = null;
    $this->_userCalendars = null;
    $this->_sharedCalendars = null;
    $this->executeCache();
  }
  
  /**
   * Retourne la liste de tâches par défaut
   * 
   * @return Taskslist
   */
  public function getDefaultTaskslist() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDefaultTaskslist()");
    // Si la liste de taches n'est pas déjà chargée
    if (!isset($this->_defaultTaskslist) || !is_object($this->_defaultTaskslist)) {
      // Charge depuis la base de données
      $_taskslist = $this->objectmelanie->getDefaultTaskslist();
      if (!$_taskslist || !isset($_taskslist)) {
        $this->_defaultTaskslist = null;
      }
      else {
        $Taskslist = $this->__getNamespace() . '\\Taskslist';
        $this->_defaultTaskslist = new $Taskslist($this);
        $this->_defaultTaskslist->setObjectMelanie($_taskslist);
      }
      if (!isset($this->_defaultTaskslist)) {
        // Si pas de default taskslist le récupérer dans userTaskslists
        if (!isset($this->_userTaskslists)) {
          $this->getUserTaskslists();
        }
        $this->_defaultTaskslist = $this->_userTaskslists[$this->uid] ?: null;
        if (is_object($this->_defaultTaskslist)) {
          $this->_defaultTaskslist->setUserMelanie($this);
        }
      }
      $this->executeCache();
    }
    else {
      $this->_defaultTaskslist->setUserMelanie($this);
    }
    return $this->_defaultTaskslist;
  }

  /**
   * Modifie la liste de tâches par défaut de l'utilisateur
   * 
   * @param string|Taskslist $taskslist Liste de tâches à mettre par défaut pour l'utilisateur
   * 
   * @return boolean
   */
  public function setDefaultTaskslist($taskslist) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setDefaultTaskslist()");
    if (is_object($taskslist)) {
      $taskslist_id = $taskslist->id;
    }
    else if (is_string($taskslist)) {
      $taskslist_id = $taskslist;
    }
    else {
      return false;
    }
    if ($this->savePreference(self::PREF_SCOPE_TASKSLIST, \LibMelanie\Config\ConfigMelanie::TASKSLIST_PREF_DEFAULT_NAME, $taskslist_id)) {
      if (is_object($taskslist)) {
        $this->_defaultTaskslist = $taskslist;
      }
      else {
        $this->_defaultTaskslist = null;
      }
      $this->executeCache();
      return true;
    }
    return false;
  }

  /**
   * Création de la liste de taches par défaut pour l'utilisateur courant
   * 
   * @param string $taskslistName [Optionnel] Nom de la liste de taches
   * 
   * @return true si la création est OK, false sinon
   */
  public function createDefaultTaskslist($taskslistName = null) {
    // Gestion du nom de la liste de taches
    if (isset($taskslistName)) {
      $taskslistName = str_replace('%%fullname%%', $this->fullname, $taskslistName);
      $taskslistName = str_replace('%%name%%', $this->name, $taskslistName);
      $taskslistName = str_replace('%%email%%', $this->email, $taskslistName);
      $taskslistName = str_replace('%%uid%%', $this->uid, $taskslistName);
    }
    // Création de la liste de taches
    $Taskslist = $this->__getNamespace() . '\\Taskslist';
    $taskslist = new $Taskslist($this);
    $taskslist->name = $taskslistName ?: $this->fullname;
    $taskslist->id = $this->uid;
    // Création de la liste de tâches
    if ($taskslist->save()) {
      // Création du default taskslist
      $this->setDefaultTaskslist($taskslist->id);
      $this->executeCache();
      return true;
    }
    return false;
  }

  /**
   * Retourne la liste des liste de tâches de l'utilisateur
   * 
   * @return Taskslist[]
   */
  public function getUserTaskslists() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserTaskslists()");
    // Si la liste des listes de taches n'est pas encore chargée
    if (!isset($this->_userTaskslists)) {
      $this->_userTaskslists = [];
      // Si les listes de taches partagés sont chargés on utilise les données
      if (isset($this->_sharedTaskslists)) {
        foreach ($this->_sharedTaskslists as $_key => $_list) {
          if (!is_object($this->_sharedTaskslists[$_key])) {
            $this->_sharedTaskslists = null;
            $this->_userTaskslists = null;
            return $this->getUserTaskslists();
          }
          $this->_sharedTaskslists[$_key]->setUserMelanie($this);
          if ($_list->owner == $this->uid) {
            $this->_userTaskslists[$_key] = $_list;
          }
        }
      }
      else {
        $_taskslists = $this->objectmelanie->getUserTaskslists();
        if (!isset($_taskslists)) {
          return null;
        }
        $Taskslist = $this->__getNamespace() . '\\Taskslist';
        foreach ($_taskslists as $_taskslist) {
          $taskslist = new $Taskslist($this);
          $taskslist->setObjectMelanie($_taskslist);
          $this->_userTaskslists[$_taskslist->id] = $taskslist;
        }
      }
      $this->executeCache();
    }
    else {
      foreach ($this->_userTaskslists as $_key => $_list) {
        if (!is_object($this->_userTaskslists[$_key])) {
          $this->_userTaskslists = null;
          return $this->getUserTaskslists();
        }
        $this->_userTaskslists[$_key]->setUserMelanie($this);
      }
    }
    return $this->_userTaskslists;
  }

  /**
   * Retourne la liste des liste de taches de l'utilisateur et celles qui lui sont partagés
   * 
   * @return Taskslist[]
   */
  public function getSharedTaskslists() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedTaskslists()");
    // Si la liste des listes de tâches n'est pas encore chargée on liste depuis la base
    if (!isset($this->_sharedTaskslists)) {
      $_taskslists = $this->objectmelanie->getSharedTaskslists();
      if (!isset($_taskslists)) {
        return null;
      }
      $this->_sharedTaskslists = [];
      $Taskslist = $this->__getNamespace() . '\\Taskslist';
      foreach ($_taskslists as $_taskslist) {
        $taskslist = new $Taskslist($this);
        $taskslist->setObjectMelanie($_taskslist);
        $this->_sharedTaskslists[$_taskslist->id] = $taskslist;
      }
      $this->executeCache();
    }
    else {
      foreach ($this->_sharedTaskslists as $_key => $_list) {
        if (!is_object($this->_sharedTaskslists[$_key])) {
          $this->_sharedTaskslists = null;
          return $this->getSharedTaskslists();
        }
        $this->_sharedTaskslists[$_key]->setUserMelanie($this);
      }
    }
    return $this->_sharedTaskslists;
  }

  /**
   * Nettoyer les donnés en cache 
   * (appelé lors de la modification d'un calendrier)
   */
  public function cleanTaskslists() {
    $this->_defaultTaskslist = null;
    $this->_userTaskslists = null;
    $this->_sharedTaskslists = null;
    $this->executeCache();
  }
  
  /**
   * Retourne la liste de contacts par défaut
   * 
   * @return Addressbook
   */
  public function getDefaultAddressbook() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDefaultAddressbook()");
    // Si le carnet n'est pas déjà chargé
    if (!isset($this->_defaultAddressbook)) {
      // Charge depuis la base de données
      $_addressbook = $this->objectmelanie->getDefaultAddressbook();
      if (!$_addressbook) {
        $this->_defaultAddressbook = null;
      }
      else {
        $Addressbook = $this->__getNamespace() . '\\Addressbook';
        $this->_defaultAddressbook = new $Addressbook($this);
        $this->_defaultAddressbook->setObjectMelanie($_addressbook);
      }
      if (!isset($this->_defaultAddressbook)) {
        if (!isset($this->_userAddressbooks)) {
          $this->getUserAddressbooks();
        }
        $this->_defaultAddressbook = $this->_userAddressbooks[$this->uid] ?: null;
        if (isset($this->_defaultAddressbook)) {
          $this->_defaultAddressbook->setUserMelanie($this);
        }
      }
      $this->executeCache();
    }
    else {
      $this->_defaultAddressbook->setUserMelanie($this);
    }
    return $this->_defaultAddressbook;
  }

  /**
   * Modifie le carnet d'adresses par défaut de l'utilisateur
   * 
   * @param string|Addressbook $addressbook Carnet d'adresses à mettre par défaut pour l'utilisateur
   * 
   * @return boolean
   */
  public function setDefaultAddressbook($addressbook) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setDefaultAddressbook()");
    if (is_object($addressbook)) {
      $addressbook_id = $addressbook->id;
    }
    else if (is_string($addressbook)) {
      $addressbook_id = $addressbook;
    }
    else {
      return false;
    }
    if ($this->savePreference(self::PREF_SCOPE_ADDRESSBOOK, \LibMelanie\Config\ConfigMelanie::ADDRESSBOOK_PREF_DEFAULT_NAME, $addressbook_id)) {
      if (is_object($addressbook)) {
        $this->_defaultAddressbook = $addressbook;
      }
      else {
        $this->_defaultAddressbook = null;
      }
      $this->executeCache();
      return true;
    }
    return false;
  }

  /**
   * Création du carnet d'adresses par défaut pour l'utilisateur courant
   * 
   * @param string $addressbookName [Optionnel] Nom du carnet d'adresses
   * 
   * @return true si la création est OK, false sinon
   */
  public function createDefaultAddressbook($addressbookName = null) {
    // Gestion du nom du carnet d'adresses
    if (isset($addressbookName)) {
      $addressbookName = str_replace('%%fullname%%', $this->fullname, $addressbookName);
      $addressbookName = str_replace('%%name%%', $this->name, $addressbookName);
      $addressbookName = str_replace('%%email%%', $this->email, $addressbookName);
      $addressbookName = str_replace('%%uid%%', $this->uid, $addressbookName);
    }
    // Création du carnet d'adresses
    $Addressbook = $this->__getNamespace() . '\\Addressbook';
    $addressbook = new $Addressbook($this);
    $addressbook->name = $addressbookName ?: $this->fullname;
    $addressbook->id = $this->uid;
    // Création du carnet d'adresses
    if ($addressbook->save()) {
      // Création du default addressbook
      $this->setDefaultAddressbook($addressbook->id);
      $this->executeCache();
      return true;
    }
    return false;
  }

  /**
   * Retourne la liste des liste de contacts de l'utilisateur
   * 
   * @return Addressbook[]
   */
  public function getUserAddressbooks() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserAddressbooks()");
    // Si la liste des carnets n'est pas encore chargée
    if (!isset($this->_userAddressbooks)) {
      $this->_userAddressbooks = [];
      // Si les listes de carnets partagés sont chargés on utilise les données
      if (isset($this->_sharedAddressbooks)) {
        foreach ($this->_sharedAddressbooks as $_key => $_book) {
          if (!is_object($this->_sharedAddressbooks[$_key])) {
            $this->_sharedAddressbooks = null;
            $this->_userAddressbooks = null;
            return $this->getUserAddressbooks();
          }
          $this->_sharedAddressbooks[$_key]->setUserMelanie($this);
          if ($_book->owner == $this->uid) {
            $this->_userAddressbooks[$_key] = $_book;
          }
        }
      }
      else {
        $_addressbooks = $this->objectmelanie->getUserAddressbooks();
        if (!isset($_addressbooks)) {
          return null;
        }
        $Addressbook = $this->__getNamespace() . '\\Addressbook';
        foreach ($_addressbooks as $_addressbook) {
          $addressbook = new $Addressbook($this);
          $addressbook->setObjectMelanie($_addressbook);
          $this->_userAddressbooks[$_addressbook->id] = $addressbook;
        }
      }
      $this->executeCache();
    }
    else {
      foreach ($this->_userAddressbooks as $_key => $_book) {
        if (!is_object($this->_userAddressbooks[$_key])) {
          $this->_userAddressbooks = null;
          return $this->getUserAddressbooks();
        }
        $this->_userAddressbooks[$_key]->setUserMelanie($this);
      }
    }
    return $this->_userAddressbooks;
  }
  /**
   * Retourne la liste des liste de contacts de l'utilisateur et celles qui lui sont partagés
   * 
   * @return Addressbook[]
   */
  public function getSharedAddressbooks() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedAddressbooks()");
    // Si la liste des carnets n'est pas encore chargée on liste depuis la base
    if (!isset($this->_sharedAddressbooks)) {
      $_addressbooks = $this->objectmelanie->getSharedAddressbooks();
      if (!isset($_addressbooks)) {
        return null;
      }
      $this->_sharedAddressbooks = [];
      $Addressbook = $this->__getNamespace() . '\\Addressbook';
      foreach ($_addressbooks as $_addressbook) {
        $addressbook = new $Addressbook($this);
        $addressbook->setObjectMelanie($_addressbook);
        $this->_sharedAddressbooks[$_addressbook->id] = $addressbook;
      }
      $this->executeCache();
    }
    else {
      foreach ($this->_sharedAddressbooks as $_key => $_book) {
        if (!is_object($this->_sharedAddressbooks[$_key])) {
          $this->_sharedAddressbooks = null;
          return $this->getSharedAddressbooks();
        }
        $this->_sharedAddressbooks[$_key]->setUserMelanie($this);
      }
    }
    return $this->_sharedAddressbooks;
  }

  /**
   * Nettoyer les donnés en cache 
   * (appelé lors de la modification d'un calendrier)
   */
  public function cleanAddressbooks() {
    $this->_defaultAddressbook = null;
    $this->_userAddressbooks = null;
    $this->_sharedAddressbooks = null;
    $this->executeCache();
  }
  
  /**
   * ***************************************************
   * DATA MAPPING
   */
  /**
   * Est-ce que l'utilisateur est en fait un objet de partage ?
   * 
   * @return boolean true s'il s'agit d'un objet de partage, false sinon
   */
  protected function getMapIs_objectshare() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapIs_objectshare()");
    return isset($this->uid) && strpos($this->uid, $this->getObjectShareDelimiter()) !== false 
        || isset($this->email) && strpos($this->email, $this->getObjectShareDelimiter()) !== false;
  }

  /**
   * Récupère l'objet de partage associé à l'utilisateur courant
   * si celui ci est bien un objet de partage
   * 
   * @return ObjectShare Objet de partage associé, null si pas d'objet de partage
   */
  protected function getMapObjectshare() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapObjectshare()");
    if (!isset($this->objectshare)) {
      if (isset($this->uid) && strpos($this->uid, $this->getObjectShareDelimiter()) !== false 
          || isset($this->email) && strpos($this->email, $this->getObjectShareDelimiter()) !== false) {
        $class = $this->__getNamespace() . '\\ObjectShare';
        $this->objectshare = new $class($this->_server, $this->_itemName);
        $this->objectshare->setObjectMelanie($this->objectmelanie);
      }
    }
    return $this->objectshare;
  }

  /**
   * Mapping is_synchronisation_enable field
   * 
   * @return boolean true si la synchronisation est activée pour l'utilisateur
   */
  protected function getMapIs_synchronisation_enable() {
    return true;
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
    return 'STANDARD';
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
   * Mapping is_individuelle field
   * 
   * @return boolean true si la boite est individuelle
   */
  protected function getMapIs_individuelle() {
    return $this->objectmelanie->type == Config::get(Config::LDAP_TYPE_INDIVIDUELLE);
  }

  /**
   * Mapping is_partagee field
   * 
   * @return boolean true si la boite est partagée
   */
  protected function getMapIs_partagee() {
    return $this->objectmelanie->type == Config::get(Config::LDAP_TYPE_PARTAGEE);
  }

  /**
   * Mapping is_fonctionnelle field
   * 
   * @return boolean true si la boite est fonctionnelle
   */
  protected function getMapIs_fonctionnelle() {
    return $this->objectmelanie->type == Config::get(Config::LDAP_TYPE_FONCTIONNELLE);
  }

  /**
   * Mapping is_ressource field
   * 
   * @return boolean true si la boite est une ressource
   */
  protected function getMapIs_ressource() {
    return $this->objectmelanie->type == Config::get(Config::LDAP_TYPE_RESSOURCE);
  }
  
  /**
   * Mapping is_unite field
   * 
   * @return boolean true si la boite est une unite
   */
  protected function getMapIs_unite() {
    return $this->objectmelanie->type == Config::get(Config::LDAP_TYPE_UNITE);
  }

  /**
   * Mapping is_service field
   * 
   * @return boolean true si la boite est un service
   */
  protected function getMapIs_service() {
    return $this->objectmelanie->type == Config::get(Config::LDAP_TYPE_SERVICE);
  }

  /**
   * Mapping is_personne field
   * 
   * @return boolean true si la boite est une personne
   */
  protected function getMapIs_personne() {
    return $this->objectmelanie->type == Config::get(Config::LDAP_TYPE_PERSONNE);
  }

  /**
   * Mapping is_applicative field
   * 
   * @return boolean true si la boite est une application
   */
  protected function getMapIs_applicative() {
    return $this->objectmelanie->type == Config::get(Config::LDAP_TYPE_APPLICATIVE);
  }

  /**
   * Mapping is_list field
   * 
   * @return boolean true si la boite est une list
   */
  protected function getMapIs_list() {
    return $this->objectmelanie->type == Config::get(Config::LDAP_TYPE_LIST);
  }

  /**
   * Mapping is_listab field
   * 
   * @return boolean true si la boite est une listab
   */
  protected function getMapIs_listab() {
    return $this->objectmelanie->type == Config::get(Config::LDAP_TYPE_LISTAB);
  }

  /**
   * Mapping is_mailbox field
   * 
   * @return boolean true s'il s'agit bien d'une boite (valeur par défaut pour la MCE)
   */
  protected function getMapIs_mailbox() {
    return true;
  }

  /**
   * Mapping shares field
   * 
   * @return Share[] Liste des partages de l'objet
   */
  protected function getMapShares() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapShares()");
    if (!isset($this->_shares)) {
      $this->_shares = [];
    }
    return $this->_shares;
  }
}
