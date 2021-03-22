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
use LibMelanie\Objects\TaskslistMelanie;
use LibMelanie\Objects\UserMelanie;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\Config;

/**
 * Classe liste de tâches pour Melanie2
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage API Mélanie2
 *             @api
 * @property string $id Identifiant unique de la liste de tâche
 * @property string $owner Identifiant du propriétaire de la liste de tâche
 * @property string $name Nom complet de la liste de tâche
 * @property int $perm Permission associée, utiliser asRight()
 * @property string $ctag CTag de la liste de tâche
 * @property int $synctoken SyncToken de la liste de tâche
 * @property-read string $caldavurl URL CalDAV pour la liste de tâches
 * @method bool load() Charge les données de la liste de tâche depuis la base de données
 * @method bool exists() Non implémentée
 * @method bool save() Non implémentée
 * @method bool delete() Non implémentée
 * @method void getCTag() Charge la propriété ctag avec l'identifiant de modification de la liste de tâche
 * @method void getTimezone() Charge la propriété timezone avec le timezone de la liste de tâche
 * @method bool asRight($action) Retourne un boolean pour savoir si les droits sont présents
 */
class Taskslist extends Melanie2Object {
  /**
   * Accès aux objets associés
   * UID de l'utilisateur du calendrier
   * 
   * @var string $usermelanie
   * @ignore
   *
   */
  public $usermelanie;
  
  /**
   * Constructeur de l'objet
   * 
   * @param UserMelanie $usermelanie          
   */
  function __construct($usermelanie = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");
    // Définition du calendrier melanie2
    $this->objectmelanie = new TaskslistMelanie();
    // Définition des objets associés
    if (isset($usermelanie)) {
      $this->usermelanie = $usermelanie;
      $this->objectmelanie->user_uid = $this->usermelanie->uid;
    }
  }
  
  /**
   * Défini l'utilisateur Melanie
   * 
   * @param UserMelanie $usermelanie          
   * @ignore
   *
   */
  public function setUserMelanie($usermelanie) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setUserMelanie()");
    $this->usermelanie = $usermelanie;
    $this->objectmelanie->user_uid = $this->usermelanie->uid;
  }
  
  /**
   * ***************************************************
   * METHOD MAPPING
   */
  /**
   * Récupère la liste de toutes les tâches
   * need: $this->id
   * 
   * @return Task[]
   */
  public function getAllTasks() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getAllTasks()");
    $_tasks = $this->objectmelanie->getAllTasks();
    if (!isset($_tasks))
      return null;
    $tasks = [];
    foreach ($_tasks as $_task) {
      $task = new Task($this->usermelanie, $this);
      $task->setObjectMelanie($_task);
      $tasks[$_task->uid . $_task->taskslist] = $task;
    }
    // Détruit les variables pour libérer le plus rapidement de la mémoire
    unset($_tasks);
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    return $tasks;
  }
  
  /**
   * ***************************************************
   * DATA MAPPING
   */
  /**
   * Mapping caldavurl field
   */
  protected function getMapCaldavurl() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapCaldavurl()");
    if (!isset($this->objectmelanie)) throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    $url = null;
    if (Config::is_set(Config::TASKSLIST_CALDAV_URL)) {
      $url = str_replace(['%u', '%o', '%i'], [$this->usermelanie->uid, $this->objectmelanie->owner, $this->objectmelanie->id], Config::get(Config::TASKSLIST_CALDAV_URL));
    }
    return $url;
  }
}