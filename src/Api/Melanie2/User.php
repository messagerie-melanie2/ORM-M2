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
namespace LibMelanie\Api\Melanie2;

use LibMelanie\Lib\Melanie2Object;
use LibMelanie\Objects\UserMelanie;
use LibMelanie\Log\M2Log;

/**
 * Classe utilisateur pour Melanie2
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage API Mélanie2
 *             @api
 * @property string $dn DN de l'utilisateur dans l'annuaire            
 * @property string $uid Identifiant unique de l'utilisateur
 * @property string $fullname Nom complet de l'utilisateur
 * @property string $name Nom de l'utilisateur
 * @property string $email Adresse email principale de l'utilisateur
 * @property array $email_list Liste de toutes les adresses email de l'utilisateur
 * @property string $email_send Adresse email d'émission principale de l'utilisateur
 * @property array $email_send_list Liste de toutes les adresses email d'émission de l'utilisateur
 * @property string $password_need_change Est-ce que le mot de passe doit changer et pour quelle raison ? (Si la chaine n'est pas vide, le mot de passe doit changer)
 * @property array $shares Liste des partages de la boite (format <uid>:<droit>)
 * @property string $away_response Message d'absence de l'utilisateur (TODO: Objet pour traiter la syntaxe)
 * @property string $internet_access_admin Accés Internet positionné par l'administrateur
 * @property string $internet_access_user Accés Internet positionné par l'utilisateur
 * @property string $use_photo_ader Photo utilisable sur le réseau ADER (RIE)
 * @property string $use_photo_intranet Photo utilisable sur le réseau Intranet
 * @property string $service Service de l'utilisateur dans l'annuaire Mélanie2
 * @property string $employee_number Champ RH
 * @property string $zone Zone de diffusion de l'utilisateur
 * @property string $street Adresse - Rue de l'utilisateur
 * @property string $postalcode Adresse - Code postal de l'utilisateur
 * @property string $locality Adresse - Ville de l'utilisateur
 * @property array $info Champ d'information de l'utilisateur
 * @property string $description Description de l'utilisateur
 * @property string $phonenumber Numéro de téléphone de l'utilisateur
 * @property string $faxnumber Numéro de fax de l'utilisateur
 * @property string $mobilephone Numéro de mobile de l'utilisateur
 * @property string $roomnumber Numéro de bureau de l'utilisateur
 * @property string $title Titre de l'utilisateur
 * @property string $business_category Catégorie professionnelle de l'utilisateur
 * @property string $vpn_profile Profil VPN de l'utilisateur
 * @property string $update_personnal_info Est-ce que l'utilisateur a le droit de mettre à jour ses informations personnelles
 * @property array $server_routage Champ de routage pour le serveur de message de l'utilisateur
 * @property-read string $server_host Host du serveur de messagerie de l'utilisateur
 * @property-read string $server_user User du serveur de messagerie de l'utilisateur
 * @property string $synchro_access_admin Accés synchronisation mobile positionné par l'administrateur
 * @property string $synchro_access_user Accés synchronisation mobile positionné par l'utilisateur
 * @property string $mission Mission de l'utilisateur
 * @property string $photo Photo de l'utilisateur
 * @property string $gender Genre de l'utilisateur
 * 
 * @method string getTimezone() [OSOLETE] Chargement du timezone de l'utilisateur
 * @method bool authentification($password, $master = false) Authentification de l'utilisateur sur l'annuaire Mélanie2
 * @method bool load() Charge les données de l'utilisateur depuis l'annuaire Mélanie2 (en fonction de l'uid ou l'email)
 * @method bool exists() Est-ce que l'utilisateur existe dans l'annuaire Mélanie2 (en fonction de l'uid ou l'email)
 */
class User extends Melanie2Object {
  /**
   * Constructeur de l'objet
   */
  function __construct() {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");
    // Définition de l'utilisateur melanie2
    $this->objectmelanie = new UserMelanie();
  }
   
