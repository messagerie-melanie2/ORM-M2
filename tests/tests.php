<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * Ce fichier est un exemple d'utilisation
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
 * 
 * @package Librairie Mélanie2
 * @subpackage Tests
 * @author PNE Messagerie/Apitech
 */
include_once 'includes/libm2.php';

use LibMelanie\Objects\UserMelanie;
use LibMelanie\Objects\CalendarMelanie;
use LibMelanie\Objects\EventMelanie;
use LibMelanie\Objects\HistoryMelanie;
use LibMelanie\Config\ConfigMelanie;

/**
 * Prompt pour password
 * 
 * @param string $prompt          
 * @return void|string
 */
function prompt_silent($prompt = "Enter Password:") {
  if (preg_match('/^win/i', PHP_OS)) {
    $vbscript = sys_get_temp_dir() . 'prompt_password.vbs';
    file_put_contents($vbscript, 'wscript.echo(InputBox("' . addslashes($prompt) . '", "", "password here"))');
    $command = "cscript //nologo " . escapeshellarg($vbscript);
    $password = rtrim(shell_exec($command));
    unlink($vbscript);
    return $password;
  } else {
    $command = "/usr/bin/env bash -c 'echo OK'";
    if (rtrim(shell_exec($command)) !== 'OK') {
      trigger_error("Can't invoke bash");
      return;
    }
    $command = "/usr/bin/env bash -c 'read -s -p \"" . addslashes($prompt) . "\" mypassword && echo \$mypassword'";
    $password = rtrim(shell_exec($command));
    echo "\n";
    return $password;
  }
}

