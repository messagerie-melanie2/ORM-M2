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
use LibMelanie\Api\Mi\User;
use LibMelanie\Api\Mi\Group;

$log = function ($message) {
	echo "[LibM2] $message \r\n";
};
M2Log::InitDebugLog($log);
M2Log::InitErrorLog($log);

echo "########################\r\n";

// Définition de l'utilisateur
$user = new User();
$user->uid = '<uid>';

echo "\r\nUtilisateur '".get_class($user)."' : $user->uid \r\n\r\n";

if ($user->load()) {
    echo "\r\nUtilisateur chargé \r\n\r\n";

    $group = new Group(null, 'webmail.moncompte.grouplistes');
    $group->dn = "<dn>";

    echo "\r\nGroupe '".get_class($group)."' : $group->dn \r\n\r\n";

    if ($group->load(['owners', 'members_email', 'is_dynamic'])) {
        echo "\r\nGroupe chargé \r\n\r\n";
        if ($group->isOwner($user)) {
            echo "\r\nUtilisateur owner du groupe \r\n\r\n";
        }
        else {
            echo "\r\nUtilisateur pas owner du groupe \r\n\r\n";
        }
        echo "\r\nGroupe owners = " . var_export($group->owners, 1) . " \r\n\r\n";
        echo "\r\nGroupe members = " . var_export($group->members_email, 1) . " \r\n\r\n";
        echo "\r\nGroupe is_dynamic = " . var_export($group->is_dynamic, 1) . " \r\n\r\n";
        echo "\r\nGroupe = $group \r\n\r\n";

        if ($group->authentification(null, true)) {
            echo "\r\nAuthentification réussie pour le groupe \r\n\r\n";
        }
        else {
            echo "\r\nÉchec d'authentification pour le groupe \r\n\r\n";
        }
    }
    else {
        echo "\r\nErreur de chargement du groupe \r\n\r\n";
    }
}
else {
    echo "\r\nErreur de chargement de l'utilisateur \r\n\r\n";
}

echo "\r\n\r\n";
echo "#### 1: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "#### Cycles: ".gc_collect_cycles()." ######\r\n";
echo "#### 2: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "#### Peak: ".(memory_get_peak_usage(true)/1024/1024) . ' MiB'." ######\r\n";

$temps_fin = microtime_float();
echo "#####################################\r\n";
echo "DUREE EXECUTION: ".round($temps_fin - $temps_debut, 4)."\r\n";
echo "#####################################\r\n";
