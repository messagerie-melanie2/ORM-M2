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
 * Classe objet partagé LDAP pour Melanie2
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage API Mélanie2
 *             @api
 * @property string $uid Identifiant unique de l'utilisateur
 * @property string $fullname Nom complet de l'utilisateur
 * @property string $email_send Adresse email d'émission principale de l'utilisateur
 * @property array $email_send_list Liste de toutes les adresses email d'émission de l'utilisateur
 * @property-read User $mailbox Récupère la boite mail associé à l'objet de partage
 */
class ObjectShare extends Melanie2Object {
  /**
   * Délimiteur de l'objet de partage
   * 
   * @var string
   */
  const DELIMITER = '.-.';
  /**
   * Boite associée à l'objet de partage
   * 
   * @var User
   */
  private $_mailbox;
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
   * Retourne la boite mail associée à l'objet de partage
   * 
   * @return \LibMelanie\Api\Melanie2\User
   */
  protected function getMapMailbox() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapMailbox()");
    if (!isset($this->_mailbox)) {
      $uid = explode(self::DELIMITER, $this->uid, 2);
      $uid = $uid[1];
      $this->_mailbox = new User();
      $this->_mailbox->uid = $uid;
      if (!$this->_mailbox->load()) {
        $this->_mailbox = null;
      }
    }
    return $this->_mailbox;
  }
}