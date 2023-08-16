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
 * @property string $delegated_to A qui le participant délègue sa participation
 * @property string $delegated_from Qui a délégué la participation du participant
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
  protected $_email;

  /**
   * Nom du participant
   * 
   * @var string $name
   * @ignore
   *
   */
  protected $_name;

  /**
   * Uid du participant
   * 
   * @var string $uid
   * @ignore
   *
   */
  protected $_uid;

  /**
   * Est-ce que le mode En attente est activé pour ce participant
   * @var boolean
   * @ignore
   */
  protected $_need_action;

  /**
   * Est-ce que l'évenement a éte enregistré pour le participant via le en attente
   * @var boolean
   * @ignore
   */
  protected $_is_saved;

  /**
   * Est-ce que le participant est externe à l'annuaire ?
   * @var boolean
   * @ignore
   */
  protected $_is_external;

  /**
   * Est-ce que le participant est une liste dans l'annuaire ?
   * @var boolean
   * @ignore
   */
  protected $_is_list;

  /**
   * Est-ce que le participant est une boite individuelle dans l'annuaire ?
   * @var boolean
   * @ignore
   */
  protected $_is_individuelle;

  /**
   * Est-ce que le participant est une ressource dans l'annuaire ?
   * @var boolean
   * @ignore
   */
  protected $_is_ressource;

  /**
   * Réponse du participant
   * 
   * @var string $response Attendee::RESPONSE_*
   * @ignore
   *
   */
  protected $_response;

  /**
   * Role du participant
   * 
   * @var string $role Attendee::ROLE_*
   * @ignore
   *
   */
  protected $_role;

  /**
   * Type du participant
   * 
   * @var string $type Attendee::ROLE_*
   * @ignore
   *
   */
  protected $_type;

  /**
   * Est-ce que le participant s'est invité
   * 
   * @var boolean
   * @ignore
   */
  protected $_self_invite;

  /**
   * Le participant est délégué par un autre participant
   * 
   * @var string
   * @ignore
   */
  protected $_delegated_from;

  /**
   * Le participant a délégué a un autre participant
   * 
   * @var string
   * @ignore
   */
  protected $_delegated_to;

  /**
   * Utilisateur associé a l'attendee
   * 
   * @var User
   */
  protected $_user;
  
  // Attendee Response Fields
  const RESPONSE_NEED_ACTION = DefaultConfig::NEED_ACTION;
  const RESPONSE_ACCEPTED = DefaultConfig::ACCEPTED;
  const RESPONSE_DECLINED = DefaultConfig::DECLINED;
  const RESPONSE_IN_PROCESS = DefaultConfig::IN_PROCESS;
  const RESPONSE_TENTATIVE = DefaultConfig::TENTATIVE;
  const RESPONSE_DELEGATED = DefaultConfig::DELEGATED;
  
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
  public function __construct($event = null, $user = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->__construct()");

    // Définition de l'évènement Mél associé
    if (isset($event)) {
      $this->objectmelanie = $event->getObjectMelanie();
    }
    
    // Définition de l'utilisateur Mél associé
    if (isset($user)) {
      $this->_user = $user;
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
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->render()");
    $attendee = [];
    $attendee[Config::get(Config::NAME)] = $this->_name;
    $attendee[Config::get(Config::ROLE)] = $this->_role;
    $attendee[Config::get(Config::RESPONSE)] = $this->_response;
    if (isset($this->_type)) {
      $attendee[Config::get(Config::CUTYPE)] = $this->_type;
    }
    if ($this->_self_invite) {
      $attendee[Config::get(Config::SELF_INVITE_ATTENDEE)] = $this->_self_invite;
    }
    if (isset($this->_is_saved)) {
      $attendee[Config::get(Config::IS_SAVED_ATTENDEE)] = $this->_is_saved;
    }
    if (isset($this->_delegated_from)) {
      $attendee[Config::get(Config::DELEGATED_FROM)] = $this->_delegated_from;
    }
    if (isset($this->_delegated_to)) {
      $attendee[Config::get(Config::DELEGATED_TO)] = $this->_delegated_to;
    }
    if (isset($this->_is_external)) {
      $attendee[Config::get(Config::IS_EXTERNAL_ATTENDEE)] = $this->_is_external;

      if (!$this->_is_external) {
        if (isset($this->_uid)) {
          $attendee[Config::get(Config::UID_ATTENDEE)] = $this->_uid;
        }
        $attendee[Config::get(Config::IS_LIST_ATTENDEE)] = $this->_is_list;
        $attendee[Config::get(Config::IS_INDIVIDUELLE_ATTENDEE)] = $this->_is_individuelle;
        $attendee[Config::get(Config::IS_RESSOURCE_ATTENDEE)] = $this->_is_ressource;
        if (isset($this->_need_action)) {
          $attendee[Config::get(Config::NEED_ACTION)] = $this->_need_action;
        }
      }
      else {
        $attendee[Config::get(Config::NEED_ACTION)] = false;
      }
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
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->define()");
    if (!is_array($attendee)) {
      M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->define(): attendee not an array");
      return null;
    }
    $this->_name = isset($attendee[Config::get(Config::NAME)]) ? $attendee[Config::get(Config::NAME)] : "";
    $this->_role = isset($attendee[Config::get(Config::ROLE)]) ? $attendee[Config::get(Config::ROLE)] : MappingMce::REQ_PARTICIPANT;
    $this->_response = isset($attendee[Config::get(Config::RESPONSE)]) ? $attendee[Config::get(Config::RESPONSE)] : MappingMce::ATT_NEED_ACTION;
    $this->_type = isset($attendee[Config::get(Config::CUTYPE)]) ? $attendee[Config::get(Config::CUTYPE)] : null;
    $this->_self_invite = isset($attendee[Config::get(Config::SELF_INVITE_ATTENDEE)]) ? $attendee[Config::get(Config::SELF_INVITE_ATTENDEE)] : false;
    $this->_need_action = isset($attendee[Config::get(Config::NEED_ACTION_ATTENDEE)]) ? $attendee[Config::get(Config::NEED_ACTION_ATTENDEE)] : null;
    $this->_is_saved = isset($attendee[Config::get(Config::IS_SAVED_ATTENDEE)]) ? $attendee[Config::get(Config::IS_SAVED_ATTENDEE)] : null;
    $this->_is_external = isset($attendee[Config::get(Config::IS_EXTERNAL_ATTENDEE)]) ? $attendee[Config::get(Config::IS_EXTERNAL_ATTENDEE)] : null;
    $this->_is_list = isset($attendee[Config::get(Config::IS_LIST_ATTENDEE)]) ? $attendee[Config::get(Config::IS_LIST_ATTENDEE)] : null;
    $this->_is_ressource = isset($attendee[Config::get(Config::IS_RESSOURCE_ATTENDEE)]) ? $attendee[Config::get(Config::IS_RESSOURCE_ATTENDEE)] : null;
    $this->_is_individuelle = isset($attendee[Config::get(Config::IS_INDIVIDUELLE_ATTENDEE)]) ? $attendee[Config::get(Config::IS_INDIVIDUELLE_ATTENDEE)] : null;
    $this->_uid = isset($attendee[Config::get(Config::UID_ATTENDEE)]) ? $attendee[Config::get(Config::UID_ATTENDEE)] : null;
    $this->_delegated_from = isset($attendee[Config::get(Config::DELEGATED_FROM)]) ? $attendee[Config::get(Config::DELEGATED_FROM)] : null;
    $this->_delegated_to = isset($attendee[Config::get(Config::DELEGATED_TO)]) ? $attendee[Config::get(Config::DELEGATED_TO)] : null;
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
    $this->_email = $email;
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
    $this->_email = $email;
  }
  /**
   * Get email property
   * 
   * @ignore
   *
   */
  protected function getMapEmail() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapEmail()");
    if (empty($this->_email) && isset($this->_user)) {
      $this->_email = $this->_user->email;
    }
    return $this->_email;
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
    $this->_name = $name;
  }
  /**
   * Get name property
   * 
   * @ignore
   *
   */
  protected function getMapName() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapName()");
    if (empty($this->_name) && isset($this->_user)) {
      $this->_name = $this->_user->fullname;
    }
    return $this->_name;
  }

  /**
   * Set is_saved property
   * 
   * @param string $is_saved          
   * @ignore
   */
  protected function setMapIs_saved($is_saved) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapIs_saved($is_saved)");
    $this->_is_saved = $is_saved;
  }
  /**
   * Get is_saved property
   * 
   * @ignore
   */
  protected function getMapIs_saved() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapIs_saved()");
    return $this->_is_saved;
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
    $this->_self_invite = $self_invite;
  }
  /**
   * Get self invite property
   *
   * @ignore
   *
   */
  protected function getMapSelf_invite() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapSelf_invite()");
    return $this->_self_invite;
  }

  /**
   * Set delegated_from property
   *
   * @param string $delegated_from
   * @ignore
   */
  protected function setMapDelegated_from($delegated_from) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapDelegated_from($delegated_from)");
    $this->_delegated_from = $delegated_from;
  }
  /**
   * Get delegated_from property
   *
   * @ignore
   */
  protected function getMapDelegated_from() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapDelegated_from()");
    return $this->_delegated_from;
  }

  /**
   * Set delegated_to property
   *
   * @param string $delegated_to
   * @ignore
   */
  protected function setMapDelegated_to($delegated_to) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapDelegated_to($delegated_to)");
    $this->_delegated_to = $delegated_to;
  }
  /**
   * Get delegated_to property
   *
   * @ignore
   */
  protected function getMapDelegated_to() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapDelegated_to()");
    return $this->_delegated_to;
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
      $this->_type = MappingMce::$MapAttendeeTypeObjectToMce[$type];
  }
  /**
   * Get type property
   * 
   * @ignore
   *
   */
  protected function getMapType() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapType()");
    if (isset(MappingMce::$MapAttendeeTypeMceToObject[$this->_type]))
      return MappingMce::$MapAttendeeTypeMceToObject[$this->_type];
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
      $this->_response = MappingMce::$MapAttendeeResponseObjectToMce[$response];
  }
  /**
   * Get response property
   * 
   * @ignore
   *
   */
  protected function getMapResponse() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapResponse()");
    if (isset(MappingMce::$MapAttendeeResponseMceToObject[$this->_response]))
      return MappingMce::$MapAttendeeResponseMceToObject[$this->_response];
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
      $this->_role = MappingMce::$MapAttendeeRoleObjectToMce[$role];
  }
  /**
   * Get role property
   * 
   * @ignore
   *
   */
  protected function getMapRole() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapRole()");
    if (isset(MappingMce::$MapAttendeeRoleMceToObject[$this->_role]))
      return MappingMce::$MapAttendeeRoleMceToObject[$this->_role];
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
    $this->_uid = $uid;
  }
  /**
   * Mapping attendee uid field
   */
  protected function getMapUid() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapUid()");
    // Si l'email n'est pas set ou si c'est un externe on considère que c'est individuel
    if (!isset($this->_email) || isset($this->_is_external) && $this->_is_external) {
      return null;
    }

    // Doit-on rechercher dans l'annuaire ?
    if (!isset($this->_uid)) {
        $this->_setAttendeeFromUser();
    }
    return $this->_uid;
  }

  /**
   * Mapping is_individuelle field
   * 
   * @return boolean true si la boite est individuelle
   */
  protected function getMapIs_individuelle() {
    // Si l'email n'est pas set ou si c'est un externe on considère que c'est individuel
    if (!isset($this->_email) || isset($this->_is_external) && $this->_is_external) {
      return true;
    }

    // Doit-on rechercher dans l'annuaire ?
    if (!isset($this->_is_individuelle)) {
        $this->_setAttendeeFromUser();

        if ($this->_is_external) {
          return true;
        }
    }
    return $this->_is_individuelle;
  }

  /**
   * Mapping is_ressource field
   * 
   * @return boolean true si la boite est une ressource
   */
  protected function getMapIs_ressource() {
    // Si l'email n'est pas set ou si c'est un externe ce n'est pas une ressource (en tout cas ça nous concerne pas)
    if (!isset($this->_email) || isset($this->_is_external) && $this->_is_external) {
      return false;
    }

    // Doit-on rechercher dans l'annuaire ?
    if (!isset($this->_is_ressource)) {
        $this->_setAttendeeFromUser();

        if ($this->_is_external) {
          return false;
        }
    }
    return $this->_is_ressource;
  }

  /**
   * Mapping is_list field
   * 
   * @return boolean true si la boite est une liste
   */
  protected function getMapIs_list() {
    // Si l'email n'est pas set ou si c'est un externe ce n'est pas une liste (en tout cas ça nous concerne pas)
    if (!isset($this->_email) || isset($this->_is_external) && $this->_is_external) {
      return false;
    }

    // Doit-on rechercher dans l'annuaire ?
    if (!isset($this->_is_list)) {
        $this->_setAttendeeFromUser();

        if ($this->_is_external) {
          return false;
        }
    }
    return $this->_is_list;
  }

  /**
   * Mapping members field
   * 
   * @return array] Liste d'adresses email
   */
  protected function getMapMembers() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapMembers()");
    if (isset($this->_email)) {
      $Group = $this->__getNamespace() . '\\Group';
      $group = new $Group();
      $group->email = $this->_email;
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
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapNeed_action()");
    if (!isset($this->_need_action) 
        && (isset($this->_email) || isset($this->_uid))) {
      $need_action = Config::get(Config::NEED_ACTION_ENABLE);
      if ($need_action) {
        $filter = Config::get(Config::NEED_ACTION_DISABLE_FILTER);
      }
      else {
        $filter = Config::get(Config::NEED_ACTION_ENABLE_FILTER);
      }
      if (isset($filter)) {
        if (!isset($this->_user)) {
          $User = $this->__getNamespace() . '\\User';
          $this->_user = new $User();
          if (isset($this->_email)) {
            $this->_user->email = $this->_email;
          }
          else {
            $this->_user->uid = $this->_uid;
          }
        }
        $fields = [];
        foreach ($filter as $field => $f) {
          $fields[] = $field;
        }
        if ($this->_user->load($fields) && ($this->_user->is_individuelle || $this->_user->is_applicative)) {
          foreach ($fields as $field) {
            $match = false;
            if (is_array($this->_user->$field)) {
              if (in_array($filter[$field], $this->_user->$field)) {
                $match = true;
              }
            }
            else if ($this->_user->$field == $filter[$field]) {
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
      $this->_need_action = $need_action;
    }   
    return $this->_need_action;
  }

  /**
   * Positionne les champs de l'attendee à partir de l'information de l'annuaire
   */
  protected function _setAttendeeFromUser() {
    if (!isset($this->_user)) {
      $User = $this->__getNamespace() . '\\User';
      $this->_user = new $User();
      $this->_user->email = $this->_email;
    }
    if ($this->_user->load()) {
      // Si c'est une liste elle n'a pas d'uid
      $this->_is_list = $this->_user->is_list;
      if (!$this->_is_list) {
        $this->_uid = $this->_user->uid;
      }
      $this->_name = $this->_user->fullname;
      $this->_is_ressource = $this->_user->is_ressource;
      $this->_is_individuelle = $this->_user->is_individuelle || $this->_user->is_applicative;
      $this->_is_external = false;
    }
    else {
      // C'est un participant externe
      $this->_is_external = true;

      // Réinitialiser les paramètres juste au cas ou
      $this->_uid = null;
      $this->_is_ressource = null;
      $this->_is_individuelle = null;
      $this->_is_list = null;
    }
  }
}