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

use LibMelanie\Api\Defaut\Workspaces\Hashtag;
use LibMelanie\Api\Mel\User;
use LibMelanie\Api\Mel\Workspace;
use LibMelanie\Api\Mel\Workspaces\Share;
use LibMelanie\Log\M2Log;

$log = function ($message) {
	echo "[LibM2] $message \r\n";
};
M2Log::InitDebugLog($log);
M2Log::InitErrorLog($log);

echo "########################\r\n";

//ConfigSQL::setCurrentBackend(ConfigSQL::$HORDE_1);

// Définition de l'utilisateur
$user = new User();
$user->uid = 'thomas.payen';

// $workspace = new Workspace($user);
// $workspace->uid = "86ffb2fcdf76c9b183e6e1ad39e6a31b603e54ad";
// if ($workspace->load()) {
// 	// $workspace->title = "Mon titre modifié avec des droits";
// 	// $workspace->modified = new DateTime('now');

// 	// $shares = [];
// 	// $share = new Share($workspace);
// 	// $share->user = $user->uid;
// 	// $share->rights = Share::RIGHT_OWNER;
// 	// $shares[] = $share;
// 	// $share = new Share($workspace);
// 	// $share->user = 'thomas.test1';
// 	// $share->rights = Share::RIGHT_WRITE;
// 	// $shares[] = $share;
// 	// $share = new Share($workspace);
// 	// $share->user = 'thomas.test2';
// 	// $share->rights = Share::RIGHT_WRITE;
// 	// $shares[] = $share;
// 	// $workspace->shares = $shares;

// 	var_export($workspace->hashtags);

// 	$workspace->hashtags = ['Interministériel'];
// 	$workspace->save();
// }

// $workspaces = (new Workspace())->listPublicsWorkspaces('modified', false, 5, 5);
// $workspaces = $user->getSharedWorkspaces('modified', false, 2, 1);

// // // Lister les workspaces de l'utilisateur
// // $workspaces = $user->getSharedWorkspaces();

// // var_export($workspaces);

// foreach ($workspaces as $workspace) {
// 	echo $workspace->modified . ': ' . $workspace->title . ' / ' . $workspace->description . " \r\n\r\n";
// }

$hashtags = (new Hashtag())->getList();

$hashtags = (new Hashtag())->getList(null, null, null, "label");

$hash = new Hashtag();
$hash->label = "a%";
$operators = ["label" => \LibMelanie\Config\MappingMce::like];
$hashtags = $hash->getList(null, null, $operators, "label", true, 5);


foreach ($hashtags as $hashtag) {
	echo $hashtag->label . " \r\n\r\n";
}

// if (count($workspaces) === 0) {
// 	$workspace = new Workspace($user);
// 	$workspace->uid = uniqid(md5(time()), true);
// 	$workspace->title = 'Mon autre espace de travail';
// 	$workspace->description = 'C\'est un autre test pour voir';
// 	$workspace->creator = $user->uid;
// 	$workspace->created = new DateTime('now');
// 	$workspace->modified = new DateTime('now');
// 	$workspace->ispublic = false;
// 	var_export($workspace);
// 	$workspace->save();
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
