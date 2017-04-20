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
namespace LibMelanie\Lib;

use LibMelanie\Config\ConfigMelanie;
use LibMelanie\Api\Melanie2\Exception;
use LibMelanie\Api\Melanie2\Event;
use LibMelanie\Log\M2Log;
use LibMelanie\Api\Melanie2\User;
use LibMelanie\Api\Melanie2\Calendar;

// Utilisation de la librairie Sabre VObject pour la conversion ICS
require_once 'vendor/autoload.php';
use Sabre\VObject;

/**
 * Class de génération de l'évènement en fonction de l'ICS
 * Méthodes Statiques
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Lib Mélanie2
 */
class ICSToEvent {
  /**
   * Identifiant de l'outil utilisant l'ICS (pour la génération)
   * 
   * @var string
   */
  const PRODID = '-//ORM LibMelanie2 PHP/PNE Messagerie/MEDDE';
  /**
   * Version ICalendar utilisé pour la génération de l'ICS
   * 
   * @var string
   */
  const VERSION = '2.0';
  /**
   * Format de datetime pour la base de données
   * 
   * @var string
   */
  const DB_DATE_FORMAT = 'Y-m-d H:i:s';
  /**
   * Format court de datetime pour la base de données
   * 
   * @var string
   */
  const SHORT_DB_DATE_FORMAT = 'Y-m-d';
  /**
   * Liste des extensions interdites pour les pièces jointes
   * 
   * @var string
   */
  const FORBIDDEN_ATTACH_EXT = "/(.*\.tst|.*\.reg|.*\.cmd|.*\.bat|.*\.exe|.*\.com|.*\.bas|.*\.chm|.*\.cpl|.*\.crt|.*\.hta|.*\.isp|.*\.js|.*\.jse|.*\.msc|.*\.msi|.*\.msp|.*\.mst|.*\.pcd|.*\.pif|.*\.scr|.*\.sct|.*\.shb|.*\.shs|.*\.vb|.*\.vbe|.*\.vbs|.*\.wsc|.*\.wsf|.*\.wsh|.*\.lnk|.*\.\{.*|.*\.nws|.*\.rar|.*\.pamelatest)([^0-9a-zA-Z-_]|$)/";
  
  /**
   * Constructeur privé pour ne pas instancier la classe
   */
  private function __construct() {
  }
  
