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
use LibMelanie\Objects\ObjectMelanie;
use LibMelanie\Config\MappingMelanie;
use LibMelanie\Log\M2Log;

/**
 * Classe pour la gestion des Sync pour les taskslist
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage API Mélanie2
 *             @api
 * @property integer $token Numéro de token associé à l'objet Sync
 * @property string $taskslist Identifiant du taskslist associé à l'objet Sync
 * @property string $uid UID de la tâche concernée par le Sync
 * @property string $action Action effectuée sur l'uid (add, mod, del)
 * @method bool load() Chargement du TaskslistSync, en fonction du taskslist et du token
 * @method bool exists() Test si le TaskslistSync existe, en fonction du taskslist et du token
 */
class TaskslistSync extends Melanie2Object {
  
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
   * @param \LibMelanie\Objects\TaskslistMelanie $calendarmelanie          
   */
  function __construct($taskslistmelanie = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    // M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->__construct()");
    // Définition de la propriété de l'objet
    $this->objectmelanie = new ObjectMelanie('TaskslistSync');
    
    // Définition des objets associés
    if (isset($taskslistmelanie)) {
      $this->objectmelanie->taskslist = $taskslistmelanie->id;
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
  public function listTaskslistSync($limit = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->listTaskslistSync($limit)");
    $result = [
        'added' => [],
        'modified' => [],
        'deleted' => []
    ];
    if (isset($this->token)) {
      $operators = [
          'token' => \LibMelanie\Config\MappingMelanie::sup
      ];
      foreach ($this->objectmelanie->getList(null, null, $operators, 'token', false, $limit) as $_taskslistSync) {
        $mapAct = self::$actionMapper[$_taskslistSync->action];
        $result[$mapAct][] = $_taskslistSync->uid . '.ics';
      }
    } else {
      $task = new \LibMelanie\Api\Melanie2\Task();
      $task->taskslist = $this->objectmelanie->taskslist;
      foreach ($task->getList([
          'uid'
      ]) as $_task) {
        $result['added'][] = $_task->uid . '.ics';
      }
    }
    
    return $result;
  }

/**
 * ***************************************************
 * DATA MAPPING
 */
}