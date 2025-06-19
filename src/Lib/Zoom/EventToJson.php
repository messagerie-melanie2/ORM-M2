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
namespace LibMelanie\Lib\Zoom;

use LibMelanie\Api\Defaut\Event;

/**
 * Class de génération d'un JSON compatible Zoom.us en fonction de l'objet évènement
 * Méthodes Statiques
 *
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage Lib
 *            
 */
class EventToJson {

  /**
   * Agenda défini dans Zoom (donné par défaut)
   * @var string $agenda
   */
  protected static $agenda = 'Bnum';

  /**
   * Type de l'évènement Zoom
   * @var int $type
   */
  protected static $type = 2;

  /**
   * Constructeur privé pour ne pas instancier la classe
   */
  private function __construct() {}

  /**
   * Génére un JSON compatible Zoom.us en fonction de l'évènement passé en paramètre
   * L'évènement doit être de type Event de la librairie LibM2
   *
   * @param Event $event
   * 
   * @return string $json
   */
  public static function Convert($event) {
    $json = [
      'agenda'            => self::$agenda,
      'start_time'        => self::GetStartTime($event),
      'duration'          => self::GetDuration($event),
      'timezone'          => $event->timezone,
      'default_password'  => false,
      'pre_schedule'      => false,
      'topic'             => $event->title,
      'type'              => self::$type,
    ];
    return json_encode($json);
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