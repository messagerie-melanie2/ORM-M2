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
 * @subpackage Tests
 * @author PNE Messagerie/Apitech
 *
 */

var_dump(gc_enabled());
ini_set('zend.enable_gc', 1);
var_dump(gc_enabled());

$temps_debut = microtime_float();
declare(ticks = 1);

function microtime_float() {
	return array_sum(explode(' ', microtime()));
}

// PAMELA - Application name configuration for ORM Mél
if (! defined('CONFIGURATION_APP_LIBM2')) {
    define('CONFIGURATION_APP_LIBM2', 'roundcube');
}

require_once 'includes/libm2.php';

use LibMelanie\Api\Melanie2\User;

$users = [
    "numero.six",
    "thomas.payen",
    "julien.delamarre.i",
    "thomas.test1",
    "thomas.test2",
    "arnaud.goubier.i",
    "lili.rush",
    "arnaud.test2",
    "laurent.beroujon",
    "arnaud.test1",
    "thomas.test3",
    "philippe.martinak2",
    "elisabeth.houot",
    "julien.test2",
    "nina.myers",
    "thomas.test5",
    "rachid.ouildah",
    "philippe.martinak",
    "thomas.test4",
    "julien.test1",
    "aeryn.sun",
    "test.mantis4423",
    "julien.test3",
    
];

// Récupération de l'utilisateur
$username = $users[mt_rand(0,sizeof($users)-1)];
//$username = $users[0];
$pid = getmypid();

echo "performances.php [$pid] ### DEBUT DU SCRIPT \r\n";
echo "performances.php [$pid] Utilisateur : $username \r\n";

// Utilisateur
$user = new User();
$user->uid = $username;

// Boucle
for ($i=0; $i < 1; $i++) { 
    $calendars = $user->getSharedCalendars();
    // Parcours la liste des calendriers de l'utilisateur
    foreach ($calendars as $calendar) {
        //echo "performances.php [$pid] Calendar : ".$calendar->id." \r\n";
        $events = $calendar->getAllEvents();
        //echo "performances.php [$pid] Events : ".count($events)." \r\n\r\n";
    }
}

echo "\r\n\r\n";
echo "performances.php [$pid] #### 1: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "performances.php [$pid] #### Cycles: ".gc_collect_cycles()." ######\r\n";
echo "performances.php [$pid] #### 2: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "performances.php [$pid] #### Peak: ".(memory_get_peak_usage(true)/1024/1024) . ' MiB'." ######\r\n";

$temps_fin = microtime_float();
echo "performances.php [$pid] #####################################\r\n";
echo "performances.php [$pid] DUREE EXECUTION: ".round($temps_fin - $temps_debut, 4)."\r\n";
echo "performances.php [$pid] #####################################\r\n";

