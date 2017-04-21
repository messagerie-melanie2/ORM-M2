<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * ORM M2 Copyright © 2017 PNE Annuaire et Messagerie/MEDDE
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
 * @property string $uid Identifiant unique de l'utilisateur
 * @property string $email Adresse email de l'utilisateur
 * @method string getTimezone() Chargement l'évènement, en fonction du taskslist et de l'uid
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
   * DATA MAPPING
   */
  /**
   * Mapping uid field
   * 
   * @param string $uid          
   */
  protected function setMapUid($uid) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapUid($uid)");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    $this->objectmelanie->setUid($uid);
  }
  /**
   * Mapping uid field
   */
  protected function getMapUid() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapUid()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    return $this->objectmelanie->getUid();
  }
  
  /**
   * Mapping email field
   * 
   * @param string $email          
   */
  protected function setMapEmail($email) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapEmail($email)");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    $this->objectmelanie->setEmail($email);
  }
  /**
   * Mapping email field
   */
  protected function getMapEmail() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapEmail()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    return $this->objectmelanie->getEmail();
  }
  
  /**
   * ***************************************************
   * METHOD MAPPING
   */
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
}