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
use LibMelanie\Objects\HistoryMelanie;
use LibMelanie\Config\MappingMce;
use LibMelanie\Exceptions;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\Config;
use LibMelanie\Config\DefaultConfig;

/**
 * Classe tâche par defaut,
 * implémente les API de la librairie pour aller chercher les données dans la base de données
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $id Identifiant unique de la tâche
 * @property string $taskslist Identifiant de la liste de tâches associée
 * @property string $uid UID de la tâche
 * @property string $owner Créateur de la tâche
 * @property string $name Nom de la tâche
 * @property string $description Description de la tâche
 * @property Task::PRIORITY_* $priority Priorité de la tâche
 * @property string $category Catégorie de la tâche
 * @property int $alarm Alarme en minute (TODO: class Alarm)
 * @property Task::COMPLETED_* $completed Tâche terminée
 * @property Task::CLASS_* $class Class de la tâche (privé/public)
 * @property string $assignee Utilisateur à qui est assigné la tâche
 * @property int $estimate Estimation de la tâche ?
 * @property string $parent ID de la tâche parente
 * @property int $due Timestamp correspondant à la date de fin prévue
 * @property int $completed_date Timestamp correspondant à la date de fin réelle
 * @property int $start Timestamp correspondant à la date de début
 * @property int $modified Timestamp de la modification de la tâche
 *           Liste des attributs :
 * @property int $percent_complete Pourcentage de réalisation pour la tâche
 * @property string $status Status de la tâche
 * @property string $ics ICS associé à l'évènement courant, calculé à la volée en attendant la mise en base de données
 * @method bool load() Chargement l'évènement, en fonction du taskslist et de l'uid
 * @method bool exists() Test si l'évènement existe, en fonction du taskslist et de l'uid
 * @method bool save() Sauvegarde l'évènement et l'historique dans la base de données
 * @method bool delete() Supprime l'évènement et met à jour l'historique dans la base de données
 */
class Task extends MceObject {
  // Accès aux objets associés
  /**
   * Utilisateur associé à l'objet
   * 
   * @var User
   */
  protected $user;
  /**
   * Liste de tâches associée à l'objet
   * 
   * @var Taskslist
   */
  protected $taskslistmce;
  /**
   * Tableau d'attributs pour l'évènement
   * 
   * @var string[$attribute]
   */
  private $attributes;
  /**
   * Permet de savoir si les attributs ont déjà été chargés depuis la base
   * 
   * @var bool
   */
  private $attributes_loaded = false;
  
  // CLASS Fields
  const CLASS_PRIVATE = DefaultConfig::PRIV;
  const CLASS_PUBLIC = DefaultConfig::PUB;
  const CLASS_CONFIDENTIAL = DefaultConfig::CONFIDENTIAL;
  
  // PRIORITY Fields
  const PRIORITY_NO = DefaultConfig::NO_PRIORITY;
  const PRIORITY_VERY_HIGH = DefaultConfig::VERY_HIGH;
  const PRIORITY_HIGH = DefaultConfig::HIGH;
  const PRIORITY_NORMAL = DefaultConfig::NORMAL;
  const PRIORITY_LOW = DefaultConfig::LOW;
  const PRIORITY_VERY_LOW = DefaultConfig::VERY_LOW;
  
  // COMPLETED Fields
  const COMPLETED_TRUE = DefaultConfig::COMPLETED;
  const COMPLETED_FALSE = DefaultConfig::NOTCOMPLETED;
  
  // STATUS Fields
  const STATUS_IN_PROCESS = 'IN-PROCESS';
  const STATUS_NEEDS_ACTION = 'NEEDS-ACTION';
  const STATUS_CANCELLED = 'CANCELLED';
  const STATUS_COMPLETED = 'COMPLETED';
  
  /**
   * Constructeur de l'objet
   * 
   * @param User $user          
   * @param Taskslist $taskslist        
   */
  function __construct($user = null, $taskslist = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    // M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->__construct()");
    // Définition de la tâche melanie2
    $this->objectmelanie = new ObjectMelanie('TaskMelanie');
    
    // Définition des objets associés
    if (isset($user))
      $this->user = $user;
    if (isset($taskslist)) {
      $this->taskslistmce = $taskslist;
      $this->objectmelanie->taskslist = $this->taskslistmce->id;
    }
  }
  
