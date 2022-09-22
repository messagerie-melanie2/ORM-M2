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
use LibMelanie\Objects\EventMelanie;
use LibMelanie\Objects\HistoryMelanie;
use LibMelanie\Config\MappingMce;
use LibMelanie\Exceptions;
use LibMelanie\Log\M2Log;
use LibMelanie\Lib\ICS;
use LibMelanie\Config\Config;
use LibMelanie\Config\DefaultConfig;
use stdClass;

/**
 * Classe evenement par defaut,
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
 * @property Event::TRANSP_* $transparency Etat de transparence de l'événement
 * @property Event::PRIORITY_* $priority Priorité de l'événement
 * @property int $sequence Séquence de l'événement
 * @property int $alarm Alarme en minute (TODO: class Alarm)
 * @property Attendee[] $attendees Tableau d'objets Attendee
 * @property boolean $hasattendees Est-ce que cette instance de l'événement a des participants
 * @property string $start String au format compatible DateTime, date de début
 * @property string $end String au format compatible DateTime, date de fin
 * @property \DateTime $dtstart DateTime basée sur le champ $start
 * @property \DateTime $dtend DateTime basée sur le champ $end
 * @property-read \DateTime $dtstart_utc DateTime basée sur le champ $start au timezone UTC
 * @property-read \DateTime $dtend_utc DateTime basée sur le champ $end au timezone UTC
 * @property string $timezone Timezone de l'événement
 * @property boolean $all_day Est-ce que c'est un événement journée entière
 * @property int $created Timestamp de création de l'évènement
 * @property int $modified Timestamp de la modification de l'évènement
 * @property Recurrence $recurrence objet Recurrence
 * @property Organizer $organizer objet Organizer
 * @property Exception[] $exceptions Liste d'exception
 * @property Attachment[] $attachments Liste des pièces jointes associées à l'évènement (URL ou Binaire)
 * @property bool $deleted Défini si l'exception est un évènement ou juste une suppression
 * @property-read string $realuid UID réellement stocké dans la base de données (utilisé pour les exceptions) (Lecture seule)
 * @property string $ics ICS associé à l'évènement courant, calculé à la volée en attendant la mise en base de données
 * @property-read VObject\Component\VCalendar $vcalendar Object VCalendar associé à l'évènement, peut permettre des manipulations sur les récurrences
 * @property boolean $move Il s'ajout d'un MOVE, les participants sont conservés
 * @property integer $version Version de schéma pour l'événement
 * 
 * @method bool load() Chargement l'évènement, en fonction du calendar et de l'uid
 * @method bool exists() Test si l'évènement existe, en fonction du calendar et de l'uid
 * @method bool save() Sauvegarde l'évènement et l'historique dans la base de données
 * @method bool delete() Supprime l'évènement et met à jour l'historique dans la base de données
 */
class Event extends MceObject {
  /**
   * Format de datetime pour la base de données
   *
   * @var string
   */
  const DB_DATE_FORMAT = 'Y-m-d H:i:s';
  
  // Accès aux objets associés
  /**
   * Utilisateur associé à l'objet
   * 
   * @var User
   */
  protected $user;
  /**
   * Calendrier associé à l'objet
   * 
   * @var Calendar
   */
  protected $calendarmce;
  
  // object privé
  /**
   * Recurrence liée à l'objet
   * 
   * @var Recurrence $recurrence
   */
  private $recurrence;
  /**
   * Organisateur de l'évènement
   * 
   * @var string
   */
  protected $organizer;
  /**
   * L'évènement est supprimé
   * 
   * @var boolean
   */
  protected $deleted;
  /**
   * Tableau des participants
   * 
   * @var Attendee[]
   */
  protected $_attendees;
  /**
   * Tableau d'exceptions pour la récurrence
   * 
   * @var Exception[]
   */
  private $exceptions;
  /**
   * Tableau d'exceptions a supprimer au moment du save
   * 
   * @var Exception[]
   */
  private $deleted_exceptions;
  /**
   * Tableau d'attributs pour l'évènement
   * 
   * @var array[$attribute]
   */
  protected $attributes;
  /**
   * Permet de savoir si les attributs ont déjà été chargés depuis la base
   * 
   * @var bool
   */
  protected $attributes_loaded = false;
  /**
   * Tableau contenant les pièces jointes de l'évènement
   * 
   * @var Attachment[]
   */
  protected $attachments;
  /**
   * DateTime basée sur le champ $start
   * 
   * @var \DateTime
   */
  protected $dtstart;
  /**
   * DateTime basée sur le champ $start au timezone UTC
   *
   * @var \DateTime
   */
  protected $dtstart_utc;
  /**
   * DateTime basée sur le champ $end
   *
   * @var \DateTime
   */
  protected $dtend;
  /**
   * DateTime basée sur le champ $end au timezone UTC
   *
   * @var \DateTime
   */
  protected $dtend_utc;
  
  /**
   * Object VCalendar disponible via le VObject
   * 
   * @var VCalendar
   */
  private $vcalendar;
  
  /**
   * Défini s'il s'agit d'un move qui nécessite de conserver les participants
   * Dans ce cas les participants doivent être doublés
   * 
   * @var boolean
   */
  protected $move = false;
  
  /**
   * La génération de l'ICS doit elle retourner des freebusy
   * Il n'y aura donc pas de participants, pièces jointes et informations supplémentaires
   * 
   * @var boolean
   */
  public $ics_freebusy = false;
  /**
   * La génération de l'ICS doit elle retourner les pièces jointes ?
   * 
   * @var boolean
   */
  public $ics_attachments = true;
  
  /**
   * **
   * CONSTANTES
   */
  // CLASS Fields
  const CLASS_PRIVATE = DefaultConfig::PRIV;
  const CLASS_PUBLIC = DefaultConfig::PUB;
  const CLASS_CONFIDENTIAL = DefaultConfig::CONFIDENTIAL;
  // STATUS Fields
  const STATUS_TENTATIVE = DefaultConfig::TENTATIVE;
  const STATUS_CONFIRMED = DefaultConfig::CONFIRMED;
  const STATUS_CANCELLED = DefaultConfig::CANCELLED;
  const STATUS_NONE = DefaultConfig::NONE;
  const STATUS_TELEWORK = DefaultConfig::TELEWORK;
  // TRANS Fields
  const TRANS_TRANSPARENT = ICS::TRANSP_TRANSPARENT;
  const TRANS_OPAQUE = ICS::TRANSP_OPAQUE;
  // PRIORITY Fields
  const PRIORITY_NO = 0;
  const PRIORITY_VERY_HIGH = 1;
  const PRIORITY_HIGH = 2;
  const PRIORITY_NORMAL = 3;
  const PRIORITY_LOW = 4;
  const PRIORITY_VERY_LOW = 5;
  
  /**
   * Constructeur de l'objet
   * 
   * @param User $user          
   * @param Calendar $calendar          
   */
  public function __construct($user = null, $calendar = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    // M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->__construct()");
    // Définition de l'évènement melanie2
    $this->objectmelanie = new EventMelanie();
    
    // Définition des objets associés
    if (isset($user))
      $this->user = $user;
    if (isset($calendar)) {
      $this->calendarmce = $calendar;
      $this->objectmelanie->calendar = $this->calendarmce->id;
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
  }
  /**
   * Retourne l'utilisateur MCE
   * 
   * @return User
   */
  public function getUserMelanie() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserMelanie()");
    return $this->user;
  }
  
