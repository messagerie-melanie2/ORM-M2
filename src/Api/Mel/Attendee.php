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
namespace LibMelanie\Api\Mel;

use LibMelanie\Api\Defaut;
use LibMelanie\Config\MappingMce;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\DefaultConfig;

/**
 * Classe attendee pour les évènements pour Mel
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Mel
 * @api
 * 
 * @property string $email Email du participant
 * @property string $name Nom du participant
 * @property string $uid Uid du participant
 * @property boolean $self_invite Est-ce que ce participant s'est lui même invité
 * @property-read boolean $need_action Est-ce que le mode En attente est activé pour ce participant
 * @property Attendee::RESPONSE_* $response Réponse du participant
 * @property Attendee::ROLE_* $role Role du participant
 */
class Attendee extends Defaut\Attendee {
  /**
   * Domaine de l'email des ressources Bnum
   */
  const bnum_resources_email_domain = 'bnum.i2';

  /**
   * Ressource associée a l'attendee
   * 
   * @var Resource
   */
  protected $_resource;

  /**
   * Get type property
   * 
   * @ignore
   *
   */
  protected function getMapType() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapType()");
    if (isset(MappingMce::$MapAttendeeTypeMceToObject[$this->_type])) {
      return MappingMce::$MapAttendeeTypeMceToObject[$this->_type];
    }
    else {
      if (isset($this->_email) && strpos($this->_email, self::bnum_resources_email_domain) !== false) {
        // Il s'agit d'une ressource Bnum à traiter
        $this->_setAttendeeFromResource();

        return MappingMce::$MapAttendeeTypeMceToObject[$this->_type];
      }
      else {
        // Charger le type d'utilisateur depuis l'annuaire
        $this->_setAttendeeFromUser();

        if ($this->_user->is_ressource) {
          return self::TYPE_RESOURCE;
        }
        else if ($this->_user->is_list) {
          return self::TYPE_GROUP;
        }
        else {
          return self::TYPE_INDIVIDUAL;
        }
      }
    }
    return self::TYPE_INDIVIDUAL;
  }

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

    if (strpos($this->_email, self::bnum_resources_email_domain) !== false) {
      $this->_setAttendeeFromResource();
    }
  }

  /**
   * Positionne les champs de l'attendee à partir de l'information de l'annuaire
   */
  protected function _setAttendeeFromResource() {
    if (!isset($this->_resource)) {
      $Resource = $this->__getNamespace() . '\\Resource';
      $this->_resource = new $Resource();
      $this->_resource->email = $this->_email;
    }
    if ($this->_resource->load()) {
      // Si c'est une liste elle n'a pas d'uid
      $this->_is_list = false;
      $this->_uid = $this->_resource->uid;
      $this->_name = $this->_resource->fullname;
      $this->_is_ressource = true;
      $this->_is_individuelle = false;
      $this->_is_external = false;

      // Retrouver le type
      switch ($this->_resource->type) {
        case 'Flex Office':
          $this->_type = MappingMce::$MapAttendeeTypeObjectToMce[DefaultConfig::FLEX_OFFICE];
          break;
        case 'Salle':
          $this->_type = MappingMce::$MapAttendeeTypeObjectToMce[DefaultConfig::ROOM];
          break;
        case 'Voiture':
          $this->_type = MappingMce::$MapAttendeeTypeObjectToMce[DefaultConfig::CAR];
          break;
        case 'Matériel':
          $this->_type = MappingMce::$MapAttendeeTypeObjectToMce[DefaultConfig::HARDWARE];
          break;
      }
    }
  }
}