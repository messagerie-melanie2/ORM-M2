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

use LibMelanie\Api\Melanie2\User;
use LibMelanie\Api\Melanie2\Task;
use LibMelanie\Api\Melanie2\Taskslist;
use LibMelanie\Config\MappingMelanie;
use LibMelanie\Log\M2Log;

$log = function ($message) {
	echo "[LibM2] $message \r\n";
};
M2Log::InitDebugLog($log);
M2Log::InitErrorLog($log);

echo "########################\r\n";

$user = new User();
$user->uid = 'thomas.test1';
// $takslist = $user->getDefaultTaskslist();

// var_dump($takslist);

$result = [
		'added' => [],
		'modified' => [],
		'deleted' => [],
];

$task = new \LibMelanie\Api\Melanie2\Task();
$task->taskslist = 'thomas.payen';
foreach ($task->getList(['uid']) as $_task) {
	$result['added'][] = $_task->uid.'.ics';
}

var_export($result);

// $taskslists = $user->getSharedTaskslists();
// var_dump($taskslists);

// $defaultTaskslist = $user->getDefaultTaskslist();
// var_dump($defaultTaskslist);

// $taskslist = $taskslists[0];

// $list_uid = [	'482e6a72-4587-4dc9-8ef0-bfdf283ac870',
// 		'19c7acb6-cb53-419c-b9e3-9fb72c9cfee5',
// 		'81c307ba-e550-426a-8343-7751a33dccef',
// 		'ca52f55f-2bb9-4817-af89-dddfa712d14e',
// 		'5d1007da-d203-4531-9af3-13ffab2869e7'];

// $task = new Task($user, $taskslist);

// $task->name = '%tache%';
// //$task->uid = '8d3fc4c05552a753f42fa0b0af09602e';
// $task->uid = $list_uid;

// $tasks = $task->getList(
// 		['uid', 'name', 'taskslist', 'start'], /* Champs à retourner par la requête */
// 		'(#name# OR #uid#) AND #taskslist#', /* Filtre à appliquer à la requête */
// 		[ /* Liste des opérateurs à appliquer */
// 				'name' => MappingMelanie::like, /* Utilisation du LIKE */
// 				'uid' => MappingMelanie::eq /* Utilisation du différent (<>) */
// 		]
// );

// var_dump($tasks);

// $tasks = $taskslist->getAllTasks();
// var_dump(count($tasks));

// $tasks['8d3fc4c05552a753f42fa0b0af09602e']->description = 'C\'est ma description';
// $tasks['8d3fc4c05552a753f42fa0b0af09602e']->modified = time();
// $tasks['8d3fc4c05552a753f42fa0b0af09602e']->save();
// var_dump($tasks['8d3fc4c05552a753f42fa0b0af09602e']);

// $task = new Task($user, $taskslists[0]);
// $task->name = 'Ma tache a moi';
// $task->description = 'Et sa descriptiooooooon';
// $task->category = 'Une tache de test';
// $task->alarm = 5;
// $task->due = strtotime('2013-03-27 15:00:00');
// $task->modified = time();
// $task->priority = Task::PRIORITY_VERY_HIGH;
// $task->uid = date('YmdHis') . '.' . substr(str_pad(base_convert(microtime(), 10, 36), 16, uniqid(mt_rand()), STR_PAD_LEFT), -16) . '@zp.ac.melanie2.i2';
// $task->id = md5(uniqid(mt_rand(), true));
// $task->save();
// var_dump($task);

echo "#### 1: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "#### Cycles: ".gc_collect_cycles()." ######\r\n";
echo "#### 2: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "#### Peak: ".(memory_get_peak_usage(true)/1024/1024) . ' MiB'." ######\r\n";

$temps_fin = microtime_float();
echo "#####################################\r\n";
echo "DUREE EXECUTION: ".round($temps_fin - $temps_debut, 4)."\r\n";
echo "#####################################\r\n";
