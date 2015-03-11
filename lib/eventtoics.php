<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 *
 * ORM M2 Copyright (C) 2015  PNE Annuaire et Messagerie/MEDDE
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

use LibMelanie\Config\ConfigMelanie;
use LibMelanie\Api\Melanie2\Attachment;
use LibMelanie\Api\Melanie2\Recurrence;
use LibMelanie\Api\Melanie2\User;
use LibMelanie\Api\Melanie2\Event;
use LibMelanie\Api\Melanie2\Attendee;
use LibMelanie\Api\Melanie2\Calendar;
use LibMelanie\Log\M2Log;

// Utilisation de la librairie Sabre VObject pour la conversion ICS
require_once 'vendor/autoload.php';
use Sabre\VObject;

/**
 * Class de génération de l'ICS en fonction de l'objet évènement
 * Méthodes Statiques
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Lib Mélanie2
 *
 */
class EventToICS {
	/**
	 * Identifiant de l'outil utilisant l'ICS (pour la génération)
	 * @var string
	 */
	const PRODID = '-//ORM LibMelanie2 PHP/PNE Messagerie/MEDDE';
	/**
	 * Version ICalendar utilisé pour la génération de l'ICS
	 * @var string
	 */
	const VERSION = '2.0';

	/**
	 * Variable configurable depuis l'extérieur pour définir
	 * si les pièces jointes sont proposées via une URL
	 * ou directement en binaire (encodage base64)
	 * @var bool
	 */
	public static $USE_ATTACH_URL = true;


