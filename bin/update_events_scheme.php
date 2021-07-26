#!/usr/bin/env php
<?php
/**
* Ce script a pour but de mettre a jour les enregistrements d'événements sous l'ancien schema Horde vers le nouveau Mel
*
* L'ORM sait gérer les deux formats mais des optimisations de base de données nous oblige à abandonner l'ancien schéma
*
* Dans les mises à jour nécessaire au bon fonctionnement, voici la liste des champs à alimenter :
*   - event_realuid l'uid réel d'un évenement permettant d'identifier les récurrences
*   - organizer_calendar_id l'identifiant du calendrier de l'organisateur, pour permettre de faire les jointures avec les participants
*   -
*
*
*/
use LibMelanie\Sql;

// PAMELA - Application name configuration for ORM Mél
if (!defined('CONFIGURATION_APP_LIBM2')) {
	define('CONFIGURATION_APP_LIBM2', 'roundcube');
}

include_once __DIR__.'/../includes/libm2.php';

# DEFINITIONS INITIALES
// Recuperation du temps de depart
$temps_debut = microtime_float();

// l'usage des ticks est nécessaire depuis PHP 4.3.0
declare(ticks = 1);

// Installation des gestionnaires de signaux
pcntl_signal(SIGTERM, "sig_handler");
##

## CONFIGURATION
// Configuration du script
$config = [
	// Nom du calendrier a mettre à jour
	'calendar' => null,

	// Limite du nombre d'entrée à traiter, peut être null
	'limit' => 1000000,

	// Configuration des traces
	'trace' => false,

	// Configuration du mode bouchon : si true aucune donnees n'est inseree dans la base de donnees
	'bouchon' => false,

	// Activer le mode debug pour logger dans un fichier
	'debug' => true,

	// Chemin vers le fichier de debug
	'debug_file' => '/var/log/update_events_scheme.log'
];
##

# FONCTIONS GENERIQUES
// Recuperation du temps
function microtime_float() {
	return array_sum(explode(' ', microtime()));
}

// Gestion du fichier de logs
function log_error($message) {
	global $config;
	$time = date('d-M-Y H:i:s');
	if ($config['debug']) error_log("[$time] $message\r\n", 3, $config['debug_file']);
}

// gestionnaire de signaux système
function sig_handler($signo)
{
	global $temps_debut;
	switch ($signo) {
		case SIGTERM:
			// gestion de l'extinction
			$temps_fin = microtime_float();
			$temps_exec = round($temps_fin - $temps_debut, 2);
			trigger_error("Timeout: Fin de signal envoye au bout de $temps_exec sec");
			die();
		break;
		default:
		// gestion des autres signaux
	}
}
##

# TRAITEMENT

$message = "Demarrage du traitement";
log_error($message);

if ($config['trace']) {
	echo "Lancement du script de migration des anciens événements de la base de données\r\n";
	echo "--------------------------------------------------\r\n\r\n";
}

// Requête pour lister les enregistrements a mettre à jour
$params = [];
if (isset($config['calendar'])) {
	$params['calendar_id'] = $config['calendar'];
	if (isset($config['limit'])) {
		$params['limit'] = $config['limit'];
		$query = "SELECT event_uid, calendar_id FROM kronolith_events WHERE calendar_id = :calendar_id AND event_realuid IS NULL LIMIT :limit;";
	}
	else {
		$query = "SELECT event_uid, calendar_id FROM kronolith_events WHERE calendar_id = :calendar_id AND event_realuid IS NULL;";
	}
}
else {
	if (isset($config['limit'])) {
		$params['limit'] = $config['limit'];
		$query = "SELECT event_uid, calendar_id FROM kronolith_events WHERE event_realuid IS NULL LIMIT :limit;";
	}
	else {
		$query = "SELECT event_uid, calendar_id FROM kronolith_events WHERE event_realuid IS NULL;";
	}
}


if ($config['trace']) {
	echo "Execution de la requete...\r\n";
	echo $query."\r\n";
	echo "--------------------------------------------------\r\n\r\n";
}

$results = Sql\Sql::i()->executeQuery($query, $params);
log_error(str_replace([':calendar_id', ':limit'], $params, $query));

