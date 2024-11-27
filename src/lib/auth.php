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
 * Classe de gestion de l'authentification
 * 
 * @package Lib
 */
class Auth {
    /**
     * Authentification Basic
     * 
     * @var string
     */
    const HEADER_BASIC = 'Basic';

    /**
     * Authentification Bearer (token ou jwt)
     * 
     * @var string
     */
    const HEADER_BEARER = 'Bearer';

    /**
     * Authentification Apikey (depuis la conf)
     * 
     * @var string
     */
    const HEADER_APIKEY = 'Apikey';

    /**
	 *  Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() {}

    /**
     * Validation de l'authentification des API
     * 
     * @return boolean
     */
    public static function validate()
    {
        // Valider l'adresse IP source ?
        if (Config::get('ip_address_filter', false) && !self::validateIP()) {
            Log::LogError("Source IP is not listed in autorized IP list");
            return false;
        }

        // Récupération du type d'authentification
        list($auth_type, $auth_value) = self::getAuthorization();

        if ($auth_type !== false) {
            $auth_type = strtolower($auth_type);
            if (Config::get('auth_type_'.$auth_type, false) === true) {
                return call_user_func([self::class, $auth_type], $auth_value);
            }
        }
        else if (Config::get('auth_type_none', false) === true) {
            Log::LogInfo("No authentication");
            return true;
        }
        return false;
    }

    /**
     * Est-ce que l'adresse IP du client est dans les IP authorisées ?
     * 
     * @return boolean
     */
    private static function validateIP()
    {
        return in_array(Request::ipAddress(), Config::get('valid_ip_addresses_list', []));
    }

    /**
     * Validation de l'authentification login/mot de passe
     */
    private static function basic($value) 
    {
        Log::LogInfo("Basic authentication");
        $value = base64_decode($value);
        list($user, $password) = explode(':', $value, 2);
        $user = Objects::gi()->user();
        $user->uid = $user;
        \Lib\Log::LogDebug("Basic authentication for user $user");
        return $user->authentification($password);
    }

    /**
     * Validation de l'authentification par token ou jwt
     * 
     * @param string $token
     */
    private static function bearer($token) 
    {
        Log::LogInfo("Bearer authentication");
        return false;
    }

    /**
     * Validation de l'authentification par une clé d'api dans la configuration
     * 
     * @param string $key
     */
    private static function apikey($key) 
    {
        Log::LogInfo("Apikey authentication");
        return in_array($key, Config::get('api_keys', []));
    }

    /**
     * Récupération du header Autorization
     * 
     * @return array $type $value
     */
    private static function getAuthorization()
    {
        $headers = Request::getHeaders();

        if (isset($headers['Authorization'])) {
            return explode(' ', trim($headers['Authorization']), 2);
        }
        // Bug w/ fetch lower case header
        else if (isset($headers['authorization'])) {
            return explode(' ', trim($headers['authorization']), 2);
        }
        else {
            return [false, false];
        }
    }
}