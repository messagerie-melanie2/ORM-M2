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
use LibMelanie\Config\MappingMelanie;
use LibMelanie\Log\M2Log;
use LibMelanie\Ldap\Ldap;
use LibMelanie\Config\Config;
use LibMelanie\Config\DefaultConfig;

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
 * @property boolean $self_invite Est-ce que ce participant s'est lui même invité
 * @property-read boolean $need_action Est-ce que le mode En attente est activé pour ce participant
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
   * Est-ce que le mode En attente est activé pour ce participant
   * @var boolean
   * @ignore
   */
  private $need_action;
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
  /**
   * Est-ce que le participant s'est invité
   * 
   * @var boolean
   * @ignore
   */
  private $self_invite;
  
  // Attendee Response Fields
  const RESPONSE_NEED_ACTION = DefaultConfig::NEED_ACTION;
  const RESPONSE_ACCEPTED = DefaultConfig::ACCEPTED;
  const RESPONSE_DECLINED = DefaultConfig::DECLINED;
  const RESPONSE_IN_PROCESS = DefaultConfig::IN_PROCESS;
  const RESPONSE_TENTATIVE = DefaultConfig::TENTATIVE;
  
  // Attendee Role Fields
  const ROLE_CHAIR = DefaultConfig::CHAIR;
  const ROLE_REQ_PARTICIPANT = DefaultConfig::REQ_PARTICIPANT;
  const ROLE_OPT_PARTICIPANT = DefaultConfig::OPT_PARTICIPANT;
  const ROLE_NON_PARTICIPANT = DefaultConfig::NON_PARTICIPANT;
  
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
    $attendee[Config::get(Config::NAME)] = $this->name;
    $attendee[Config::get(Config::ROLE)] = $this->role;
    $attendee[Config::get(Config::RESPONSE)] = $this->response;
    if ($this->self_invite) {
      $attendee[Config::get(Config::SELF_INVITE_ATTENDEE)] = $this->self_invite;
    }
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
    $this->name = isset($attendee[Config::get(Config::NAME)]) ? $attendee[Config::get(Config::NAME)] : "";
    $this->role = isset($attendee[Config::get(Config::ROLE)]) ? $attendee[Config::get(Config::ROLE)] : MappingMelanie::REQ_PARTICIPANT;
    $this->response = isset($attendee[Config::get(Config::RESPONSE)]) ? $attendee[Config::get(Config::RESPONSE)] : MappingMelanie::ATT_NEED_ACTION;
    $this->self_invite = isset($attendee[Config::get(Config::SELF_INVITE_ATTENDEE)]) ? $attendee[Config::get(Config::SELF_INVITE_ATTENDEE)] : false;
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
   * Set self invite property
   *
   * @param string $self_invite
   * @ignore
   *
   */
  protected function setMapSelf_invite($self_invite) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapSelf_invite($self_invite)");
    $this->self_invite = $self_invite;
  }
  /**
   * Get self invite property
   *
   * @ignore
   *
   */
  protected function getMapSelf_invite() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapSelf_invite()");
    return $this->self_invite;
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
    if (!isset($this->uid) && isset($this->email)) {
      $infos = Ldap::GetUserInfosFromEmail($this->email);
      $this->uid = isset($infos['uid']) ? $infos['uid'][0] : null;
    }      
    if (!isset($this->uid))
      $this->uid = $this->email;
    return $this->uid;
  }
  
  /**
   * Mapping attendee need_action field
   */
  protected function getMapNeed_action() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapNeed_action()");
    if (!isset($this->need_action) 
        && isset($this->email)) {
      $infos = Ldap::GetUserInfosFromEmail($this->email);
      $this->need_action = isset($infos) && isset($infos['info']) && in_array('ORM.Agenda.EnAttente: oui', $infos['info']);
    }   
    return $this->need_action;
  }
}