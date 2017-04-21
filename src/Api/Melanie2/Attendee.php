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
use LibMelanie\Config\ConfigMelanie;
use LibMelanie\Config\MappingMelanie;
use LibMelanie\Log\M2Log;
use LibMelanie\Ldap\LDAPMelanie;

/**
 * Classe attendee pour les évènements pour Melanie2
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage API Mélanie2
 *             @api
 * @property string $email Email du participant
 * @property string $name Nom du participant
 * @property string $uid Uid du participant
 * @property Attendee::RESPONSE_* $response Réponse du participant
 * @property Attendee::ROLE_* $role Role du participant
 */
class Attendee extends Melanie2Object {
  // Propriétés private
  /**
   * Email du participant
   * 
   * @var string $email
   * @ignore
   *
   */
  private $email;
  /**
   * Nom du participant
   * 
   * @var string $name
   * @ignore
   *
   */
  private $name;
  /**
   * Uid du participant
   * 
   * @var string $uid
   * @ignore
   *
   */
  private $uid;
  /**
   * Réponse du participant
   * 
   * @var string $response Attendee::RESPONSE_*
   * @ignore
   *
   */
  private $response;
  /**
   * Role du participant
   * 
   * @var string $role Attendee::ROLE_*
   * @ignore
   *
   */
  private $role;
  
  // Attendee Response Fields
  const RESPONSE_NEED_ACTION = ConfigMelanie::NEED_ACTION;
  const RESPONSE_ACCEPTED = ConfigMelanie::ACCEPTED;
  const RESPONSE_DECLINED = ConfigMelanie::DECLINED;
  const RESPONSE_IN_PROCESS = ConfigMelanie::IN_PROCESS;
  const RESPONSE_TENTATIVE = ConfigMelanie::TENTATIVE;
  
  // Attendee Role Fields
  const ROLE_CHAIR = ConfigMelanie::CHAIR;
  const ROLE_REQ_PARTICIPANT = ConfigMelanie::REQ_PARTICIPANT;
  const ROLE_OPT_PARTICIPANT = ConfigMelanie::OPT_PARTICIPANT;
  const ROLE_NON_PARTICIPANT = ConfigMelanie::NON_PARTICIPANT;
  
  /**
   * Constructeur de l'objet
   * 
   * @param Event $event          
   */
  function __construct($event = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");
    // Définition de l'évènement melanie2 associé
    if (isset($event))
      $this->objectmelanie = $event->getObjectMelanie();
  }
  
  /**
   * Render the attendee
   * 
   * @return attendee array
   * @ignore
   *
   */
  public function render() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->render()");
    $attendee = [];
    $attendee[ConfigMelanie::NAME] = $this->name;
    $attendee[ConfigMelanie::ROLE] = $this->role;
    $attendee[ConfigMelanie::RESPONSE] = $this->response;
    return $attendee;
  }
  
  /**
   * Define the attendee
   * 
   * @param array $attendee          
   * @ignore
   *
   */
  public function define($attendee) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->define()");
    if (!is_array($attendee)) {
      M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->define(): attendee not an array");
      return null;
    }
    $this->name = isset($attendee[ConfigMelanie::NAME]) ? $attendee[ConfigMelanie::NAME] : "";
    $this->role = isset($attendee[ConfigMelanie::ROLE]) ? $attendee[ConfigMelanie::ROLE] : MappingMelanie::REQ_PARTICIPANT;
    $this->response = isset($attendee[ConfigMelanie::RESPONSE]) ? $attendee[ConfigMelanie::RESPONSE] : MappingMelanie::ATT_NEED_ACTION;
  }
  
  /**
   * Set the attendee email
   * 
   * @param string $email          
   * @ignore
   *
   */
  public function setEmail($email) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setEmail($email)");
    $this->email = $email;
  }
  
  /**
   * ****************************
   * GETTER SETTER
   */
  /**
   * Set email property
   * 
   * @param string $email          
   * @ignore
   *
   */
  protected function setMapEmail($email) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapEmail($email)");
    $this->email = $email;
  }
  /**
   * Get email property
   * 
   * @ignore
   *
   */
  protected function getMapEmail() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapEmail()");
    return $this->email;
  }
  
  /**
   * Set name property
   * 
   * @param string $name          
   * @ignore
   *
   */
  protected function setMapName($name) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapName($name)");
    $this->name = $name;
  }
  /**
   * Get name property
   * 
   * @ignore
   *
   */
  protected function getMapName() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapName()");
    return $this->name;
  }
  
  /**
   * Set response property
   * 
   * @param Attendee::RESPONSE $response          
   * @ignore
   *
   */
  protected function setMapResponse($response) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapResponse($response)");
    if (isset(MappingMelanie::$MapAttendeeResponseObjectMelanie[$response]))
      $this->response = MappingMelanie::$MapAttendeeResponseObjectMelanie[$response];
  }
  /**
   * Get response property
   * 
   * @ignore
   *
   */
  protected function getMapResponse() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapResponse()");
    if (isset(MappingMelanie::$MapAttendeeResponseObjectMelanie[$this->response]))
      return MappingMelanie::$MapAttendeeResponseObjectMelanie[$this->response];
    else
      return MappingMelanie::$MapAttendeeResponseObjectMelanie[self::RESPONSE_NEED_ACTION];
  }
  
  /**
   * Set role property
   * 
   * @param Attendee::ROLE $role          
   * @ignore
   *
   */
  protected function setMapRole($role) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapRole($role)");
    if (isset(MappingMelanie::$MapAttendeeRoleObjectMelanie[$role]))
      $this->role = MappingMelanie::$MapAttendeeRoleObjectMelanie[$role];
  }
  /**
   * Get role property
   * 
   * @ignore
   *
   */
  protected function getMapRole() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapRole()");
    if (isset(MappingMelanie::$MapAttendeeRoleObjectMelanie[$this->role]))
      return MappingMelanie::$MapAttendeeRoleObjectMelanie[$this->role];
    else
      return MappingMelanie::$MapAttendeeRoleObjectMelanie[self::ROLE_REQ_PARTICIPANT];
  }
  
  /**
   * Mapping attendee uid field
   * 
   * @param string $uid          
   */
  protected function setMapUid($uid) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapUid($uid)");
    $this->uid = $uid;
  }
  /**
   * Mapping attendee uid field
   */
  protected function getMapUid() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapUid()");
    if (!isset($this->uid) && isset($this->email))
      $this->uid = LDAPMelanie::GetUidFromMail($this->email);
    if (!isset($this->uid))
      $this->uid = $this->email;
    return $this->uid;
  }
}