	/**
	 *  Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() { }

	/**
	 * Génére un ICS en fonction de l'évènement passé en paramètre
	 * L'évènement doit être de type Event de la librairie LibM2
	 * Gère également les exceptions, peut donc retourner plusieurs composant VEVENT
	 * @param Event $event
	 * @param Calendar $calendar
	 * @param User $user
	 * @return string $ics
	 */
	public static function Convert(Event $event, Calendar $calendar = null, User $user = null) {
	  M2Log::Log(M2Log::LEVEL_DEBUG, "EventToICS->Convert()");
		$vcalendar = new VObject\Component\VCalendar();
		$vevent = $vcalendar->add('VEVENT');
		// PRODID et Version
		$vcalendar->PRODID = self::PRODID;
		$vcalendar->VERSION = self::VERSION;
		// Configuration pour l'utilisation des URLs pour les pièces jointes
		// Se fait en fonction des informations fournies par le client
		self::$USE_ATTACH_URL = !isset($_SERVER["HTTP_X_MOZ_ATTACHMENTS"]) || $_SERVER["HTTP_X_MOZ_ATTACHMENTS"] != 1;
		// Gestion du timezone
		if (isset($user)) {
		  $timezone = $user->getTimezone();
		}
	  elseif (isset($calendar)) {
			$timezone = $calendar->getTimezone();
		}
		if (empty($timezone)) {
		  $timezone = ConfigMelanie::CALENDAR_DEFAULT_TIMEZONE;
		}
		// UID
		$vevent->UID = $event->uid;
		if (!$event->deleted) {
			$vevent = self::getVeventFromEvent($vevent, $event, $calendar, $user);
			// Type récurrence
			if (isset($event->recurrence->type)
					&& $event->recurrence->type !== Recurrence::RECURTYPE_NORECUR) {
				$timeStart = new \DateTime($event->start);
				$rrule = $event->recurrence->rrule;
				$params = [];
				foreach ($rrule as $key => $value) {
				  if (!is_string($value)) {
            if ($value instanceof \DateTime) {
              $value = $value->format('Ymd').'T'.$value->format('His').'Z';
            }
				  }
				  if ($key == ICS::INTERVAL && $value == 1) {
				    // On n'affiche pas l'interval 1 dans l'ICS
				    continue;
				  }
				  $params[] = "$key=$value";
				}
				// Construction de la récurrence
				$vevent->add(ICS::RRULE, implode(';',$params));
			}
		}
		// Alarm properties
		$snooze_time = $event->getAttribute(ICS::X_MOZ_SNOOZE_TIME);
		if (isset($snooze_time)) $vevent->add(ICS::X_MOZ_SNOOZE_TIME, $snooze_time);
		$last_ack = $event->getAttribute(ICS::X_MOZ_LASTACK);
		if (isset($last_ack)) $vevent->add(ICS::X_MOZ_LASTACK, $last_ack);
		// Exceptions
		if (count($event->exceptions > 0)) {
			$exdate = array();
			$first = true;
			$starttime = '';
			foreach ($event->exceptions as $exception) {
			  if ($event->deleted) {
			    if ($first) {
			      $first = false;
			      $starttime = new \DateTime($exception->start, new \DateTimeZone($timezone));
			      $endtime = new \DateTime($exception->end, new \DateTimeZone($timezone));
			      $vevent->DTSTART = $starttime;
			      $rdate = clone $starttime;
			      $rdate->setTimezone(new \DateTimeZone('UTC'));
			      $date = $rdate->format('Ymd') . 'T' . $rdate->format('His') . 'Z';
			      if ($starttime->format('His') == '000000' && $endtime->format('His') == '000000') {
			        $vevent->add(ICS::RDATE, $date, [ICS::VALUE => ICS::VALUE_DATE]);
			      } else {
			        $vevent->add(ICS::RDATE, $date, [ICS::VALUE => ICS::VALUE_DATE_TIME]);
			      }
			      $dateTime = new \DateTime('@'.$exception->modified, new \DateTimeZone($timezone));
			      $dateTime->setTimezone(new \DateTimeZone('UTC'));
			      $date = $dateTime->format('Ymd') . 'T' . $dateTime->format('His') . 'Z';
			      $vevent->add(ICS::DTSTAMP, $date);
			      $vevent->add(ICS::LAST_MODIFIED, $date);
			      $vevent->add(ICS::CREATED, $date);
			      $vevent->SUMMARY = $exception->title;
			      $vevent->add(ICS::X_MOZ_GENERATION, count($event->exceptions));
			      $vevent->add(ICS::X_MOZ_FAKED_MASTER, "1");
			    }
			    $exdatetime = new \DateTime($exception->recurrenceId, new \DateTimeZone($timezone));
					$date = $exdatetime->format('Ymd') . 'T' . $vevent->DTSTART->getDateTime()->format('His');
			  }
				elseif ($vevent->DTSTART[ICS::VALUE] === ICS::VALUE_DATE_TIME) {
					$exdatetime = new \DateTime($exception->recurrenceId, new \DateTimeZone($timezone));
					$date = $exdatetime->format('Ymd');
				} else {
					$exdatetime = new \DateTime($exception->recurrenceId, new \DateTimeZone($timezone));
					$date = $exdatetime->format('Ymd') . 'T' . $vevent->DTSTART->getDateTime()->format('His');
				}
				if ($exception->deleted && !$event->deleted) {
					$exdate[] = $date;
				} else {
			    $vexception = $vcalendar->add('VEVENT');
					// UID
					$vexception->UID = $exception->uid;
					if ($vevent->DTSTART[ICS::VALUE] === ICS::VALUE_DATE) {
						$vexception->add(ICS::RECURRENCE_ID, $date, [ICS::VALUE => ICS::VALUE_DATE]);
					} else {
						if (isset($timezone)) {
							$vexception->add(ICS::RECURRENCE_ID, $date, [ICS::VALUE => ICS::VALUE_DATE_TIME, ICS::TZID => $timezone]);
						} else {
							$vexception->add(ICS::RECURRENCE_ID, $date, [ICS::VALUE => ICS::VALUE_DATE_TIME]);
						}
					}
					$vexception = self::getVeventFromEvent($vexception, $exception, $calendar, $user);
				}
			}
			// Gestion des EXDATE
			if (count($exdate) > 0) {
				if ($vevent->DTSTART[ICS::VALUE] === ICS::VALUE_DATE) {
					$vevent->add(ICS::EXDATE, implode(',', $exdate), array(ICS::VALUE => ICS::VALUE_DATE));
				} else {
					if (isset($timezone)) {
						$vevent->add(ICS::EXDATE, implode(',', $exdate), array(ICS::VALUE => ICS::VALUE_DATE_TIME, ICS::TZID => $timezone));
					} else {
						$vevent->add(ICS::EXDATE, implode(',', $exdate), array(ICS::VALUE => ICS::VALUE_DATE_TIME));
					}
				}
			}
		}
		return $vcalendar->serialize();
	}

