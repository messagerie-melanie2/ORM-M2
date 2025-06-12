<?php
/**
* Ce fichier est développé pour la gestion de la librairie Mélanie2
* Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
* Des objets génériques vont permettre d'accèder et de mettre à jour les données
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
*/
namespace LibMelanie\Lib;

use LibMelanie\Log\M2Log;
use LibMelanie\Config\Config;

/**
* Classe de gestion des requêtes HTTP
*
* @author PNE Messagerie/Apitech
* @package Librairie Mélanie2
* @subpackage Lib Mélanie2
*
*/
class HTTPRequest {
    /**
     * Permet d'effectuer une requête GET
     * 
     * @param string $url URL de la requête
     * @param array $params [Optionnel] Paramètres à ajouter à l'URL
     * @param array $headers [Optionnel] En-têtes HTTP à envoyer
     * @param string|null $curl_cafile [Optionnel] Chemin vers le fichier CA pour la vérification SSL
     * @param string|null $curl_proxy [Optionnel] Proxy à utiliser pour la requête cURL
     * 
     * @return array Contient le code HTTP et le contenu de la réponse
     */
    public static function Get($url, $params = null, $headers = [], $curl_cafile = null, $curl_proxy = null) {
        M2Log::Log(M2Log::LEVEL_DEBUG, "HTTPRequest::Get($url)");
        
        // Gestion des paramètres
        if (isset($params)) {
            $_p = [];
            foreach ($params as $key => $param) {
                $_p[] = "$key=$param";
            }
            $url .= '?' . implode('&', $_p);
        }
        
        return self::_curl($url, [], $headers, $curl_cafile, $curl_proxy);
    }
    
    /**
     * Permet d'effectuer une requête POST
     * 
     * @param string $url URL de la requête
     * @param string $postfields [Optionnel] Données à envoyer dans le corps de la requête
     * @param array $headers [Optionnel] En-têtes HTTP à envoyer
     * @param string|null $curl_cafile [Optionnel] Chemin vers le fichier CA pour la vérification SSL
     * @param string|null $curl_proxy [Optionnel] Proxy à utiliser pour la requête cURL
     * 
     * @return array Contient le code HTTP et le contenu de la réponse
     */
    public static function Post($url, $postfields = '', $headers = [], $curl_cafile = null, $curl_proxy = null) {
        M2Log::Log(M2Log::LEVEL_DEBUG, "HTTPRequest::Post($url)");
                
        // Options list
        $options = [
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $postfields,
        ];
        
        return self::_curl($url, $options, $headers, $curl_cafile, $curl_proxy);
    }

    /**
     * Permet d'effectuer une requête cURL générique
     * 
     * @param string $url URL de la requête
     * @param array $options [Optionnel] Options cURL à appliquer
     * @param array $headers [Optionnel] En-têtes HTTP à envoyer
     * @param string|null $curl_cafile [Optionnel] Chemin vers le fichier CA pour la vérification SSL
     * @param string|null $curl_proxy [Optionnel] Proxy à utiliser pour la requête cURL
     * 
     * @return array Contient le code HTTP et le contenu de la réponse
     */
    protected static function _curl($url, $options = [], $headers = [], $curl_cafile = null, $curl_proxy = null) {
        
        $fp = fopen('/var/log/roundcube/bnum/curl_errors.log', 'w');

        // Options list
        $options = [
            CURLOPT_RETURNTRANSFER  => true, // return web page
            CURLOPT_HEADER          => false, // don't return headers
            CURLOPT_USERAGENT       => Config::get('CURLOPT_USERAGENT', 'ORM/HTTPRequest'), // name of client
            CURLOPT_CONNECTTIMEOUT  => Config::get('CURLOPT_CONNECTTIMEOUT', 120), // time-out on connect
            CURLOPT_TIMEOUT         => Config::get('CURLOPT_TIMEOUT', 1200), // time-out on response
            CURLOPT_SSL_VERIFYPEER  => Config::get('CURLOPT_SSL_VERIFYPEER', 0),
            CURLOPT_SSL_VERIFYHOST  => Config::get('CURLOPT_SSL_VERIFYHOST', 0),
            CURLOPT_HTTPHEADER      => $headers,
            CURLOPT_VERBOSE         => true,
            CURLOPT_STDERR          => fopen('/var/log/roundcube/bnum/curl_errors.log', 'w'),
        ] + $options;
        
        // CA File
        $curl_cafile = $curl_cafile ?? Config::get('CURLOPT_CAINFO');
        if (isset($curl_cafile)) {
            $options[CURLOPT_CAINFO] = $curl_cafile;
            $options[CURLOPT_CAPATH] = $curl_cafile;
        }
        
        // HTTP Proxy
        $curl_proxy = $curl_proxy ?? Config::get('CURLOPT_PROXY');
        if (isset($curl_proxy)) {
            $options[CURLOPT_PROXY] = $curl_proxy;
        }
        
        // open connection
        $ch = curl_init($url);
        // Set the options
        curl_setopt_array($ch, $options);
        // Execute the request and get the content
        $content = curl_exec($ch);
        
        // Get error
        if ($content === false) {
            M2Log::Log(M2Log::LEVEL_ERROR, "HTTPRequest::_curl($url) Error " . curl_errno($ch) . " : " . curl_error($ch));
        }
        
        // Get the HTTP Code
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // Close connection
        curl_close($ch);
        
        // Return the content
        return [
            'httpCode' => $httpcode,
            'content' => $content
        ];
    }
}
