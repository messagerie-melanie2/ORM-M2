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

// Configuration du nom de l'application pour l'ORM
if (!defined('CONFIGURATION_APP_LIBM2')) {
  define('CONFIGURATION_APP_LIBM2', 'roundcube');
}

// // Définition des inclusions
set_include_path(__DIR__.'/..');
require_once 'includes/libm2.php';
require_once '../../../digital-workplace/github/Roundcube-Mel/vendor/autoload.php';

use LibMelanie\Mail\Mail;

$ical = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Groupe Messagerie MTES/ORM LibMCE
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
END:VEVENT
END:VCALENDAR";

// if (Mail::mail('thomas.test1@developpement-durable.gouv.fr', 'Test depuis l\'ORM', '<p>Ce message est envoyé depuis l\'ORM Mélanie2</p>', null, null, 'bnum')) {
if (Mail::Send('bnum', 'thomas.test1@developpement-durable.gouv.fr', 'Test depuis l\'ORM', '<p>Ce message est envoyé depuis l\'ORM Mélanie2</p>', null, null, null, null, null, null, null, null, $ical)) {
  echo "Mail envoyé\r\n\r\n";
} else {
  echo "Erreur lors de l'envoi du mail : " . Mail::getLastError() . "\r\n\r\n";
}

echo "#### 1: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "#### Cycles: ".gc_collect_cycles()." ######\r\n";
echo "#### 2: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "#### Peak: ".(memory_get_peak_usage(true)/1024/1024) . ' MiB'." ######\r\n";

$temps_fin = microtime_float();
echo "#####################################\r\n";
echo "DUREE EXECUTION: ".round($temps_fin - $temps_debut, 4)."\r\n";
echo "#####################################\r\n";