  /**
   * ***************************************************
   * METHOD MAPPING
   */
  /**
   * Récupère la liste des objets de partage accessibles à l'utilisateur
   *
   * @return ObjectShare[] Liste d'objets
   */
  function getObjectsShared() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getBalpList()");
    $list = $this->objectmelanie->getBalp();
    $balp = [];
    foreach ($list as $key => $object) {
      $balp[$key] = new ObjectShare();
      $balp[$key]->setObjectMelanie($object);
    }
    return $balp;
  }
  /**
   * Récupère la liste des objets de partage accessibles au moins en émission à l'utilisateur
   *
   * @return ObjectShare[] Liste d'objets
   */
  function getObjectsSharedEmission() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getBalpListEmission()");
    $list = $this->objectmelanie->getBalpEmission();
    $balp = [];
    foreach ($list as $key => $object) {
      $balp[$key] = new ObjectShare();
      $balp[$key]->setObjectMelanie($object);
    }
    return $balp;
  }
  /**
   * Récupère la liste des objets de partage accessibles en gestionnaire à l'utilisateur
   *
   * @return ObjectShare[] Liste d'objets
   */
  function getObjectsSharedGestionnaire() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getBalpListGestionnaire()");
    $list = $this->objectmelanie->getBalpGestionnaire();
    $balp = [];
    foreach ($list as $key => $object) {
      $balp[$key] = new ObjectShare();
      $balp[$key]->setObjectMelanie($object);
    }
    return $balp;
  }
  /**
   * Retourne le calendrier par défaut
   * 
   * @return Calendar
   */
  function getDefaultCalendar() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDefaultCalendar()");
    $_calendar = $this->objectmelanie->getDefaultCalendar();
    if (!$_calendar)
      return null;
    $calendar = new Calendar($this);
    $calendar->setObjectMelanie($_calendar);
    return $calendar;
  }
  /**
   * Retourne la liste des calendriers de l'utilisateur
   * 
   * @return Calendar[]
   */
  function getUserCalendars() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserCalendars()");
    $_calendars = $this->objectmelanie->getUserCalendars();
    if (!isset($_calendars))
      return null;
    $calendars = [];
    foreach ($_calendars as $_calendar) {
      $calendar = new Calendar($this);
      $calendar->setObjectMelanie($_calendar);
      $calendars[$_calendar->id] = $calendar;
    }
    return $calendars;
  }
  /**
   * Retourne la liste des calendriers de l'utilisateur et ceux qui lui sont partagés
   * 
   * @return Calendar[]
   */
  function getSharedCalendars() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedCalendars()");
    $_calendars = $this->objectmelanie->getSharedCalendars();
    if (!isset($_calendars))
      return null;
    $calendars = [];
    foreach ($_calendars as $_calendar) {
      $calendar = new Calendar($this);
      $calendar->setObjectMelanie($_calendar);
      $calendars[$_calendar->id] = $calendar;
    }
    return $calendars;
  }
  
  /**
   * Retourne la liste de tâches par défaut
   * 
   * @return Taskslist
   */
  function getDefaultTaskslist() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDefaultTaskslist()");
    $_taskslist = $this->objectmelanie->getDefaultTaskslist();
    if (!$_taskslist)
      return null;
    $taskslist = new Taskslist($this);
    $taskslist->setObjectMelanie($_taskslist);
    return $taskslist;
  }
  /**
   * Retourne la liste des liste de tâches de l'utilisateur
   * 
   * @return Taskslist[]
   */
  function getUserTaskslists() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserTaskslists()");
    $_taskslists = $this->objectmelanie->getUserTaskslists();
    if (!isset($_taskslists))
      return null;
    $taskslists = [];
    foreach ($_taskslists as $_taskslist) {
      $taskslist = new Taskslist($this);
      $taskslist->setObjectMelanie($_taskslist);
      $taskslists[$_taskslist->id] = $taskslist;
    }
    return $taskslists;
  }
  /**
   * Retourne la liste des liste de tâches de l'utilisateur et celles qui lui sont partagés
   * 
   * @return Taskslist[]
   */
  function getSharedTaskslists() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedTaskslists()");
    $_taskslists = $this->objectmelanie->getSharedTaskslists();
    if (!isset($_taskslists))
      return null;
    $taskslists = [];
    foreach ($_taskslists as $_taskslist) {
      $taskslist = new Taskslist($this);
      $taskslist->setObjectMelanie($_taskslist);
      $taskslists[$_taskslist->id] = $taskslist;
    }
    return $taskslists;
  }
  
  /**
   * Retourne la liste de contacts par défaut
   * 
   * @return Addressbook
   */
  function getDefaultAddressbook() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDefaultAddressbook()");
    $_addressbook = $this->objectmelanie->getDefaultAddressbook();
    if (!$_addressbook)
      return null;
    $addressbook = new Addressbook($this);
    $addressbook->setObjectMelanie($_addressbook);
    return $addressbook;
  }
  /**
   * Retourne la liste des liste de contacts de l'utilisateur
   * 
   * @return Addressbook[]
   */
  function getUserAddressbooks() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserAddressbooks()");
    $_addressbooks = $this->objectmelanie->getUserAddressbooks();
    if (!isset($_addressbooks))
      return null;
    $addressbooks = [];
    foreach ($_addressbooks as $_addressbook) {
      $addressbook = new Addressbook($this);
      $addressbook->setObjectMelanie($_addressbook);
      $addressbooks[$_addressbook->id] = $addressbook;
    }
    return $addressbooks;
  }
  /**
   * Retourne la liste des liste de contacts de l'utilisateur et celles qui lui sont partagés
   * 
   * @return Addressbook[]
   */
  function getSharedAddressbooks() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedAddressbooks()");
    $_addressbooks = $this->objectmelanie->getSharedAddressbooks();
    if (!isset($_addressbooks))
      return null;
    $addressbooks = [];
    foreach ($_addressbooks as $_addressbook) {
      $addressbook = new Addressbook($this);
      $addressbook->setObjectMelanie($_addressbook);
      $addressbooks[$_addressbook->id] = $addressbook;
    }
    return $addressbooks;
  }
  
  /**
   * ***************************************************
   * DATA MAPPING
   */
  /**
   * Récupération du champ server_host
   * 
   * @return mixed|NULL Valeur du serveur host, null si non trouvé
   */
  protected function getMapServer_Host() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapServer_Host()");
    $routage = $this->server_routage;
    foreach ($routage as $route) {
      if (strpos($route, '%') !== false) {
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
  protected function getMapServer_User() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapServer_User()");
    $routage = $this->server_routage;
    foreach ($routage as $route) {
      if (strpos($route, '%') !== false) {
        $route = explode('@', $route, 2);
        return $route[0];
      }
    }
    return null;
  }
}