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
use LibMelanie\Objects\ObjectMelanie;
use LibMelanie\Log\M2Log;

/**
 * Classe pour la gestion des Sync pour les addressbook
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property integer $token Numéro de token associé à l'objet Sync
 * @property string $addressbook Identifiant de l'addressbook associé à l'objet Sync
 * @property string $uid UID du contact concerné par le Sync
 * @property string $action Action effectuée sur l'uid (add, mod, del)
 * @method bool load() Chargement du AddressbookSync, en fonction de l'addressbook et du token
 * @method bool exists() Test si le AddressbookSync existe, en fonction de l'addressbook et du token
 */
class AddressbookSync extends MceObject {
  
  /**
   * Mapping des actions entre la base et SabreDAV
   * 
   * @var array
   */
  private static $actionMapper = [
      'add' => 'added',
      'mod' => 'modified',
      'del' => 'deleted'
  ];
  
  /**
   * Constructeur de l'objet
   * 
   * @param Addressbook $addressbook          
   */
  function __construct($addressbook = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    // M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->__construct()");
    // Définition de la propriété de l'objet
    $this->objectmelanie = new ObjectMelanie('AddressbookSync');
    
    // Définition des objets associés
    if (isset($addressbook)) {
      $this->objectmelanie->addressbook = $addressbook->id;
    }
  }
  
  /**
   * ***************************************************
   * METHOD MAPPING
   */
  /**
   * Ne pas implémenter la sauvegarde pour l'instant
   * Le SyncToken est alimenté par le trigger
   * 
   * @return boolean
   */
  function save() {
    return false;
  }
  /**
   * Ne pas implémenter la suppression pour l'instant
   * Le SyncToken est alimenté par le trigger
   * 
   * @return boolean
   */
  function delete() {
    return false;
  }
  
  /**
   * Liste les actions par uid depuis le dernier token
   * 
   * @param integer $limit
   *          [Optionnel]
   */
  public function listAddressbookSync($limit = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->listAddressbookSync($limit)");
    $result = [
        'added' => [],
        'modified' => [],
        'deleted' => []
    ];
    if (isset($this->token)) {
      $operators = [
          'token' => \LibMelanie\Config\MappingMce::sup
      ];
      foreach ($this->objectmelanie->getList(null, null, $operators, 'token', false, $limit) as $_addressbookSync) {
        $mapAct = self::$actionMapper[$_addressbookSync->action];
        // MANTIS 0004696: [SyncToken] Ne retourner qu'un seul uid
        $uid = $this->uidencode($_addressbookSync->uid) . '.vcf';
        if (!in_array($uid, $result['added'])
            && !in_array($uid, $result['modified'])
            && !in_array($uid, $result['deleted'])) {
          $result[$mapAct][] = $uid;
        }     
      }
    } else {
      $Contact = $this->__getNamespace() . '\\Contact';
      $contact = new $Contact();
      $contact->addressbook = $this->objectmelanie->addressbook;
      foreach ($contact->getList(['id', 'uid']) as $_contact) {
        $result['added'][] = $this->uidencode($_contact->uid) . '.vcf';
      }
    }
    
    return $result;
  }

  /**
   * ***************************************************
   * PRIVATE
   */
  /**
   * Encodage d'un uid pour les uri (pour les / notamment)
   * @param string $uid
   * @return string
   */
  private function uidencode($uid) {
    $search = ['/'];
    $replace = ['%2F'];
    return str_replace($search, $replace, $uid);
  }
}