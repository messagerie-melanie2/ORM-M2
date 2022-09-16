<?php

/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * ORM Mél Copyright © 2021 Groupe Messagerie/MTE
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

use LibMelanie\Api\Defaut\Event;
use LibMelanie\Api\Defaut\User;
use LibMelanie\Api\Defaut\Calendar;
use LibMelanie\Log\M2Log;

// Utilisation de la librairie Sabre VObject pour la conversion ICS
@include_once 'vendor/autoload.php';
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
   * @param Calendar $calendar
   * @param User $user
   * @param boolean $useattachments
   *       
   * @return Event
   */
  public static function Convert($ics, $event, $calendar = null, $user = null, $useattachments = true) {
    // Ajouter les options FORGIVING et IGNORE_INVALID_LINES au parser ICS
    $vcalendar = VObject\Reader::read($ics, VObject\Reader::OPTION_FORGIVING + VObject\Reader::OPTION_IGNORE_INVALID_LINES);
    $exceptions = [];
    // 0005907: [ICS] Prendre en compte le X-WR-TIMEZONE
    $x_wr_timezone = $vcalendar->{ICS::X_WR_TIMEZONE};
    if (isset($x_wr_timezone)) {
      $_list_timezones = \DateTimeZone::listIdentifiers();
      if (!in_array($x_wr_timezone, $_list_timezones)) {
        unset($x_wr_timezone);
      }
    }
    foreach ($vcalendar->VEVENT as $vevent) {
      $recurrence_id = $vevent->{ICS::RECURRENCE_ID};
      if (isset($recurrence_id)) {
        $Exception = $event->__getNamespace() . '\\Exception';
        if (isset($event->exceptions[date($Exception::FORMAT_ID, strtotime($recurrence_id))])) {
          $object = $event->exceptions[date($Exception::FORMAT_ID, strtotime($recurrence_id))];
        }
        else {
          $object = new $Exception($event, $user, $calendar);
          $object->recurrence_id = $recurrence_id;
        }
      } else {
        $object = $event;
      }
      // UID
      if (!isset($vevent->UID))
        continue;
      else
        $object->uid = $vevent->UID;
      // Owner
      if (isset($recurrence_id) && (!isset($object->owner) || empty($object->owner))) {
        M2Log::Log(M2Log::LEVEL_DEBUG, "ICSToEvent::Convert() SetOwner = " . isset($user) && isset($user->uid) ? $user->uid : $calendar->owner);
        if (isset($event) && isset($event->owner)) {
          $object->owner = $event->owner;
        }
        else {
          $object->owner = isset($user) && isset($user->uid) ? $user->uid : $calendar->owner;
        }        
      }
      // DTSTART & DTEND
      if (isset($vevent->DTSTART) && isset($vevent->DTEND)) {
        $startDate = $vevent->DTSTART->getDateTime();
        $endDate = $vevent->DTEND->getDateTime();
      }
      else if (isset($vevent->DTSTART) && isset($vevent->DURATION)) {
        $startDate = $vevent->DTSTART->getDateTime();
        $endDate = clone $startDate;
        $duration = new \DateInterval(strval($vevent->DURATION));
        $endDate->add($duration);
      }
      if (isset($startDate)) {
        $object->all_day = isset($vevent->DTSTART->parameters[ICS::VALUE]) && $vevent->DTSTART->parameters[ICS::VALUE] == ICS::VALUE_DATE;
        $object->dtstart = $startDate;
        $object->dtend = $endDate;
      }
      if (isset($x_wr_timezone)) {
        $object->timezone = $x_wr_timezone;
      }
      
      // Recurrence ID
      if (isset($recurrence_id)) {
        $date = $recurrence_id->getDateTime();
        if ($date->getTimezone()->getName() != $object->timezone) {
          $date->setTimezone(new \DateTimeZone($object->timezone));
        }
        $object->recurrence_id = $date->format(self::DB_DATE_FORMAT);
      }
      // Cas du FAKED MASTER
      if (isset($vevent->{ICS::X_MOZ_FAKED_MASTER}) && intval($vevent->{ICS::X_MOZ_FAKED_MASTER}->getValue()) == 1) {
        // X MOZ LASTACK
        if (isset($vevent->{ICS::X_MOZ_LASTACK})) {
          $object->setAttribute(ICS::X_MOZ_LASTACK, $vevent->{ICS::X_MOZ_LASTACK}->getValue());
        } else {
          $object->deleteAttribute(ICS::X_MOZ_LASTACK);
        }
        // X MOZ SNOOZE TIME
        if (isset($vevent->{ICS::X_MOZ_SNOOZE_TIME})) {
          $object->setAttribute(ICS::X_MOZ_SNOOZE_TIME, $vevent->{ICS::X_MOZ_SNOOZE_TIME}->getValue());
        } else {
          $object->deleteAttribute(ICS::X_MOZ_SNOOZE_TIME);
        }
        // X MOZ GENERATION
        if (isset($vevent->{ICS::X_MOZ_GENERATION})) {
          $object->setAttribute(ICS::X_MOZ_GENERATION, $vevent->{ICS::X_MOZ_GENERATION}->getValue());
        } else {
          $object->deleteAttribute(ICS::X_MOZ_GENERATION);
        }
        // DTSTAMP
        if (isset($vevent->{ICS::DTSTAMP})) {
          $object->setAttribute(ICS::DTSTAMP, $vevent->{ICS::DTSTAMP}->getValue());
        } else {
          $object->deleteAttribute(ICS::DTSTAMP);
        }
        // LAST-MODIFIED
        if (isset($vevent->{ICS::LAST_MODIFIED})) {
          $object->setAttribute(ICS::LAST_MODIFIED, $vevent->{ICS::LAST_MODIFIED}->getValue());
        } else {
          $object->deleteAttribute(ICS::LAST_MODIFIED);
        }
        // CREATED
        if (isset($vevent->{ICS::CREATED})) {
          $object->setAttribute(ICS::CREATED, $vevent->{ICS::CREATED}->getValue());
          $object->created = strtotime($vevent->CREATED->getValue());
        } else {
          $object->deleteAttribute(ICS::CREATED);
          if (!isset($object->created)) {
            $object->created = time();
          }
        }
        
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
        if (isset($startDate)) {
          $object->alarm = ($startDate->format("U") - $alarmDate->format("U")) / 60;
          if ($object->alarm === 0) {
            $object->alarm = 1;
          }
        }
      } else {
        $object->alarm = 0;
      }
      // Gestion des alarmes même pour les occurrences
      if (!isset($recurrence_id)) {
        // X MOZ LASTACK
        if (isset($vevent->{ICS::X_MOZ_LASTACK})) {
          $object->setAttribute(ICS::X_MOZ_LASTACK, $vevent->{ICS::X_MOZ_LASTACK}->getValue());
        } else {
          $object->deleteAttribute(ICS::X_MOZ_LASTACK);
        }
        // X MOZ SNOOZE TIME
        if (isset($vevent->{ICS::X_MOZ_SNOOZE_TIME})) {
          $object->setAttribute(ICS::X_MOZ_SNOOZE_TIME, $vevent->{ICS::X_MOZ_SNOOZE_TIME}->getValue());
        } else {
          $object->deleteAttribute(ICS::X_MOZ_SNOOZE_TIME);
        }
        // 0005238: [ICS] Enregistrer les attributs X-MOZ-SNOOZE-TIME-*
        $children = $vevent->children;
        $attributes = [];
        // Parcours tous les children pour trouver les X-MOZ-SNOOZE-TIME-
        foreach($children as $key => $child) {
          if (substr(strtoupper($child->name), 0, strlen("X-MOZ-SNOOZE-TIME-")) === "X-MOZ-SNOOZE-TIME-") {
            $attributes[$child->name] = $child->getValue();
          }
        }
        // Si des attributs ont été trouvé on les stock
        if (!empty($attributes)) {
          $object->setAttribute("X-MOZ-SNOOZE-TIME-CHILDREN", json_encode($attributes));
        }
        else {
          $object->deleteAttribute("X-MOZ-SNOOZE-TIME-CHILDREN");
        }
      }
      // SEQUENCE
      if (isset($vevent->SEQUENCE)) {
        $object->sequence = $vevent->SEQUENCE->getValue();
      } else {
        $object->deleteAttribute(ICS::SEQUENCE);
        $object->sequence = 0;
      }
      // X-MOZ-RECEIVED-SEQUENCE
      if (isset($vevent->{ICS::X_MOZ_RECEIVED_SEQUENCE})) {
        $object->setAttribute(ICS::X_MOZ_RECEIVED_SEQUENCE, $vevent->{ICS::X_MOZ_RECEIVED_SEQUENCE}->getValue());
      } else {
        $object->deleteAttribute(ICS::X_MOZ_RECEIVED_SEQUENCE);
      }
      // X-MOZ-RECEIVED-DTSTAMP
      if (isset($vevent->{ICS::X_MOZ_RECEIVED_DTSTAMP})) {
        $object->setAttribute(ICS::X_MOZ_RECEIVED_DTSTAMP, $vevent->{ICS::X_MOZ_RECEIVED_DTSTAMP}->getValue());
      } else {
        $object->deleteAttribute(ICS::X_MOZ_RECEIVED_DTSTAMP);
      }
      // X Moz Send Invitations
      if (isset($vevent->{ICS::X_MOZ_SEND_INVITATIONS})) {
        $object->setAttribute(ICS::X_MOZ_SEND_INVITATIONS, $vevent->{ICS::X_MOZ_SEND_INVITATIONS}->getValue());
      } 
      else {
        $object->deleteAttribute(ICS::X_MOZ_SEND_INVITATIONS);
      }
      // X Moz Send Invitations Undisclosed
      if (isset($vevent->{ICS::X_MOZ_SEND_INVITATIONS_UNDISCLOSED})) {
        $object->setAttribute(ICS::X_MOZ_SEND_INVITATIONS_UNDISCLOSED, $vevent->{ICS::X_MOZ_SEND_INVITATIONS_UNDISCLOSED}->getValue());
      } 
      else {
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
        $object->transparency = $vevent->TRANSP->getValue();
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
        $object->created = strtotime($vevent->CREATED->getValue());
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
        // 0005064: [ICS] si l'organisateur existe, ne pas le modifier depuis l'ICS
        $organizer = $object->organizer;
        $organizer_email = isset($organizer) ? $organizer->email : null;
        if (isset($vevent->ORGANIZER) && !isset($organizer_email)) {
          $parameters = $vevent->ORGANIZER->parameters;
          if (isset($parameters[ICS::CN])) {
            $organizer->name = $parameters[ICS::CN]->getValue();
          }
          if (isset($parameters[ICS::RSVP])) {
            $organizer->rsvp = $parameters[ICS::RSVP]->getValue();
          }
          if (isset($parameters[ICS::ROLE])) {
            $organizer->role = $parameters[ICS::ROLE]->getValue();
          }
          if (isset($parameters[ICS::PARTSTAT])) {
            $organizer->partstat = $parameters[ICS::PARTSTAT]->getValue();
          }
          if (isset($parameters[ICS::SENT_BY])) {
            $organizer->email = str_replace('mailto:', '', strtolower($parameters[ICS::SENT_BY]->getValue()));
            // 0005096: Le champ X-M2-ORG-MAIL n'est pas alimenté pour une modification d'événement
            $organizer->owner_email = str_replace('mailto:', '', strtolower($vevent->ORGANIZER->getValue()));
          }
          else {
            $organizer->email = str_replace('mailto:', '', strtolower($vevent->ORGANIZER->getValue()));
          }
          if (isset($parameters[ICS::X_M2_ORG_MAIL])) {
            $organizer->owner_email = str_replace('mailto:', '', strtolower($parameters[ICS::X_M2_ORG_MAIL]->getValue()));
          }
          // Gérer l'organizer calendar
          $organizer_calendar = $organizer->calendar;
          if (!isset($organizer_calendar) && !$organizer->extern && isset($calendar) && $organizer->uid == $calendar->owner) {
            // ici on peut dire qu'on est dans le calendrier organisateur
            $organizer->calendar = $calendar->id;
          }
          $object->organizer = $organizer;
        }
        $_attendees = [];
        $Attendee = $event->__getNamespace() . '\\Attendee';
        foreach ($vevent->ATTENDEE as $prop) {
          $attendee = $prop->parameters;
          $_attendee = new $Attendee($object);
          // Email de l'attendee
          $_attendee->email = str_replace('mailto:', '', strtolower($prop->getValue()));
          // Rechercher la réponse du participant courant
          $_old_response = null;
          foreach ($object->attendees as $_old_attendee) {
            if (strtolower($_old_attendee->email) == strtolower($_attendee->email)) {
              $_old_response = $_old_attendee->response;
            }
          }
          // Ne pas conserver de participant avec la même adresse mail que l'organisateur
          // Test de non suppression du participant pour voir / PENDING: Test non concluant on réactive
          if ($object->organizer->email == $_attendee->email) {
            continue;
          }
          // Gestion du CNAME
          if (isset($attendee[ICS::CN])) {
            $_attendee->name = $attendee[ICS::CN]->getValue();
          }
          // Gestion du PARTSTAT
          // MANTIS 4016: Gestion des COPY/MOVE
          if (isset($attendee[ICS::PARTSTAT]) && !$copy) {
            switch ($attendee[ICS::PARTSTAT]->getValue()) {
              case ICS::PARTSTAT_DECLINED :
                $_attendee->response = $Attendee::RESPONSE_DECLINED;
                break;
              case ICS::PARTSTAT_IN_PROCESS :
                $_attendee->response = $Attendee::RESPONSE_IN_PROCESS;
                break;
              case ICS::PARTSTAT_NEEDS_ACTION :
                if (isset($_old_response) 
                    && $_old_response != $Attendee::RESPONSE_NEED_ACTION) {
                  $_attendee->response = $_old_response;
                }
                else {
                  $_attendee->response = $Attendee::RESPONSE_NEED_ACTION;
                }
                break;
              case ICS::PARTSTAT_TENTATIVE :
                $_attendee->response = $Attendee::RESPONSE_TENTATIVE;
                break;
              case ICS::PARTSTAT_ACCEPTED :
              case ICS::PARTSTAT_DELEGATED :
              case ICS::PARTSTAT_COMPLETED :
              default :
                $_attendee->response = $Attendee::RESPONSE_ACCEPTED;
                break;
            }
          } else {
            $_attendee->response = $Attendee::RESPONSE_NEED_ACTION;
          }            
          // Gestion du ROLE
          if (isset($attendee[ICS::ROLE])) {
            switch ($attendee[ICS::ROLE]->getValue()) {
              case ICS::ROLE_CHAIR :
                $_attendee->role = $Attendee::ROLE_CHAIR;
                break;
              case ICS::ROLE_NON_PARTICIPANT :
                $_attendee->role = $Attendee::ROLE_NON_PARTICIPANT;
                $_attendee->response = $Attendee::RESPONSE_ACCEPTED;
                break;
              case ICS::ROLE_OPT_PARTICIPANT :
                $_attendee->role = $Attendee::ROLE_OPT_PARTICIPANT;
                break;
              case ICS::ROLE_REQ_PARTICIPANT :
              default :
                $_attendee->role = $Attendee::ROLE_REQ_PARTICIPANT;
                break;
            }
          } else {
            $_attendee->role = $Attendee::ROLE_REQ_PARTICIPANT;
          }
          // Gestion du TYPE
          if (isset($attendee[ICS::CUTYPE])) {
            switch ($attendee[ICS::CUTYPE]->getValue()) {
              case ICS::CUTYPE_GROUP:
                $_attendee->type = $Attendee::TYPE_GROUP;
                break;
              case ICS::CUTYPE_INDIVIDUAL:
                $_attendee->type = $Attendee::TYPE_INDIVIDUAL;
                break;
              case ICS::CUTYPE_RESOURCE:
                $_attendee->type = $Attendee::TYPE_RESOURCE;
                break;
              case ICS::CUTYPE_ROOM:
                $_attendee->type = $Attendee::TYPE_ROOM;
                break;
              case ICS::CUTYPE_UNKNOWN:
              default:
                $_attendee->type = $Attendee::TYPE_UNKNOWN;
                break;
            }
          }
          // Ajout de l'attendee
          $_attendees[] = $_attendee;
        }
        $object->attendees = $_attendees;
      }
      else {
        // MANTIS 0005086: Impossible de vider la liste des participants
        $object->attendees = [];
      }
      // ATTACH
      $Attachment = $event->__getNamespace() . '\\Attachment';
      $organizer_calendar = isset($organizer) ? $organizer->calendar : null;
      if ($useattachments && (!isset($calendar) || !isset($organizer) || !isset($organizer_calendar) || $organizer->extern || $organizer_calendar == $calendar->id)) {
        if (isset($vevent->ATTACH)) {
          $attachments = $object->attachments;
          $_attachments = [];
          // MANTIS 0004706: L'enregistrement d'une pièce jointe depuis l'ICS ne se fait pas dans le bon dossier vfs
          $attach_path = $object->realuid . '/' . $object->calendar;
          // 0006077: Lister les pièces jointes de la récurrence maitre dans les occurrences
          if (isset($recurrence_id)) {
            $attach_master_path = $object->uid . '/' . $object->calendar;
          }
          foreach ($vevent->ATTACH as $prop) {
            $attach = $prop->parameters;
            $_attach = new $Attachment();
            if (isset($attach[ICS::VALUE]) && $attach[ICS::VALUE]->getValue() == ICS::VALUE_BINARY) {
              $_attach->type = $Attachment::TYPE_BINARY;
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
              $_attach->path = $attach_path;
              $_attach->isfolder = false;
              $save = true;
              foreach ($attachments as $key => $attachment) {
                if ($attachment->path == $attach_path && $attachment->name == $_attach->name) {
                  unset($attachments[$key]);
                }
                else if (isset($attach_master_path) && $attachment->path == $attach_master_path && $attachment->name == $_attach->name) {
                  $save = false;
                  unset($attachments[$key]);
                }
              }
              if ($save) {
                $_attach->save();
                $_attachments[] = $_attach;
              }
            } else {
              $_found = false;
              $data = $prop->getValue();
              // Si pas de VALUE, on est peut être sur une URL melanie2web
              foreach ($attachments as $key => $attachment) {
                if ($attachment->type == $Attachment::TYPE_BINARY && $attachment->getDownloadURL() == $data) {
                  $_attachments[] = $attachment;
                  unset($attachments[$key]);
                  $_found = true;
                }
                else if ($attachment->type == $Attachment::TYPE_URL && $attachment->url == $data) {
                  $_attachments[] = $attachment;
                  unset($attachments[$key]);
                  $_found = true;
                }
                else if (isset($recurrence_id)) {
                  $attachment->path = $object->uid . '/' . $object->calendar;
                  if ($attachment->getDownloadURL() == $data) {
                    unset($attachments[$key]);
                    $_attachments[] = $attachment;
                    $_found = true;
                  }
                }
              }
              // Ce n'est pas une url M2Web, donc c'est une url classique
              if (!$_found) {
                $_attach->type = $Attachment::TYPE_URL;
                $_attach->url = $data;
                $_attachments[] = $_attach;
              }
            }
          }
          $object->attachments = $_attachments;
          // Supprimer les pièces jointes qui ne sont plus nécessaire
          foreach ($attachments as $attachment) {
            if ($attachment->type == $Attachment::TYPE_BINARY) {
              $attachment->delete();
            }
          }
        }
        else {
          // Supprimer toutes les pièces jointes
          $attachments = $object->attachments;
          foreach ($attachments as $attachment) {
            if ($attachment->type == $Attachment::TYPE_BINARY) {
              $attachment->delete();
            }
          }
          $object->attachments = [];
        }
      }

      // Gestion de la récurrence
      if (isset($vevent->RRULE) && !isset($recurrence_id)) {
        $Recurrence = $event->__getNamespace() . '\\Recurrence';
        $object->recurrence = new $Recurrence($object);
        $object->recurrence->rrule = $vevent->RRULE->getParts();
        if (isset($vevent->EXDATE)) {
          $Exception = $event->__getNamespace() . '\\Exception';
          foreach ($vevent->EXDATE as $exdate) {
            $exception = new $Exception($event, $user, $calendar);
            $date = $exdate->getDateTime();
            // Enregistrer les exceptions en GMT dans la base
            $date->setTimezone(new \DateTimeZone('GMT'));
            $exception->recurrence_id = $date->format(self::DB_DATE_FORMAT);
            $exception->deleted = true;
            $exception->uid = $event->uid;
            
            $exceptions[] = $exception;
          }
        }
        // Gérer les RDATE
        if (isset($vevent->RDATE)) {
          $rrule = $object->recurrence->rrule;
          $rrule[ICS::RDATE] = [];
          foreach ($vevent->RDATE as $rdate) {
            $rrule[ICS::RDATE][] = $rdate->getDateTime();
          }
          $object->recurrence->rrule = $rrule;
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