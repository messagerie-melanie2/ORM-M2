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
namespace LibMelanie\Api\Gn;

use LibMelanie\Api\Defaut;
use LibMelanie\Api\Defaut\User;
use LibMelanie\Log\M2Log;

/**
 * Classe objet partagé LDAP pour GN
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/GN
 * @api
 * 
 * @property string $uid Identifiant unique de l'utilisateur
 * @property string $fullname Nom complet de l'utilisateur
 * @property string $email_send Adresse email d'émission principale de l'utilisateur
 * @property array $email_send_list Liste de toutes les adresses email d'émission de l'utilisateur
 * @property-read User $mailbox Récupère la boite mail associé à l'objet de partage
 * @property-read string $user_uid L'uid de l'utilisateur de l'objet de partage
 */
class ObjectShare extends Defaut\ObjectShare {
  /**
   * Délimiteur de l'objet de partage
   * 
   * @var string
   */
  const DELIMITER = '.-.';


    /**
     * Retourne la boite mail associée à l'objet de partage
     *
     * @return User
     */
    protected function getMapMailbox() {
        M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapMailbox()");
        if (!isset($this->_mailbox)) {
            $uid = explode(static::DELIMITER, $this->uid, 2);
            $this->_user_uid = $uid[0];
            $this->_mailbox_uid = $uid[1];
            $d = explode('@', $this->_mailbox_uid);
            $this->_user_uid .= "@".$d[1];
            $class = $this->__getNamespace() . '\\User';
            $this->_mailbox = new $class($this->_server, $this->_itemName);
            $this->_mailbox->uid = $this->_mailbox_uid;
            $this->_mailbox->load();
        }
        return $this->_mailbox;
    }

      /**
   * Retourne l'uid de l'utilisateur de l'objet de partage
   *
   * @return string
   */
  protected function getMapUser_uid() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapUser_Uid()");
    if (!isset($this->_user_uid)) {
        $uid = explode(static::DELIMITER, $this->uid, 2);
        $this->_user_uid = $uid[0];
        $this->_mailbox_uid = $uid[1];
        $d = explode('@', $this->_mailbox_uid);
        $this->_user_uid .= "@".$d[1];
    }
    return $this->_user_uid;
  }

    /**
   * Retourne l'uid de la boite possedant l'objet de partage
   *
   * @return string
   */
  protected function getMapMailbox_uid() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapMailbox_uid()");
    if (!isset($this->_mailbox_uid)) {
        $this->_mailbox_uid = $this->mailbox->uid;
    }
    return $this->_mailbox_uid;
  }
}