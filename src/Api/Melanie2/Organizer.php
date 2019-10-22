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

use LibMelanie\Ldap\LDAPMelanie;
use LibMelanie\Ldap\Ldap;
use LibMelanie\Lib\Melanie2Object;
use LibMelanie\Exceptions;
use LibMelanie\Log\M2Log;
use LibMelanie\Lib\ICS;

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
 * @property string $role Role de l'organisateur
 * @property string $partstat Statut de participation de l'organisateur
 * @property string $sent_by Sent-By pour l'organisateur
 * @property string $owner_email Email du owner du calendrier s'il est partagé
 * @property string $rsvp Repondez svp pour l'organisateur
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
   * Valeurs decodées de organizer_json
   * 
   * @var array
   */
  private $organizer_json_decoded = null;
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
    $this->extern = null;
    
    // Définition de l'évènement melanie2
    if (isset($event)) {
      $this->event = $event;
      $this->objectmelanie = $this->event->getObjectMelanie();
    }
  }
  
  /**
   * Défini l'event associé à l'objet organizer
   * @param Event $event
   */
  public function setEvent($event) {
    $this->event = $event;
    $this->objectmelanie = $this->event->getObjectMelanie();
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
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
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
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
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
    if ($this->extern !== $extern) {
      // Intialisation de l'email et du nom de l'organisateur
      $this->organizer_email = null;
      $this->organizer_name = null;
    }
    $this->setOrganizerParam('extern', $extern);
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
    $extern = $this->getOrganizerParam('extern');
    if (isset($extern)) {
      $this->extern = $extern;      
    }
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
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->objectmelanie->organizer_calendar = $calendar;
    $this->objectmelanie->organizer_calendar_id = $calendar;
    if (isset($this->event) 
        && $calendar == $this->event->calendar
        && $this->event->getCalendarMelanie()->owner != $this->event->owner) {      
      $infos = Ldap::GetUserInfos($this->event->getCalendarMelanie()->owner);
      if (Ldap::GetMapValue($infos, 'user_type_entree', 'mineqtypeentree') == 'BALI') {
        $newName = Ldap::GetMapValue($infos, 'user_cn', 'cn');
        $owner = $this->event->owner;
        if (strpos($this->event->owner, '.-.')) {
          $owner = explode('.-.', $owner, 2);
          $owner = $owner[0];
        }
        $ownerInfos = Ldap::GetUserInfos($owner, null, [Ldap::GetMap('user_display_name', 'displayname')]);
        $oldName = Ldap::GetMapValue($ownerInfos, 'user_display_name', 'displayname');
        $ownerEmail = Ldap::GetMapValue($infos, 'user_mail', 'mailpr');
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapCalendar() oldName: $oldName");
        $newName = str_replace(' - ', " (via $oldName) - ", $newName);
        $this->setMapName($newName);
        $this->setMapOwner_email($ownerEmail);
      }
    }
  }
  /**
   * Mapping calendar field
   * 
   * @ignore
   */
  protected function getMapCalendar() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapCalendar()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if ($this->event->useJsonData()) {
      if ($this->event instanceof Exception) {
        $eventParent = $this->event->getEventParent();
        if (isset($eventParent)) {
          return $eventParent->getObjectMelanie()->organizer_calendar_id;
        }       
      }
      return $this->objectmelanie->organizer_calendar_id;
    }
    return $this->objectmelanie->organizer_calendar;
  }
  
  /**
   * Mapping organizer attendees field
   * 
   * @ignore
   */
  protected function getMapAttendees() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapAttendees()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
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
   * @ignore   
   */
  protected function setMapEmail($email) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapEmail($email)");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if (!isset($this->extern)) {
      $this->extern = $this->getOrganizerParam('extern');
    }
    // Si l'organisateur est externe au ministère
    if (!isset($this->extern)) {
      if (strpos($email, '.-.') !== false) {
        $e = explode('.-.', $email, 2);
        $objPartage = $e[0];
        $infos = Ldap::GetUserInfosFromEmail($e[1]);
      }
      else {
        $infos = Ldap::GetUserInfosFromEmail($email);
      }
      if (is_null($infos)) {
        $this->objectmelanie->organizer_uid = null;
        $this->extern = true;
      } else {
        if (isset($objPartage)) {
          $this->objectmelanie->organizer_uid = $objPartage . '.-.' . Ldap::GetMapValue($infos, 'user_uid', 'uid');
        }
        else {
          $this->objectmelanie->organizer_uid = Ldap::GetMapValue($infos, 'user_uid', 'uid');
        }        
        $this->extern = false;
        $name = $this->getOrganizerParam(ICS::CN);
        if (!isset($name)) {
          $this->setMapName(Ldap::GetMapValue($infos, 'user_cn', 'cn'));
        }        
      }
      $this->setOrganizerParam('extern', $this->extern);
    }
    $this->organizer_email = $email;
    // Position du mail dans organizer_json
    $this->setOrganizerParam('mailto', $email);
  }
  /**
   * Mapping organizer email field
   * 
   * @ignore
   */
  protected function getMapEmail() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapEmail()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if (is_null($this->organizer_email)) {
      if ($this->event->useJsonData()) {
        $this->organizer_email = $this->getOrganizerParam('mailto');
      }
      else {
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
        $this->setOrganizerParam('extern', $this->extern);
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
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    // Si l'organisateur est externe au ministère
    $this->organizer_name = $name;
    // Position du name dans organizer_json
    $this->setOrganizerParam(ICS::CN, $name);
  }
  /**
   * Mapping name field
   * 
   * @ignore
   */
  protected function getMapName() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapName()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if (is_null($this->organizer_name)) {
      if ($this->event->useJsonData()) {
        $this->organizer_name = $this->getOrganizerParam(ICS::CN);
      }
      else {
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
    }
    return $this->organizer_name;
  }
  /**
   * Mapping role field
   *
   * @param string $role
   * @ignore
   */
  protected function setMapRole($role) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapRole($role)");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->setOrganizerParam(ICS::ROLE, $role);
  }
  /**
   * Mapping role field
   *
   * @ignore
   */
  protected function getMapRole() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapRole()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if ($this->event->useJsonData()) {
      return $this->getOrganizerParam(ICS::ROLE);
    }
    else {
      return ICS::ROLE_CHAIR;
    }
  }
  /**
   * Mapping owner_email field
   *
   * @param string $owner_email
   * @ignore
   */
  protected function setMapOwner_email($owner_email) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapOwner_email()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->setOrganizerParam(ICS::X_M2_ORG_MAIL, $owner_email);
  }
  /**
   * Mapping owner_email field
   *
   * @ignore
   */
  protected function getMapOwner_email() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapOwner_email()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if ($this->event->useJsonData()) {
      return $this->getOrganizerParam(ICS::X_M2_ORG_MAIL);
    }
    else {
      return null;
    }
  }
  /**
   * Mapping partstat field
   *
   * @param string $partstat
   * @ignore
   */
  protected function setMapPartstat($partstat) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapPartstat($partstat)");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->setOrganizerParam(ICS::PARTSTAT, $partstat);
  }
  /**
   * Mapping partstat field
   *
   * @ignore
   */
  protected function getMapPartstat() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapPartstat()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if ($this->event->useJsonData()) {
      return $this->getOrganizerParam(ICS::PARTSTAT);
    }
    else {
      return ICS::PARTSTAT_ACCEPTED;
    }
  }
  /**
   * Mapping sent_by field
   *
   * @param string $partstat
   * @ignore
   */
  protected function setMapSent_by($sent_by) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapSent_by($sent_by)");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->setOrganizerParam(ICS::SENT_BY, $sent_by);
  }
  /**
   * Mapping sent_by field
   *
   * @ignore
   */
  protected function getMapSent_by() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapSent_by()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if ($this->event->useJsonData()) {
      return $this->getOrganizerParam(ICS::SENT_BY);
    }
    else {
      return null;
    }
  }
  /**
   * Mapping rsvp field
   *
   * @param string $rsvp
   * @ignore
   */
  protected function setMapRsvp($rsvp) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapRsvp($rsvp)");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->setOrganizerParam(ICS::RSVP, $rsvp);
  }
  /**
   * Mapping rsvp field
   *
   * @ignore
   */
  protected function getMapRsvp() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapRsvp()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if ($this->event->useJsonData()) {
      return $this->getOrganizerParam(ICS::RSVP);
    }
    else {
      return ICS::RSVP_TRUE;
    }
  }
  /**
   * Positionne la valeur du paramètre dans organizer_json
   * 
   * @param string $param
   * @param string $value
   */
  private function setOrganizerParam($param, $value) {
    if (!isset($this->organizer_json_decoded)) {
      $this->organizer_json_decoded = json_decode($this->objectmelanie->organizer_json, true);
    }
    if (isset($value)) {
      $this->organizer_json_decoded[$param] = $value;
    }
    else {
      unset($this->organizer_json_decoded[$param]);      
    }
    $this->objectmelanie->organizer_json = json_encode($this->organizer_json_decoded);
  }
  /**
   * Retourne la valeur du paramètre dans organizer_json
   * 
   * @param string $param
   * @return mixed
   */
  private function getOrganizerParam($param) {
    if (!isset($this->organizer_json_decoded)) {
      $this->organizer_json_decoded = json_decode($this->objectmelanie->organizer_json, true);
    }
    return isset($this->organizer_json_decoded[$param]) ? $this->organizer_json_decoded[$param] : null;
  }
}