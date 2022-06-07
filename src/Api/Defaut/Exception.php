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

use LibMelanie\Objects\EventMelanie;
use LibMelanie\Objects\HistoryMelanie;
use LibMelanie\Exceptions;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\Config;

/**
 * Classe exception pour MCE,
 * étend sur la class Event
 * implémente les API de la librairie pour aller chercher les données dans la base de données
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $id Identifiant unique de l'évènement
 * @property string $calendar Identifiant du calendrier de l'évènement
 * @property string $uid UID de l'évènement
 * @property string $owner Créateur de l'évènement
 * @property string $keywords Keywords
 * @property string $title Titre de l'évènement
 * @property string $description Description de l'évènement
 * @property string $category Catégorie de l'évènment
 * @property string $location Lieu de l'évènement
 * @property Event::STATUS_* $status Statut de l'évènement
 * @property Event::CLASS_* $class Class de l'évènement (privé/public)
 * @property int $alarm Alarme en minute (TODO: class Alarm)
 * @property Attendee[] $attendees Tableau d'objets Attendee
 * @property string $start String au format compatible DateTime, date de début
 * @property string $end String au format compatible DateTime, date de fin
 * @property int $modified Timestamp de la modification de l'évènement
 * @property Recurrence $recurrence Inaccessible depuis une exception
 * @property bool $deleted Défini si l'exception est un évènement ou juste une suppression
 * @property string $recurrence_id Défini la date de l'exception pour l'occurrence (nouvelle version)
 * @property-read string $realuid UID réellement stocké dans la base de données (utilisé pour les exceptions) (Lecture seule)
 * 
 * @method bool load() Chargement l'évènement, en fonction du calendar et de l'uid
 * @method bool exists() Test si l'évènement existe, en fonction du calendar et de l'uid
 * @method bool save() Sauvegarde l'évènement et l'historique dans la base de données
 * @method bool delete() Supprime l'évènement et met à jour l'historique dans la base de données
 */
class Exception extends Event {
  /**
   * Evenement parent de l'exception
   * 
   * @var Event $eventParent
   */
  private $eventParent;
  
  // Constantes
  const RECURRENCE_ID = '@RECURRENCE-ID';
  const FORMAT_ID = 'Ymd';
  const FORMAT_REC_ID = 'Y-m-d H:i:s';
  const FORMAT_STR = 'YYYYmmdd';
  
  /**
   * ***************************************************
   * PUBLIC METHODS
   */
  /**
   * Constructeur de l'objet
   * 
   * @param EventMelanie $eventParent          
   * @param User $user          
   * @param Calendar $calendar          
   */
  public function __construct($eventParent = null, $user = null, $calendar = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    if (isset($eventParent)) {
      if (!isset($user))
        $user = $eventParent->getUserMelanie();
      if (!isset($calendar))
        $calendar = $eventParent->getCalendarMelanie();
    }
    // Appel au constructeur parent
    parent::__construct($user, $calendar);
    
    // Définition de l'évènement parent à l'exception
    $this->eventParent = $eventParent;
  }
  
