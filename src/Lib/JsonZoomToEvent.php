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

use LibMelanie\Api\Defaut\Event;

/**
 * Class de génération d'un événement depuis un JSON venant de Zoom.us
 * Méthodes Statiques
 *
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage Lib
 *            
 */
class JsonZoomToEvent {

  /**
   * Constructeur privé pour ne pas instancier la classe
   */
  private function __construct() {}

  /**
   * Génére un Event basé sur le JSON Zoom passé en paramètre
   * L'évènement doit être de type Event de la librairie LibM2
   *
   * @param string $json
   * @param Event $event
   * 
   * @return string $json
   */
  public static function Convert($json, $event) {
    $data = json_decode($json, true);
    
    if (isset($data['start_time'])) {
      $event->dtstart = new \DateTime($data['start_time'], new \DateTimeZone('UTC'));

      if (isset($data['timezone'])) {
        // Set the timezone to the specified timezone in the data
        $event->dtstart->setTimezone(new \DateTimeZone($data['timezone']));
        $event->timezone = $data['timezone'];
      }
    }
    
    if (isset($data['duration'])) {
      $event->dtend = clone $event->dtstart;
      $event->dtend->modify("+{$data['duration']} minutes");
    }
    
    if (isset($data['topic'])) {
      $event->title = $data['topic'];
    }

    if (isset($data['id'])) {
      $event->zoom_meeting_id = $data['id'];
    }
    
    return $event;

  }

  /**
   * To set a meeting's start time in GMT, use the yyyy-MM-ddTHH:mm:ssZ date-time format
   *
   * @param Event $event
   * @return string|null
   */
  protected static function GetStartTime($event) {
    $startTime = $event->dtstart;
    if (isset($startTime)) {
      // Convert to UTC timezone
      $startTime->setTimezone(new \DateTimeZone('UTC'));
      return $startTime->format('Y-m-d\TH:i:s\Z');
    }
    return null;
  }

  /**
   * Get the duration of the event in minutes
   * 
   * @param Event $event
   * @return int|null
   */
  protected static function GetDuration($event) {
    $startTime = $event->dtstart;
    $endTime = $event->dtend;
    if (isset($startTime) && isset($endTime)) {
      // Convert to UTC timezone
      $startTime->setTimezone(new \DateTimeZone('UTC'));
      $endTime->setTimezone(new \DateTimeZone('UTC'));

      // Check if the start time is before the end time
      if ($startTime > $endTime) {
        return null; // Invalid event duration
      }

      // Calculate the duration in minutes
      $duration = $endTime->getTimestamp() - $startTime->getTimestamp();
      return $duration / 60; // Convert to minutes
    } else {
      return null;
    }
  }
}