	/**
	 * Méthode permettant de générer l'objet VEVENT à partir des données de l'évènement
	 * Cette méthode est séparée pour être appelé plusieurs fois, dans le cas où l'évènement a des exceptions
	 * @param VObject\Component $vevent
	 * @param Event $event
	 * @param Calendar $calendar
	 * @param User $user
	 */
	private static function getVeventFromEvent(VObject\Component $vevent, Event $event, Calendar $calendar = null, User $user = null) {
	  M2Log::Log(M2Log::LEVEL_DEBUG, "EventToICS->getVeventFromEvent()");
	  // Timezone
		if (isset($user)) {
		  $timezone = $user->getTimezone();
		}
	  elseif (isset($calendar)) {
			$timezone = $calendar->getTimezone();
		}
		if (empty($timezone)) {
		  $timezone = ConfigMelanie::CALENDAR_DEFAULT_TIMEZONE;
		}
		M2Log::Log(M2Log::LEVEL_DEBUG, "EventToICS->getVeventFromEvent() timezone : " . $timezone);
		// Class
		if (isset($event->class)) {
			switch ($event->class) {
				case Event::CLASS_CONFIDENTIAL:
					$vevent->CLASS = ICS::CLASS_CONFIDENTIAL;
					break;
				case Event::CLASS_PRIVATE:
					$vevent->CLASS = ICS::CLASS_PRIVATE;
					break;
				case Event::CLASS_PUBLIC:
				default:
				  $vevent->CLASS = ICS::CLASS_PUBLIC;
				  break;
			}
		} else $vevent->CLASS = ICS::CLASS_PUBLIC;

		// Status
		if (isset($event->status)) {
		  switch ($event->status) {
		    default:
		    case Event::STATUS_CONFIRMED:
		    case Event::STATUS_NONE:
		      $vevent->STATUS = ICS::STATUS_CONFIRMED;
		      break;
		    case Event::STATUS_CANCELLED:
		      $vevent->STATUS = ICS::STATUS_CANCELLED;
		      break;
		    case Event::STATUS_TENTATIVE:
		      $vevent->STATUS = ICS::STATUS_TENTATIVE;
		      break;
		  }
		} else $vevent->STATUS = ICS::STATUS_CONFIRMED;

		// DTSTAMP
		if (isset($event->modified)) {
 		  $dateTime = new \DateTime('@'.$event->modified, new \DateTimeZone($timezone));
		  $dateTime->setTimezone(new \DateTimeZone('UTC'));
		  $date = $dateTime->format('Ymd') . 'T' . $dateTime->format('His') . 'Z';
		  $vevent->add(ICS::DTSTAMP, $date);
		  $vevent->add(ICS::LAST_MODIFIED, $date);
		  $vevent->add(ICS::CREATED, $date);
		}

		// DateTime
		if (isset($event->start) && isset($event->end)) {
			$dateTimeStart = new \DateTime($event->start);
			$dateTimeEnd = new \DateTime($event->end);

			if ($dateTimeEnd->format('H:i:s') == $dateTimeStart->format('H:i:s') && $dateTimeStart->format('H:i:s') == "00:00:00") {
				// All day event
				$vevent->DTSTART = $dateTimeStart;
				$vevent->DTEND = $dateTimeEnd;
			} else {
				$dateTimeStart->setTimezone(new \DateTimeZone($timezone));
				$dateTimeEnd->setTimezone(new \DateTimeZone($timezone));
				$vevent->DTSTART = $dateTimeStart;
				$vevent->DTEND = $dateTimeEnd;
			}
		}

		if (($event->class == Event::CLASS_PRIVATE
				|| $event->class == Event::CLASS_CONFIDENTIAL)
				&& $event->owner != $user->uid
				&& isset($calendar)
				&& $calendar->owner !=  $user->uid
		    && !$calendar->asRight(\LibMelanie\Config\ConfigMelanie::PRIV)) {
			$vevent->SUMMARY = 'Événement privé';
		} else {
			// Titre
			if (isset($event->title) && $event->title != "") $vevent->SUMMARY = $event->title;
			// Catégories
			if (isset($event->category) && $event->category != "") {
			  $categories = explode(',', $event->category);
			  foreach ($categories as $category) {
			    $vevent->add(ICS::CATEGORIES, $category);
			  }
			}
			// Description
			if (isset($event->description) && $event->description != "") $vevent->DESCRIPTION = $event->description;
			// Location
			if (isset($event->location) && $event->location != "") $vevent->LOCATION = $event->location;
			// Alarm
			if (isset($event->alarm) && $event->alarm != 0) {
				$valarm = $vevent->add('VALARM');
				$valarm->TRIGGER = '-PT'.$event->alarm.'M';
				$valarm->ACTION = ICS::ACTION_DISPLAY;
        // Attributs sur l'alarme
				$x_moz_lastack = $event->getAttribute(ICS::X_MOZ_LASTACK);
				if (isset($x_moz_lastack)) $vevent->{ICS::X_MOZ_LASTACK} = $x_moz_lastack;
				$x_moz_snooze_time = $event->getAttribute(ICS::X_MOZ_SNOOZE_TIME);
				if (isset($x_moz_snooze_time)) $vevent->{ICS::X_MOZ_SNOOZE_TIME} = $x_moz_snooze_time;
			}
			// Traitement participants
			$organizer_attendees = $event->attendees;
			if (!is_null($organizer_attendees)
					&& is_array($organizer_attendees)
					&& count($organizer_attendees) > 0) {
				// Add organizer
				$vevent->add(ICS::ORGANIZER,
				    'mailto:'.$event->organizer->email,
				    [
				      ICS::CN => $event->organizer->name,
				      ICS::ROLE => ICS::ROLE_CHAIR,
				      ICS::PARTSTAT => ICS::PARTSTAT_ACCEPTED,
				      ICS::RSVP => 'TRUE',
				    ]);
				foreach ($organizer_attendees as $attendee) {
					// Role
					switch ($attendee->role) {
						case Attendee::ROLE_CHAIR:
							$role = ICS::ROLE_CHAIR;
							break;
						default:
						case Attendee::ROLE_REQ_PARTICIPANT:
							$role = ICS::ROLE_REQ_PARTICIPANT;
							break;
						case Attendee::ROLE_OPT_PARTICIPANT:
							$role = ICS::ROLE_OPT_PARTICIPANT;
							break;
						case Attendee::ROLE_NON_PARTICIPANT:
							$role = ICS::ROLE_NON_PARTICIPANT;
							break;
					}
					// Parstat
					switch ($attendee->response) {
						case Attendee::RESPONSE_ACCEPTED:
							$partstat = ICS::PARTSTAT_ACCEPTED;
							break;
						case Attendee::RESPONSE_DECLINED:
							$partstat = ICS::PARTSTAT_DECLINED;
							break;
						case Attendee::RESPONSE_IN_PROCESS:
							$partstat = ICS::PARTSTAT_IN_PROCESS;
							break;
						default:
						case Attendee::RESPONSE_NEED_ACTION:
							$partstat = ICS::PARTSTAT_NEEDS_ACTION;
							break;
						case Attendee::RESPONSE_TENTATIVE:
							$partstat = ICS::PARTSTAT_TENTATIVE;
							break;
					}
					// Add attendee
					$vevent->add(ICS::ATTENDEE, 'mailto:'.$attendee->email, [
    						ICS::CN => $attendee->name,
    						ICS::PARTSTAT => $partstat,
    						ICS::ROLE => $role,
					      ICS::RSVP => 'TRUE',
					    ]);
				}
			}
			// Calendar infos
			if (isset($calendar)) {
			  $vevent->add(ICS::X_CALDAV_CALENDAR_ID, $calendar->id);
			  $vevent->add(ICS::X_CALDAV_CALENDAR_OWNER, $calendar->owner);
			}
			// Sequence
			$sequence = $event->getAttribute(ICS::SEQUENCE);
			if (isset($sequence)) $vevent->SEQUENCE = $sequence;
			// X Moz Send Invitations
			$send_invitation = $event->getAttribute(ICS::X_MOZ_SEND_INVITATIONS);
			if (isset($send_invitation)) $vevent->add(ICS::X_MOZ_SEND_INVITATIONS, $send_invitation);
			// X Moz Generation
			$moz_generation = $event->getAttribute(ICS::X_MOZ_GENERATION);
			if (isset($moz_generation)) $vevent->add(ICS::X_MOZ_GENERATION, $moz_generation);
			// Gestion des pièces jointes
			$attachments = $event->attachments;
			if (isset($attachments)
					&& is_array($attachments)
					&& count($attachments) > 0) {
				foreach ($attachments as $attachment) {
					$params = array();
					if ($attachment->type == Attachment::TYPE_URL) {
						// Pièce jointe URL
						$data = $attachment->data;
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
		return $vevent;
	}

	/**
	 * Ajoute le timezone au VCalendar
	 * @param VObject\Component $vcalendar
	 * @param string $timezone
	 */
	private static function generationTimezone(VObject\Component $vcalendar, $timezone) {
		if (!ConfigMelanie::ICS_ADD_TIMEZONE) return;

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
}