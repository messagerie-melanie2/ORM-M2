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
namespace LibMelanie\Api\Dgfip;

use LibMelanie\Api\Mce;

/**
 * Classe objet partagé LDAP pour DGFIP
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/DGFIP
 * @api
 * 
 * @property string $uid Identifiant unique de l'utilisateur
 * @property string $fullname Nom complet de l'utilisateur
 * @property string $email_send Adresse email d'émission principale de l'utilisateur
 * @property array $email_send_list Liste de toutes les adresses email d'émission de l'utilisateur
 * @property-read User $mailbox Récupère la boite mail associé à l'objet de partage
 * @property-read string $user_uid L'uid de l'utilisateur de l'objet de partage
 */
class ObjectShare extends Mce\ObjectShare {
  /**
   * Délimiteur de l'objet de partage
   * 
   * @var string
   */
  const DELIMITER = '+';

  /**
   * Crée l'objet de partage a partir d'une mailbox
   * 
   * @param User $mailbox
   * @param User $user
   */
  public function setMailbox($mailbox, $user) {
    $this->_mailbox = $mailbox;
    $this->objectmelanie = clone $mailbox->getObjectMelanie();
    $this->objectmelanie->uid = $user->uid . self::DELIMITER . $this->objectmelanie->uid;
  }
}