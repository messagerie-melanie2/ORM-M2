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
use LibMelanie\Objects\TaskslistMelanie;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\Config;

/**
 * Classe liste de tâches pour MCE
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/MCE
 * @api
 * 
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
class Taskslist extends MceObject {
  /**
   * Accès aux objets associés
   * Utilisateur associé à l'objet
   * 
   * @var User $user
   * @ignore
   *
   */
  public $user;
  
  /**
   * Constructeur de l'objet
   * 
   * @param User|string $user ou $id
   */
  function __construct($user = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");
    // Définition du calendrier melanie2
    $this->objectmelanie = new TaskslistMelanie();
    // Définition des objets associés
    if (isset($user)) {
      if (is_object($user)) {
        $this->user = $user;
        $this->objectmelanie->user_uid = $this->user->uid;
      }
      else {
        $this->objectmelanie->id = $user;
      }
    }
  }
  
  /**
   * Défini l'utilisateur MCE
   * 
   * @param User $user          
   * @ignore
   *
   */
  public function setUserMelanie($user) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setUserMelanie()");
    $this->user = $user;
    $this->objectmelanie->user_uid = $this->user->uid;
  }
  
  /**
   * ***************************************************
   * METHOD MAPPING
   */
  /**
   * Enregistrement de l'objet
   * Nettoie le cache du user
   * 
   * @return null si erreur, boolean sinon (true insert, false update)
   */
  public function save() {
    $ret = $this->objectmelanie->save();
    if (!is_null($ret) && isset($this->user)) {
      $this->user->cleanTaskslists();
    }
    return $ret;
  }

  /**
   * Suppression de l'objet
   * Nettoie le cache du user
   * 
   * @return boolean
   */
  public function delete() {
    $ret = $this->objectmelanie->delete();
    if ($ret && isset($this->user)) {
      $this->user->cleanTaskslists();
    }
    return $ret;
  }

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
    $Task = $this->__getNamespace() . '\\Task';
    foreach ($_tasks as $_task) {
      $task = new $Task($this->user, $this);
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
      $url = str_replace(['%u', '%o', '%i'], [$this->user->uid, $this->objectmelanie->owner, $this->objectmelanie->id], Config::get(Config::TASKSLIST_CALDAV_URL));
    }
    return $url;
  }
}