if ($config['trace']) {
	echo "Resultats de la requete...\r\n";
	echo var_export($results, 1)."\r\n";
	echo "--------------------------------------------------\r\n\r\n";
}
// Compter le nombre d'update et d'erreur
$updatesN = 0;
$errorsN = 0;

// Désactiver les appels aux triggers
// $result_trigger = Sql\Sql::i()->executeQuery('SET session_replication_role = replica;');

// Parcourir les résultats
$query_find_org = "SELECT calendar_id FROM kronolith_events WHERE char_length(event_attendees) > 10 AND event_uid = :event_uid;";
$query_update = "UPDATE kronolith_events SET event_realuid = :event_realuid, organizer_calendar_id = :organizer_calendar_id WHERE calendar_id = :calendar_id AND event_uid = :event_uid;";
foreach ($results as $result) {
	$realuid = $result['event_uid'];
	if (strpos($realuid, 'RECURRENCE-ID') !== false) {
		$realuid = substr($realuid, 0, strlen($realuid) - 23);
		if ($config['trace']) {
			echo "Update ".$result['event_uid']." real uid : $realuid\r\n";
		}
	}
	// mise en place des paramètres pour l'update
	$params_update = [
		'event_realuid' 		=> $realuid,
		'organizer_calendar_id' => null,
		'calendar_id' 			=> $result['calendar_id'],
		'event_uid' 			=> $result['event_uid'],
	];
	// Il faut trouver l'organisateur de l'événement s'il existe
	$params_find_org = [
		'event_uid' => $result['event_uid'],
	];
	try {
		$results_find_org = Sql\Sql::i()->executeQuery($query_find_org, $params_find_org);
		if (count($results_find_org) === 1 && isset($results_find_org[0])) {
			$params_update['organizer_calendar_id'] = $results_find_org[0]['calendar_id'];
			if ($config['trace']) {
				echo $result['event_uid']." organizer calendar id : ".$results_find_org[0]['calendar_id']."\r\n";
			}
		}
		if ($config['trace']) {
			echo str_replace([':event_realuid', ':organizer_calendar_id', ':calendar_id', ':event_uid'], $params_update, $query_update)."\r\n";
		}
		log_error(str_replace([':event_realuid', ':organizer_calendar_id', ':calendar_id', ':event_uid'], $params_update, $query_update));
		// Mode bouchon ?
		if ($config['bouchon'] !== true) {
			$result_update = Sql\Sql::i()->executeQuery($query_update, $params_update);
			if ($config['trace']) {
			  echo var_export($result_update, 1)."\r\n";
			}
			if ($result_update) {
				$updatesN++;
			}
			else {
				$errorsN++;
			}
		}
	}
	catch (Exception $ex) {
		log_error("[ERROR] Erreur sur l'evenement " . $result['event_uid']);
		log_error("[ERROR] " . $ex->getMessage());
		$errorsN++;
	}
}

log_error("Fin du traitement");
log_error("Nombre d'updates lancés : ".count($results));
log_error("Nombre d'updates effectués : $updatesN");
log_error("Nombre d'erreurs : $errorsN");

$temps_fin = microtime_float();

if ($config['trace']) {
	echo "\r\n--------------------------------------------------\r\n";
	echo "Fin du script de migration\r\n";
	echo "Nombre d'updates lancés : ".count($results)."\r\n";
	echo "Nombre d'updates effectués : $updatesN\r\n";
	echo "Nombre d'erreurs : $errorsN\r\n";
	echo "--------------------------------------------------\r\n\r\n";

	echo "#### 1: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
	echo "#### Cycles: ".gc_collect_cycles()." ######\r\n";
	echo "#### 2: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
	echo "#### Peak: ".(memory_get_peak_usage(true)/1024/1024) . ' MiB'." ######\r\n";

	echo "#####################################\r\n";
	echo "DUREE EXECUTION: ".round($temps_fin - $temps_debut, 4)."\r\n";
	echo "#####################################\r\n";
}
log_error("Durée du traitement : ".round($temps_fin - $temps_debut, 4));
