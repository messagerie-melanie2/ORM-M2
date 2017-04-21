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

use LibMelanie\Objects\ObjectMelanie;
use LibMelanie\Ldap\LDAPMelanie;
use LibMelanie\Lib\Melanie2Object;
use LibMelanie\Objects\EventMelanie;
use LibMelanie\Config\ConfigMelanie;
use LibMelanie\Config\MappingMelanie;
use LibMelanie\Exceptions;
use LibMelanie\Log\M2Log;

/**
 * Classe evenement pour Melanie2,
 * implémente les API de la librairie pour aller chercher les données dans la base de données
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage API Mélanie2
 *             @api
 * @property string $name Nom de l'organisateur
 * @property string $calendar Calendrier de l'organisateur
 * @property-read Attendee[] $attendees Tableau d'objets Attendee pour l'organisateur (Lecture seule)
 * @property string $email Email de l'organisateur
 * @property string $uid Uid de l'organisateur
 * @property bool $extern Boolean pour savoir si l'organisateur est externe au ministère
 */
class Organizer extends Melanie2Object {
  // Accès aux objets associés
  /**
   * Evenement associé à l'objet
   * 
   * @var Event
   */
  private $event;
  
  // object privé
  /**
   * Email de l'organisateur de l'évènement
   * 
   * @var string
   */
  private $organizer_email = null;
  /**
   * Nom de l'organisateur de l'évènement
   * 
   * @var string
   */
  private $organizer_name = null;
  /**
   * Défini si l'organisateur est externe au ministère
   * Cela change la façon de le sauvegarder
   * 
   * @var boolean
   */
  private $extern;
  
  /**
   * **
   * CONSTANTES
   */
  const ORGANIZER_EXTERN = "ORGANIZER-EXTERN";
  const ORGANIZER_EXTERN_NAME = "ORGANIZER-EXTERN-NAME";
  
  /**
   * Constructeur de l'objet
   * 
   * @param Event $event          
   */
  function __construct(Event $event = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");
    // Intialisation de l'email de l'organisateur
    $this->organizer_email = null;
    $this->organizer_name = null;
    $this->extern = false;
    
    // Définition de l'évènement melanie2
    if (isset($event)) {
      $this->event = $event;
      $this->objectmelanie = $this->event->getObjectMelanie();
    }
  }
  
  /**
   * ***************************************************
   * DATA MAPPING
   */
  /**
   * Mapping uid field
   * 
   * @param string $uid          
   * @ignore
   *
   */
  protected function setMapUid($uid) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapUid($uid)");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    $this->objectmelanie->organizer_uid = $uid;
  }
  /**
   * Mapping uid field
   * 
   * @ignore
   *
   */
  protected function getMapUid() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapName()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    return $this->objectmelanie->organizer_uid;
  }
  
  /**
   * Mapping extern field
   * 
   * @param boolean $extern          
   * @ignore
   *
   */
  protected function setMapExtern($extern) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapExtern($extern)");
    // RAZ
    if ($this->extern != $extern) {
      // Intialisation de l'email et du nom de l'organisateur
      $this->organizer_email = null;
      $this->organizer_name = null;
    }
    $this->extern = $extern;
  }
  /**
   * Mapping extern field
   * 
   * @ignore
   *
   */
  protected function getMapExtern() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapExtern()");
    return $this->extern;
  }
  
  /**
   * Mapping calendar field
   * 
   * @param string $calendar          
   * @ignore
   *
   */
  protected function setMapCalendar($calendar) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapCalendar($calendar)");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    $this->objectmelanie->organizer_calendar = $calendar;
  }
  /**
   * Mapping calendar field
   * 
   * @ignore
   *
   */
  protected function getMapCalendar() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapCalendar()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    return $this->objectmelanie->organizer_calendar;
  }
  
  /**
   * Mapping organizer attendees field
   */
  protected function getMapAttendees() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapAttendees()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    if (!isset($this->objectmelanie->organizer_attendees))
      return null;
    $_attendees = unserialize($this->objectmelanie->organizer_attendees);
    $attendees = [];
    foreach ($_attendees as $key => $_attendee) {
      $attendee = new Attendee($this);
      $attendee->setEmail($key);
      $attendee->define($_attendee);
      $attendees[] = $attendee;
    }
    return $attendees;
  }
  
  /**
   * Mapping organizer email field
   * 
   * @param string $email          
   */
  protected function setMapEmail($email) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapEmail()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    if ($this->organizer_email != $email) {
      // Si l'organisateur est externe au ministère
      if ($this->extern) {
        $this->event->setAttribute(self::ORGANIZER_EXTERN, $email);
      } else {
        $uid = LDAPMelanie::GetUidFromMail($email);
        if (is_null($uid)) {
          $this->objectmelanie->organizer_uid = null;
          $this->extern = true;
          $this->setMapEmail($email);
        } else {
          $this->objectmelanie->organizer_uid = $uid;
          $this->extern = false;
        }
      }
      $this->organizer_email = $email;
    }
  }
  /**
   * Mapping organizer email field
   */
  protected function getMapEmail() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapEmail()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    if (is_null($this->organizer_email)) {
      // Si l'organisateur est externe au ministère
      $email = $this->event->getAttribute(self::ORGANIZER_EXTERN);
      if (!is_null($email)) {
        $this->organizer_email = $email;
        $this->extern = true;
      } else {
        $email = LDAPMelanie::GetMailFromUid($this->objectmelanie->organizer_uid);
        if (is_null($email)) {
          $this->organizer_email = '';
          $this->extern = true;
        } else {
          $this->organizer_email = $email;
          $this->extern = false;
        }
      }
    }
    return $this->organizer_email;
  }
  
  /**
   * Mapping name field
   * 
   * @param string $name          
   * @ignore
   *
   */
  protected function setMapName($name) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapName($name)");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    // Si l'organisateur est externe au ministère
    if ($this->organizer_name != $name && $this->extern) {
      $this->event->setAttribute(self::ORGANIZER_EXTERN_NAME, $name);
    }
    $this->organizer_name = $name;
  }
  /**
   * Mapping name field
   * 
   * @ignore
   *
   */
  protected function getMapName() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapName()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    if (is_null($this->organizer_name)) {
      // Si l'organisateur est externe au ministère
      $name = $this->event->getAttribute(self::ORGANIZER_EXTERN_NAME);
      if ($name) {
        $this->organizer_name = $name;
        $this->extern = true;
      } else {
        $name = LDAPMelanie::GetNameFromUid($this->objectmelanie->organizer_uid);
        if (is_null($name)) {
          $this->organizer_name = '';
          $this->extern = true;
        } else {
          $this->organizer_name = $name;
          $this->extern = false;
        }
      }
    }
    return $this->organizer_name;
  }
}