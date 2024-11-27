
<?php
/**
 * Ce fichier est développé pour la gestion des API de la librairie Mélanie2
 * Ces API permettent d'accéder à la librairie en REST
 *
 * ORM API Copyright © 2022  Groupe MCD/MTE
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

// PAMELA - Application name configuration for ORM Mél
if (! defined('CONFIGURATION_APP_LIBM2')) {
    define('CONFIGURATION_APP_LIBM2', 'roundcube');
}

require_once 'vendor/autoload.php';

// Lance l'initialisation de la configuration
Lib\Config::init();

// Lance l'initialisation des logs
Lib\Log::init();

// Gérer l'authentification de la requête
if (Lib\Auth::validate()) {
    // Lancement du routing
    Lib\Routing::process();
}
else {
    Lib\Response::error("Authentication is not valid");
}

// Retourne la réponse
Lib\Response::send();