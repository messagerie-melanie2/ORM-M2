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
use LibMelanie\Config\MappingMce;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\Config;
use LibMelanie\Config\DefaultConfig;

/**
 * Classe attendee pour les évènements par defaut
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $email Email du participant
 * @property string $name Nom du participant
 * @property string $uid Uid du participant
 * @property string $type Type du participant (individuel, ressource, ...)
 * @property boolean $self_invite Est-ce que ce participant s'est lui même invité
 * @property boolean $is_saved Est-ce que l'événement a été enregistré dans son agenda en attente ?
 * @property-read boolean $need_action Est-ce que le mode En attente est activé pour ce participant
 * @property Attendee::RESPONSE_* $response Réponse du participant
 * @property Attendee::ROLE_* $role Role du participant
 * @property-read boolean $is_individuelle Est-ce qu'il s'agit d'une boite individuelle
 * @property-read boolean $is_ressource Est-ce qu'il s'agit d'une boite de ressource
 * @property-read boolean $is_list Est-ce qu'il s'agit d'une liste
 * @property-read User[] $members Liste des membres appartenant au groupe
 */
class Attendee extends MceObject {

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
   * Est-ce que l'évenement a éte enregistré pour le participant via le en attente
   * @var boolean
   * @ignore
   */
  private $is_saved;

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
   * Type du participant
   * 
   * @var string $type Attendee::ROLE_*
   * @ignore
   *
   */
  private $type;

  /**
   * Est-ce que le participant s'est invité
   * 
   * @var boolean
   * @ignore
   */
  private $self_invite;

  /**
   * Utilisateur associé a l'attendee
   * 
   * @var User
   */
  private $user;
  
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

  // Attendee Type Fields
  const TYPE_INDIVIDUAL = DefaultConfig::INDIVIDUAL;
	const TYPE_GROUP = DefaultConfig::GROUP;
	const TYPE_RESOURCE = DefaultConfig::RESOURCE;
	const TYPE_ROOM = DefaultConfig::ROOM;
	const TYPE_UNKNOWN = DefaultConfig::UNKNOWN;
  
