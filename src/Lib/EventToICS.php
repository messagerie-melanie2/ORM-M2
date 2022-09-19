<?php

/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 *
 * ORM M2 Copyright © 2017  PNE Annuaire et Messagerie/MEDDE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace LibMelanie\Lib;

use LibMelanie\Api\Defaut\Attachment;
use LibMelanie\Api\Defaut\Recurrence;
use LibMelanie\Api\Defaut\User;
use LibMelanie\Api\Defaut\Event;
use LibMelanie\Api\Defaut\Attendee;
use LibMelanie\Api\Defaut\Calendar;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\Config;

// Utilisation de la librairie Sabre VObject pour la conversion ICS
@include_once 'vendor/autoload.php';
use Sabre\VObject;

/**
 * Class de génération de l'ICS en fonction de l'objet évènement
 * Méthodes Statiques
 *
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage Lib
 *            
 */
class EventToICS {
  /**
   * Identifiant de l'outil utilisant l'ICS (pour la génération)
   *
   * @var string
   */
  const PRODID = '-//Groupe Messagerie MTES/ORM LibMCE';
  /**
   * Version ICalendar utilisé pour la génération de l'ICS
   *
   * @var string
   */
  const VERSION = '2.0';

  /**
   * Variable configurable depuis l'extérieur pour définir
   * si les pièces jointes sont proposées via une URL
   * ou directement en binaire (encodage base64)
   *
   * @var bool
   */
  public static $USE_ATTACH_URL = true;

  /**
   * Constructeur privé pour ne pas instancier la classe
   */
  private function __construct() {
  }

  /**
   * Génére un ICS en fonction de l'évènement passé en paramètre
   * L'évènement doit être de type Event de la librairie LibM2
   * Gère également les exceptions, peut donc retourner plusieurs composant VEVENT
   *
   * @param Event $event
   * @param Calendar $calendar
   * @param User $user
   * @param VObject\Component\VCalendar $vcalendar
   * @param boolean $useattachments
   *          Si l'ics doit inclure les pièces jointes
   * @param boolean $isfreebusy
   *          Si on ne retourne que les freebusy (pas de pièce jointe ou de participants)
   * 
   * @return string $ics
   */
  public static function Convert($event, $calendar = null, $user = null, VObject\Component\VCalendar $vcalendar = null, $useattachments = true, $isfreebusy = false) {
    if (!isset($vcalendar)) {
      $vcalendar = self::getVCalendar($event, $calendar, $user, $useattachments, $isfreebusy);
    }
    return $vcalendar->serialize();
  }