do {
  echo "Quel est l'uid de l'utilisateur ? ";
  fscanf(STDIN, "%s", $user_uid);
  if (!isset($user_uid) || $user_uid == "") {
    echo "Erreur dans l'uid utilisateur\r\n";
    exit(1);
  }
  
  $user = new UserMelanie($user_uid);
  
  $password = prompt_silent("Quel est le mot de passe ? ");
  if (!$user->authentification($password)) {
    echo "\r\nErreur de mot de passe\r\n";
    exit(1);
  }
  
  $defaultCalendar = $user->getDefaultCalendar();
  if (isset($defaultCalendar->calendar_id)) {
    echo "Calendrier par défaut pour l'utilisateur $user_uid : \r\n";
    echo "calendar_id: " . $defaultCalendar->calendar_id . "\r\ncalendar_name: " . $defaultCalendar->calendar_name . "\r\nperm_calendar: " . $defaultCalendar->perm_calendar . "\r\ndroit ecriture ? " . ($defaultCalendar->asRight("write") ? "oui" : "non") . "\r\n";
  } else {
    echo "Pas de calendrier défini par défaut\r\n";
    $calendars = $user->getUserCalendars();
    if (isset($calendars) && is_array($calendars)) {
      $defaultCalendar = $calendars[0];
      echo "Calendrier par défaut pour l'utilisateur $user_uid : \r\n";
      echo "calendar_id: " . $defaultCalendar->calendar_id . "\r\ncalendar_name: " . $defaultCalendar->calendar_name . "\r\nperm_calendar: " . $defaultCalendar->perm_calendar . "\r\ndroit ecriture ? " . ($defaultCalendar->asRight("write") ? "oui" : "non") . "\r\n";
    } else {
      echo "Pas de calendrier par défaut\r\n";
    }
  }
  
  echo "\r\n\r\n";
  $calendarsList = $user->getSharedCalendars();
  echo "Liste des calendriers pour l'utilisateur $user_uid : \r\n";
  
  foreach ($calendarsList as $calendar) {
    echo "calendar_id: " . $calendar->calendar_id . "\r\ncalendar_name: " . $calendar->calendar_name . "\r\nperm_calendar: " . $calendar->perm_calendar . "\r\ndroit ecriture ? " . ($calendar->asRight("write") ? "oui" : "non") . "\r\n";
  }
  
  echo "\r\n";
  
  echo "Quel est l'uid du calendrier à afficher ? ";
  fscanf(STDIN, "%s", $calendar_id);
  if (!isset($calendar_id) || $calendar_id == "") {
    echo "Erreur dans le nom du calendrier\r\n";
    exit(1);
  }
  
  $calendar = new CalendarMelanie();
  $calendar->calendar_id = $calendar_id;
  $calendar->user_uid = $user_uid;
  $calendar->load();
  // echo "calendar_id: ".$calendar->calendar_id . "\r\ncalendar_name: " . $calendar->calendar_name. "\r\nperm_calendar: " . $calendar->perm_calendar . "\r\ndroit ecriture ? " . ($calendar->asRight("write") ? "oui" : "non") . "\r\n";
  echo var_export($calendar, true);
  
  echo "Afficher les évènements du calendrier [O/N] ? ";
  fscanf(STDIN, "%c", $rep);
  if (!isset($rep) || $rep == "") {
    echo "Erreur dans la réponse\r\n";
    exit(1);
  }
  if ($rep == 'O') {
    echo "Quel date de debut ? ";
    fscanf(STDIN, "%s", $event_start);
    if (!isset($event_start) || $event_start == "") {
      echo "Erreur dans event_start\r\n";
      exit(1);
    }
    
    echo "Quel date de fin ? ";
    fscanf(STDIN, "%s", $event_end);
    if (!isset($event_end) || $event_end == "") {
      echo "Erreur dans event_end\r\n";
      exit(1);
    }
    
    $eventsList = $calendar->getRangeEvents($event_start, $event_end);
    foreach ($eventsList as $event) {
      echo "uid: " . $event->uid . " - title: " . $event->title . " - organisateur: " . $event->organizer_uid . "\r\n";
    }
  }
  
  echo "Modifier un évènement [O/N] ? ";
  fscanf(STDIN, "%c", $rep);
  if (!isset($rep) || $rep == "") {
    echo "Erreur dans la réponse\r\n";
    exit(1);
  }
  if ($rep == 'O' || $rep == 'o') {
    echo "Quel est l'uid de l'évènement à afficher ? ";
    fscanf(STDIN, "%s", $event_uid);
    if (!isset($event_uid) || $event_uid == "") {
      echo "Erreur dans l'uid\r\n";
      exit(1);
    }
    
    $event = new EventMelanie();
    $event->event_uid = $event_uid;
    $event->calendar_id = $calendar_id;
    $event->load();
    echo var_export($event, true);
    echo "\r\n";
    
    $history = new HistoryMelanie();
    $history->history_action = ConfigMelanie::HISTORY_ADD;
    $history->object_uid = ConfigMelanie::CALENDAR_PREF_SCOPE . ":" . $event->calendar_id . ":" . $event->event_uid;
    $history->load();
    echo var_export($history, true);
    echo "\r\n";
    
    $history = new HistoryMelanie();
    $history->history_action = ConfigMelanie::HISTORY_MODIFY;
    $history->object_uid = ConfigMelanie::CALENDAR_PREF_SCOPE . ":" . $event->calendar_id . ":" . $event->event_uid;
    $history->load();
    echo var_export($history, true);
    echo "\r\n";
    
    do {
      echo "Quel champ voulez vous modifier ? ";
      fscanf(STDIN, "%s", $field);
      if (!isset($field) || $field == "") {
        echo "Erreur dans le champ\r\n";
        exit(1);
      }
      echo "\r\n";
      echo "Quel nouvelle valeur voulez vous donner [$field] ? ";
      fscanf(STDIN, "%s", $value);
      if (!isset($value) || $value == "") {
        echo "Erreur dans la valeur\r\n";
        exit(1);
      }
      $event->$field = $value;
      echo "\r\n";
      echo var_export($event, true);
      echo "\r\n";
      echo "Modifier un nouveau champ [O/N] ? ";
      fscanf(STDIN, "%c", $rep);
      if (!isset($rep) || $rep == "") {
        echo "Erreur dans la réponse\r\n";
        exit(1);
      }
      echo "\r\n";
    } while ( $rep == 'O' );
    
    echo "\r\n";
    $event->save();
  }
  
  echo "\r\n";
} while ( 1 );