  /**
   * Génére un évènement mélanie2 en fonction de l'ics passé en paramètre
   * L'évènement doit être de type Event de la librairie LibM2
   * Gère également les exceptions dans l'évènement en fonction des RECURRENCE-ID
   * 
   * @param string $ics          
   * @param Event $event          
   * @return Event
   */
  public static function Convert($ics, Event $event, Calendar $calendar = null, User $user = null) {
    $vcalendar = VObject\Reader::read($ics);
    // Gestion du timezone
    if (isset($calendar)) {
      $timezone = $calendar->getTimezone();
    } else {
      $timezone = ConfigMelanie::CALENDAR_DEFAULT_TIMEZONE;
    }
    $exceptions = [];
    foreach ($vcalendar->VEVENT as $vevent) {
      $recurrence_id = $vevent->{ICS::RECURRENCE_ID};
      if (isset($recurrence_id)) {
        $object = new Exception($event);
      } else {
        $object = $event;
      }
      // UID
      if (!isset($vevent->UID))
        continue;
      else
        $object->uid = $vevent->UID;
      // Owner
      if (isset($recurrence_id) && empty($object->owner)) {
        $object->owner = isset($user) && isset($user->uid) ? $user->uid : $event->owner;
      }
      // Recurrence ID
      if (isset($recurrence_id)) {
        $date = $recurrence_id->getDateTime();
        $date->setTimezone(new \DateTimeZone($timezone));
        $object->recurrenceId = $date->format(self::SHORT_DB_DATE_FORMAT);
      }
      // Cas du FAKED MASTER
      if (isset($vevent->{ICS::X_MOZ_FAKED_MASTER}) && intval($vevent->{ICS::X_MOZ_FAKED_MASTER}->getValue()) == 1) {
        $object->deleted = true;
        continue;
      }
      // Gestion du COPY/MOVE
      if (isset($vevent->{ICS::X_CM2V3_ACTION})) {
        $copy = strtolower($vevent->{ICS::X_CM2V3_ACTION}) == 'copy';
        $object->move = strtolower($vevent->{ICS::X_CM2V3_ACTION}) == 'move';
      } else {
        $copy = false;
        $object->move = false;
      }
      // SUMMARY
      if (isset($vevent->SUMMARY))
        $object->title = $vevent->SUMMARY->getValue();
      else
        $object->title = '';
      // DESCRIPTION
      if (isset($vevent->DESCRIPTION)) {
        $object->description = $vevent->DESCRIPTION->getValue();
        // MANTIS 4002: Ajouter le creator dans la description lors de la génération de l'ICS
        $object->description = preg_replace('/^(\[.+?)+(\])\\n\\n/i', "", $object->description, 1);
      } else
        $object->description = '';
      // LOCATION
      if (isset($vevent->LOCATION))
        $object->location = $vevent->LOCATION->getValue();
      else
        $object->location = '';
      // CATEGORY
      if (isset($vevent->CATEGORIES)) {
        $categories = [];
        foreach ($vevent->CATEGORIES as $category) {
          $categories[] = $category->getValue();
        }
        $object->category = implode(',', $categories);
      } else
        $object->category = '';
      // VALARM
      if (isset($vevent->VALARM)) {
        $alarmDate = $vevent->VALARM->getEffectiveTriggerTime();
        if (isset($vevent->DTSTART)) {
          $startDate = $vevent->DTSTART->getDateTime();
          $object->alarm = ($startDate->format("U") - $alarmDate->format("U")) / 60;
          if ($object->alarm === 0) {
            $object->alarm = 1;
          }
        }
        // X MOZ LASTACK
        if (isset($vevent->{ICS::X_MOZ_LASTACK})) {
          $object->setAttribute(ICS::X_MOZ_LASTACK, $vevent->{ICS::X_MOZ_LASTACK});
        } else {
          $object->deleteAttribute(ICS::X_MOZ_LASTACK);
        }
        // X MOZ SNOOZE TIME
        if (isset($vevent->{ICS::X_MOZ_SNOOZE_TIME})) {
          $object->setAttribute(ICS::X_MOZ_SNOOZE_TIME, $vevent->{ICS::X_MOZ_SNOOZE_TIME});
        } else {
          $object->deleteAttribute(ICS::X_MOZ_SNOOZE_TIME);
        }
      } else
        $object->alarm = 0;
      // SEQUENCE
      if (isset($vevent->SEQUENCE)) {
        $object->setAttribute(ICS::SEQUENCE, $vevent->SEQUENCE->getValue());
      } else {
        $object->deleteAttribute(ICS::SEQUENCE);
      }
      // X Moz Send Invitations
      if (isset($vevent->{ICS::X_MOZ_SEND_INVITATIONS})) {
        $object->setAttribute(ICS::X_MOZ_SEND_INVITATIONS, $vevent->{ICS::X_MOZ_SEND_INVITATIONS}->getValue());
      } else {
        $object->deleteAttribute(ICS::X_MOZ_SEND_INVITATIONS);
      }
      // X Moz Send Invitations Undisclosed
      if (isset($vevent->{ICS::X_MOZ_SEND_INVITATIONS_UNDISCLOSED})) {
        $object->setAttribute(ICS::X_MOZ_SEND_INVITATIONS_UNDISCLOSED, $vevent->{ICS::X_MOZ_SEND_INVITATIONS_UNDISCLOSED}->getValue());
      } else {
        $object->deleteAttribute(ICS::X_MOZ_SEND_INVITATIONS_UNDISCLOSED);
      }
      // X MOZ GENERATION
      if (isset($vevent->{ICS::X_MOZ_GENERATION})) {
        $object->setAttribute(ICS::X_MOZ_GENERATION, $vevent->{ICS::X_MOZ_GENERATION}->getValue());
      } else {
        $object->deleteAttribute(ICS::X_MOZ_GENERATION);
      }
      // TRANSP
      if (isset($vevent->TRANSP)) {
        $object->setAttribute(ICS::TRANSP, $vevent->TRANSP->getValue());
      } else {
        $object->deleteAttribute(ICS::TRANSP);
      }
      // DTSTAMP
      if (isset($vevent->DTSTAMP))
        $object->modified = strtotime($vevent->DTSTAMP->getValue());
      else if (isset($vevent->{ICS::LAST_MODIFIED}))
        $object->modified = strtotime($vevent->{ICS::LAST_MODIFIED}->getValue());
      else if (isset($vevent->CREATED))
        $object->modified = strtotime($vevent->CREATED->getValue());
      else
        $object->modified = time();
      
      // CREATED
      if (isset($vevent->CREATED)) {
        $object->setAttribute(ICS::CREATED, $vevent->CREATED->getValue());
      } else {
        $object->deleteAttribute(ICS::CREATED);
      }
      // DTSTART & DTEND
      if (isset($vevent->DTSTART) && isset($vevent->DTEND)) {
        $object->start = $vevent->DTSTART->getDateTime()->format(self::DB_DATE_FORMAT);
        ;
        $object->end = $vevent->DTEND->getDateTime()->format(self::DB_DATE_FORMAT);
      }
      // CLASS
      if (isset($vevent->CLASS)) {
        switch ($vevent->CLASS->getValue()) {
          case ICS::CLASS_PUBLIC :
          default :
            $object->class = Event::CLASS_PUBLIC;
            break;
          case ICS::CLASS_CONFIDENTIAL :
            $object->class = Event::CLASS_CONFIDENTIAL;
            break;
          case ICS::CLASS_PRIVATE :
            $object->class = Event::CLASS_PRIVATE;
            break;
        }
      } else
        $object->class = Event::CLASS_PUBLIC;
      // STATUS
      if (isset($vevent->STATUS)) {
        switch ($vevent->STATUS->getValue()) {
          default :
          case ICS::STATUS_CONFIRMED :
            $object->status = Event::STATUS_CONFIRMED;
            break;
          case ICS::STATUS_CANCELLED :
            $object->status = Event::STATUS_CANCELLED;
            break;
          case ICS::STATUS_TENTATIVE :
            $object->status = Event::STATUS_TENTATIVE;
            break;
        }
      } else
        $object->status = Event::STATUS_NONE;
      // ATTENDEE
      if (isset($vevent->ATTENDEE)) {
        if (isset($vevent->ORGANIZER)) {
          $object->organizer->email = str_replace('mailto:', '', strtolower($vevent->ORGANIZER->getValue()));
          $paramters = $vevent->ORGANIZER->parameters;
          if (isset($paramters[ICS::CN])) {
            $object->organizer->name = $paramters[ICS::CN]->getValue();
          }
        }
        $_attendees = [];
        foreach ($vevent->ATTENDEE as $prop) {
          $attendee = $prop->parameters;
          $_attendee = new \LibMelanie\Api\Melanie2\Attendee($object);
          // Email de l'attendee
          $_attendee->email = str_replace('mailto:', '', strtolower($prop->getValue()));
          // Ne pas conserver de participant avec la même adresse mail que l'organisateur
          if ($object->organizer->email == $_attendee->email) {
            continue;
          }
          // Gestion du CNAME
          if (isset($attendee[ICS::CN]))
            $_attendee->name = $attendee[ICS::CN]->getValue();
          // Gestion du PARTSTAT
          // MANTIS 4016: Gestion des COPY/MOVE
          if (isset($attendee[ICS::PARTSTAT]) && !$copy) {
            switch ($attendee[ICS::PARTSTAT]->getValue()) {
              case ICS::PARTSTAT_DECLINED :
                $_attendee->response = \LibMelanie\Api\Melanie2\Attendee::RESPONSE_DECLINED;
                break;
              case ICS::PARTSTAT_IN_PROCESS :
                $_attendee->response = \LibMelanie\Api\Melanie2\Attendee::RESPONSE_IN_PROCESS;
                break;
              case ICS::PARTSTAT_NEEDS_ACTION :
                $_attendee->response = \LibMelanie\Api\Melanie2\Attendee::RESPONSE_NEED_ACTION;
                break;
              case ICS::PARTSTAT_TENTATIVE :
                $_attendee->response = \LibMelanie\Api\Melanie2\Attendee::RESPONSE_TENTATIVE;
                break;
              case ICS::PARTSTAT_ACCEPTED :
              case ICS::PARTSTAT_DELEGATED :
              case ICS::PARTSTAT_COMPLETED :
              default :
                $_attendee->response = \LibMelanie\Api\Melanie2\Attendee::RESPONSE_ACCEPTED;
                break;
            }
          } else
            $_attendee->response = \LibMelanie\Api\Melanie2\Attendee::RESPONSE_NEED_ACTION;
          // Gestion du ROLE
          if (isset($attendee[ICS::ROLE])) {
            switch ($attendee[ICS::ROLE]->getValue()) {
              case ICS::ROLE_CHAIR :
                $_attendee->role = \LibMelanie\Api\Melanie2\Attendee::ROLE_CHAIR;
                break;
              case ICS::ROLE_NON_PARTICIPANT :
                $_attendee->role = \LibMelanie\Api\Melanie2\Attendee::ROLE_NON_PARTICIPANT;
                break;
              case ICS::ROLE_OPT_PARTICIPANT :
                $_attendee->role = \LibMelanie\Api\Melanie2\Attendee::ROLE_OPT_PARTICIPANT;
                break;
              case ICS::ROLE_REQ_PARTICIPANT :
              default :
                $_attendee->role = \LibMelanie\Api\Melanie2\Attendee::ROLE_REQ_PARTICIPANT;
                break;
            }
          } else
            $_attendee->role = \LibMelanie\Api\Melanie2\Attendee::ROLE_REQ_PARTICIPANT;
          // Ajout de l'attendee
          $_attendees[] = $_attendee;
        }
        $object->attendees = $_attendees;
      }
      // ATTACH
      if (isset($vevent->ATTACH)) {
        $attachments = $object->attachments;
        $_attachments = [];
        foreach ($vevent->ATTACH as $prop) {
          $attach = $prop->parameters;
          $_attach = new \LibMelanie\Api\Melanie2\Attachment();
          if (isset($attach[ICS::VALUE]) && $attach[ICS::VALUE]->getValue() == ICS::VALUE_BINARY) {
            $_attach->type = \LibMelanie\Api\Melanie2\Attachment::TYPE_BINARY;
            $_attach->data = $prop->getValue();
            if (isset($attach[ICS::X_MOZILLA_CALDAV_ATTACHMENT_NAME])) {
              $_attach->name = $attach[ICS::X_MOZILLA_CALDAV_ATTACHMENT_NAME]->getValue();
            } elseif (isset($attach[ICS::X_EVOLUTION_CALDAV_ATTACHMENT_NAME])) {
              $_attach->name = $attach[ICS::X_EVOLUTION_CALDAV_ATTACHMENT_NAME]->getValue();
            }
            // Vérifier si l'extension est autorisée
            if (preg_match(self::FORBIDDEN_ATTACH_EXT, $_attach->name)) {
              continue;
            }
            $_attach->modified = time();
            $_attach->owner = isset($user) ? $user->uid : $object->owner;
            $_attach->path = $object->uid . '/' . $object->owner;
            $_attach->isfolder = false;
            foreach ($attachments as $key => $attachment) {
              if ($attachment->path == $_attach->path && $attachment->name == $_attach->name) {
                unset($attachments[$key]);
              }
            }
            $_attach->save();
            $_attachments[] = $_attach;
          } else {
            $is_m2web_url = false;
            $data = $prop->getValue();
            // Si pas de VALUE, on est peut être sur une URL melanie2web
            foreach ($attachments as $key => $attachment) {
              if ($attachment->getDownloadURL() == $data) {
                unset($attachments[$key]);
                $is_m2web_url = true;
              }
            }
            // Ce n'est pas une url M2Web, donc c'est une url classique
            if (!$is_m2web_url) {
              $attach_uri = $object->getAttribute('ATTACH-URI');
              $attach_uri_array = explode('%%URI-SEPARATOR%%', $attach_uri);
              if (!in_array($data, $attach_uri_array)) {
                $attach_uri_array[] = $data;
                $attach_uri = implode('%%URI-SEPARATOR%%', $attach_uri_array);
                $object->setAttribute('ATTACH-URI', $attach_uri);
              }
              $_attach->type = \LibMelanie\Api\Melanie2\Attachment::TYPE_URL;
              $_attach->url = $data;
              $_attachments[] = $_attach;
            }
          }
        }
        $object->attachments = $_attachments;
        $attach_uri = $object->getAttribute('ATTACH-URI');
        $attach_uri_array = explode('%%URI-SEPARATOR%%', $attach_uri);
        $save_attach_uri = false;
        // Supprimer les pièces jointes qui ne sont plus nécessaire
        foreach ($attachments as $attachment) {
          if ($attachment->type == \LibMelanie\Api\Melanie2\Attachment::TYPE_URL) {
            if ($key = array_search($attachment->url, $attach_uri_array)) {
              unset($attach_uri_array[$key]);
              $save_attach_uri = true;
            }
          } else {
            $attachment->delete();
          }
        }
        if ($save_attach_uri) {
          $attach_uri = implode('%%URI-SEPARATOR%%', $attach_uri_array);
          $object->setAttribute('ATTACH-URI', $attach_uri);
        }
      } else {
        $attach_uri = $object->getAttribute('ATTACH-URI');
        $attach_uri_array = explode('%%URI-SEPARATOR%%', $attach_uri);
        $save_attach_uri = false;
        // Supprimer toutes les pièces jointes
        $attachments = $object->attachments;
        foreach ($attachments as $attachment) {
          if ($attachment->type == \LibMelanie\Api\Melanie2\Attachment::TYPE_URL) {
            if ($key = array_search($attachment->url, $attach_uri_array)) {
              unset($attach_uri_array[$key]);
              $save_attach_uri = true;
            }
          } else {
            $attachment->delete();
          }
        }
        if ($save_attach_uri) {
          $attach_uri = implode('%%URI-SEPARATOR%%', $attach_uri_array);
          $object->setAttribute('ATTACH-URI', $attach_uri);
        }
      }
      // Gestion de la récurrence
      if (isset($vevent->RRULE) && !isset($recurrence_id)) {
        $object->recurrence = new \LibMelanie\Api\Melanie2\Recurrence($object);
        $object->recurrence->rrule = $vevent->RRULE->getParts();
        if (isset($vevent->EXDATE)) {
          foreach ($vevent->EXDATE as $exdate) {
            $exception = new \LibMelanie\Api\Melanie2\Exception($event, $user, $calendar);
            $date = $exdate->getDateTime();
            $date->setTimezone(new \DateTimeZone($timezone));
            $exception->recurrenceId = $date->format(self::SHORT_DB_DATE_FORMAT);
            $exception->deleted = true;
            $exception->uid = $event->uid;
            
            $exceptions[] = $exception;
          }
        }
      }
      // Ajout de l'objet aux exceptions
      if (isset($recurrence_id)) {
        $exceptions[] = $object;
      }
    }
    // Ajoute les exceptions à l'évènement
    $event->exceptions = $exceptions;
    // Retourne l'évènement généré
    return $event;
  }
}