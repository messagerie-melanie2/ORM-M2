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
namespace LibMelanie\Api\Defaut\Users;

use LibMelanie\Lib\MceObject;

/**
 * Classe utilisateur par defaut
 * pour la gestion des partages de messagerie
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut/Users
 * @api
 * 
 * @property string $user Identifiant de l'utilisateur
 * @property string $type Type de partage (Voir Share::TYPE_*)
 */
class Share extends MceObject {
  // Type de partage : Lecture seule, Ecriture, Emission, Gestionnaire
  const TYPE_READ = 'L';
  const TYPE_WRITE = 'E';
  const TYPE_SEND = 'C';
  const TYPE_ADMIN = 'G';

  /**
   * Liste des propriétés à sérialiser pour le cache
   */
  protected $serializedProperties = [
    'user',
    'type',
  ];

  /**
   * Identifiant de l'utilisateur
   * @var string $user
   */
  protected $user;
  /**
   * Type de partage (Voir Share::TYPE_*)
   * @var string $type
   */
  protected $type;
  
  /**
   * Mapping user field
   *
   * @param string $user
   */
  protected function setMapUser($user) {
    $this->user = $user;
  }
  /**
   * Mapping user field
   * 
   * @return string $user
   */
  protected function getMapUser() {
    return $this->user;
  }
  /**
   * Mapping user field
   *
   * @return boolean
   */
  protected function issetMapUser() {
    return isset($this->user);
  }

  /**
   * Mapping type field
   *
   * @param string $type
   */
  protected function setMapType($type) {
    $this->type = $type;
  }
  /**
   * Mapping type field
   * 
   * @return string $type
   */
  protected function getMapType() {
    return $this->type;
  }
  /**
   * Mapping type field
   *
   * @return boolean
   */
  protected function issetMapType() {
    return isset($this->type);
  }
}