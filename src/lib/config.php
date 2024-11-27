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

namespace Lib;

/**
 * Classe de gestion de la configuration des API
 * 
 * @package Lib
 */
class Config {
    /**
	 *  Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() {}

    /**
     * Initialisation de la configuration
     */
    public static function init()
    {
        global $config, $default, $mapping, $routing;
        
        require_once __DIR__.'/../config.inc.php';
        require_once __DIR__.'/../config/default.inc.php';
        require_once __DIR__.'/../config/mapping.inc.php';
        require_once __DIR__.'/../config/routing.inc.php';

        // Gestion du mapping
        $config['mapping'] = array_merge($mapping, self::get('mapping', []));

        // Gestion du routing
        $config['routing'] = array_merge($routing, self::get('routing', []));
    }

    /**
     * Récupération d'une valeur de configuration
     * 
     * @param string $name
     * @param mixed $default
     * 
     * @return mixed
     */
    public static function get($name, $default = null) {
        global $config, $default;

        if (isset($config[$name])) {
            return $config[$name];
        }
        else if (isset($default[$name])) {
            return $default[$name];
        }
        else {
            return $default;
        }
    }
}