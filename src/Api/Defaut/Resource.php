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
use LibMelanie\Log\M2Log;
use LibMelanie\Api\Defaut\UserPrefs;
use LibMelanie\Api\Defaut\Calendar;
use LibMelanie\Objects\ResourceMelanie;

/**
 * Classe ressource par defaut
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $dn                 DN de la ressource dans l'annuaire            
 * @property string $uid                Identifiant unique de la ressource
 * @property string $fullname           Nom complet de la ressource
 * @property string $name               Nom de la ressource
 * @property string $email              Adresse email principale de la ressource
 * @property array  $email_list         Liste de toutes les adresses email de la ressource
 * @property string $type               Type de ressource (voir Resource::TYPE_*)
 * @property string $service            Service de la ressource
 * @property string $bal                Type de boite aux lettres
 * @property string $street             Rue de la ressource
 * @property string $postalcode         Code postal de la ressource
 * @property string $locality           Ville de la ressource
 * @property string $description        Description de la ressource
 * @property string $roomnumber         Numéro de bureau de la ressource
 * @property string $title              Titre de la ressource
 * @property string $batiment           Batiment de la ressource
 * @property string $etage              Etage de la ressource
 * @property string $capacite           Capacité de la ressource
 * @property string $caracteristiques   Caractéristiques de la ressource
 * 
 * @method bool save() Enregistrement de la ressource dans l'annuaire
 * @method bool load() Chargement de la ressource dans l'annuaire
 * @method bool delete() Suppression de la ressource dans l'annuaire
 */
abstract class Resource extends MceObject {

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
   * Liste des preferences de la ressource
   * 
   * @var UserPrefs[]
   * @ignore
   */
  protected $_preferences;

  /**
   * Liste des propriétés à sérialiser pour le cache
   */
  protected $serializedProperties = [
    'otherldapobject',
    '_server',
    '_isLoaded',
    '_isExist',
    '_preferences',
  ];

  /**
   * Configuration de l'item name associé à l'objet courant
   * 
   * @var string
   * @ignore
   */
  protected $_itemName;

  // **** Constantes pour les preferences
  /**
   * Scope de preference par defaut pour la ressource
   */
  const PREF_SCOPE_DEFAULT = \LibMelanie\Config\ConfigMelanie::GENERAL_PREF_SCOPE;
  /**
   * Scope de preference pour les calendriers de la ressource
   */
  const PREF_SCOPE_CALENDAR = \LibMelanie\Config\ConfigMelanie::CALENDAR_PREF_SCOPE;

  /**
   * Type de ressource : Flex office
   */
  const TYPE_FLEX_OFFICE = 'Flex Office';
  /**
   * Type de ressource : Salle
   */
  const TYPE_SALLE = 'Salle';
  /**
   * Type de ressource : Véhicule
   */
  const TYPE_VEHICULE = 'Véhicule';
  /**
   * Type de ressource : Matériel
   */
  const TYPE_MATERIEL = 'Matériel';

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
  const LOAD_ATTRIBUTES = ['fullname', 'uid', 'name', 'email', 'email_list', 'shares', 'type', 'bal', 'roomnumber', 'batiment', 'etage', 'capacite', 'caracteristiques', 'street', 'postalcode', 'locality', 'description', 'title'];

  /**
   * Configuration du mapping qui surcharge la conf
   */
  const MAPPING = [];

  /**
   * DN a utiliser comme base pour les requetes
   */
  const DN = null;

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

    // Définition de la ressource
    $this->objectmelanie = new ResourceMelanie($server, null, static::MAPPING, $this->_itemName, static::DN);
    // Gestion d'un second serveur d'annuaire dans le cas ou les informations sont répartis
    if (isset(\LibMelanie\Config\Ldap::$OTHER_LDAP)) {
      $this->otherldapobject = new ResourceMelanie(\LibMelanie\Config\Ldap::$OTHER_LDAP, null, static::MAPPING, $this->_itemName, static::DN);
    }
    $this->_server = $server ?: \LibMelanie\Config\Ldap::$SEARCH_LDAP;
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
   * Charge les données de la ressource depuis l'annuaire (en fonction de l'uid ou l'email)
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
   * Est-ce que la ressource existe dans l'annuaire (en fonction de l'uid ou l'email)
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
   * Récupère la préférence de la ressource
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
   * Liste les préférences de la ressource et les conserves en mémoire
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
   * Enregistre la préférence de la ressource
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
   * Enregistre la préférence de la ressource avec le scope Default
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
   * Enregistre la préférence de la ressource avec le scope Calendar
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
   * Supprime la préférence de la ressource
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
   * Retourne le calendrier par défaut
   * 
   * @return Calendar Calendrier par défaut de la ressource, null s'il n'existe pas
   */
  public function getDefaultCalendar() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getDefaultCalendar()");
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
   * Modifie le calendrier par défaut de la ressource
   * 
   * @param string|Calendar $calendar Calendrier à mettre par défaut pour la ressource
   * 
   * @return boolean
   */
  public function setDefaultCalendar($calendar) {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->setDefaultCalendar()");
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
   * Création du calendrier par défaut pour la ressource courant
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
   * Retourne la liste des calendriers de la ressource
   * 
   * @return Calendar[]
   */
  public function getUserCalendars() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getUserCalendars()");
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
   * Retourne la liste des calendriers de la ressource et ceux qui lui sont partagés
   * 
   * @return Calendar[]
   */
  public function getSharedCalendars() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getSharedCalendars()");
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
}
