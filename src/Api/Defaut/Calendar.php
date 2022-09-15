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
use LibMelanie\Objects\CalendarMelanie;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\Config;

/**
 * Classe calendrier par defaut
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $id Identifiant unique du calendrier
 * @property string $owner Identifiant du propriétaire du calendrier
 * @property string $name Nom complet du calendrier
 * @property int $perm Permission associée, utiliser asRight()
 * @property string $ctag CTag du calendrier
 * @property int $synctoken SyncToken du calendrier
 * @property-read string $caldavurl URL CalDAV pour le calendrier
 * 
 * @method bool load() Charge les données du calendrier depuis la base de données
 * @method bool exists() Non implémentée
 * @method bool save() Non implémentée
 * @method bool delete() Non implémentée
 * @method void getCTag() Charge la propriété ctag avec l'identifiant de modification du calendrier
 * @method void getTimezone() Charge la propriété timezone avec le timezone du calendrier
 * @method bool asRight($action) Retourne un boolean pour savoir si les droits sont présents
 */
class Calendar extends MceObject {
  /**
   * Accès aux objets associés
   * Utilisateur associé à l'objet
   * 
   * @var User
   * @ignore
   *
   */
  protected $user;
  
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
    $this->objectmelanie = new CalendarMelanie();
    
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
      $this->user->cleanCalendars();
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
      $this->user->cleanCalendars();
    }
    return $ret;
  }

  /**
   * Récupère la liste de tous les évènements
   * need: $this->id
   * 
   * @return Event[]
   */
  public function getAllEvents() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getAllEvents()");
    $_events = $this->objectmelanie->getAllEvents();
    if (!isset($_events)) {
      return null;
    }
    $events = [];
    $exceptions = [];
    $Event = $this->__getNamespace() . '\\Event';
    $Exception = $this->__getNamespace() . '\\Exception';
    foreach ($_events as $_event) {
      try {
        $_event->setIsExist();
        $_event->setIsLoaded();
        if (strpos($_event->uid, $Exception::RECURRENCE_ID) === false) {
          $event = new $Event($this->user, $this);
          $event->setObjectMelanie($_event);
          $events[$event->uid . $event->calendar] = $event;
        } else {
          $exception = new $Exception(null, $this->user, $this);
          $exception->setObjectMelanie($_event);
          if (!isset($exceptions[$exception->uid . $exception->calendar]) || !is_array($exceptions[$exception->uid . $exception->calendar]))
            $exceptions[$exception->uid . $exception->calendar] = [];
          // Filtrer les exceptions qui n'ont pas de date
          if (empty($exception->start) || empty($exception->end)) {
            $exception->deleted = true;
          } else {
            $exception->deleted = false;
          }
          $recId = new \DateTime(substr($exception->realuid, strlen($exception->realuid) - strlen($Exception::FORMAT_STR . $Exception::RECURRENCE_ID), strlen($Exception::FORMAT_STR)));
          $exceptions[$exception->uid . $exception->calendar][$recId->format($Exception::FORMAT_ID)] = $exception;
        }
      } catch (\Exception $ex) {
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getAllEvents() Exception: " . $ex);
      }
    }
    // Détruit les variables pour libérer le plus rapidement de la mémoire
    unset($_events);
    // Traitement des exceptions qui n'ont pas d'évènement associé
    // On crée un faux évènement qui va contenir ces exceptions
    foreach ($exceptions as $key => $_exceptions) {
      if (!isset($events[$key])) {
        $event = new $Event($this->user, $this);
        $modified = 0;
        foreach ($_exceptions as $_exception) {
          $uid = $_exception->uid;
          $_exception->setEventParent($event);
          if (!isset($_exception->modified))
            $_exception->modified = 0;
          if ($_exception->modified > $modified)
            $modified = $_exception->modified;
        }
        if (isset($uid)) {
          $event->uid = $uid;
          $event->deleted = true;
          $event->modified = $modified;
          $event->exceptions = $_exceptions;
          $event->setIsExist();
          $event->setIsLoaded();
          $events[$event->uid . $event->calendar] = $event;
        }
      } else {
        foreach ($_exceptions as $_exception) {
          $events[$key]->addException($_exception);
        }
      }
    }
    // Détruit les variables pour libérer le plus rapidement de la mémoire
    unset($exceptions);
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    return $events;
  }
  
  /**
   * Récupère la liste des évènements entre start et end
   * need: $this->id
   * 
   * @param string $start
   *          Date de début
   * @param string $end
   *          Date de fin
   * @param int $modified
   *          Date de derniere modification des événements
   * @param boolean $is_freebusy
   *          Est-ce que l'on cherche des freebusy
   * @param string $category 
   *          Catégorie des événements a récupérer
   * @return Event[]
   */
  public function getRangeEvents($start = null, $end = null, $modified = null, $is_freebusy = false, $category = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getRangeEvents()");
    $_events = $this->objectmelanie->getRangeEvents($start, $end, $modified, $is_freebusy, $category);
    if (!isset($_events) || $_events === false)
      return null;
    $events = [];
    $exceptions = [];
    $Event = $this->__getNamespace() . '\\Event';
    $Exception = $this->__getNamespace() . '\\Exception';
    foreach ($_events as $_event) {
      try {
        $_event->setIsExist();
        $_event->setIsLoaded();
        if (strpos($_event->uid, $Exception::RECURRENCE_ID) === false) {
          $event = new $Event($this->user, $this);
          $event->setObjectMelanie($_event);
          $events[$event->uid . $event->calendar] = $event;
        } else {
          $exception = new $Exception(null, $this->user, $this);
          $exception->setObjectMelanie($_event);
          if (!isset($exceptions[$exception->uid . $exception->calendar]) || !is_array($exceptions[$exception->uid . $exception->calendar]))
            $exceptions[$exception->uid . $exception->calendar] = [];
          // Filtrer les exceptions qui n'ont pas de date
          if (empty($exception->start) || empty($exception->end)) {
            $exception->deleted = true;
          } else {
            $exception->deleted = false;
          }
          $recId = new \DateTime(substr($exception->realuid, strlen($exception->realuid) - strlen($Exception::FORMAT_STR . $Exception::RECURRENCE_ID), strlen($Exception::FORMAT_STR)));
          $exceptions[$exception->uid . $exception->calendar][$recId->format($Exception::FORMAT_ID)] = $exception;
        }
      } catch (\Exception $ex) {
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getRangeEvents() Exception: " . $ex);
      }
    }
    // Détruit les variables pour libérer le plus rapidement de la mémoire
    unset($_events);
    // Traitement des exceptions qui n'ont pas d'évènement associé
    // On crée un faux évènement qui va contenir ces exceptions
    foreach ($exceptions as $key => $_exceptions) {
      if (!isset($events[$key])) {
        $event = new $Event($this->user, $this);
        $modified = 0;
        foreach ($_exceptions as $_exception) {
          $uid = $_exception->uid;
          $_exception->setEventParent($event);
          if (!isset($_exception->modified))
            $_exception->modified = 0;
          if ($_exception->modified > $modified)
            $modified = $_exception->modified;
        }
        if (isset($uid)) {
          $event->uid = $uid;
          $event->deleted = true;
          $event->modified = $modified;
          $event->exceptions = $_exceptions;
          $event->setIsExist();
          $event->setIsLoaded();
          $events[$event->uid . $event->calendar] = $event;
        }
      } else {
        foreach ($_exceptions as $_exception) {
          $events[$key]->addException($_exception);
        }
      }
    }
    // Détruit les variables pour libérer le plus rapidement de la mémoire
    unset($exceptions);
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    return $events;
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
    if (Config::is_set(Config::CALENDAR_CALDAV_URL)) {
      $url = str_replace(['%u', '%o', '%i'], [$this->user->uid, $this->objectmelanie->owner, $this->objectmelanie->id], Config::get(Config::CALENDAR_CALDAV_URL));
    }
    return $url;
  }
}