  /**
   * Défini l'utilisateur MCE
   * 
   * @param User $user          
   * @ignore
   *
   */
  function setUserMelanie($user) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setUserMelanie()");
    $this->user = $user;
  }
  
  /**
   * Défini la liste de tâches MCE
   * 
   * @param Taskslist $taskslist          
   * @ignore
   *
   */
  function setTaskslistMelanie($taskslist) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setTaskslistMelanie()");
    $this->taskslistmce = $taskslist;
    $this->objectmelanie->taskslist = $this->taskslistmce->id;
  }
  
  /**
   * Retourne un attribut supplémentaire pour la tâche
   * 
   * @param string $name
   *          Nom de l'attribut
   * @return string|NULL valeur de l'attribut, null s'il n'existe pas
   */
  public function getAttribute($name) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getAttribute($name)");
    // Si les attributs n'ont pas été chargés
    if (!$this->attributes_loaded) {
      $this->loadAttributes();
    }
    if (!isset($this->attributes[$name])) {
      return null;
    }
    return $this->attributes[$name]->value;
  }
  /**
   * Met à jour ou ajoute l'attribut
   * 
   * @param string $name
   *          Nom de l'attribut
   * @param string $value
   *          Valeur de l'attribut
   */
  public function setAttribute($name, $value) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setAttribute($name)");
    if (!isset($value)) {
      // Si name est a null on supprime le champ
      $this->deleteAttribute($name);
    } else {
      // Création de l'objet s'il n'existe pas
      if (!isset($this->attributes))
        $this->attributes = [];
      $TaskProperty = $this->__getNamespace() . '\\TaskProperty';
      $taskproperty = new $TaskProperty();
      $taskproperty->task = $this->uid;
      if (isset($this->taskslistmce)) {
        $taskproperty->taskslist = $this->taskslistmce->id;
      } else {
        $taskproperty->taskslist = $this->taskslist;
      }
      $taskproperty->user = isset($this->owner) ? $this->owner : $this->user->uid;
      $taskproperty->key = $name;
      $taskproperty->value = $value;
      $this->attributes[$name] = $taskproperty;
    }
  }
  /**
   * Suppression d'un attribut
   * 
   * @param string $name          
   */
  public function deleteAttribute($name) {
    // Si les attributs n'ont pas été chargés
    if (!$this->attributes_loaded) {
      $this->loadAttributes();
    }
    // Si l'atrribut existe, on le supprime
    if (isset($this->attributes[$name])) {
      return $this->attributes[$name]->delete();
    }
    return false;
  }
  
  /**
   * ***************************************************
   * METHOD MAPPING
   */
  /**
   * Mapping de la sauvegarde de l'objet
   * Appel la sauvegarde de l'historique en même temps
   * 
   * @ignore
   *
   */
  function save() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->save()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    // Sauvegarde l'objet
    $insert = $this->objectmelanie->save();
    if (!is_null($insert)) {
      // Sauvegarde des attributs
      $this->saveAttributes();
      // Gestion de l'historique
      $history = new HistoryMelanie();
      $history->uid = Config::get(Config::TASKSLIST_PREF_SCOPE) . ":" . $this->objectmelanie->taskslist . ":" . $this->objectmelanie->uid;
      $history->action = $insert ? Config::get(Config::HISTORY_ADD) : Config::get(Config::HISTORY_MODIFY);
      $history->timestamp = time();
      $history->description = "LibM2/" . Config::get(Config::APP_NAME);
      $history->who = isset($this->user) ? $this->user->uid : $this->objectmelanie->taskslist;
      // Enregistrement dans la base
      return $history->save();
    }
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->save() Error: return false");
    return false;
  }
  
  /**
   * Mapping de la suppression de l'objet
   * Appel la sauvegarde de l'historique en même temps
   * 
   * @ignore
   *
   */
  function delete() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->delete()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    // Suppression de l'objet
    if ($this->objectmelanie->delete()) {
      // Suppression des attributs liés à la tâche
      $this->deleteAttributes();
      // Gestion de l'historique
      $history = new HistoryMelanie();
      $history->uid = Config::get(Config::TASKSLIST_PREF_SCOPE) . ":" . $this->objectmelanie->taskslist . ":" . $this->objectmelanie->uid;
      $history->action = Config::get(Config::HISTORY_DELETE);
      $history->timestamp = time();
      $history->description = "LibM2/" . Config::get(Config::APP_NAME);
      $history->who = isset($this->user) ? $this->user->uid : $this->objectmelanie->taskslist;
      // Enregistrement dans la base
      return $history->save();
    }
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->delete() Error: return false");
    return false;
  }
  
  /**
   * Appel le load maitre
   * 
   * @ignore
   *
   */
  function load() {
    $ret = $this->objectmelanie->load();
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    return $ret;
  }
  
  /**
   * Permet de récupérer la liste d'objet en utilisant les données passées
   * (la clause where s'adapte aux données)
   * Il faut donc peut être sauvegarder l'objet avant d'appeler cette méthode
   * pour réinitialiser les données modifiées (propriété haschanged)
   * 
   * @param String[] $fields
   *          Liste les champs à récupérer depuis les données
   * @param String $filter
   *          Filtre pour la lecture des données en fonction des valeurs déjà passé, exemple de filtre : "((#description# OR #title#) AND #start#)"
   * @param String[] $operators
   *          Liste les propriétés par operateur (MappingMce::like, MappingMce::supp, MappingMce::inf, MappingMce::diff)
   * @param String $orderby
   *          Tri par le champ
   * @param bool $asc
   *          Tri ascendant ou non
   * @param int $limit
   *          Limite le nombre de résultat (utile pour la pagination)
   * @param int $offset
   *          Offset de début pour les résultats (utile pour la pagination)
   * @param String[] $case_unsensitive_fields
   *          Liste des champs pour lesquels on ne sera pas sensible à la casse
   * @return Task[] Array
   */
  function getList($fields = [], $filter = "", $operators = [], $orderby = "", $asc = true, $limit = null, $offset = null, $case_unsensitive_fields = []) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getList()");
    $_tasks = $this->objectmelanie->getList($fields, $filter, $operators, $orderby, $asc, $limit, $offset, $case_unsensitive_fields);
    if (!isset($_tasks))
      return null;
    $tasks = [];
    foreach ($_tasks as $_task) {
      $_task->setIsExist();
      $_task->setIsLoaded();
      $task = new static($this->user, $this->taskslistmce);
      $task->setObjectMelanie($_task);
      $tasks[$_task->uid] = $task;
    }
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    return $tasks;
  }
  
  /**
   * Sauvegarde les attributs dans la base de données
   */
  private function saveAttributes() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->saveAttributes()");
    // Parcours les attributs pour les enregistrer
    if (isset($this->attributes)) {
      foreach ($this->attributes as $name => $attribute) {
        $attribute->save();
      }
    }
  }
  /**
   * Charge les attributs en mémoire
   */
  private function loadAttributes() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->loadAttributes()");
    // Création de l'objet s'il n'existe pas
    if (!isset($this->attributes))
      $this->attributes = [];
    $TaskProperty = $this->__getNamespace() . '\\TaskProperty';
    // Génération de l'attribut pour le getList
    $taskproperty = new $TaskProperty();
    $taskproperty->task = $this->uid;
    if (isset($this->taskslistmce)) {
      $taskproperty->taskslist = $this->taskslistmce->id;
    } else {
      $taskproperty->taskslist = $this->taskslist;
    }
    $taskproperty->user = isset($this->owner) ? $this->owner : $this->user->uid;
    $properties = $taskproperty->getList();
    // Récupération de la liste des attributs
    foreach ($properties as $property) {
      $this->attributes[$property->key] = $property;
    }
    $this->attributes_loaded = true;
  }
  /**
   * Supprime les attributs
   */
  private function deleteAttributes() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->loadAttributes()");
    if (!$this->attributes_loaded) {
      $this->loadAttributes();
    }
    // Parcours les attributs pour les enregistrer
    if (isset($this->attributes)) {
      foreach ($this->attributes as $name => $attribute) {
        $attribute->delete();
      }
    }
  }
  
  /**
   * ***************************************************
   * DATA MAPPING
   */
  /**
   * Mapping class field
   * 
   * @param Task::CLASS_* $class          
   */
  protected function setMapClass($class) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapClass($class)");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    $this->objectmelanie->class = MappingMce::$MapClassObjectToMce[$class];
  }
  /**
   * Mapping class field
   */
  protected function getMapClass() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapClass()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    return MappingMce::$MapClassMceToObject[$this->objectmelanie->class];
  }
  
  /**
   * Mapping priority field
   * 
   * @param Task::PRIORITY_* $priority          
   */
  protected function setMapPriority($priority) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapPriority($priority)");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    $this->objectmelanie->priority = MappingMce::$MapPriorityObjectToMce[$priority];
  }
  /**
   * Mapping priority field
   * 
   * @return Task::PRIORITY_*
   */
  protected function getMapPriority() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapPriority()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    return MappingMce::$MapPriorityMceToObject[$this->objectmelanie->priority];
  }
  
  /**
   * Mapping percent_complete field
   * 
   * @param int $percent_complete          
   */
  protected function setMapPercent_Complete($percent_complete) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapPercent_Complete($percent_complete)");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    $this->setAttribute('PERCENT-COMPLETE', intval($percent_complete));
  }
  /**
   * Mapping percent_complete field
   * 
   * @return int
   */
  protected function getMapPercent_Complete() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapPercent_Complete()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    return intval($this->getAttribute('PERCENT-COMPLETE'));
  }
  /**
   * Mapping percent_complete field
   * 
   * @return boolean
   */
  protected function issetMapPercent_Complete() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->issetMapPercent_Complete()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    $percent_complete = $this->getAttribute('PERCENT-COMPLETE');
    return isset($percent_complete);
  }
  
  /**
   * Mapping status field
   * 
   * @param Task::STATUS_* $status          
   */
  protected function setMapStatus($status) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapStatus($status)");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    $this->setAttribute('STATUS', $status);
  }
  /**
   * Mapping percent_complete field
   * 
   * @return int
   */
  protected function getMapStatus() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapStatus()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    return $this->getAttribute('STATUS');
  }
  /**
   * Mapping percent_complete field
   * 
   * @return boolean
   */
  protected function issetMapStatus() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->issetMapStatus()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    $status = $this->getAttribute('STATUS');
    return isset($status);
  }
  /**
   * Map ics to current task
   * 
   * @ignore
   *
   */
  protected function setMapIcs($ics) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapsIcs()");
    \LibMelanie\Lib\ICSToTask::Convert($ics, $this, $this->taskslistmce, $this->user);
  }
  /**
   * Map current task to ics
   * 
   * @return string $ics
   * @ignore
   *
   */
  protected function getMapIcs() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapIcs()");
    return \LibMelanie\Lib\TaskToICS::Convert($this, $this->taskslistmce, $this->user);
  }
}