  /**
   * Génére un VObject\Component\VCalendar en fonction de l'évènement passé en paramètre
   * L'évènement doit être de type Event de la librairie LibM2
   * Gère également les exceptions, peut donc retourner plusieurs composant VEVENT
   *
   * @param Event $event
   * @param Calendar $calendar
   * @param User $user
   * @param boolean $useattachments
   *          Si l'ics doit inclure les pièces jointes
   * @param boolean $isfreebusy
   *          Si on ne retourne que les freebusy (pas de pièce jointe ou de participants)
   * 
   * @return VObject\Component\VCalendar $vcalendar
   */
  public static function getVCalendar($event, $calendar = null, $user = null, $useattachments = true, $isfreebusy = false, $vcalendar = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "EventToICS->getVCalendar()");
    if (!isset($vcalendar)) {
      $vcalendar = new VObject\Component\VCalendar();
    }
    $vevent = $vcalendar->add('VEVENT');
    // PRODID et Version
    $vcalendar->PRODID = self::PRODID;
    $vcalendar->VERSION = self::VERSION;
    // Configuration pour l'utilisation des URLs pour les pièces jointes
    // Se fait en fonction des informations fournies par le client
    self::$USE_ATTACH_URL = !isset($_SERVER["HTTP_X_MOZ_ATTACHMENTS"]) || $_SERVER["HTTP_X_MOZ_ATTACHMENTS"] != 1;
    // UID
    $vevent->UID = $event->uid;
    if (!$event->deleted) {
      $vevent = self::getVeventFromEvent($vevent, $event, $calendar, $user, $useattachments, $isfreebusy);
      // Type récurrence
      if ($event->recurrence->type && $event->recurrence->type !== Recurrence::RECURTYPE_NORECUR) {
        $rrule = $event->recurrence->rrule;
        $params = [];
        if (isset($rrule) && is_array($rrule) && count($rrule) > 0) {
          foreach ($rrule as $key => $value) {
            if (!is_string($value)) {
              if ($value instanceof \DateTime) {
                $value = $value->format('Ymd') . 'T' . $value->format('His') . 'Z';
              }
            }
            if ($key == ICS::INTERVAL && $value == 1) {
              // On n'affiche pas l'interval 1 dans l'ICS
              continue;
            }
            else if ($key == ICS::RDATE) {
              // Gérer le RDATE enregistré dans la récurrence
              if (is_array($value)) {
                foreach ($value as $val) {
                  // Ajout du RDATE
                  $vevent->add(ICS::RDATE, $val);
                }
              }
              else {
                // Ajout du RDATE
                $vevent->add(ICS::RDATE, $value);
              }
              // Le RDATE ne va pas dans le RRULE
              continue;
            }
            else if (is_array($value)) {
              $value = implode(',', $value);
            }
            $params[] = "$key=$value";
          }
          // Construction de la récurrence
          $vevent->add(ICS::RRULE, implode(';', $params));
        }
      }
    }
    // Exceptions
    if (is_array($event->exceptions) && count($event->exceptions) > 0) {
      $exdate = [];
      $first = true;
      foreach ($event->exceptions as $exception) {
        $exRecId = $exception->recurrence_id;
        $allDay = $exception->all_day;
        if ($allDay) {
          $exdatetime = new \DateTime($exRecId, new \DateTimeZone('UTC'));
        }
        else {
          $exdatetime = new \DateTime($exRecId, new \DateTimeZone($exception->timezone));
        }
        if ($event->deleted) {
          if ($first) {
            $first = false;
            $rdate_d = clone $exdatetime;
            $rdate_d->setTimezone(new \DateTimeZone('UTC'));
            if ($exception->all_day) {
              $date = $rdate_d->format('Ymd');
              $vevent->add(ICS::RDATE, $date, [
                  ICS::VALUE => ICS::VALUE_DATE
              ]);
              $vevent->add(ICS::DTSTART, $rdate_d->format('Ymd'), [
                  ICS::VALUE => ICS::VALUE_DATE
              ]);
            } else {
              $date = $rdate_d->format('Ymd') . 'T' . $rdate_d->format('His') . 'Z';
              $vevent->add(ICS::RDATE, $date);
              $vevent->DTSTART = clone $exdatetime;
            }
            if (!isset($exception->modified)) {
              continue;
            }
            $dateTime = new \DateTime('@' . $exception->modified);
            $dateTime->setTimezone(new \DateTimeZone('UTC'));
            $date = $dateTime->format('Ymd') . 'T' . $dateTime->format('His') . 'Z';
            // Attributs sur l'alarme
            $x_moz_lastack = $event->getAttribute(ICS::X_MOZ_LASTACK);
            if (isset($x_moz_lastack))
              $vevent->{ICS::X_MOZ_LASTACK} = $x_moz_lastack;
            $x_moz_snooze_time = $event->getAttribute(ICS::X_MOZ_SNOOZE_TIME);
            if (isset($x_moz_snooze_time))
              $vevent->{ICS::X_MOZ_SNOOZE_TIME} = $x_moz_snooze_time;
            // X Moz Generation
            $moz_generation = $event->getAttribute(ICS::X_MOZ_GENERATION);
            if (isset($moz_generation))
              $vevent->add(ICS::X_MOZ_GENERATION, $moz_generation);
            // 0005238: [ICS] Enregistrer les attributs X-MOZ-SNOOZE-TIME-*
            $attributes = $event->getAttribute("X-MOZ-SNOOZE-TIME-CHILDREN");
            if (isset($attributes)) {
              $attributes = json_decode($attributes, true);
              foreach ($attributes as $name => $value) {
                $vevent->add($name, $value);
              }
            }
            // DTSTAMP
            $dtstamp = $event->getAttribute(ICS::DTSTAMP);
            if (isset($dtstamp))
              $vevent->add(ICS::DTSTAMP, $dtstamp);
            else
              $vevent->add(ICS::DTSTAMP, $date);
            // LAST-MODIFIED
            $last_modified = $event->getAttribute(ICS::LAST_MODIFIED);
            if (isset($last_modified))
              $vevent->add(ICS::LAST_MODIFIED, $last_modified);
            else
              $vevent->add(ICS::LAST_MODIFIED, $date);
            // CREATED
            $created = $event->getAttribute(ICS::CREATED);
            if (isset($created))
              $vevent->add(ICS::CREATED, $created);
            else
              $vevent->add(ICS::CREATED, $date);
            // $vevent->SUMMARY = $exception->title;
            // $vevent->add(ICS::X_MOZ_GENERATION, count($event->exceptions));
            $vevent->add(ICS::X_MOZ_FAKED_MASTER, "1");
          }
          if (!isset($vevent->DTSTART)) {
            continue;
          }
          if ($vevent->DTSTART[ICS::VALUE] == ICS::VALUE_DATE) {
            $date = $exdatetime->format('Ymd');
          } else {
            $date = $exdatetime->format('Ymd') . 'T' . $exdatetime->format('His');
          }
        } elseif ($vevent->DTSTART[ICS::VALUE] == ICS::VALUE_DATE) {
          $date = $exdatetime->format('Ymd');
        } else {
          if (!isset($vevent->DTSTART)) {
            continue;
          }
          $date = $exdatetime->format('Ymd') . 'T' . $exdatetime->format('His');
        }
        if ($exception->deleted && !$event->deleted) {
          $exdate[] = $date;
        } else if (isset($exception->start) && isset($exception->end)) {
          $vexception = $vcalendar->add('VEVENT');
          // UID
          $vexception->UID = $exception->uid;
          if (!$isfreebusy || !$event->deleted) {
            if ($vevent->DTSTART[ICS::VALUE] == ICS::VALUE_DATE) {
              $vexception->add(ICS::RECURRENCE_ID, $date, [
                  ICS::VALUE => ICS::VALUE_DATE
              ]);
            } else {
              $vexception->add(ICS::RECURRENCE_ID, $date, [
                  ICS::TZID => $exception->timezone
              ]);
            }
          }
          $vexception = self::getVeventFromEvent($vexception, $exception, $calendar, $user, $useattachments, $isfreebusy);
        }
      }
      // Gestion des EXDATE
      if (count($exdate) > 0) {
        foreach ($exdate as $date) {
          if ($vevent->DTSTART[ICS::VALUE] == ICS::VALUE_DATE) {
            $vevent->add(ICS::EXDATE, $date, [
                ICS::VALUE => ICS::VALUE_DATE
            ]);
          } else {
            $vevent->add(ICS::EXDATE, $date, [
                ICS::TZID => $event->timezone
            ]);
          }
        }
      }
    }
    return $vcalendar;
  }

  /**
   * Méthode permettant de générer l'objet VEVENT à partir des données de l'évènement
   * Cette méthode est séparée pour être appelé plusieurs fois, dans le cas où l'évènement a des exceptions
   *
   * @param VObject\Component $vevent
   * @param Event $event
   * @param Calendar $calendar
   * @param User $user
   * @param boolean $useattachments
   *          Si l'ics doit inclure les pièces jointes
   * @param boolean $isfreebusy
   *          Si on ne retourne que les freebusy (pas de pièce jointe ou de participants)
   * @return VObject\Component $vevent
   */
  private static function getVeventFromEvent(VObject\Component $vevent, $event, $calendar = null, $user = null, $useattachments = true, $isfreebusy = false) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "EventToICS->getVeventFromEvent()");
    // Class
    if (isset($event->class)) {
      switch ($event->class) {
        case Event::CLASS_CONFIDENTIAL :
          $vevent->CLASS = ICS::CLASS_CONFIDENTIAL;
          break;
        case Event::CLASS_PRIVATE :
          $vevent->CLASS = ICS::CLASS_PRIVATE;
          break;
        case Event::CLASS_PUBLIC :
        default :
          $vevent->CLASS = ICS::CLASS_PUBLIC;
          break;
      }
    } else
      $vevent->CLASS = ICS::CLASS_PUBLIC;

    // Status
    if (isset($event->status)) {
      switch ($event->status) {
        default :
        case Event::STATUS_CONFIRMED :
          $vevent->STATUS = ICS::STATUS_CONFIRMED;
          break;
        case Event::STATUS_NONE :
        case Event::STATUS_TELEWORK :
          break;
        case Event::STATUS_CANCELLED :
          $vevent->STATUS = ICS::STATUS_CANCELLED;
          break;
        case Event::STATUS_TENTATIVE :
          $vevent->STATUS = ICS::STATUS_TENTATIVE;
          break;
      }
    } else
      $vevent->STATUS = ICS::STATUS_CONFIRMED;

    // DTSTAMP
    if (isset($event->modified)) {
      $dateTime = new \DateTime('@' . $event->modified, new \DateTimeZone($event->timezone));
      $dateTime->setTimezone(new \DateTimeZone('UTC'));
      $date = $dateTime->format('Ymd\THis\Z');
      $vevent->add(ICS::DTSTAMP, $date);
      $vevent->add(ICS::LAST_MODIFIED, $date);
      if (isset($event->created)) {
        $dateTime = new \DateTime('@' . $event->created, new \DateTimeZone($event->timezone));
        $dateTime->setTimezone(new \DateTimeZone('UTC'));
        $dateCreated = $dateTime->format('Ymd\THis\Z');
        $vevent->add(ICS::CREATED, $dateCreated);
      }
      else {
        $created = $event->getAttribute(ICS::CREATED);
        if (isset($created))
          $vevent->add(ICS::CREATED, $created);
        else
          $vevent->add(ICS::CREATED, $date);
      }
    }

    // DateTime
    if (isset($event->start) && isset($event->end)) {
      if ($event->all_day) {
        // All day event
        $vevent->add(ICS::DTSTART, $event->dtstart->format('Ymd'), [
            ICS::VALUE => ICS::VALUE_DATE
        ]);
        $vevent->add(ICS::DTEND, $event->dtend->format('Ymd'), [
            ICS::VALUE => ICS::VALUE_DATE
        ]);
      } else {
        $vevent->DTSTART = $event->dtstart;
        $vevent->DTEND = $event->dtend;
      }
    } // MANTIS 0005074: [ICS] Si l'événement n'a pas de date générer une date standard
    else {
      $vevent->DTSTART = new \DateTime('1970-01-02 09:00:00');
      $vevent->DTEND = new \DateTime('1970-01-02 10:00:00');
    }

    // Problème de user uid vide
    $user_uid = $user->uid;
    // Test si l'événement est privé, confidentiel ou public
    if (($event->class == Event::CLASS_PRIVATE || $event->class == Event::CLASS_CONFIDENTIAL) && (($event->owner != $user_uid && isset($calendar) && $calendar->owner != $user_uid && strpos($event->owner, $user_uid . '.-.') !== 0 && !$calendar->asRight(Config::get(Config::PRIV))) || !isset($user_uid))) {
      $vevent->SUMMARY = 'Événement privé';
    } else if ($isfreebusy) {
      $vevent->SUMMARY = '[' . self::convertStatusToFR($event->status) . '] Événement';
    } else {
      // Titre
      if (isset($event->title) && $event->title != "") {
        $vevent->SUMMARY = self::cleanUTF8String($event->title);
      } else {
        $vevent->SUMMARY = 'Sans titre';
      }
      // Catégories
      if (isset($event->category) && $event->category != "") {
        $categories = explode(',', $event->category);
        foreach ($categories as $category) {
          $vevent->add(ICS::CATEGORIES, $category);
        }
      }
      // Description
      if (isset($event->description) && $event->description != "") {
        $vevent->DESCRIPTION = self::cleanUTF8String($event->description);
      }
      // Location
      if (isset($event->location) && $event->location != "") {
        $vevent->LOCATION = self::cleanUTF8String($event->location);
      }
      // Alarm
      if (isset($event->alarm) && $event->alarm != 0) {
        $valarm = $vevent->add('VALARM');
        $valarm->TRIGGER = self::formatAlarm($event->alarm);
        $valarm->ACTION = ICS::ACTION_DISPLAY;
      }
      // Attributs sur l'alarme
      $x_moz_lastack = $event->getAttribute(ICS::X_MOZ_LASTACK);
      if (isset($x_moz_lastack))
        $vevent->{ICS::X_MOZ_LASTACK} = $x_moz_lastack;
      $x_moz_snooze_time = $event->getAttribute(ICS::X_MOZ_SNOOZE_TIME);
      if (isset($x_moz_snooze_time))
        $vevent->{ICS::X_MOZ_SNOOZE_TIME} = $x_moz_snooze_time;
      // 0005238: [ICS] Enregistrer les attributs X-MOZ-SNOOZE-TIME-*
      $attributes = $event->getAttribute("X-MOZ-SNOOZE-TIME-CHILDREN");
      if (isset($attributes)) {
        $attributes = json_decode($attributes, true);
        foreach ($attributes as $name => $value) {
          $vevent->add($name, $value);
        }
      }
      // Récupérer l'organisateur
      $organizer_email = trim($event->organizer->email);
      // Traitement participants
      $organizer_attendees = $event->attendees;
      if (!is_null($organizer_attendees) && is_array($organizer_attendees) && count($organizer_attendees) > 0 && isset($organizer_email) && !empty($organizer_email)) {
        // Add organizer
        $params = [];
        $org_role = $event->organizer->role;
        if (isset($org_role)) {
          $params[ICS::ROLE] = $org_role;
        }
        $org_partstat = $event->organizer->partstat;
        if (isset($org_partstat)) {
          $params[ICS::PARTSTAT] = $org_partstat;
        }
        $org_rsvp = $event->organizer->rsvp;
        if (isset($org_rsvp)) {
          $params[ICS::RSVP] = $org_rsvp;
        }
        $org_name = $event->organizer->name;
        if (isset($org_name)) {
          $params[ICS::CN] = self::cleanUTF8String($org_name);
        }
        $org_sent_by = $event->organizer->sent_by;
        if (isset($org_sent_by)) {
          $params[ICS::SENT_BY] = $org_sent_by;
        }
        $org_owner_email = $event->organizer->owner_email;
        if (isset($org_owner_email)) {
          $params[ICS::X_M2_ORG_MAIL] = 'mailto:' . $org_owner_email;
        }
        $vevent->add(ICS::ORGANIZER, 'mailto:' . $organizer_email, $params);
        foreach ($organizer_attendees as $attendee) {
          // RSVP
          $rsvp = 'TRUE';
          // Parstat
          switch ($attendee->response) {
            case Attendee::RESPONSE_ACCEPTED :
              $partstat = ICS::PARTSTAT_ACCEPTED;
              break;
            case Attendee::RESPONSE_DECLINED :
              $partstat = ICS::PARTSTAT_DECLINED;
              break;
            case Attendee::RESPONSE_IN_PROCESS :
              $partstat = ICS::PARTSTAT_IN_PROCESS;
              break;
            default :
            case Attendee::RESPONSE_NEED_ACTION :
              $partstat = ICS::PARTSTAT_NEEDS_ACTION;
              break;
            case Attendee::RESPONSE_TENTATIVE :
              $partstat = ICS::PARTSTAT_TENTATIVE;
              break;
          }
          // Role
          switch ($attendee->role) {
            case Attendee::ROLE_CHAIR :
              $role = ICS::ROLE_CHAIR;
              break;
            default :
            case Attendee::ROLE_REQ_PARTICIPANT :
              $role = ICS::ROLE_REQ_PARTICIPANT;
              break;
            case Attendee::ROLE_OPT_PARTICIPANT :
              $role = ICS::ROLE_OPT_PARTICIPANT;
              break;
            case Attendee::ROLE_NON_PARTICIPANT :
              $role = ICS::ROLE_NON_PARTICIPANT;
              $partstat = ICS::PARTSTAT_ACCEPTED;
              $rsvp = 'FALSE';
              break;
          }
          $params = [
              ICS::PARTSTAT => $partstat,
              ICS::ROLE => $role,
              ICS::RSVP => $rsvp
          ];
          $attendee_name = $attendee->name;
          if (!empty($attendee_name)) {
            $params[ICS::CN] = self::cleanUTF8String($attendee_name);
          }
          // Type
          if (isset($attendee->type) && $attendee->type != Attendee::TYPE_INDIVIDUAL) {
            // Individual est le type par défaut, pas besoin de le définir
            switch ($attendee->type) {
              case Attendee::TYPE_GROUP:
                $params[ICS::CUTYPE] = ICS::CUTYPE_GROUP;
                break;
              case Attendee::TYPE_RESOURCE:
                $params[ICS::CUTYPE] = ICS::CUTYPE_RESOURCE;
                break;
              case Attendee::TYPE_ROOM:
                $params[ICS::CUTYPE] = ICS::CUTYPE_ROOM;
                break;
              case Attendee::TYPE_UNKNOWN:
                $params[ICS::CUTYPE] = ICS::CUTYPE_UNKNOWN;
                break;
            }
          }
          // 0006294: Ajouter l'information dans un participant quand il a été enregistré en attente
          if (is_bool($attendee->is_saved)) {
            $params[ICS::X_MEL_EVENT_SAVED] = $attendee->is_saved;
          }
          // Add attendee
          $vevent->add(ICS::ATTENDEE, 'mailto:' . $attendee->email, $params);
        }
      }
      // Calendar infos
      if (isset($calendar)) {
        // Ne plus utiliser ces informations qui ne devraient pas être nécessaire
        $vevent->add(ICS::X_CALDAV_CALENDAR_ID, $calendar->id);
        $vevent->add(ICS::X_CALDAV_CALENDAR_OWNER, $calendar->owner);
        // MANTIS 4002: Ajouter le creator dans la description lors de la génération de l'ICS
        if ($event->owner != $calendar->owner && !empty($event->owner) && strpos($vevent->DESCRIPTION, "[" . $event->owner . "]") === false) {
          $vevent->DESCRIPTION = "[" . $event->owner . "]\n\n" . $vevent->DESCRIPTION;
        }
      }
      // Sequence
      $sequence = $event->sequence;
      if (isset($sequence)) {
        $vevent->SEQUENCE = $sequence;
      }
      else {
        $sequence = $event->getAttribute(ICS::SEQUENCE);
        if (isset($sequence))
          $vevent->SEQUENCE = $sequence;
      }
      // RECEIVED-SEQUENCE
      $received_sequence = $event->getAttribute(ICS::X_MOZ_RECEIVED_SEQUENCE);
      if (isset($received_sequence))
        $vevent->add(ICS::X_MOZ_RECEIVED_SEQUENCE, $received_sequence);
      // RECEIVED-DTSTAMP
      $received_dtstamp = $event->getAttribute(ICS::X_MOZ_RECEIVED_DTSTAMP);
      if (isset($received_dtstamp))
        $vevent->add(ICS::X_MOZ_RECEIVED_DTSTAMP, $received_dtstamp);
      // X Moz Send Invitations
      $send_invitation = $event->getAttribute(ICS::X_MOZ_SEND_INVITATIONS);
      if (isset($send_invitation))
        $vevent->add(ICS::X_MOZ_SEND_INVITATIONS, $send_invitation);
      // X Moz Send Invitations Undisclosed
      $send_invitation_undisclosed = $event->getAttribute(ICS::X_MOZ_SEND_INVITATIONS_UNDISCLOSED);
      if (isset($send_invitation_undisclosed))
        $vevent->add(ICS::X_MOZ_SEND_INVITATIONS_UNDISCLOSED, $send_invitation_undisclosed);
      // X Moz Generation
      $moz_generation = $event->getAttribute(ICS::X_MOZ_GENERATION);
      if (isset($moz_generation))
        $vevent->add(ICS::X_MOZ_GENERATION, $moz_generation);
      // Transp
      $transp = $event->transparency;
      if (isset($transp))
        $vevent->add(ICS::TRANSP, $transp);
      // Gestion des pièces jointes
      if ($useattachments) {
        $attachments = $event->attachments;
        if (isset($attachments) && is_array($attachments) && count($attachments) > 0) {
          foreach ($attachments as $attachment) {
            $params = [];
            if ($attachment->type == Attachment::TYPE_URL) {
              // Pièce jointe URL
              $data = $attachment->url;
            } else {
              // Pièce jointe binaire
              if (self::$USE_ATTACH_URL) {
                // URL de téléchargement
                $data = $attachment->url;
                $params[ICS::X_CM2V3_SEND_ATTACH_INVITATION] = 'TRUE';
                $params[ICS::X_CM2V3_ATTACH_HASH] = $attachment->hash;
                $params[ICS::FMTTYPE] = $attachment->contenttype;
              } else {
                // Envoie du binaire directement
                $data = $attachment->data;
                $params[ICS::ENCODING] = ICS::ENCODING_BASE64;
                $params[ICS::VALUE] = ICS::VALUE_BINARY;
                $params[ICS::FMTTYPE] = $attachment->contenttype;
              }
              $params[ICS::X_MOZILLA_CALDAV_ATTACHMENT_NAME] = $attachment->name;
              $params[ICS::SIZE] = $attachment->size;
            }
            // Add attachment
            $vevent->add(ICS::ATTACH, $data, $params);
          }
        }
      }
    }
    return $vevent;
  }

  /**
   * Ajoute le timezone au VCalendar
   *
   * @param VObject\Component $vcalendar
   * @param string $timezone
   */
  private static function generationTimezone(VObject\Component $vcalendar, $timezone) {
    if (!Config::get(Config::ICS_ADD_TIMEZONE))
      return;

    if ($timezone === 'Europe/Paris') {
      $vtimezone = $vcalendar->add('VTIMEZONE');
      $vtimezone->TZID = 'Europe/Paris';
      $vtimezone->add(ICS::X_LIC_LOCATION, 'Europe/Paris');
      $daylight = $vtimezone->add('DAYLIGHT');
      $daylight->TZOFFSETFROM = '+0100';
      $daylight->TZOFFSETTO = '+0200';
      $daylight->TZNAME = 'CEST';
      $daylight->DTSTART = '19700329T020000';
      $daylight->RRULE = 'FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3';
      $standard = $vtimezone->add('STANDARD');
      $standard->TZOFFSETFROM = '+0200';
      $standard->TZOFFSETTO = '+0100';
      $standard->TZNAME = 'CET';
      $standard->DTSTART = '19701025T030000';
      $standard->RRULE = 'FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10';
    }
  }

  /**
   * Formatte l'alarme en minutes en un trigger ICS
   *
   * @param int $alarm
   *          En minutes
   * @return string
   */
  private static function formatAlarm($alarm) {
    if ($alarm < 0) {
      $trigger = "P";
      $alarm = -$alarm;
    } else {
      $trigger = "-P";
    }

    // Nombre de semaines, 10080 minutes
    if ($alarm >= 10080 && ($alarm % 10080) === 0) {
      $nb_weeks = (int)($alarm / 10080);
      $alarm -= $nb_weeks * 10080;
      $trigger .= $nb_weeks . "W";
    }
    // Nombre de jours, 1440 minutes
    if ($alarm >= 1440) {
      $nb_days = (int)($alarm / 1440);
      $alarm -= $nb_days * 1440;
      $trigger .= $nb_days . "D";
    }
    if ($alarm > 0) {
      $trigger .= "T";
    }
    // Nombre d'heures, 60 minutes
    if ($alarm >= 60) {
      $nb_hours = (int)($alarm / 60);
      $alarm -= $nb_hours * 60;
      $trigger .= $nb_hours . "H";
    }
    // Nombre de minutes
    if ($alarm > 0) {
      $trigger .= $alarm . "M";
    }
    return $trigger;
  }

  /**
   * Nettoyage UTF8 des données
   * Utilisé pour éviter le bloquage des synchros par Lightning
   *
   * @param string $string
   * @return mixed
   */
  private static function cleanUTF8String($string) {
    return preg_replace('/[\x01\x02\x03\x04\x05\x06\x07\x08\x0B\x0E\x11\x12\x13\x14\x17\x19\x1A\x1E\x1C\x1B\x1D\x1F]/', '', $string);
  }

  /**
   * Retourne la traduction francaise du status ICS
   *
   * @param string $status
   * @return string
   */
  private static function convertStatusToFR($status) {
    $convert = array(
        'confirmed' => 'Confirmé',
        'tentative' => 'Provisoire',
        'cancelled' => 'Annulé',
        'default'   => 'Libre'
    );
    if (isset($convert[$status])) {
      return $convert[$status];
    } else {
      return $convert['default'];
    }
  }
}