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
include_once 'includes/libm2.php';

use LibMelanie\Log\M2Log;


$log = function ($message) {
	echo "[LibM2] $message \r\n";
};
M2Log::InitDebugLog($log);
M2Log::InitErrorLog($log);

echo "########################\r\n";

//ConfigSQL::setCurrentBackend(ConfigSQL::$HORDE_1);

// Définition de l'utilisateur
$user = new LibMelanie\Api\Mce\User();
$user->uid = 'thomas.payen';

// Créer une notification
$notification = new LibMelanie\Api\Mce\Notification($user);

$notification->category = "webconf";
$notification->title = "Thomas Payen vient de lancer une webconférence";
$notification->content = "Vous pouvez rejoindre directement la webconférence en cours via le lien disponible ci-dessous";
$notification->action = serialize([
	['href' => "/bureau/?_task=workspace&_action=workspace&_uid=gmcd-1",
	'text' => "Rejoindre",
	'title' => "Cliquez pour rejoindre la webconférence",]
]);

// Ajouter la notification au User
$user->addNotification($notification);

// // Supprimer une notification du User
// $user->deleteNotification('1c12646f-e3f3-4a53-b3c7-4411f21036a5');

// // Passer la notification du User en lu
// $user->readNotification("8cbf99e0-be9f-4305-b4ca-e5eff97ccfdd");

// // Récupérer toutes les notifications du User
// $notifications = $user->getNotifications();

// // Récupérer les dernières notifications du User
// $notifications = $user->getNotifications(1645786914);

// foreach ($notifications as $n) {
//   echo "$n\r\n";
// }

echo "\r\n\r\n";
echo "#### 1: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "#### Cycles: ".gc_collect_cycles()." ######\r\n";
echo "#### 2: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "#### Peak: ".(memory_get_peak_usage(true)/1024/1024) . ' MiB'." ######\r\n";

$temps_fin = microtime_float();
echo "#####################################\r\n";
echo "DUREE EXECUTION: ".round($temps_fin - $temps_debut, 4)."\r\n";
echo "#####################################\r\n";
