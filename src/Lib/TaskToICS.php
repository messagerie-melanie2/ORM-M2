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

use LibMelanie\Api\Defaut\User;
use LibMelanie\Api\Defaut\Task;
use LibMelanie\Api\Defaut\Taskslist;
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
class TaskToICS {
	/**
	 * Identifiant de l'outil utilisant l'ICS (pour la génération)
	 * @var string
	 */
	const PRODID = '-//Groupe Messagerie MTES/ORM LibMCE';
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
	 * Génére un ICS en fonction de la tâche passée en paramètre
	 * La tâche doit être de type Task de la librairie LibM2
	 * @param Task $task
	 * @param Taskslist $taskslist
	 * @param User $user
	 * @return string $ics
	 */
	public static function Convert($task, $taskslist = null, User $user = null) {
	  M2Log::Log(M2Log::LEVEL_DEBUG, "TaskToICS->Convert()");
		$vcalendar = new VObject\Component\VCalendar();
		$vtodo = $vcalendar->add('VTODO');
		// PRODID et Version
		$vcalendar->PRODID = self::PRODID;
		$vcalendar->VERSION = self::VERSION;
		// UID
		$vtodo->UID = $task->uid;
		// Génération de l'objet vtodo
		$vtodo = self::getVtodoFromTask($vtodo, $task, $taskslist, $user);
		return $vcalendar->serialize();
	}

	/**
	 * Méthode permettant de générer l'objet VTODO à partir des données de l'évènement
	 * Cette méthode est séparée pour être appelé plusieurs fois, dans le cas où l'évènement a des exceptions
	 * @param VObject\Component $vtodo
	 * @param Task $task
	 * @param Taskslist $taskslist
	 * @param User $user
	 * @return VTodo
	 */
	private static function getVtodoFromTask(VObject\Component $vtodo, $task, $taskslist = null, $user = null) {
	  	M2Log::Log(M2Log::LEVEL_DEBUG, "TaskToICS->getVtodoFromTask()");
	  	// Timezone
		if (isset($user)) {
		  	$timezone = $user->getTimezone();
		}
	  	elseif (isset($taskslist)) {
			$timezone = $taskslist->getTimezone();
		}
		if (empty($timezone)) {
		  	$timezone = Config::get(Config::CALENDAR_DEFAULT_TIMEZONE);
		}
		M2Log::Log(M2Log::LEVEL_DEBUG, "TaskToICS->getVtodoFromTask() timezone : " . $timezone);
		// Class
		if (isset($task->class)) {
			switch ($task->class) {
				case Task::CLASS_CONFIDENTIAL:
					$vtodo->CLASS = ICS::CLASS_CONFIDENTIAL;
					break;
				case Task::CLASS_PRIVATE:
					$vtodo->CLASS = ICS::CLASS_PRIVATE;
					break;
				case Task::CLASS_PUBLIC:
				default:
				  $vtodo->CLASS = ICS::CLASS_PUBLIC;
				  break;
			}
		} else $vtodo->CLASS = ICS::CLASS_PUBLIC;

		// Status
		if (isset($task->status)) {
		  switch ($task->status) {
	      case Task::STATUS_CANCELLED:
	        $vtodo->STATUS = ICS::STATUS_CANCELLED;
	        break;
	      case Task::STATUS_COMPLETED:
	        $vtodo->STATUS = ICS::STATUS_COMPLETED;
	        break;
	      case Task::STATUS_IN_PROCESS:
	        $vtodo->STATUS = ICS::STATUS_IN_PROCESS;
	        break;
	      default:
	      case Task::STATUS_NEEDS_ACTION:
	        $vtodo->STATUS = ICS::STATUS_NEEDS_ACTION;
	        break;
		  }
		} else $vtodo->STATUS = ICS::STATUS_NEEDS_ACTION;

		// DTSTAMP
		if (isset($task->modified)) {
 		  $dateTime = new \DateTime('@'.$task->modified, new \DateTimeZone($timezone));
		  $dateTime->setTimezone(new \DateTimeZone('UTC'));
		  $date = $dateTime->format('Ymd\THis\Z');
		  $vtodo->add(ICS::DTSTAMP, $date);
		  $vtodo->add(ICS::LAST_MODIFIED, $date);
		  $vtodo->add(ICS::CREATED, $date);
		}

		// DTSTART
		if (isset($task->start)) {
		  $dateTime = new \DateTime('@'.$task->start, new \DateTimeZone($timezone));
		  $vtodo->add(ICS::DTSTART, $dateTime->format('Ymd\THis\Z'));
		}
		// DUE
		if (isset($task->due)) {
		  $dateTime = new \DateTime('@'.$task->due, new \DateTimeZone($timezone));
		  $vtodo->add(ICS::DUE, $dateTime->format('Ymd\THis\Z'));
		}
		// COMPLETED
		if (isset($task->completed_date)) {
		  $dateTime = new \DateTime('@'.$task->completed_date, new \DateTimeZone($timezone));
		  $vtodo->add(ICS::COMPLETED, $dateTime->format('Ymd\THis\Z'));
		}

		if (($task->class == Task::CLASS_PRIVATE
				|| $task->class == Task::CLASS_CONFIDENTIAL)
				&& $task->owner != $user->uid
				&& isset($taskslist)
				&& $taskslist->owner !=  $user->uid
		    	&& !$taskslist->asRight(Config::get(Config::PRIV))) {
			$vtodo->SUMMARY = 'Événement privé';
		} else {
			// Titre
			if (isset($task->name) && $task->name != "") $vtodo->SUMMARY = $task->name;
			// Catégories
			if (isset($task->category) && $task->category != "") {
			  $categories = explode(',', $task->category);
			  foreach ($categories as $category) {
			    $vtodo->add(ICS::CATEGORIES, $category);
			  }
			}
			// Description
			if (isset($task->description) && $task->description != "") $vtodo->DESCRIPTION = $task->description;
			// Percent complete
			if (isset($task->percent_complete)) $vtodo->add(ICS::PERCENT_COMPLETE, $task->percent_complete);
      // Priority
      if (isset($task->priority)) {
        switch ($task->priority) {
          case Task::PRIORITY_VERY_HIGH:
            $vtodo->PRIORITY = 1;
            break;
          case Task::PRIORITY_HIGH:
            $vtodo->PRIORITY = 3;
            break;
          case Task::PRIORITY_NORMAL;
            $vtodo->PRIORITY = 5;
            break;
          case Task::PRIORITY_LOW;
            $vtodo->PRIORITY = 7;
            break;
          case Task::PRIORITY_VERY_LOW;
            $vtodo->PRIORITY = 9;
            break;
          default:
            $vtodo->PRIORITY = 0;
            break;
        }
      }
      // Parent
      if (isset($task->parent)) {
        $vtodo->{ICS::RELATED_TO} = $task->parent;
      }
			// Alarm
			if (isset($task->alarm) && $task->alarm != 0) {
				$valarm = $vtodo->add('VALARM');
				$valarm->TRIGGER = self::formatAlarm($task->alarm);
				$valarm->ACTION = ICS::ACTION_DISPLAY;
				// Attributs sur l'alarme
				$x_moz_lastack = $task->getAttribute(ICS::X_MOZ_LASTACK);
				if (isset($x_moz_lastack)) $vtodo->{ICS::X_MOZ_LASTACK} = $x_moz_lastack;
				$x_moz_snooze_time = $task->getAttribute(ICS::X_MOZ_SNOOZE_TIME);
				if (isset($x_moz_snooze_time)) $vtodo->{ICS::X_MOZ_SNOOZE_TIME} = $x_moz_snooze_time;
			}
			// Taskslist infos
			if (isset($taskslist)) {
			  $vtodo->add(ICS::X_CALDAV_CALENDAR_ID, $taskslist->id);
			  $vtodo->add(ICS::X_CALDAV_CALENDAR_OWNER, $taskslist->owner);
			}
			// Sequence
			$sequence = $task->getAttribute(ICS::SEQUENCE);
			if (isset($sequence)) $vtodo->SEQUENCE = $sequence;
			// X Moz Generation
			$moz_generation = $task->getAttribute(ICS::X_MOZ_GENERATION);
			if (isset($moz_generation)) $vtodo->add(ICS::X_MOZ_GENERATION, $moz_generation);
		}
		return $vtodo;
	}
	