  /**
   * Défini le calendrier MCE
   * 
   * @param Calendar $calendar      
   * @ignore
   *
   */
  public function setCalendarMelanie($calendar) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setCalendarMelanie()");
    $this->calendarmce = $calendar;
    $this->objectmelanie->calendar = $this->calendarmce->id;
  }
  /**
   * Retourne le calendrier MCE
   * 
   * @return Calendar
   */
  public function getCalendarMelanie() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getCalendarMelanie()");
    return $this->calendarmce;
  }
  
  /**
   * Retourne un attribut supplémentaire pour l'évènement
   * 
   * @param string $name
   *          Nom de l'attribut
   * @return string|NULL valeur de l'attribut, null s'il n'existe pas
   */
  public function getAttribute($name) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getAttribute($name)");
    // Schéma de version 2
    if ($this->version >= 2) {
      $attributes = json_decode($this->objectmelanie->properties, true);
      if (isset($attributes[$name])) {
        return $attributes[$name];
      }
      else {
        return null;
      }
    }
    else {
      // Version 1 on prend les attributs de HordePref
      // Si les attributs n'ont pas été chargés
      if (!$this->attributes_loaded) {
        $this->loadAttributes();
      }
      if (!isset($this->attributes[$name])) {
        return null;
      }
      return $this->attributes[$name]->value;
    }
    
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
      // Si les attributs n'ont pas été chargés
      if (!$this->attributes_loaded) {
        $this->loadAttributes();
      }
      if (isset($this->attributes[$name])) {
        $this->attributes[$name]->value = $value;
      }
      else {
        $EventProperty = $this->__getNamespace() . '\\EventProperty';
        $eventproperty = new $EventProperty();
        $eventproperty->event = $this->objectmelanie->uid;
        if (isset($this->calendarmce)) {
          $eventproperty->calendar = $this->calendarmce->id;
        } else {
          $eventproperty->calendar = $this->calendar;
        }
        // Problème de User avec DAViCal
        if (isset($this->calendarmce)) {
          $eventproperty->user = $this->calendarmce->owner;
        } else if (isset($this->owner)) {
          $eventproperty->user = $this->owner;
        } else {
          $eventproperty->user = '';
        }
        
        $eventproperty->key = $name;
        $eventproperty->value = $value;
        $eventproperty->setIsLoaded();
        $eventproperty->setIsExist(false);
        $this->attributes[$name] = $eventproperty;
      }
      // 0005093: Ne plus utiliser la table lightning_attributes
      $this->objectmelanie->properties = $this->attributesToJson($this->attributes);
    }
  }
  /**
   * Method permettant de définir directement la liste des attributs de l'évènement
   * 
   * @param array $attributes          
   */
  public function setAttributes($attributes) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setAttributes()");
    // Positionne la liste des attributs
    $this->attributes = $attributes;
    $this->attributes_loaded = true;
    // 0005093: Ne plus utiliser la table lightning_attributes
    $this->objectmelanie->properties = $this->attributesToJson($this->attributes);
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
      if ($this->attributes[$name]->delete()) {
        unset($this->attributes[$name]);
        // 0005093: Ne plus utiliser la table lightning_attributes
        $this->objectmelanie->properties = $this->attributesToJson($this->attributes);
      }
    }
    return false;
  }

  /**
   * Converti la liste des attributs en une valeur json exploitable
   * 
   * @return string json properties
   */
  protected function attributesToJson() {
    $properties = [];
    foreach ($this->attributes as $name => $attribute) {
      $properties[$name] = $attribute->value;
    }
    return json_encode($properties);
  }
  
  /**
   * ***************************************************
   * EVENT METHOD
   */

   /**
    * Nouvelle version de l'enregistrement des participants
    */
  protected function saveAttendees() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->saveAttendees()");
    // Détecter les attendees pour tous les évènements et exceptions
    $hasAttendees = $this->getMapHasAttendees();
    // Récupération de l'organisateur
    $organizer = $this->getMapOrganizer();
    if (!$hasAttendees || $organizer->extern || /* MANTIS 4016: Gestion des COPY/MOVE */$this->move) {
      return false;
    }
    $organizer_calendar_id = $organizer->calendar;
    if (is_null($organizer_calendar_id)) {
      // On n'arrive pas à retrouver l'événement de l'organisateur
      $events = $this->listEventsByUid($this->uid);
      // Est-ce que l'événement existe quelque part ?
      if (count($events) === 0) {
        if ($this->userIsOrganizer($organizer->uid, $this->user->uid)) {
          // L'évènement n'existe pas, l'organisateur est celui qui créé l'évènement
          // Donc on est dans le cas d'une création interne
          $organizer->calendar = $this->calendar;
          // Positionne les événements en attente
          $this->saveNeedAction();
          return true;
        }
        else {
          // XXX: Gérer ici le MANTIS 0006687 ?
          
          // L'évènement n'existe pas, mais l'organisateur est différent du créateur
          // On considère alors que c'est un organisateur externe (même s'il est interne au ministère)
          return $this->setExternalOrganizer($organizer);
        }
      }
      else {
        // XXX: on doit arriver ici quand le load ne retourne rien car l'évènement n'existe pas
        // On arrive également ici quand l'évenement a d'abord été créé sans participant
        // Parcourir les évènements trouvés pour chercher l'évènement de l'organisateur
        foreach ($events as $_event) {
          if (count($events) === 1 && $_event->calendar == $this->calendar) {
            $organizer_event = $this;
            $organizer_calendar_id = $this->calendar;
          }
          else if ($_event->hasattendees 
              // MANTIS 0006289: Dans le IF pour savoir si l'événement est l'événement de l'organisateur ajouter le test s'il est externe
              && !$_event->getMapOrganizer()->extern 
              && $_event->getMapOrganizer()->calendar == $_event->calendar) {
            $organizer_calendar_id = $_event->calendar;
            if (strpos($this->get_class, '\Exception') !== false) {
              $Exception = $this->__getNamespace() . '\\Exception';
              $recId = date($Exception::FORMAT_ID, strtotime($this->getMapRecurrence_id()));
              if (isset($_event->exceptions[$recId])) {
                $organizer_event = $_event->exceptions[$recId];
              }
            }
            else {
              $organizer_event = $_event;
            }
            break;
          }
        }
      }
      // Si l'organisateur n'est toujours pas trouvé
      if (!isset($organizer_calendar_id)) {
        // XXX: Gérer ici le MANTIS 0006687 ?

        // On considère également que c'est un organisateur externe
        return $this->setExternalOrganizer($organizer);
      }
    }
    // Positionner le calendar_id de l'organisateur dans l'événement
    $this->getMapOrganizer()->calendar = $organizer_calendar_id;
    // Test si on est dans le calendrier de l'organisateur (dans ce cas on sauvegarde directement les participants)
    if ($organizer_calendar_id != $this->calendar) {
      $Attendee = $this->__getNamespace() . '\\Attendee';
      
      // Définition de la sauvegarde de l'évènement de l'organisateur
      $save = false;
      if (!isset($organizer_event)) {
        $Calendar = $this->__getNamespace() . '\\Calendar';
        $organizer_calendar = new $Calendar($this->user);
        $organizer_calendar->id = $organizer_calendar_id;
        $organizer_calendar->load();
        // Recuperation de l'évènement de l'organisateur
        if (strpos($this->get_class, '\Exception') === false) {
          $Event = $this->__getNamespace() . '\\Event';
          $organizer_event = new $Event($this->user, $organizer_calendar);
        }
        else {
          $Event = $this->__getNamespace() . '\\Exception';
          $organizer_event = new $Event(null, $this->user, $organizer_calendar);
          $organizer_event->recurrence_id = $this->recurrence_id;
        }
        $organizer_event->uid = $this->objectmelanie->realuid;
        if (!$organizer_event->load()) {
          if (strpos($this->get_class, '\Exception') !== false) {
            // Si c'est juste l'exception qui n'existe pas on la crée
            $Event = $this->__getNamespace() . '\\Event';
            $organizer_master_event = new $Event($this->user, $organizer_calendar);
            $organizer_master_event->uid = $this->objectmelanie->realuid;
            if ($organizer_master_event->load()) {
              // Créer l'exception chez l'organisateur
              $organizer_event = $this->createOrganizerException($organizer_master_event);
            }
            else {
              // Normalement on ne devrait pas arriver là mais au cas ou on gère en externe
              return $this->setExternalOrganizer($organizer);
            }
          }
          else {
            // Si l'évènement de l'organisateur n'existe pas (surement supprimé ?), on le considère en externe
            return $this->setExternalOrganizer($organizer);
          }
        }
        else {
          // MANTIS 0006800: Pour une invitation interne, un participant ne peut pas modifier l'horaire
          $this->keepNeedActionFieldsEvent($this, $organizer_event);
        }
      }
      if (!$this->deleted && isset($this->objectmelanie->attendees)) {
        // Recupération de la réponse du participant
        $response = $Attendee::RESPONSE_NEED_ACTION;
        foreach ($this->getMapAttendees() as $attendee) {
          // 0005028: L'enregistrement de la réponse d'un participant ne se base pas sur la bonne valeur
          if (strtolower($attendee->uid) == strtolower($this->calendarmce->owner)) {
            $response = $attendee->response;
            // MANTIS 0004708: Lors d'un "s'inviter" utiliser les informations de l'ICS
            $att_email = $attendee->email;
            $att_name = $attendee->name;
            break;
          }
        }
        // Mise à jour du participant
        if ($response != $Attendee::RESPONSE_NEED_ACTION) {
          // Récupère les participants de l'organisateur
          $organizer_attendees = $organizer_event->getMapAttendees();
          $invite = true;
          foreach ($organizer_attendees as $attendee) {
            // 0005028: L'enregistrement de la réponse d'un participant ne se base pas sur la bonne valeur
            if (strtolower($attendee->uid) == strtolower($this->calendarmce->owner)) {
              if ($attendee->response != $response) {
                // 0006178: Quand un participant répond a une invitation, modifier automatiquement son statut
                switch ($response) {
                  case $Attendee::RESPONSE_ACCEPTED:
                    $this->status = static::STATUS_CONFIRMED;
                    break;
                  case $Attendee::RESPONSE_DECLINED:
                    $this->status = static::STATUS_NONE;
                    break;
                  case $Attendee::RESPONSE_TENTATIVE:
                    $this->status = static::STATUS_TENTATIVE;
                    break;
                }
                $attendee->response = $response;
                if (empty($attendee->name) && isset($att_name)) {
                  $attendee->name = $att_name;
                }
                $organizer_event->setMapAttendees($organizer_attendees);
                // Sauvegarde de l'evenement de l'organisateur
                $save = true;
                $invite = false;
              } else {
                // MANTIS 0004471: Problème lorsque la réponse du participant ne change pas
                $invite = false;
              }
              break;
            }
          }
          // S'inviter dans la réunion
          if ($invite && Config::get(Config::SELF_INVITE)) {
            $attendee = new $Attendee($organizer_event);
            // MANTIS 0004708: Lors d'un "s'inviter" utiliser les informations de l'ICS
            $attendee->email = isset($att_email) ? $att_email : $this->user->email;
            $attendee->name = isset($att_name) ? $att_name : '';
            $attendee->response = $response;
            $attendee->role = $Attendee::ROLE_OPT_PARTICIPANT;
            $attendee->self_invite = true;
            $organizer_attendees[] = $attendee;
            $organizer_event->attendees = $organizer_attendees;
            $save = true;
          }
        }
        unset($this->objectmelanie->attendees);
      }
      // MANTIS 0006752: Lors du saveAttendees, forcer la date de l'événement de l'organisateur
      foreach (['start', 'end', 'all_day', 'timezone'] as $field) {
        $this->getObjectMelanie()->setFieldValueToData($field, $organizer_event->getObjectMelanie()->getFieldValueFromData($field));
      }
      // Sauvegarde de l'evenement si besoin
      if ($save) {
        $organizer_event->modified = time();
        // Ne pas appeler le saveAttendees pour éviter les doubles sauvegardes (mode en attente)
        $organizer_event->save(false);
        if (strpos($this->get_class, '\Exception') !== false) {
          // Si on est dans une exception on met à jour le modified du maitre également
          $Event = $this->__getNamespace() . '\\Event';
          $organizer_master_event = new $Event($this->user, $organizer_calendar);
          $organizer_master_event->uid = $this->uid;
          // Mise à jour de l'etag pour tout le monde
          $organizer_master_event->getObjectMelanie()->updateMeetingEtag();
        }
        else {
          // Mise à jour de l'etag pour tout le monde
          $this->objectmelanie->updateMeetingEtag();
        }
      }
    }
    else {
      // Récupérer l'événement organisateur pour comparer les participants
      if (!isset($organizer_event)) {
        if ($organizer_calendar_id == $this->calendar) {
          $organizer_event = $this;
        }
        else {
          // Recuperation de l'évènement de l'organisateur
          if (strpos($this->get_class, '\Exception') === false) {
            $Event = $this->__getNamespace() . '\\Event';
            $organizer_event = new $Event($this->user, $this->calendarmce);
          }
          else {
            $Event = $this->__getNamespace() . '\\Exception';
            $organizer_event = new $Event(null, $this->user, $this->calendarmce);
            $organizer_event->recurrence_id = $this->recurrence_id;
          }
          $organizer_event->uid = $this->uid;
          if (!$organizer_event->load()) {
            // L'événement n'existe pas donc on passe la variable a null
            $organizer_event = null;
          }
          else {
            // MANTIS 0006800: Pour une invitation interne, un participant ne peut pas modifier l'horaire
            $this->keepNeedActionFieldsEvent($this, $organizer_event);
          }
        }
      }
      // Si l'événement existe et qu'il a changé il y a moins de 10 minutes, on va comparer les participants
      if (isset($organizer_event)
          && (time() - $organizer_event->modified) < 60*10) {
        foreach ($organizer_event->attendees as $organizer_attendee) {
          if ($organizer_attendee->self_invite) {
            // Si ce participant s'est lui même invité on vérifie qu'il n'a pas été supprimé entre temps
            $found = false;
            // Parcours les participants de l'événement courant pour trouver le participant
            foreach ($this->getMapAttendees() as $attendee) {
              if (strtolower($attendee->email) == strtolower($organizer_attendee->email)) {
                $found = true;
                break;
              }
            }
            if (!$found) {
              $attendees = $this->getMapAttendees();
              $attendees[] = $organizer_attendee;
              $this->setMapAttendees($attendees);
            }
          }
        }
      }
      // Positionne les événements en attente
      $this->saveNeedAction();
    }
    return true;
  }

  /**
   * Créer l'exception dans l'agenda de l'organisateur
   * 
   * @param Event $organizer_event
   */
  public function createOrganizerException($organizer_event) {
    // Créer l'exception chez l'organisateur
    $Exception = $this->__getNamespace() . '\\Exception';
    // L'exception n'existe pas, alors qu'on en veut une chez le participant
    // XXX: Traiter ce cas en créant une exception dans l'évènement de l'organisateur
    $organizer_event_exception = new $Exception($organizer_event);
    $organizer_event_exception->attendees = $organizer_event->getMapAttendees();
    $organizer_event_exception->recurrence_id = $this->recurrence_id;
    // Récupération des champs de l'événement maitre
    foreach (['uid', 'owner', 'class', 'status', 'title', 'description', 'location', 'category', 'alarm', 'transparency', 'all_day', 'timezone'] as $field) {
      $organizer_event_exception->$field = $organizer_event->$field;
    }
    // Gestion de l'organizer json
    $organizer_json = $organizer_event->getObjectMelanie()->getFieldValueFromData('organizer_json');
    $organizer_event_exception->getObjectMelanie()->setFieldValueToData('organizer_json', $organizer_json);
    $organizer_event_exception->getObjectMelanie()->setFieldHasChanged('organizer_json');
    $this->objectmelanie->setFieldValueToData('organizer_json', $organizer_json);
    $this->objectmelanie->setFieldHasChanged('organizer_json');
    // Dates de l'occurrence
    $start = new \DateTime($this->recurrence_id, new \DateTimeZone($organizer_event->timezone));
    $end = clone $start;
    $interval = $organizer_event->getMapDtstart()->diff($organizer_event->getMapDtend());
    $end->add($interval);
    $organizer_event_exception->setMapDtstart($start);
    $organizer_event_exception->setMapDtend($end);
    $organizer_event_exception->created = time();
    $organizer_event_exception->modified = time();
    // Récupérer les attributs sur la notification des participants
    $organizer_event_exception->setAttribute(ICS::X_MOZ_SEND_INVITATIONS, $organizer_event->getAttribute(ICS::X_MOZ_SEND_INVITATIONS));
    $organizer_event_exception->setAttribute(ICS::X_MOZ_SEND_INVITATIONS_UNDISCLOSED, $organizer_event->getAttribute(ICS::X_MOZ_SEND_INVITATIONS_UNDISCLOSED));

    $organizer_event->addException($organizer_event_exception);
    $organizer_event->modified = time();
    // Enregistre l'évenement de l'organisateur
    $organizer_event->save(false);

    return $organizer_event_exception;
  }

  /**
   * Conserver les champs de l'organisateur pour les champs déterminants du en attente
   */
  public function keepNeedActionFieldsEvent($event, $organizer_event) {
    // Liste des champs qui sont déterminants
    $needActionFieldsList = [
      'start',
      'end',
      'all_day',
      'title',
      'description',
      'timezone',
      'location',
      'enddate',
      'count',
      'interval',
      'type',
      'days',
      'recurrence_json',
    ];
    // Copier la liste des champs importants qui ont changés
    foreach ($needActionFieldsList as $field) {
      if ($event->getObjectMelanie()->getFieldValueFromData($field) != $organizer_event->getObjectMelanie()->getFieldValueFromData($field)) {
        $event->getObjectMelanie()->setFieldValueToData($field, $organizer_event->getObjectMelanie()->getFieldValueFromData($field));
        $event->getObjectMelanie()->setFieldHasChanged($field);
      }
    }
  }

  /**
   * L'organisateur est un externe
   * 
   * @param Organizer $organizer
   * 
   * @return boolean false
   */
  protected function setExternalOrganizer($organizer) {
    $this->getMapOrganizer()->extern = true;
    $this->getMapOrganizer()->email = $organizer->email;
    $this->getMapOrganizer()->name = $organizer->name;
    return false;
  }

  /**
   * Lister les événements associés à un uid et un owner
   * 
   * @param string $uid Uid a rechercher
   * @param string $owner [Optionnel] owner associé
   * 
   * @return Event[] Liste des Events
   */
  protected function listEventsByUid($uid, $owner = null) {
    $class = str_replace('\Exception', '\Event', $this->get_class);
    $listevents = new $class();
    $listevents->realuid = $uid;
    // XXX: Problème dans la gestion des participants
    // N'utiliser l'organizer uid que s'il existe ?
    if (isset($owner) && !empty($owner)) {
      $listevents->owner = $owner;
    }
    return $listevents->getList(null, null, null, 'attendees', false);
  }

  /**
   * Est-ce que l'utilisateur est l'organisateur en se basant sur l'uid
   * 
   * @param string $organizer_uid Uid de l'organisateur
   * @param string $user_uid Uid de l'utilisateur
   * 
   * @return boolean
   */
  protected function userIsOrganizer($organizer_uid, $user_uid) {
    $class = str_replace('\Exception', '\Event', $this->get_class);
    $objectShareClass = str_replace('\Event', '\ObjectShare', $class);
    $delimiter = constant("$objectShareClass::DELIMITER");
    return (strtolower($organizer_uid) == strtolower($user_uid) 
        || strpos(strtolower($organizer_uid), strtolower($user_uid) . $delimiter) !== false);
  }
  
  /**
   * Enregistrer l'événement en attente dans l'agenda des participants
   * Vérifie dans un premier temps que l'événement a besoin de RAZ les participants (en attente)
   * Parcours la liste des participants, 
   *   si le participant est sur Mélanie2 et qu'il a le mode en attente
   *     chercher l'événement dans la bdd
   *     si l'événement existe on reprend les modifications (date/heure, titre, location, description, récurrence)
   *       et on repasse en en attente 
   *     si l'événement n'existe pas, on le crée dans l'agenda du participant avec les éléments de base (date/heure, titre, location, description, récurrence) 
   */
  protected function saveNeedAction() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->saveNeedAction()");
    // Liste des champs qui sont déterminant pour remettre à 0 le en attente
    $needActionFieldsList = [
        'start',
        'end',
        'all_day',
        'timezone',
        'location',
        'enddate',
        'count',
        'interval',
        'type',
        'days',
        'recurrence_json',
    ];
    // Liste des champs a copier pour l'événement en attente
    $copyFieldsList = [
        'start',
        'end',
        'all_day',
        'timezone',
        'title',
        'location',
        'enddate',
        'count',
        'interval',
        'type',
        'days',
        'exceptions',
        'modified',
        'modified_json',
        'description',
        'sequence',
        'recurrence_json',
        'organizer_json',
        'organizer_calendar_id',
    ];
    // Vérifier si l'enregistrement en attente est nécessaire
    if ($this->exists()) {
      // L'événement existe, il faut vérifier les changements
      $saveNeedAction = false;
      foreach ($copyFieldsList as $field) {
        $saveNeedAction = $saveNeedAction || $this->objectmelanie->fieldHasChanged($field);
        if ($saveNeedAction) {
          break;
        }
      }
      // Gestion des exceptions
      $saveNeedAction = $saveNeedAction || $this->objectmelanie->fieldHasChanged('exceptions');
      // Gestion des participants
      $saveNeedAction = $saveNeedAction || $this->objectmelanie->fieldHasChanged('attendees');
    }
    else {
      // L'événement n'existe pas, il faut faire du en attente
      $saveNeedAction = true;
    }

    $Attendee = $this->__getNamespace() . '\\Attendee';
    // Si la sauvegarde en attente doit se faire
    if ($saveNeedAction) {
      $attendees_uid = [];
      $clean_deleted_attendees = true;
      // Parcours la liste des participant
      $attendees = $this->getMapAttendees();
      
      $User = $this->__getNamespace() . '\\User';
      $Calendar = $this->__getNamespace() . '\\Calendar';
      if (strpos($this->get_class, '\Exception') === false) {
        $Event = $this->__getNamespace() . '\\Event';
      }
      else {
        $Event = $this->__getNamespace() . '\\Exception';
      }

      if (is_array($attendees) && count($attendees) > 0) {
        foreach ($attendees as $attendee_key => $attendee) {
          // MANTIS 0006052: [En attente] Problème avec les non participants
          if ($attendee->role == Attendee::ROLE_NON_PARTICIPANT) {
            continue;
          }
          // Gére le cas d'une liste
          if ($attendee->is_list) {
            $this->attendeeList($attendee, $attendees_uid, $Attendee, $User, $Calendar, $Event, $copyFieldsList, $needActionFieldsList, $attendees, $attendee_key, $is_list_saved);

            // Gérer le is_saved pour toute la liste
            $attendees[$attendee_key]->is_saved = $is_list_saved ? true : null;
          }
          else {
            // MANTIS 0006801: [En attente] Gestion des boites partagées
            if (!$attendee->is_individuelle) {
              $clean_deleted_attendees = false;
            }
            $attendee_uid = $attendee->uid;
            // Récupérer la liste des participants
            if (isset($attendee_uid)) {
              $attendees_uid[] = $attendee_uid;
              // 0005097: [En attente] Vérifier que le participant n'est pas aussi l'organisateur
              if ($attendee_uid != $this->calendarmce->owner
                  && $attendee->need_action) {
                // Gestion du participant
                $this->attendeeEventNeedAction($attendee_uid, $User, $Calendar, $Event, $copyFieldsList, $needActionFieldsList, $attendees, $attendee_key, $is_saved);

                // Gérer le is_saved pour le participant
                $attendees[$attendee_key]->is_saved = $is_saved ? true : null;
              }
            }
            else {
              $attendees[$attendee_key]->is_saved = null;
            }
          }
        }
        $this->setMapAttendees($attendees);
      }

      // MANTIS 0005053: [En attente] Lors de la suppression d'un participant, passer son événement en annulé
      if ($this->exists()) {
        $attendees_uid[] = $this->calendar;
        $Event = $this->__getNamespace() . '\\Event';
        $event = new $Event();
        $event->uid = $this->uid;
        $event->calendar = $attendees_uid;
        // Liste des opérateurs
        $operators = [
            'uid'       => MappingMce::eq,
            'calendar'  => MappingMce::diff,
        ];
        // Filtre
        $filter = "#uid# AND #calendar#";
        $User = $this->__getNamespace() . '\\User';
        // Lister les événements pour les passer en annulé
        foreach ($event->getList(null, $filter, $operators) as $_e) {
          // Vérifier que le mode en attente est activé pour cet utilisateur
          $listAttendee = new $Attendee();
          $listAttendee->uid = $_e->calendar;

          if ($listAttendee->need_action) {
            // Copier l'événement même pour une annulation
            $this->copyEventNeedAction($this, $_e, null, $copyFieldsList, $needActionFieldsList, null, null, strpos($this->get_class, '\Exception') !== false, true);
            // Doit on annuler l'événement pour le participant ?
            if ($clean_deleted_attendees) {
              $_e->status = self::STATUS_CANCELLED;

              // 0006698: Incrémenter la séquence des participants dans le cas d'une suppression par l'organisateur
              if (!empty($_e->sequence)) {
                $_e->sequence = $_e->sequence + 1;
              }
              else {
                $_e->sequence = 1;
              }
            }
            $_e->modified = time();
            $_e->save(false);
          }       
        }
      }
    }      
  }

  /**
   * Gérer les participants d'une liste pour le en attendee
   * Méthode récursive pour les listes de listes
   * 
   * @param Attendee $attendee
   * @param array $attendees_uid [In/Out]
   * @param string $Attendee
   * @param string $User
   * @param string $Calendar
   * @param string $Event
   * @param array $copyFieldsList
   * @param array $needActionFieldsList
   * @param array $attendees
   * @param string $attendee_key
   * @param boolean $is_list_saved
   */
  protected function attendeeList($attendee, &$attendees_uid, $Attendee, $User, $Calendar, $Event, $copyFieldsList, $needActionFieldsList, $attendees, $attendee_key, &$is_list_saved) {
    $is_list_saved = true;
    foreach ($attendee->members as $member) {
      // L'utilisateur existe bien dans l'annuaire
      $listAttendee = new $Attendee();

      // Gérer le cas où le participant est dans la liste mais aussi dans les participants
      if ($this->isAttendee($member, $attendees)) {
        continue;
      }
      $listAttendee->email = $member;

      $attendeeUid = $listAttendee->uid;

      if ($listAttendee->is_list) {
        // Gérer les listes imbriquées
        $this->attendeeList($listAttendee, $attendees_uid, $Attendee, $User, $Calendar, $Event, $copyFieldsList, $needActionFieldsList, $attendees, null, $is_saved);
        $is_list_saved &= $is_saved;
      }
      else if (isset($attendeeUid) && $listAttendee->is_individuelle) {
        $attendees_uid[] = $listAttendee->uid;
        // 0005097: [En attente] Vérifier que le participant n'est pas aussi l'organisateur
        if ($listAttendee->uid != $this->calendarmce->owner) {
          if ($listAttendee->need_action) {
            // Parcours les members et traite ceux qui ont le need_action activé
            // Gestion du participant
            $this->attendeeEventNeedAction($listAttendee->uid, $User, $Calendar, $Event, $copyFieldsList, $needActionFieldsList, $attendees, null, $is_saved);
            $is_list_saved &= $is_saved;
          }
        }
      }
    }
  }

  /**
   * Est-ce que cette adresse email fait déjà partie de la liste des participants
   * 
   * @param string $email
   * @param Attendee[] $attendees
   * 
   * @return boolean
   */
  private function isAttendee($email, $attendees) {
    $isAttendee = false;
    foreach ($attendees as $attendee) {
      if (strtolower($email) == strtolower($attendee->email)) {
        $isAttendee = true;
        break;
      }
    }
    return $isAttendee;
  }

  /**
   * Enregistre l'événement dans l'agenda du participant
   * 
   * @param string $attendee_uid Uid du participant
   * @param string $User Classe User
   * @param string $Calendar Classe Calendar
   * @param string $Event Classe Event
   * @param array $copyFieldsList
   * @param array $needActionFieldsList
   * @param array $attendees
   * @param int $attendee_key
   * @param boolean $is_saved
   */
  protected function attendeeEventNeedAction($attendee_uid, $User, $Calendar, $Event, $copyFieldsList, $needActionFieldsList, &$attendees, $attendee_key, &$is_saved) {
    $is_saved = false;
    // Creation du user melanie
    $attendee_user = new $User();
    $attendee_user->uid = $attendee_uid;
    // Création du calendar melanie
    $attendee_calendar = new $Calendar($attendee_user);
    $attendee_calendar->id = $attendee_uid;
    if ($attendee_calendar->load()) {            
      // Creation de l'evenement melanie
      if (strpos($this->get_class, '\Exception') === false) {
        $attendee_event = new $Event($attendee_user, $attendee_calendar);
      }
      else {
        $attendee_event = new $Event(null, $attendee_user, $attendee_calendar);
      }
      // Enregistrement de la recurrence
      if (strpos($this->get_class, '\Exception') === false) {
        $recurrence = $this->getMapRecurrence();
        if (isset($recurrence)) {
          $attendee_recurrence = $attendee_event->getMapRecurrence();
          $attendee_recurrence->type = $recurrence->type;
          $attendee_recurrence->count = $recurrence->count;
          $attendee_recurrence->days = $recurrence->days;
          $attendee_recurrence->enddate = $recurrence->enddate;
          $attendee_recurrence->interval = $recurrence->interval;
          $attendee_event->setMapRecurrence($attendee_recurrence);
        }
      }
      else {
        $attendee_event->recurrence_id = $this->recurrence_id;
      }
      $attendee_event->uid = $this->uid;
      $save = $this->copyEventNeedAction($this, $attendee_event, $attendee_uid, $copyFieldsList, $needActionFieldsList, $attendees, $attendee_key, strpos($this->get_class, '\Exception') !== false, $attendee_event->load());
      if ($save) {
        $attendee_event->modified = time();
        // Enregistre l'événement dans l'agenda du participant
        $attendee_event->save(false);
        $is_saved = true;
      }
    }
  }
  
  /**
   * Copie l'événement dans ceux des participants pour le need action
   * 
   * @param Event $event
   * @param Event $attendee_event
   * @param string $attendee_uid
   * @param array $copyFieldsList
   * @param array $needActionFieldsList
   * @param array $attendees
   * @param int $attendee_key
   * @param boolean $isException
   * @param boolean $eventExists
   * 
   * @return boolean L'événement doit il être enregistré
   */
  protected function copyEventNeedAction($event, &$attendee_event, $attendee_uid, $copyFieldsList, $needActionFieldsList, $attendees, $attendee_key, $isException, $eventExists) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->copyEventNeedAction()");
    if ($eventExists) {
      $save = false;
      $saveAndNeedAction = false;
      // Si l'événement existe, copier la liste des champs importants qui ont changés
      foreach ($copyFieldsList as $field) {
        if ($event->getObjectMelanie()->getFieldValueFromData($field) != $attendee_event->getObjectMelanie()->getFieldValueFromData($field)) {
          $save = true;
          $newvalue = $event->getObjectMelanie()->getFieldValueFromData($field);
          $oldvalue = $attendee_event->getObjectMelanie()->getFieldValueFromData($field);
          $attendee_event->getObjectMelanie()->setFieldValueToData($field, $newvalue);
          $attendee_event->getObjectMelanie()->setFieldHasChanged($field);
          if (in_array($field, $needActionFieldsList)) {
            M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->copyEventNeedAction() [" . $event->realuid . "] needActionField: " . $field);
            // MANTIS 0006295: [En attente] Identifier des changements de lieu non majeur
            if ($field == 'location') {
              if (!(strpos($oldvalue, 'http') === 0 && strpos($newvalue, 'http') === 0
                  || strpos($oldvalue, 'http') === 0 && empty($newvalue)
                  || empty($oldvalue) && strpos($newvalue, 'http') === 0)) {
                $saveAndNeedAction = true;
              }
            }
            else {
              $saveAndNeedAction = true;
            }
          }
        }
      }
      // Gérer le cas particulier des attendees
      $attendee_event_attendees = $attendee_event->getObjectMelanie()->getFieldValueFromData('attendees');
      if (!empty($attendee_event_attendees)) {
        $attendee_event->getObjectMelanie()->setFieldValueToData('attendees', '');
        $attendee_event->getObjectMelanie()->setFieldHasChanged('attendees');
        $save = true;
      }
      // MANTIS 0006232: [En attente] Gérer les catégories des espaces de travail du BNum
      $field = 'category';
      if ($event->getObjectMelanie()->getFieldValueFromData($field) != $attendee_event->getObjectMelanie()->getFieldValueFromData($field)
          && strpos($event->getObjectMelanie()->getFieldValueFromData($field), 'ws#') === 0) {
        $value = $event->getObjectMelanie()->getFieldValueFromData($field);
        $attendee_event->getObjectMelanie()->setFieldValueToData($field, $value);
        $attendee_event->getObjectMelanie()->setFieldHasChanged($field);
      }
      // Gestion des exceptions
      if (!$isException) {
        $save = $save || $event->getObjectMelanie()->fieldHasChanged('exceptions');
      }      
      if ($save) {
        if ($saveAndNeedAction) {           
          // Modification en tentative
          $attendee_event->status = self::STATUS_TENTATIVE;
          if (isset($attendee_key)) {
            // MANTIS 0006801: [En attente] Gestion des boites partagées
            if ($attendees[$attendee_key]->is_ressource) {
              // Gestion des boites ressources
              $attendees[$attendee_key]->response = Attendee::RESPONSE_ACCEPTED;
              $attendee_event->status = self::STATUS_CONFIRMED;
              if (!isset($attendees[$attendee_key]->type) || $attendees[$attendee_key]->type == Attendee::TYPE_INDIVIDUAL) {
                $attendees[$attendee_key]->type = Attendee::TYPE_RESOURCE;
              }
            }
            else {
              // Passage en Need Action
              $attendees[$attendee_key]->response = Attendee::RESPONSE_NEED_ACTION;
            }
            $event->attendees = $attendees;
          }          
        }
        return true;
      }
    }
    else {
      // MANTIS 0006225: [En attente] Un participant décliné ne doit pas avoir l'événement recréé
      if (isset($attendee_key) && $attendees[$attendee_key]->response == Attendee::RESPONSE_DECLINED) {
        // Rechercher si un champ majeur (date, lieu) a changé
        $saveAndNeedAction = false;
        foreach ($copyFieldsList as $field) {
          if (in_array($field, $needActionFieldsList) 
              && $event->getObjectMelanie()->fieldHasChanged($field)) {
            M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->copyEventNeedAction() [" . $event->realuid . "] DECLINED needActionField: " . $field);
            $saveAndNeedAction = true;
          }
        }
        // Si aucun champ majeur n'a changé on n'enregistre pas l'événement
        if (!$saveAndNeedAction) {
          return false;
        }
      }
      // Si l'événement n'existe pas, on le génére a partir de la liste des champs de l'événement
      if ($isException
          && $event->location == $event->getEventParent()->location) {
        // Si c'est une nouvelle exception, on vérifie à partir du recurrence id que la date ne change pas
        $recurrence_id = new \DateTime($event->recurrence_id);
        // Récupération des dates de l'occurrence
        $occurrence_start_date = new \DateTime($event->start);
        $occurrence_end_date = new \DateTime($event->end);
        $occurrence_interval = $occurrence_start_date->diff($occurrence_end_date);
        // Récupération des dates de l'événement parent
        $parent_date = new \DateTime($event->getEventParent()->start);
        $parent_end_date = new \DateTime($event->getEventParent()->end);
        $parent_date->add($occurrence_interval);
        
        // Comparaison des dates pour savoir si l'occurrence a bougé
        if ($occurrence_start_date == $recurrence_id 
            && $parent_date == $parent_end_date) {
          $attendee_event->status = $attendee_event->getEventParent()->status;
        }
        else {
          $attendee_event->status = self::STATUS_TENTATIVE;
          if (isset($attendee_key)) {
            // MANTIS 0006801: [En attente] Gestion des boites partagées
            if ($attendees[$attendee_key]->is_ressource) {
              // Gestion des boites ressources
              $attendees[$attendee_key]->response = Attendee::RESPONSE_ACCEPTED;
              $attendee_event->status = self::STATUS_CONFIRMED;
              if (!isset($attendees[$attendee_key]->type) || $attendees[$attendee_key]->type == Attendee::TYPE_INDIVIDUAL) {
                $attendees[$attendee_key]->type = Attendee::TYPE_RESOURCE;
              }
            }
            else {
              // Passage en Need Action
              $attendees[$attendee_key]->response = Attendee::RESPONSE_NEED_ACTION;
            }
            $event->attendees = $attendees;
          }
        }
      }
      else {
        $attendee_event->status = self::STATUS_TENTATIVE;
        if (isset($attendee_key)) {
          // MANTIS 0006801: [En attente] Gestion des boites partagées
          if ($attendees[$attendee_key]->is_ressource) {
            // Gestion des boites ressources
            $attendees[$attendee_key]->response = Attendee::RESPONSE_ACCEPTED;
            $attendee_event->status = self::STATUS_CONFIRMED;
            if (!isset($attendees[$attendee_key]->type) || $attendees[$attendee_key]->type == Attendee::TYPE_INDIVIDUAL) {
              $attendees[$attendee_key]->type = Attendee::TYPE_RESOURCE;
            }
          }
          else {
            // Passage en Need Action
            $attendees[$attendee_key]->response = Attendee::RESPONSE_NEED_ACTION;
          }
          $event->attendees = $attendees;
        }
      }
      $attendee_event->class = self::CLASS_PUBLIC;
      $attendee_event->transparency = self::TRANS_OPAQUE;
      $attendee_event->created = time();      
      $attendee_event->owner = $attendee_uid;
      $attendee_event->alarm = 0;

      // MANTIS 0006232: [En attente] Gérer les catégories des espaces de travail du BNum
      if (strpos($event->category, 'ws#') === 0) {
        $attendee_event->category = $event->category;
      }
      
      // copier la liste des champs
      foreach ($copyFieldsList as $field) {
        $attendee_event->$field = $event->$field;
      }
      return true;
    }
    return false;
  }
  
  /**
   * L'événement est supprimé dans l'agenda de l'organisateur
   * On passe tous les participants Mélanie2 en événement annulé
   */
  protected function deleteNeedAction() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->deleteNeedAction()");
    // Parcours la liste des participant
    $attendees = $this->getMapAttendees();
    if (isset($attendees)) {
      $User = $this->__getNamespace() . '\\User';
      $Calendar = $this->__getNamespace() . '\\Calendar';
      if (strpos($this->get_class, '\Exception') === false) {
        $Event = $this->__getNamespace() . '\\Event';
      }
      else {
        $Event = $this->__getNamespace() . '\\Exception';
      }
      $Attendee = $this->__getNamespace() . '\\Attendee';
      foreach ($attendees as $key => $attendee) {
        $attendee_uid = $attendee->uid;
        // Si c'est un participant Mélanie2
        if (isset($attendee_uid)
            // 0005097: [En attente] Vérifier que le participant n'est pas aussi l'organisateur
            && $attendee_uid != $this->calendar
            && $attendee->need_action) {
          // Creation du user melanie
          $attendee_user = new $User();
          $attendee_user->uid = $attendee_uid;
          // Création du calendar melanie
          $attendee_calendar = new $Calendar($attendee_user);
          $attendee_calendar->id = $attendee_uid;
          if ($attendee_calendar->load()) {
            // Creation de l'evenement melanie
            if (strpos($this->get_class, '\Exception') === false) {
              $attendee_event = new $Event($attendee_user, $attendee_calendar);
            }
            else {
              $attendee_event = new $Event(null, $attendee_user, $attendee_calendar);
              $attendee_event->recurrence_id = $this->recurrence_id;
            }
            $attendee_event->uid = $this->uid;
            if ($attendee_event->load()) {
              // L'evement normal est supprimé
              if ($attendee_event->status == self::STATUS_TENTATIVE
                  && $attendee->response == $Attendee::RESPONSE_NEED_ACTION) {
                // Supprimer l'événement qui est en en attente
                $attendee_event->delete();
                $save = false;
              }
              else {
                // Modification en annulé
                $attendee_event->status = self::STATUS_CANCELLED;
                $save = true;

                // 0006698: Incrémenter la séquence des participants dans le cas d'une suppression par l'organisateur
                if (!empty($attendee_event->sequence)) {
                  $attendee_event->sequence = $attendee_event->sequence + 1;
                }
                else {
                  $attendee_event->sequence = 1;
                }
              }
              if ($save) {
                $attendee_event->modified = time();
                // Enregistre l'événement dans l'agenda du participant
                $attendee_event->save(false);
              }              
            }
          }
        }
      }
    }
  }
  
  /**
   * Suppression de la liste des pièces jointes liées à l'évènement
   */
  protected function deleteAttachments() {
    $event_uid = $this->objectmelanie->uid;
    $Attachment = $this->__getNamespace() . '\\Attachment';
    $class = str_replace('\Exception', '\Event', $this->get_class);
    $_events = new $class();
    $_events->uid = $event_uid;
    $nb_events = $_events->getList('count');
    $count = $nb_events['']->events_count;
    unset($nb_events);
    // Si c'est le dernier evenement avec le même uid on supprime toutes les pièces jointes
    if ($count === 0) {
      $attachments_folders = new $Attachment();
      $attachments_folders->isfolder = true;
      $attachments_folders->path = $event_uid;
      $folders_list = [];
      // Récupère les dossiers lié à l'évènement
      $folders = $attachments_folders->getList();
      if (count($folders) > 0) {
        foreach ($folders as $folder) {
          $folders_list[] = $folder->path . '/' . $folder->name;
        }
        $attachments = new $Attachment();
        $attachments->isfolder = false;
        $attachments->path = $folders_list;
        // Lecture des pièces jointes pour chaque dossier de l'évènement
        $attachments = $attachments->getList([
            'id',
            'name',
            'path'
        ]);
        if (count($attachments) > 0) {
          foreach ($attachments as $attachment) {
            // Supprime la pièce jointe
            $attachment->delete();
          }
        }
        foreach ($folders as $folder) {
          // Supprime le dossier
          $folder->delete();
        }
      }
      $folder = new $Attachment();
      $folder->isfolder = true;
      $folder->path = '';
      $folder->name = $event_uid;
      if ($folder->load()) {
        $folder->delete();
      }
    }
  }
  
  /**
   * Sauvegarde les attributs dans la base de données
   */
  protected function saveAttributes() {
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
  protected function loadAttributes() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->loadAttributes()");
    // Création de l'objet s'il n'existe pas
    if (!isset($this->attributes))
      $this->attributes = [];
    // Gérer le cas où l'event est loadé mais n'existe pas dans la base
    if (!$this->objectmelanie->getIsExist()) {
      $this->attributes_loaded = true;
      return;
    }
    $EventProperty = $this->__getNamespace() . '\\EventProperty';
    // Génération de l'attribut pour le getList
    $eventproperty = new $EventProperty();
    $eventproperty->event = $this->objectmelanie->uid;
    if (isset($this->calendarmce)) {
      $eventproperty->calendar = $this->calendarmce->id;
    } else {
      $eventproperty->calendar = $this->calendar;
    }
    // Problème de User avec DAViCal
    if (isset($this->calendarmce) && isset($this->calendarmce->owner)) {
      $eventproperty->user = $this->calendarmce->owner;
    } else if (isset($this->owner)) {
      $eventproperty->user = $this->owner;
    } else {
      $eventproperty->user = '';
    }
    $properties = $eventproperty->getList();
    // Récupération de la liste des attributs
    foreach ($properties as $property) {
      $this->attributes[$property->key] = $property;
    }
    $this->attributes_loaded = true;
  }
  /**
   * Supprime les attributs
   */
  protected function deleteAttributes() {
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
   * Charge les exceptions en mémoire
   * Doit être utilisé quand l'évènement n'existe pas, donc que le load retourne false
   * 
   * @return boolean
   */
  private function loadExceptions() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->loadExceptions()");
    $event = new static($this->user, $this->calendarmce);
    $event->realuid = $this->uid;
    $events = $event->getList();
    if (isset($events[$this->uid . $this->calendar])) {
      $this->modified = isset($events[$this->uid . $this->calendar]->modified) ? $events[$this->uid . $this->calendar]->modified : 0;
      $this->setMapExceptions($events[$this->uid . $this->calendar]->getMapExceptions());
      $this->objectmelanie->setIsExist();
      $this->objectmelanie->setIsLoaded();
    }
    if (is_array($this->exceptions) && count($this->exceptions) > 0) {
      $this->deleted = true;
      return true;
    }
    return false;
  }
  
  /**
   * Test pour savoir si on est dans une exception ou un évènement maitre
   * 
   * @return boolean
   */
  private function notException() {
    return $this->get_class == $this->__getNamespace() . '\\Event';
  }
  
  /**
   * MANTIS 0005125: Bloquer les répétitions "récursives"
   * Vérifier que la durée de l'événement est plus courte que la durée de l'événement
   * 
   * @return boolean True si tout est OK, false sinon 
   */
  private function checkRecurrence() {
    // Tableau permettant de recuperer toutes les valeurs de la recurrence
    if (isset($this->objectmelanie->recurrence_json)) {
      $recurrence = json_decode($this->objectmelanie->recurrence_json, true);
      if (isset($recurrence[ICS::FREQ])) {
        $event_duration = strtotime($this->objectmelanie->end) - strtotime($this->objectmelanie->start);
        switch ($recurrence[ICS::FREQ]) {
          case ICS::FREQ_DAILY:
            $event_max_duration = 60*60*24;
            break;
          case ICS::FREQ_WEEKLY:
            $event_max_duration = 60*60*24*7;
            break;
          case ICS::FREQ_MONTHLY:
            $event_max_duration = 60*60*24*7*31;
            break;
          case ICS::FREQ_YEARLY:
            $event_max_duration = 60*60*24*366;
            break;
        }
        return $event_max_duration >= $event_duration;
      }
    }
    return true;
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
  function save($saveAttendees = true) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->save()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    // MANTIS 0005125: Bloquer les répétitions "récursives"
    if (!$this->checkRecurrence()) {
      M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->save() La recurrence ne respecte pas les regles d'usage (duree de l'evenement plus longue que la repetition)");
      return null;
    }
    // Sauvegarde des participants
    if ($saveAttendees) {
      $this->saveAttendees();
    }
    // Supprimer les exceptions
    if (isset($this->deleted_exceptions) && is_array($this->deleted_exceptions) && count($this->deleted_exceptions) > 0) {
      M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->save() delete " . count($this->deleted_exceptions));
      foreach ($this->deleted_exceptions as $exception) {
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->save() delete " . $exception->uid);
        $exception->delete();
      }
    }
    $exMod = false;
    // Sauvegarde des exceptions
    if (isset($this->exceptions)) {
      foreach ($this->exceptions as $exception) {
        $res = $exception->save();
        $exMod = $exMod || !is_null($res);
      }
    }
    if ($this->deleted) {
      // Sauvegarde des attributs
      $this->saveAttributes();
      return false;
    }
      
    if ($exMod) {
      $this->setMapModified(time());
    }
    if (!isset($this->owner)) {
      $this->owner = $this->user->uid;
    }
    // Version du schéma par défaut
    $this->version = 2;
    // Sauvegarde l'objet
    $insert = $this->objectmelanie->save();
    if (!is_null($insert)) {
      // Sauvegarde des attributs
      $this->saveAttributes();
      // Gestion de l'historique
      $history = new HistoryMelanie();
      $history->uid = Config::get(Config::CALENDAR_PREF_SCOPE) . ":" . $this->calendar . ":" . $this->objectmelanie->uid;
      $history->action = $insert ? Config::get(Config::HISTORY_ADD) : Config::get(Config::HISTORY_MODIFY);
      $history->timestamp = time();
      $history->description = "LibM2/" . Config::get(Config::APP_NAME);
      $history->who = isset($this->user) ? $this->user->uid : $this->calendar;
      // Enregistrement dans la base
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
   * @ignore
   *
   */
  function delete() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->delete()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    // Suppression des exceptions
    if (isset($this->exceptions)) {
      foreach ($this->exceptions as $exception) {
        if (!$exception->deleted)
          $exception->delete();
      }
    }
    // Gestion de la suppression pour le mode en attente
    $organizer = $this->getMapOrganizer();
    if (isset($organizer) 
        && $organizer->calendar == $this->calendar) {
      $this->deleteNeedAction();
    }
    // Suppression de l'objet
    if ($this->objectmelanie->delete()) {
      // Suppression des attributs liés à l'évènement
      $this->deleteAttributes();
      // Suppression des pièces jointes de l'évènement
      $this->deleteAttachments();
      // Gestion de l'historique
      $history = new HistoryMelanie();
      $history->uid = Config::get(Config::CALENDAR_PREF_SCOPE) . ":" . $this->objectmelanie->calendar . ":" . $this->objectmelanie->uid;
      $history->action = Config::get(Config::HISTORY_DELETE);
      $history->timestamp = time();
      $history->description = "LibM2/" . Config::get(Config::APP_NAME);
      $history->who = isset($this->user) ? $this->user->uid : $this->objectmelanie->calendar;
      // Enregistrement dans la base
      if (!is_null($history->save()))
        return true;
    }
    else {
      // Suppression des attributs liés à l'évènement
      $this->deleteAttributes();
    }
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->delete() Error: return false");
    return false;
  }
  
  /**
   * Utilisé pour les exceptions
   * visiblement l'héritage ne fonctionne pas bien dans notre cas
   * 
   * @ignore
   *
   */
  function load() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->load()");
    $ret = $this->objectmelanie->load();
    if (!$ret && $this->notException())
      $ret = $this->loadExceptions();
    else
      $this->deleted = false;
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
   * @return Event[] Array
   */
  function getList($fields = [], $filter = "", $operators = [], $orderby = "", $asc = true, $limit = null, $offset = null, $case_unsensitive_fields = []) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getList()");
    $_events = $this->objectmelanie->getList($fields, $filter, $operators, $orderby, $asc, $limit, $offset, $case_unsensitive_fields);
    if (!isset($_events))
      return null;
    $events = [];
    $exceptions = [];
    // MANTIS 3680: Charger tous les attributs lors d'un getList
    $events_uid = [];
    $Calendar = $this->__getNamespace() . '\\Calendar';
    $Exception = $this->__getNamespace() . '\\Exception';
    // Traitement de la liste des évènements
    foreach ($_events as $_event) {
      try {
        $_event->setIsExist();
        $_event->setIsLoaded();
        if (isset($this->calendarmce) && $this->calendarmce->id == $_event->calendar) {
          $calendar = $this->calendarmce;
        } else {
          $calendar = new $Calendar($this->user);
          $calendar->id = $_event->calendar;
        }
        if (strpos($_event->uid, $Exception::RECURRENCE_ID) === false) {
          $event = new static($this->user, $calendar);
          $event->setObjectMelanie($_event);
          $event->setMapDeleted(false);
          $events[$event->uid . $event->calendar] = $event;
          // MANTIS 3680: Charger tous les attributs lors d'un getList
          $events_uid[] = $event->uid;
        } else {
          $exception = new $Exception(null, $this->user, $calendar);
          $exception->setObjectMelanie($_event);
          if (!isset($exceptions[$exception->uid . $exception->calendar]) || !is_array($exceptions[$exception->uid . $exception->calendar]))
            $exceptions[$exception->uid . $exception->calendar] = [];
          // Filtrer les exceptions qui n'ont pas de date
          if (empty($exception->start) || empty($exception->end)) {
            $exception->deleted = true;
          } else {
            $exception->deleted = false;
          }
          $recId = new \DateTime(substr($_event->uid, strlen($_event->uid) - strlen($Exception::FORMAT_STR . $Exception::RECURRENCE_ID), strlen($Exception::FORMAT_STR)));
          $exceptions[$exception->uid . $exception->calendar][$recId->format($Exception::FORMAT_ID)] = $exception;
          // MANTIS 3680: Charger tous les attributs lors d'un getList
          $events_uid[] = $_event->uid;
        }
      } catch (\Exception $ex) {
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getList() Exception: " . $ex);
      }
    }
    // Détruit les variables pour libérer le plus rapidement de la mémoire
    unset($_events);
    // Traitement des exceptions qui n'ont pas d'évènement associé
    // On crée un faux évènement qui va contenir ces exceptions
    foreach ($exceptions as $key => $_exceptions) {
      if (!isset($events[$key])) {
        $event = new static($this->user);
        $modified = 0;
        foreach ($_exceptions as $_exception) {
          $calendarid = $_exception->calendar;
          $uid = $_exception->uid;
          $_exception->setEventParent($event);
          if (!isset($_exception->modified))
            $_exception->modified = 0;
          if ($_exception->modified > $modified)
            $modified = $_exception->modified;
        }
        if (isset($uid)) {
          if (isset($this->calendarmce) && $this->calendarmce->id == $_event->calendar) {
            $calendar = $this->calendarmce;
          } else {
            $calendar = new $Calendar($this->user);
            $calendar->id = $calendarid;
          }
          $event->setCalendarMelanie($calendar);
          $event->uid = $uid;
          $event->setMapDeleted(true);
          $event->modified = $modified;
          $event->setMapExceptions($_exceptions);
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
   * Détermine si les nouvelles données en JSON peuvent être utilisés
   * 
   * @return boolean
   */
  public function useJsonData() {
    return $this->objectmelanie->modified_json === $this->objectmelanie->modified;
  }
  /**
   * Détermine si on est dans le nouveau schéma de l'ORM
   * 
   * @return boolean
   */
  private function useNewMode() {
    return Config::is_set(Config::USE_NEW_MODE) && Config::get(Config::USE_NEW_MODE);
  }
  /**
   * Mapping uid field
   *
   * @param string $uid
   */
  protected function setMapUid($uid) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapUid(" . (is_string($uid) ? $uid : "") . ")");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->objectmelanie->uid = $uid;
    $this->objectmelanie->realuid = $uid;
  }
  /**
   * Mapping modified field
   *
   * @param integer $modified
   */
  protected function setMapModified($modified) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapModified($modified)");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->objectmelanie->modified = $modified;
    $this->objectmelanie->modified_json = $modified;
  }
  /**
   * Mapping timezone field
   */
  protected function getMapTimezone() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapTimezone()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if ($this->useJsonData()) {
      $timezone = $this->objectmelanie->timezone;
    }
    else {
      if (isset($this->user)) {
        $timezone = $this->user->getTimezone();
      }
      if (!isset($timezone) && isset($this->calendarmce)) {
        $timezone = $this->calendarmce->getTimezone();
      }
    }
    if (!isset($timezone)) {
      $timezone = Config::get(Config::CALENDAR_DEFAULT_TIMEZONE);
    }
    
    return $timezone;
  }
  
  /**
   * Mapping all_day field
   */
  protected function getMapAll_day() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapAll_day()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if ($this->useJsonData()) {
      $all_day = $this->objectmelanie->all_day;
    }
    else {
      $all_day = strpos($this->objectmelanie->start, ' 00:00:00') !== false && strpos($this->objectmelanie->end, ' 00:00:00') !== false;
    }
    if (!isset($all_day)) {
      $all_day = true;
    }
    
    return $all_day;
  }
  
  /**
   * Mapping start field
   *
   * @param string $start
   */
  protected function setMapStart($start) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapStart()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->objectmelanie->start = $start;
    $this->dtstart = null;
    $this->dtstart_utc = null;
  }
  
  /**
   * Mapping dtstart field
   *
   * @param \DateTime $dtstart
   */
  protected function setMapDtstart($dtstart) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapDtstart()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->dtstart = $dtstart;
    $this->objectmelanie->start = $dtstart->format(self::DB_DATE_FORMAT);
    $this->objectmelanie->timezone = $dtstart->getTimezone()->getName();
  }
  /**
   * Mapping dtstart field
   */
  protected function getMapDtstart() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapDtstart()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if (!isset($this->dtstart)) {
      try {
        $this->dtstart = new \DateTime($this->objectmelanie->start, new \DateTimeZone($this->getMapTimezone()));
      }
      catch (\Exception $ex) {
        M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->getMapDtstart() Erreur pour l'événement '" . $this->objectmelanie->uid . "' : " . $ex->getMessage());
        $this->dtstart = new \DateTime();
      }
    }
    return $this->dtstart;
  }
  
  /**
   * Mapping dtstart_utc field
   */
  protected function getMapDtstart_utc() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapDtstart_utc()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if (!isset($this->dtstart_utc)) {
      try {
        $this->dtstart_utc = new \DateTime($this->objectmelanie->start, new \DateTimeZone($this->getMapTimezone()));
        $this->dtstart_utc->setTimezone(new \DateTimeZone('UTC'));
      }
      catch (\Exception $ex) {
        M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->getMapDtstart_utc() Erreur pour l'événement '" . $this->objectmelanie->uid . "' : " . $ex->getMessage());
        $this->dtstart_utc = new \DateTime();
      }
    }
    return $this->dtstart_utc;
  }
  
  /**
   * Mapping end field
   *
   * @param string $end
   */
  protected function setMapEnd($end) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapEnd()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->objectmelanie->end = $end;
    $this->dtend = null;
    $this->dtend_utc = null;
  }
  
  /**
   * Mapping dtend field
   *
   * @param \DateTime $dtend
   */
  protected function setMapDtend($dtend) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapDtend()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->dtend = $dtend;
    $this->objectmelanie->end = $dtend->format(self::DB_DATE_FORMAT);
    // Pas de timezone ici, il est récupéré dans le dtstart
  }
  /**
   * Mapping dtend field
   */
  protected function getMapDtend() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapDtend()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if (!isset($this->dtend)) {
      try {
        $this->dtend = new \DateTime($this->objectmelanie->end, new \DateTimeZone($this->getMapTimezone()));
      }
      catch (\Exception $ex) {
        M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->getMapDtend() Erreur pour l'événement '" . $this->objectmelanie->uid . "' : " . $ex->getMessage());
        $this->dtend = new \DateTime();
      }
    }
    return $this->dtend;
  }
  
  /**
   * Mapping dtend_utc field
   */
  protected function getMapDtend_utc() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapDtend_utc()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if (!isset($this->dtend_utc)) {
      try {
        $this->dtend_utc = new \DateTime($this->objectmelanie->end, new \DateTimeZone($this->getMapTimezone()));
        $this->dtend_utc->setTimezone(new \DateTimeZone('UTC'));
      }
      catch (\Exception $ex) {
        M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->getMapDtend_utc() Erreur pour l'événement '" . $this->objectmelanie->uid . "' : " . $ex->getMessage());
        $this->dtend_utc = new \DateTime();
      }
    }
    return $this->dtend_utc;
  }
  
  /**
   * Mapping class field
   * 
   * @param Event::CLASS_* $class          
   */
  protected function setMapClass($class) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapClass($class)");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if (isset(MappingMce::$MapClassObjectToMce[$class]))
      $this->objectmelanie->class = MappingMce::$MapClassObjectToMce[$class];
  }
  /**
   * Mapping class field
   */
  protected function getMapClass() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapClass()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if (isset(MappingMce::$MapClassMceToObject[$this->objectmelanie->class]))
      return MappingMce::$MapClassMceToObject[$this->objectmelanie->class];
    else
      return self::CLASS_PUBLIC;
  }
  
  /**
   * Mapping status field
   * 
   * @param Event::STATUS_* $status          
   */
  protected function setMapStatus($status) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapStatus($status)");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if (isset(MappingMce::$MapStatusObjectToMce[$status]))
      $this->objectmelanie->status = MappingMce::$MapStatusObjectToMce[$status];
  }
  /**
   * Mapping status field
   */
  protected function getMapStatus() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapStatus()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if (isset(MappingMce::$MapStatusMceToObject[$this->objectmelanie->status]))
      return MappingMce::$MapStatusMceToObject[$this->objectmelanie->status];
    else
      return self::STATUS_CONFIRMED;
  }
  
  /**
   * Mapping transparency field
   *
   * @param Event::TRANSP_* $transparency
   */
  protected function setMapTransparency($transparency) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapTransparency($transparency)");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->objectmelanie->transparency = $transparency;
  }
  /**
   * Mapping transparency field
   */
  protected function getMapTransparency() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapTransparency()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if ($this->useJsonData()) {
      $transparency = $this->objectmelanie->transparency;
    }
    else {
      $transparency = $this->getAttribute(ICS::TRANSP);
    }
    if (!isset($transparency)) {
      $transparency = self::TRANS_OPAQUE;
    }
    
    return $this->objectmelanie->transparency;
  }
  
  /**
   * Mapping priority field
   *
   * @param Event::PRIORITY_* $priority
   */
  protected function setMapPriority($priority) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapPriority($priority)");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->objectmelanie->priority = $priority;
  }
  /**
   * Mapping priority field
   */
  protected function getMapPriority() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapPriority()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    return $this->objectmelanie->priority;
  }
  
  /**
   * Mapping recurrence field
   * 
   * @param Recurrence $recurrence          
   */
  protected function setMapRecurrence($recurrence) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapRecurrence()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->recurrence = $recurrence;
    $this->recurrence->setObjectMelanie($this->objectmelanie);
  }
  /**
   * Mapping recurrence field
   */
  protected function getMapRecurrence() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapRecurrence()");
    if (!isset($this->recurrence)) {
      $Recurrence = $this->__getNamespace() . '\\Recurrence';
      $this->recurrence = new $Recurrence($this);
    }
    return $this->recurrence;
  }
  
  /**
   * Mapping organizer field
   * 
   * @param Organizer $organizer          
   */
  protected function setMapOrganizer($organizer) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapOrganizer()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->organizer = $organizer;
    $this->organizer->setObjectMelanie($this->objectmelanie);
  }
  /**
   * Mapping organizer field
   */
  protected function getMapOrganizer() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapOrganizer()");
    if (!isset($this->organizer)) {
      $Organizer = $this->__getNamespace() . '\\Organizer';
      $this->organizer = new $Organizer($this);
    }
    return $this->organizer;
  }
  
  /**
   * Mapping attendees field
   * 
   * @param Attendee[] $attendees          
   */
  protected function setMapAttendees($attendees) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapAttendees()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $_attendees = [];
    if (!empty($attendees)) {
      foreach ($attendees as $attendee) {
        if (is_object($attendee) 
            && $attendee instanceof Attendee) {
          $_attendees[$attendee->email] = $attendee->render();
        }
      }
    }
    $this->objectmelanie->attendees = serialize($_attendees);
    $this->_attendees = $attendees;
  }
  /**
   * Mapping attendees field
   */
  protected function getMapAttendees() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapAttendees()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();

    if (!isset($this->_attendees)) {
      // Récupération des participants
      $object_attendees = null;

      // Participants directement dans l'objet
      // TODO: Corriger le problème lorsque la variable isset mais vide (ou a:{})
      if (isset($this->objectmelanie->attendees) && $this->objectmelanie->attendees != "" && $this->objectmelanie->attendees != "a:0:{}")
        $object_attendees = $this->objectmelanie->attendees;
      // Participants appartenant à l'organisateur
      elseif (isset($this->objectmelanie->organizer_attendees) && $this->objectmelanie->organizer_attendees != "" && $this->objectmelanie->organizer_attendees != "a:0:{}")
        $object_attendees = $this->objectmelanie->organizer_attendees;
      else
        return [];

      if ($object_attendees == "")
        return [];

      $_attendees = unserialize($object_attendees);
      $this->_attendees = [];
      $newAttendees = [];
      if (is_array($_attendees) && count($_attendees) > 0) {
        $Attendee = $this->__getNamespace() . '\\Attendee';
        foreach ($_attendees as $key => $_attendee) {
          $attendee = new $Attendee($this);
          $attendee->setEmail($key);
          $attendee->define($_attendee);

          // MANTIS 0006191: Mode en attente lorsque le participant est une liste
          if ($this->getMapOrganizer()->owner_uid != $this->user->uid 
              && $attendee->is_list) {
            $this->attendeeIsList($attendee, $newAttendees, $Attendee);
          }

          $this->_attendees[] = $attendee;
        }
        // Ajouter les nouveaux participants
        $this->_attendees = $this->mergeAttendees($this->_attendees, $newAttendees);
      }
    }
    return $this->_attendees;
  }

  /**
   * Traiter la liste des participants pour sortir l'utilisateur courant s'il est présent
   * 
   * @param Attendee $attendee
   * @param Attendee[] $attendees [In/Out]
   * @param string $Attendee
   */
  protected function attendeeIsList($attendee, &$attendees, $Attendee) {
    $members = $attendee->members;
    if (is_array($members)) {
      foreach ($members as $member) {
        // L'utilisateur existe bien dans l'annuaire
        $listAttendee = new $Attendee();
        $listAttendee->email = $member;

        if ($listAttendee->is_list) {
          $this->attendeeIsList($listAttendee, $attendees, $Attendee);
        }
        else if ($listAttendee->uid == $this->user->uid) {
          $listAttendee->response = Attendee::RESPONSE_NEED_ACTION;
          $listAttendee->role = $attendee->role;

          $attendees[] = $listAttendee;
        }
      }
    }
  }

  /**
   * Merge les nouveaux attendees avec les attendees existants
   * Ne prend pas en compte les participants déjà présents
   * 
   * @param Attendee[] $attendees
   * @param Attendee[] $newAttendees
   * 
   * @return Attendee[]
   */
  protected function mergeAttendees($attendees, $newAttendees) {
    foreach ($newAttendees as $newAttendee) {
      // L'ajouter s'il n'est pas déjà présent
      $found = false;
      foreach ($attendees as $attendee) {
        if (strtolower($newAttendee->email) == strtolower($attendee->email)) {
          $found = true;
          break;
        }
      }
      // Si pas trouvé on l'ajoute
      if (!$found) {
        $attendees[] = $newAttendee;
      }
    }
    return $attendees;
  }

  /**
   * Mapping hasattendees field
   * @return boolean
   */
  protected function getMapHasAttendees() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapHasAttendees() : " . ((isset($this->objectmelanie->attendees) && $this->objectmelanie->attendees != "" && $this->objectmelanie->attendees != "a:0:{}") ? "true" : "false"));
    return (isset($this->objectmelanie->attendees) && $this->objectmelanie->attendees != "" && $this->objectmelanie->attendees != "a:0:{}") 
        || (isset($this->objectmelanie->organizer_attendees) && $this->objectmelanie->organizer_attendees != "" && $this->objectmelanie->organizer_attendees != "a:0:{}");
  }
  /**
   * Mapping real uid field
   */
  protected function getMapRealUid() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapRealUid()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    return $this->objectmelanie->uid;
  }
  
  /**
   * Mapping deleted field
   * 
   * @param bool $deleted          
   */
  protected function setMapDeleted($deleted) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapDeleted($deleted)");
    $this->deleted = $deleted;
  }
  /**
   * Mapping deleted field
   */
  protected function getMapDeleted() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapDeleted()");
    $deleted = $this->deleted;
    if (!isset($this->start) || $this->start == '1970-01-01 00:00:00') {
      $deleted = $deleted || isset($this->objectmelanie->exceptions) && strlen($this->objectmelanie->exceptions) > 16;
    }
    return $deleted;
  }
  
  /**
   * Mapping exceptions field
   * 
   * @param Exception[] $exceptions          
   * @ignore
   *
   */
  protected function setMapExceptions($exceptions) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapExceptions()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    
    $_exceptions = [];
    if (!is_array($exceptions)) {
      $exceptions = [
          $exceptions
      ];
    }
    // Rechercher les exceptions à supprimer au moment du save
    if (isset($this->exceptions) && is_array($this->exceptions) && count($this->exceptions) > 0) {
      $this->deleted_exceptions = [];
      foreach ($this->exceptions as $_exception) {
        $date = new \DateTime($_exception->recurrence_id);
        $_recId = $date->format("Ymd");
        $deleteEx = true;
        foreach ($exceptions as $exception) {
          $date = new \DateTime($exception->recurrence_id);
          $recId = $date->format("Ymd");
          if ($_recId == $recId && (!$exception->deleted || $_exception->deleted)) {
            $deleteEx = false;
            break;
          }
        }
        if ($deleteEx) {
          $this->deleted_exceptions[] = $_exception;
        }
      }
      M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapExceptions() deleted_exceptions : " . count($this->deleted_exceptions));
      // M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapExceptions() old exceptions : " . count($this->exceptions));
      // M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapExceptions() new exceptions : " . count($exceptions));
    }
    $this->exceptions = [];
    foreach ($exceptions as $exception) {
      $date = new \DateTime($exception->recurrence_id, new \DateTimeZone('GMT'));
      $recId = $date->format("Ymd");
      if (!in_array($recId, $_exceptions)) {
        $_exceptions[] = $recId;
      }
      $this->exceptions[$recId] = $exception;
    }
    
    if (count($_exceptions) > 0)
      $this->objectmelanie->exceptions = trim(implode(',', $_exceptions), " ,");
    else
      $this->objectmelanie->exceptions = '';
  }
  /**
   * Mapping exceptions field
   * 
   * @return Exception[] $exceptions
   * @ignore
   *
   */
  protected function getMapExceptions() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapExceptions()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if (!isset($this->objectmelanie->exceptions) || $this->objectmelanie->exceptions == "")
      return [];
    
    if (!isset($this->exceptions)) {
      $this->exceptions = [];
    }
    $exceptions = explode(',', $this->objectmelanie->exceptions);
    if (count($exceptions) != count($this->exceptions)) {
      $Exception = $this->__getNamespace() . '\\Exception';
      $dateStart = new \DateTime($this->objectmelanie->start);
      foreach ($exceptions as $exception) {
        // MANTIS 3881: Rendre la librairie moins sensible au format de données pour les exceptions
        if (strtotime($exception) === false)
          continue;
        $dateEx = new \DateTime($exception);
        if (!isset($this->exceptions[$dateEx->format("Ymd")])) {
          $ex = new $Exception($this);
          $ex->recurrence_id = $dateEx->format("Y-m-d") . ' ' . $dateStart->format("H:i:s");
          $ex->uid = $this->objectmelanie->uid;
          $ex->calendar = $this->objectmelanie->calendar;
          $ex->load(true);
          $this->exceptions[$dateEx->format("Ymd")] = $ex;
        }
      }
    }
    return $this->exceptions;
  }
  /**
   * Ajoute une nouvelle exception à la liste sans avoir à recharger toutes les exceptions
   * 
   * @param Exception $exception          
   * @throws Exceptions\ObjectMelanieUndefinedException
   */
  public function addException($exception) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->addException()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    
    if (!isset($this->exceptions) && !is_array($this->exceptions)) {
      $this->exceptions = [];
    }
    $timezone = $this->getMapTimezone();
    if (!isset($timezone)) {
      $timezone = $exception->timezone;
    }
    // Définition Timezone de l'utilisateur
    $user_timezone = new \DateTimeZone(!empty($timezone) ? $timezone : date_default_timezone_get());
    // Récupère les dates des exceptions
    $exceptions_dates = explode(',', $this->objectmelanie->exceptions);
    // Gestion de l'exception
    $date = new \DateTime($exception->recurrence_id, new \DateTimeZone('GMT'));
    $date->setTimezone($user_timezone);
    $recId = $date->format("Ymd");
    $this->exceptions[$recId] = $exception;
    // Ajoute l'exception à la liste des dates si elle n'est pas présente
    if (!in_array($recId, $exceptions_dates)) {
      $exceptions_dates[] = $recId;
      $this->objectmelanie->exceptions = trim(implode(',', $exceptions_dates), " ,");
    }
  }
  
  /**
   * Mapping attachments field
   * 
   * @param Attachments[] $exceptions          
   * @ignore
   *
   */
  protected function setMapAttachments($attachments) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapAttachments()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    $this->attachments = $attachments;

    // Enregister les pièces jointes en v2
    $_attachments = [];

    foreach ($attachments as $attachment) {
      $_attachment = new stdClass();
      $_attachment->type = $attachment->type;

      if ($attachment->type == Attachment::TYPE_URL) {
        // pieces jointes de type url
        $_attachment->url = $attachment->url;              
      }
      else {
        // pieces jointes de type binaire
        foreach (['name', 'path', 'modified', 'owner', 'hash', 'size', 'contenttype'] as $field) {
          $_attachment->$field = $attachment->$field;
        }
        $_attachment->storage = 'horde_vfs';
      }
      $_attachments[] = $_attachment;
    }
    $this->objectmelanie->attachments = json_encode((array)$_attachments);
  }
  /**
   * Mapping attachments field
   * 
   * @return Attachments[] $exceptions
   * @ignore
   *
   */
  protected function getMapAttachments() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapAttachments()");
    if (!isset($this->objectmelanie)) throw new Exceptions\ObjectMelanieUndefinedException();
    if (!isset($this->attachments)) {
      $this->attachments = [];
      // Gérer le cas où l'event est loadé mais n'existe pas dans la base
      if (strpos($this->get_class, '\Exception') === false && !$this->objectmelanie->getIsExist()) {
        return $this->attachments;
      }
      $Attachment = $this->__getNamespace() . '\\Attachment';
      // MANTIS 0006920: Utiliser le champs event_attachments_json pour stocker les informations sur les pieces jointes
      if ($this->version >= 2) {
        $_attachments = json_decode($this->objectmelanie->attachments);
        if (isset($_attachments)) {
          foreach ($_attachments as $_attachment) {
            $attachment = new $Attachment();
            
            $attachment->type = $_attachment->type;
            if ($_attachment->type == Attachment::TYPE_URL) {
              // pieces jointes de type url
              $attachment->url = $_attachment->url;              
            }
            else {
              // pieces jointes de type binaire
              foreach (['name', 'path', 'modified', 'owner', 'hash', 'size', 'contenttype'] as $field) {
                $attachment->$field = $_attachment->$field;
              }
            }
            $this->attachments[] = $attachment;
          }
        }
      }
      else {
        // Récupération des pièces jointes binaires
        $attachment = new $Attachment();
        $path = Config::get(Config::ATTACHMENTS_PATH);
        $calendar = $this->getMapOrganizer()->calendar;
        if (!isset($calendar))
          $calendar = $this->objectmelanie->calendar;
        $path = str_replace('%c', $calendar, $path);
        // Pour les exceptions lister les pièces jointes de l'exception et de la récurrence maitre
        if (strpos($this->get_class, '\Exception') !== false) {
          $path_ex = str_replace('%e', $this->objectmelanie->uid, $path);
          $path_rec = str_replace('%e', $this->objectmelanie->realuid, $path);
          $path = [$path_ex, $path_rec];
        }
        else {
          $path = str_replace('%e', $this->objectmelanie->uid, $path);
        }
        $attachment->path = $path;
        // MANTIS 0004689: Mauvaise optimisation du chargement des pièces jointes
        $fields = ["id", "type", "path", "name", "modified", "owner"];
        $this->attachments = $attachment->getList($fields);
        
        // Récupération des pièces jointes URL
        $attach_uri = $this->getAttribute('ATTACH-URI');
        if (isset($attach_uri)) {
          foreach (explode('%%URI-SEPARATOR%%', $attach_uri) as $uri) {
            if (isset($uri) && $uri !== "") {
              M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapAttachments(): $uri");
              $attachment = new $Attachment();
              $attachment->url = $uri;
              $attachment->type = Attachment::TYPE_URL;
              $this->attachments[] = $attachment;
            }
          }
        }
      }
    }
    return $this->attachments;
  }
  /**
   * Map ics to current event
   * 
   * @ignore
   *
   */
  protected function setMapIcs($ics) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapsIcs()");
    \LibMelanie\Lib\ICSToEvent::Convert($ics, $this, $this->calendarmce, $this->user, $this->ics_attachments);
  }
  /**
   * Map current event to ics
   * 
   * @return string $ics
   * @ignore
   *
   */
  protected function getMapIcs() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapIcs()");
    return \LibMelanie\Lib\EventToICS::Convert($this, $this->calendarmce, $this->user, null, $this->ics_attachments, $this->ics_freebusy);
  }
  /**
   * Map current event to vcalendar
   * 
   * @return VObject\Component\VCalendar $vcalendar
   * @ignore
   *
   */
  protected function getMapVcalendar() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapVcalendar()");
    return \LibMelanie\Lib\EventToICS::getVCalendar($this, $this->calendarmce, $this->user, $this->ics_attachments, $this->ics_freebusy, $this->vcalendar);
  }
  /**
   * Set current vcalendar for event
   * 
   * @param VObject\Component\VCalendar $vcalendar          
   * @ignore
   *
   */
  protected function setMapVcalendar($vcalendar) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapVcalendar()");
    $this->vcalendar = $vcalendar;
  }
  /**
   * Map move param
   * 
   * @ignore
   *
   */
  protected function setMapMove($move) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapMove()");
    $this->move = $move;
  }
  /**
   * Map move param
   * 
   * @return string $move
   * @ignore
   *
   */
  protected function getMapMove() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapMove()");
    return $this->move;
  }
}