<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * Ce fichier est un exemple d'utilisation
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
 *
 * @package Librairie Mélanie2
 * @subpackage Example
 * @author PNE Messagerie/Apitech
 *
 */

// Configuration du nom de l'application pour l'ORM
if (!defined('CONFIGURATION_APP_LIBM2')) {
	define('CONFIGURATION_APP_LIBM2', 'roundcube');
}

// Inclure le fichier includes.php
include_once 'includes/libm2.php';

// Utilisation des namespaces
/*
 * Possibilité de faire
 * use LibMelanie\Api\Melanie2;
 * au lieu de
 * use LibMelanie\Api\Melanie2\User;
 *
 * Puis d'utiliser
 * $user = new Melanie2\User();
 * pour ne pas mélanger les classes
 *
 */
// API
use LibMelanie\Api\Melanie2\User;
use LibMelanie\Api\Melanie2\Event;
use LibMelanie\Api\Melanie2\Task;
use LibMelanie\Api\Melanie2\Contact;
use LibMelanie\Api\Melanie2\Addressbook;
use LibMelanie\Api\Melanie2\Calendar;
use LibMelanie\Api\Melanie2\Taskslist;
use LibMelanie\Api\Melanie2\Attendee;
use LibMelanie\Api\Melanie2\Recurrence;
// Log
use LibMelanie\Log\M2Log;
// Config
use LibMelanie\Config\ConfigMelanie;

// Configurer les LOG
$log = function ($message) {
	echo "[LibM2] $message \r\n";
};
M2Log::InitDebugLog($log);
M2Log::InitInfoLog($log);
M2Log::InitErrorLog($log);

// Définition d'un utilisateur
$user = new User();
$user->uid = 'julien.test1';

// Définition des conteneurs de l'utilisateur
$addressbook = new Addressbook($user);
$calendar = new Calendar($user);
$taskslist = new Taskslist($user);

// ID des conteneurs
$addressbook->id = 'julien.test1';
$calendar->id = 'julien.test1';
$taskslist->id = 'julien.test1';

// Charger les conteneur
$addressbook->load();
$calendar->load();
$taskslist->load();

// Vérifier les droits
echo $addressbook->asRight(ConfigMelanie::READ) ? "addressbook->read OK" : "addressbook->read NOK";
echo $calendar->asRight(ConfigMelanie::READ) ? "calendar->read OK" : "calendar->read NOK";
echo $taskslist->asRight(ConfigMelanie::READ) ? "taskslist->read OK" : "taskslist->read NOK";

// Récupération d'un objet
$event = new Event($user, $calendar);
$event->uid = 'c2926c54-17af-4753-a614-3589eca2644e';
$event->load();

$task = new Task($user, $taskslist);
$task->uid = 'c2926c54-17af-4753-a614-3589eca2644e';
$task->load();

$contact = new Contact($user, $calendar);
$contact->uid = 'c2926c54-17af-4753-a614-3589eca2644e';
$contact->load();

// Lister tous les objets d'un conteneur
$events = $calendar->getAllEvents();
$tasks = $taskslist->getAllTasks();
$contacts = $addressbook->getAllContacts();

// Modifier un objet
$event->title = "Mon titre d'évènement";
$event->save();
$task->name = "Mon nom de tâche";
$task->save();
$contact->name = "Mon nom de contact";
$contact->save();

// Gestion d'un evenement
$attendees = $event->attendees;
if ($attendees[0]->response == ConfigMelanie::ACCEPTED) echo "Attendee accepted";
$event->recurrence->days = [Recurrence::RECURDAYS_MONDAY, Recurrence::RECURDAYS_SUNDAY];


