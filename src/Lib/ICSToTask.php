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

use LibMelanie\Api\Defaut\Task;
use LibMelanie\Api\Defaut\User;
use LibMelanie\Api\Defaut\Taskslist;
use LibMelanie\Config\Config;
use Sabre\VObject;

// Utilisation de la librairie Sabre VObject pour la conversion ICS
@include_once 'vendor/autoload.php';

/**
 * Class de génération de l'évènement en fonction de l'ICS
 * Méthodes Statiques
 *
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage Lib
 *
 */
class ICSToTask {
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
	 *  Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() { }

	/**
	 * Génére une tâche mélanie2 en fonction de l'ics passé en paramètre
	 * La tâche doit être de type Task de la librairie LibM2
	 * @param string $ics
	 * @param Task $task
	 * @param Taskslist $taskslist
	 * @param User $user
	 * @return Task
	 */
	public static function Convert($ics, $task, $taskslist = null, $user = null) {
		$vcalendar = VObject\Reader::read($ics);
		// Gestion du timezone
		if (isset($user)) {
		  $timezone = $user->getTimezone();
		}
		elseif (isset($calendar)) {
		  $timezone = $taskslist->getTimezone();
		}
		if (empty($timezone)) {
		  $timezone = Config::get(Config::CALENDAR_DEFAULT_TIMEZONE);
		}
		foreach($vcalendar->VTODO as $vtodo) {
			// UID
			if (!isset($vtodo->UID)) continue;
			else $task->uid = $vtodo->UID;
			// Owner
			if (empty($task->owner)) {
			  $task->owner = isset($user) && isset($user->uid) ? $user->uid : $task->owner;
			}
			// SUMMARY
			if (isset($vtodo->SUMMARY)) $task->name = $vtodo->SUMMARY->getValue();
			else $task->name = '';
			// DESCRIPTION
			if (isset($vtodo->DESCRIPTION)) $task->description = $vtodo->DESCRIPTION->getValue();
			else $task->description = null;
			// CATEGORY
			if (isset($vtodo->CATEGORIES)) {
			  $categories = [];
			  foreach($vtodo->CATEGORIES as $category) {
			    $categories[] = $category->getValue();
			  }
			  $task->category = implode(',', $categories);
			}
			else $task->category = '';
			// VALARM
			if (isset($vtodo->VALARM)) {
				$alarmDate = $vtodo->VALARM->getEffectiveTriggerTime();
				if (isset($startDate)) {
					$task->alarm = ($startDate->format("U") - $alarmDate->format("U")) / 60;
					if ($task->alarm === 0) {
						$task->alarm = 1;
					}
				}
				// X MOZ LASTACK
				if (isset($vtodo->{ICS::X_MOZ_LASTACK})) {
					$task->setAttribute(ICS::X_MOZ_LASTACK, $vtodo->{ICS::X_MOZ_LASTACK}->getValue());
				} else {
					$task->deleteAttribute(ICS::X_MOZ_LASTACK);
				}
				// X MOZ SNOOZE TIME
				if (isset($vtodo->{ICS::X_MOZ_SNOOZE_TIME})) {
					$task->setAttribute(ICS::X_MOZ_SNOOZE_TIME, $vtodo->{ICS::X_MOZ_SNOOZE_TIME}->getValue());
				} else {
					$task->deleteAttribute(ICS::X_MOZ_SNOOZE_TIME);
				}
			} else {
				$task->alarm = 0;
			}
			// SEQUENCE
			if (isset($vtodo->SEQUENCE)) {
        		$task->setAttribute(ICS::SEQUENCE, $vtodo->SEQUENCE->getValue());
			}
			// X MOZ GENERATION
			if (isset($vtodo->{ICS::X_MOZ_GENERATION})) {
			  	$task->setAttribute(ICS::X_MOZ_GENERATION, $vtodo->{ICS::X_MOZ_GENERATION}->getValue());
			}
			// DTSTAMP
			if (isset($vtodo->DTSTAMP)) $task->modified = $vtodo->DTSTAMP->getDateTime()->format('U');
			else if (isset($vtodo->{ICS::LAST_MODIFIED})) $task->modified = $vtodo->{ICS::LAST_MODIFIED}->getDateTime()->format('U');
			else if (isset($vtodo->CREATED)) $task->modified = $vtodo->CREATED->getDateTime()->format('U');
			else $task->modified = time();

			// DTSTART
			if (isset($vtodo->DTSTART)) $task->start = $vtodo->DTSTART->getDateTime()->format('U');
			else $task->start = null;
			// DUE
			if (isset($vtodo->DUE)) $task->due = $vtodo->DUE->getDateTime()->format('U');
			else $task->due = null;
			// COMPLETED
			if (isset($vtodo->COMPLETED)) {
				$task->completed_date = $vtodo->COMPLETED->getDateTime()->format('U');			
			}
			else {
				$task->completed_date = null;
			}
			// Parent
			if (isset($vtodo->{ICS::RELATED_TO})) $task->parent = $vtodo->{ICS::RELATED_TO};
			else $task->parent = null;
			// Percent complete
			if (isset($vtodo->{ICS::PERCENT_COMPLETE})) {
				$task->percent_complete = $vtodo->{ICS::PERCENT_COMPLETE}->getValue();
				if ($task->percent_complete == 100) {
					$task->completed = 1;
				}
				else {
					$task->completed = 0;
				}
			}
			else {
				$task->percent_complete = null;
				$task->completed = 0;
			}

			// CLASS
			if (isset($vtodo->CLASS)) {
				switch ($vtodo->CLASS->getValue()) {
					case ICS::CLASS_PUBLIC:
					default:
						$task->class = Task::CLASS_PUBLIC;
						break;
					case ICS::CLASS_CONFIDENTIAL:
						$task->class = Task::CLASS_CONFIDENTIAL;
						break;
					case ICS::CLASS_PRIVATE:
						$task->class = Task::CLASS_PRIVATE;
						break;
				}
			} else $task->class = Task::CLASS_PUBLIC;
			// STATUS
			if (isset($vtodo->STATUS)) {
				switch ($vtodo->STATUS->getValue()) {
					case ICS::STATUS_CANCELLED:
						$task->status = Task::STATUS_CANCELLED;
						break;
					case ICS::STATUS_COMPLETED:
					  $task->status = Task::STATUS_COMPLETED;
					  break;
				  case ICS::STATUS_IN_PROCESS:
				    $task->status = Task::STATUS_IN_PROCESS;
				    break;
				  default:
			    case ICS::STATUS_NEEDS_ACTION:
			      $task->status = Task::STATUS_NEEDS_ACTION;
			      break;
				}
			} else $task->status = Task::STATUS_NEEDS_ACTION;
			// Gestion de la priorité
			if (isset($vtodo->PRIORITY)) {
			  $priority = $vtodo->PRIORITY->getValue();
        if ($priority === 1 || $priority === 2) {
          $task->priority = Task::PRIORITY_VERY_HIGH;
        }
        elseif ($priority === 3 || $priority === 4) {
          $task->priority = Task::PRIORITY_HIGH;
        }
        elseif ($priority === 5) {
          $task->priority = Task::PRIORITY_NORMAL;
        }
        elseif ($priority === 6 || $priority === 7) {
          $task->priority = Task::PRIORITY_LOW;
        }
        elseif ($priority === 8 || $priority === 9) {
          $task->priority = Task::PRIORITY_VERY_LOW;
        }
        else {
          $task->priority = Task::PRIORITY_NO;
        }
			}
			else $task->priority = Task::PRIORITY_NO;
      break;
		}
		// Retourne l'évènement généré
		return $task;
	}
}