	/**
	 * Formatte l'alarme en minutes en un trigger ICS
	 * @param int $alarm En minutes
	 * @return string
	 */
	private static function formatAlarm($alarm) {
		if ($alarm < 0) {
			$trigger = "P";
			$alarm = - $alarm;
		}
		else {
			$trigger = "-P";
		}
		
		// Nombre de semaines, 10080 minutes
		if ($alarm >= 10080) {
			$nb_weeks = (int)($alarm / 10080);
			$alarm -= $nb_weeks * 10080;
			$trigger .= $nb_weeks."W";
		}
		// Nombre de jours, 1440 minutes
		if ($alarm >= 1440) {
			$nb_days = (int)($alarm / 1440);
			$alarm -= $nb_days * 1440;
			$trigger .= $nb_days."D";
		}
		if ($alarm > 0) {
			$trigger .= "T";
		}
		// Nombre d'heures, 60 minutes
		if ($alarm >= 60) {
			$nb_hours = (int)($alarm / 60);
			$alarm -= $nb_hours * 60;
			$trigger .= $nb_hours."H";
		}
		// Nombre de minutes
		if ($alarm > 0) {
			$trigger .= $alarm."M";
		}
		return $trigger;
	}

	/**
	 * Ajoute le timezone au VCalendar
	 * @param VObject\Component $vcalendar
	 * @param string $timezone
	 */
	private static function generationTimezone(VObject\Component $vcalendar, $timezone) {
	  if (!Config::get(Config::ICS_ADD_TIMEZONE)) return;

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