  /**
   * Constructeur de l'objet
   * 
   * @param Event $event          
   */
  function __construct($event = null, $user = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");

    // Définition de l'évènement Mél associé
    if (isset($event)) {
      $this->objectmelanie = $event->getObjectMelanie();
    }
    
    // Définition de l'utilisateur Mél associé
    if (isset($user)) {
      $this->user = $user;
    }
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
    if (isset($this->type)) {
      $attendee[Config::get(Config::CUTYPE)] = $this->type;
    }
    if ($this->self_invite) {
      $attendee[Config::get(Config::SELF_INVITE_ATTENDEE)] = $this->self_invite;
    }
    if (isset($this->is_saved)) {
      $attendee[Config::get(Config::IS_SAVED_ATTENDEE)] = $this->is_saved;
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
    $this->role = isset($attendee[Config::get(Config::ROLE)]) ? $attendee[Config::get(Config::ROLE)] : MappingMce::REQ_PARTICIPANT;
    $this->response = isset($attendee[Config::get(Config::RESPONSE)]) ? $attendee[Config::get(Config::RESPONSE)] : MappingMce::ATT_NEED_ACTION;
    $this->type = isset($attendee[Config::get(Config::CUTYPE)]) ? $attendee[Config::get(Config::CUTYPE)] : MappingMce::ATT_TYPE_INDIVIDUAL;
    $this->self_invite = isset($attendee[Config::get(Config::SELF_INVITE_ATTENDEE)]) ? $attendee[Config::get(Config::SELF_INVITE_ATTENDEE)] : false;
    $this->is_saved = isset($attendee[Config::get(Config::IS_SAVED_ATTENDEE)]) ? $attendee[Config::get(Config::IS_SAVED_ATTENDEE)] : null;
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
    if (empty($this->email) && isset($this->user)) {
      $this->email = $this->user->email;
    }
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
    if (empty($this->name) && isset($this->user)) {
      $this->name = $this->user->fullname;
    }
    return $this->name;
  }

  /**
   * Set is_saved property
   * 
   * @param string $is_saved          
   * @ignore
   */
  protected function setMapIs_saved($is_saved) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapIs_saved($is_saved)");
    $this->is_saved = $is_saved;
  }
  /**
   * Get is_saved property
   * 
   * @ignore
   */
  protected function getMapIs_saved() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapIs_saved()");
    return $this->is_saved;
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
   * Set type property
   * 
   * @param Attendee::TYPE $type          
   * @ignore
   *
   */
  protected function setMapType($type) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapType($type)");
    if (isset(MappingMce::$MapAttendeeTypeObjectToMce[$type]))
      $this->type = MappingMce::$MapAttendeeTypeObjectToMce[$type];
  }
  /**
   * Get type property
   * 
   * @ignore
   *
   */
  protected function getMapType() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapType()");
    if (isset(MappingMce::$MapAttendeeTypeMceToObject[$this->type]))
      return MappingMce::$MapAttendeeTypeMceToObject[$this->type];
    else
      return self::TYPE_INDIVIDUAL;
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
    if (isset(MappingMce::$MapAttendeeResponseObjectToMce[$response]))
      $this->response = MappingMce::$MapAttendeeResponseObjectToMce[$response];
  }
  /**
   * Get response property
   * 
   * @ignore
   *
   */
  protected function getMapResponse() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapResponse()");
    if (isset(MappingMce::$MapAttendeeResponseMceToObject[$this->response]))
      return MappingMce::$MapAttendeeResponseMceToObject[$this->response];
    else
      return self::RESPONSE_NEED_ACTION;
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
    if (isset(MappingMce::$MapAttendeeRoleObjectToMce[$role]))
      $this->role = MappingMce::$MapAttendeeRoleObjectToMce[$role];
  }
  /**
   * Get role property
   * 
   * @ignore
   *
   */
  protected function getMapRole() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapRole()");
    if (isset(MappingMce::$MapAttendeeRoleMceToObject[$this->role]))
      return MappingMce::$MapAttendeeRoleMceToObject[$this->role];
    else
      return self::ROLE_REQ_PARTICIPANT;
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
      if (!isset($this->user)) {
        $User = $this->__getNamespace() . '\\User';
        $this->user = new $User();
        $this->user->email = $this->email;
      }
      if ($this->user->load()) {
        $this->uid = $this->user->uid;
      }
    }      
    return $this->uid;
  }

  /**
   * Mapping is_individuelle field
   * 
   * @return boolean true si la boite est individuelle
   */
  protected function getMapIs_individuelle() {
    if (isset($this->email)) {
      if (!isset($this->user)) {
        $User = $this->__getNamespace() . '\\User';
        $this->user = new $User();
        $this->user->email = $this->email;
      }
      if ($this->user->load()) {
        return $this->user->is_individuelle || $this->user->is_applicative;
      }
    }
    return true;
  }

  /**
   * Mapping is_ressource field
   * 
   * @return boolean true si la boite est une ressource
   */
  protected function getMapIs_ressource() {
    if (isset($this->email)) {
      if (!isset($this->user)) {
        $User = $this->__getNamespace() . '\\User';
        $this->user = new $User();
        $this->user->email = $this->email;
      }
      if ($this->user->load()) {
        return $this->user->is_ressource;
      }
    }
    return true;
  }

  /**
   * Mapping is_list field
   * 
   * @return boolean true si la boite est une liste
   */
  protected function getMapIs_list() {
    if (isset($this->email)) {
      if (!isset($this->user)) {
        $User = $this->__getNamespace() . '\\User';
        $this->user = new $User();
        $this->user->email = $this->email;
      }
      if ($this->user->load()) {
        return $this->user->is_list;
      }
    }
    return false;
  }

  /**
   * Mapping members field
   * 
   * @return array] Liste d'adresses email
   */
  protected function getMapMembers() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapMembers()");
    if (isset($this->email)) {
      $Group = $this->__getNamespace() . '\\Group';
      $group = new $Group();
      $group->email = $this->email;
      if ($group->load(['members_email'])) {
        return $group->members_email;
      }
    }
    return [];
  }
  
  /**
   * Mapping attendee need_action field
   */
  protected function getMapNeed_action() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapNeed_action()");
    if (!isset($this->need_action) 
        && (isset($this->email) || isset($this->uid))) {
      $need_action = Config::get(Config::NEED_ACTION_ENABLE);
      if ($need_action) {
        $filter = Config::get(Config::NEED_ACTION_DISABLE_FILTER);
      }
      else {
        $filter = Config::get(Config::NEED_ACTION_ENABLE_FILTER);
      }
      if (isset($filter)) {
        if (!isset($this->user)) {
          $User = $this->__getNamespace() . '\\User';
          $this->user = new $User();
          if (isset($this->email)) {
            $this->user->email = $this->email;
          }
          else {
            $this->user->uid = $this->uid;
          }
        }
        $fields = [];
        foreach ($filter as $field => $f) {
          $fields[] = $field;
        }
        if ($this->user->load($fields) && ($this->user->is_individuelle || $this->user->is_applicative)) {
          foreach ($fields as $field) {
            $match = false;
            if (is_array($this->user->$field)) {
              if (in_array($filter[$field], $this->user->$field)) {
                $match = true;
              }
            }
            else if ($this->user->$field == $filter[$field]) {
              $match = true;
            }
            if ($match) {
              $need_action = !$need_action;
              break;
            }
          }
        }
        else {
          $need_action = false;
        }
      }
      $this->need_action = $need_action;
    }   
    return $this->need_action;
  }
}