  /**
   * Défini l'évènement parent de l'exception
   * 
   * @param Event $eventParent          
   */
  public function setEventParent($eventParent) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setEventParent()");
    $this->eventParent = $eventParent;
    if (isset($eventParent)) {
      if (!isset($this->user)) {
        $this->user = $eventParent->getUserMelanie();
      }
      if (!isset($this->calendar)) {
        $this->calendar = $eventParent->getCalendarMelanie();
      }
    }
  }
  /**
   * Retourne l'évènement parent de l'exception
   * 
   * @return Event
   */
  public function getEventParent() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getEventParent()");
    return $this->eventParent;
  }
  
  /**
   * ***************************************************
   * EVENT METHOD
   */
  /**
   * Pas de chargement des exceptions dans une exception
   */
  protected function loadExceptions() {
    return false;
  }
  
  /**
   * ***************************************************
   * METHOD MAPPING
   */
  /**
   * Non implémenté
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
   */
  function getList($fields = [], $filter = "", $operators = [], $orderby = "", $asc = true, $limit = null, $offset = null, $case_unsensitive_fields = []) {
    throw new Exceptions\ObjectMelanieUndefinedException();
  }
  
  /**
   * Mapping de la sauvegarde de l'objet
   * Appel la sauvegarde de l'historique en même temps
   * 
   * @return bool
   * @ignore
   *
   */
  function save($saveAttendees = true) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->save()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    if ($this->deleted)
      return false;
    
    if (!isset($this->owner)) {
      $this->owner = $this->user->uid;
    }
    // Sauvegarde des participants
    if ($saveAttendees) {
      $this->saveAttendees();
    }
    // Sauvegarde l'objet
    $insert = $this->objectmelanie->save();
    if (!is_null($insert)) {
      // Sauvegarde des attributs
      $this->saveAttributes();
      // Gestion de l'historique
      $history = new HistoryMelanie();
      $history->uid = Config::get(Config::CALENDAR_PREF_SCOPE) . ":" . $this->calendar . ":" . $this->realuid;
      $history->action = $insert ? Config::get(Config::HISTORY_ADD) : Config::get(Config::HISTORY_MODIFY);
      $history->timestamp = time();
      $history->description = "LibM2/" . Config::get(Config::APP_NAME);
      $history->who = isset($this->user) ? $this->user->uid : $this->calendar;
      // Enregistrement dans la base
      if (is_null($insert))
        $this->deleted = true;
      else
        $this->deleted = false;
      if (!is_null($history->save()))
        return $insert;
    }
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->save() Rien a sauvegarder: return null");
    return null;
  }
  
  /**
   * Mapping de la suppression de l'objet
   * Appel la sauvegarde de l'historique en même temps
   * 
   * @return bool
   * @ignore
   *
   */
  function delete() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->delete()");
    $deleted = parent::delete();
    if ($deleted)
      $this->deleted = true;
    else
      $this->deleted = false;
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    return $deleted;
  }
  
  /**
   * Mapping du chargement de l'objet
   * 
   * @return bool
   * @ignore
   *
   */
  function load($checkDeleted = false) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->load()");
    $exist = parent::load();
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->load() exist : " . $exist);
    if ($checkDeleted) {
      if ($exist && isset($this->start) && isset($this->end)) {
        $this->deleted = false;
      } else {
        $this->deleted = true;
      }
    }
    return $exist;
  }
  
  /**
   * ***************************************************
   * DATA MAPPING
   */
  /**
   * Mapping recurrence field
   * Inaccessible
   * 
   * @param Recurrence $recurrence          
   */
  protected function setMapRecurrence($recurrence) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapRecurrence()");
    throw new Exceptions\ObjectMelanieUndefinedException();
  }
  /**
   * Mapping recurrence field
   * Inaccessible
   */
  protected function getMapRecurrence() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapRecurrence()");
    throw new Exceptions\ObjectMelanieUndefinedException();
  }

  /**
   * Mapping recurrence_id field
   * 
   * @param string $recurrence_id          
   */
  protected function setMapRecurrence_id($recurrence_id) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapRecurrence_id($recurrence_id)");
    if ($recurrence_id instanceof \DateTime) {
      $this->objectmelanie->uid = $this->getMapUid() . '-' . $recurrence_id->format(self::FORMAT_ID) . self::RECURRENCE_ID;
    }
    else {
      $this->objectmelanie->uid = $this->getMapUid() . '-' . date(self::FORMAT_ID, strtotime($recurrence_id)) . self::RECURRENCE_ID;
    }
    $this->objectmelanie->realuid = $this->getMapUid();
    $this->objectmelanie->recurrence_id = $recurrence_id;
  }
  /**
   * Mapping recurrence_id field
   */
  protected function getMapRecurrence_id() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapRecurrence_id()");
    if (isset($this->objectmelanie->recurrence_id)) {
      $_recId = $this->objectmelanie->recurrence_id;
    }
    else {
      $_recId = $this->getAttribute(\LibMelanie\Lib\ICS::RECURRENCE_ID);
    }
    if (!isset($_recId) && strpos($this->objectmelanie->uid, self::RECURRENCE_ID) !== false) {
      // On tombe dans un cas pas propre ou l'exception n'a pas du tout de recurrence_id
      $_recId = substr($this->objectmelanie->uid, strlen($this->objectmelanie->uid) - strlen(self::FORMAT_STR . self::RECURRENCE_ID));
      $_recId = substr($_recId, 0, strlen(self::FORMAT_STR));
      if (isset($this->eventParent) && !$this->eventParent->deleted) {
        $startTime = $this->eventParent->getMapDtstart();
      }
      else {
        $startTime = $this->getMapDtstart();
      }
      $_recId = date("Y-m-d", strtotime($_recId)) . ' ' . $startTime->format('H:i:s');
    }
    return $_recId;
  }
  
  /**
   * Mapping uid field
   * 
   * @param string $uid          
   */
  protected function setMapUid($uid) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapUid($uid)");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    if (isset($this->objectmelanie->recurrence_id)) {
      $_recId = $this->objectmelanie->recurrence_id;
    }
    else {
      $_recId = $this->getAttribute(\LibMelanie\Lib\ICS::RECURRENCE_ID);
    }
    if (isset($_recId)) {
      $recId = new \DateTime($_recId);
      $this->objectmelanie->realuid = $uid;
      $this->objectmelanie->uid = $uid . '-' . $recId->format(self::FORMAT_ID) . self::RECURRENCE_ID;
    }
    else {
      $this->objectmelanie->realuid = $uid;
      $this->objectmelanie->uid = $uid;
    }
  }
  /**
   * Mapping uid field
   */
  protected function getMapUid() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapUid()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    if (isset($this->objectmelanie->realuid)) {
      return $this->objectmelanie->realuid;
    }
    else {
      return substr($this->objectmelanie->uid, 0, strlen($this->objectmelanie->uid) - strlen('-' . self::FORMAT_STR . self::RECURRENCE_ID));
    }
  }
  /**
   * Mapping organizer field
   */
  protected function getMapOrganizer() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapOrganizer()");
    if (!isset($this->organizer)) {
      if (isset($this->eventParent) && !$this->eventParent->deleted && isset($this->eventParent->organizer) && !empty($this->eventParent->organizer->uid)) {
        $this->organizer = clone $this->eventParent->organizer;
        $this->organizer->setEvent($this);
        $this->organizer->setObjectMelanie($this->objectmelanie);
        $this->organizer_json = $this->eventParent->organizer_json;
      }
      else {
        $Organizer = $this->__getNamespace() . '\\Organizer';
        $this->organizer = new $Organizer($this);
        if (isset($this->eventParent) && !$this->eventParent->deleted) {
          // Ajouter l'organisateur sur l'événement parent pour les occurrences suivantes
          $this->eventParent->organizer = clone $this->organizer;
          $this->eventParent->organizer->setEvent($this->eventParent);
        }        
      }
    }      
    return $this->organizer;
  }
}