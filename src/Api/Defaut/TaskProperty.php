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
 * Classe pour la gestion des propriétés des évènements
 * Permet d'ajouter de nouvelles options aux évènements
 * implémente les API de la librairie pour aller chercher les données dans la base de données
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $task Identifiant de la tâche associée
 * @property string $taskslist Identifiant du carnet d'adresse associé à la tâche
 * @property string $user Identifiant de l'utilisateur
 * @property string $key Clé pour l'accès à la propriété, elle doit être unique pour cet évènement
 * @property string $value Valeur associé à la clé
 * @method bool load() Chargement la priopriété, en fonction de la tâche, du carnet d'adresse associé, de l'utilisateur et de la clé
 * @method bool exists() Test si la priopriété existe, en fonction de la tâche, du carnet d'adresse associé, de l'utilisateur et de la clé
 * @method bool save() Sauvegarde la priopriété dans la base de données
 * @method bool delete() Supprime la priopriété, en fonction de la tâche, du carnet d'adresse associé, de l'utilisateur et de la clé
 */
class TaskProperty extends MceObject {
  /**
   * Constructeur de l'objet
   * 
   * @param User $user          
   * @param ObjectMelanie $taskmelanie          
   */
  function __construct($user = null, $taskmelanie = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    // M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->__construct()");
    // Définition de la propriété de l'évènement
    $this->objectmelanie = new ObjectMelanie('TaskProperties');
    
    // Définition des objets associés
    if (isset($user)) {
      $this->objectmelanie->user = $user->uid;
    }
    if (isset($taskmelanie)) {
      $this->objectmelanie->task = $taskmelanie->uid;
      $this->objectmelanie->taskslist = $taskmelanie->taskslist;
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
    $this->objectmelanie->user = $user->uid;
  }
  
  /**
   * Défini l'évènement lié à la propriété
   * 
   * @param ObjectMelanie $taskmelanie          
   * @ignore
   *
   */
  function setTaskMelanie($taskmelanie) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setTaskMelanie()");
    $this->objectmelanie->task = $taskmelanie->uid;
    $this->objectmelanie->taskslist = $taskmelanie->taskslist;
  }
  
  /**
   * ***************************************************
   * METHOD MAPPING
   */
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
   * @return TaskProperty[] Array
   */
  function getList($fields = [], $filter = "", $operators = [], $orderby = "", $asc = true, $limit = null, $offset = null, $case_unsensitive_fields = []) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getList()");
    $_taskproperties = $this->objectmelanie->getList($fields, $filter, $operators, $orderby, $asc, $limit, $offset, $case_unsensitive_fields);
    if (!isset($_taskproperties))
      return null;
    $taskproperties = [];
    foreach ($_taskproperties as $_taskproperty) {
      $_taskproperty->setIsExist();
      $_taskproperty->setIsLoaded();
      $taskproperty = new static();
      $taskproperty->setObjectMelanie($_taskproperty);
      $taskproperties[] = $taskproperty;
    }
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    return $taskproperties;
  }

/**
 * ***************************************************
 * DATA MAPPING
 */
}