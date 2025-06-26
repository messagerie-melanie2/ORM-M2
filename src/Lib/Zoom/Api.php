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
namespace LibMelanie\Lib\Zoom;

use LibMelanie\Log\M2Log;
use LibMelanie\Config\Config;
use LibMelanie\HTTP;

/**
 * Classe de gestion des API de Zoom
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Lib Mélanie2
 *
 */
class Api {

    /**
     * Tableau statique pour stocker les tokens d'accès
     * 
     * @var array
     */
    protected static $tokens = [];

    /**
     * URL pour obtenir le token d'accès OAuth
     * 
     * @var string
     */
    const GET_TOKEN_URL = 'https://zoom.us/oauth/token';

    /**
     * URL pour obtenir les détails d'une réunion
     * 
     * @var string
     */
    const GET_MEETING_URL = 'https://api.zoom.us/v2/meetings/%%meeting_id%%';

    /**
     * URL pour lister les réunions d'un utilisateur
     * 
     * @var string
     */
    const LIST_MEETINGS_URL = 'https://api.zoom.us/v2/users/%%username%%/meetings';

    /**
     * URL pour mettre à jour une réunion
     * 
     * @var string
     */
    const UPDATE_MEETING_URL = 'https://api.zoom.us/v2/meetings/%%meeting_id%%';

    /**
     * URL pour créer une réunion
     * 
     * @var string
     */
    const CREATE_MEETING_URL = 'https://api.zoom.us/v2/users/%%username%%/meetings';

    /**
     * URL pour supprimer une réunion
     * 
     * @var string
     */
    const DELETE_MEETING_URL = 'https://api.zoom.us/v2/meetings/%%meeting_id%%';
    
    /**
     * Récupère le token d'accès pour un compte Zoom donné
     * 
     * @param string $account_id L'ID du compte Zoom
     * 
     * @return string Le token d'accès OAuth
     */
    public static function GetToken($account_id) {
        M2Log::Log(M2Log::LEVEL_DEBUG, "Zoom/Api::GetToken($account_id)");

        $accounts = Config::Get('ZOOM_ACCOUNTS');

        if (!isset($accounts[$account_id])) {
            M2Log::Log(M2Log::LEVEL_ERROR, "Zoom/Api::GetToken - Account ID $account_id not found in configuration");
            throw new \Exception("Zoom account ID not found in configuration");
        }

        $client_id = $accounts[$account_id]['client_id'];
        
        if (!isset(self::$tokens[$client_id])) {

            $client_secret = $accounts[$account_id]['client_secret'];
            
            $params = [
                'grant_type' => 'account_credentials',
                'account_id' => $account_id,
            ];
            
            $headers = [
                'Authorization: Basic ' . base64_encode("$client_id:$client_secret")
            ];
            
            $ret = HTTP\Request::Post(self::GET_TOKEN_URL, $params, $headers);

            if ($ret['httpCode'] == 200) {
                $response = json_decode($ret['content'], true);
                self::$tokens[$client_id] = $response['access_token'];
                M2Log::Log(M2Log::LEVEL_DEBUG, "Zoom/Api::GetToken - Token retrieved successfully");
            } else {
                M2Log::Log(M2Log::LEVEL_ERROR, "Zoom/Api::GetToken - Failed to retrieve token: " . $ret['content']);
                throw new \Exception("Failed to retrieve Zoom API token");
            }
        }

        return self::$tokens[$client_id];
    }

    /**
     * Liste les réunions Zoom pour un utilisateur donné
     * 
     * @param string $account_id L'ID du compte Zoom
     * @param string $username Le nom d'utilisateur du propriétaire de la réunion
     * 
     * @return array La liste des réunions de l'utilisateur
     */
    public static function ListMeetings($account_id, $username, $params = []) {
        M2Log::Log(M2Log::LEVEL_DEBUG, "Zoom/Api::ListMeetings($account_id, $username)");

        $token = self::GetToken($account_id);
        $url = self::_getUrl(self::LIST_MEETINGS_URL, ['%%username%%' => $username]);

        $headers = [
            "Authorization: Bearer $token",
            'Content-Type: application/json',
        ];

        $ret = HTTP\Request::Get($url, $params, $headers);

        if ($ret['httpCode'] == 200) {
            return json_decode($ret['content'], true);
        } else {
            M2Log::Log(M2Log::LEVEL_ERROR, "Zoom/Api::ListMeetings - Failed to list meetings: " . $ret['content']);
            throw new \Exception("Failed to list Zoom meetings");
        }
    }

    /**
     * Récupère les détails d'une réunion Zoom
     * 
     * @param string $account_id L'ID du compte Zoom
     * @param string $meeting_id L'ID de la réunion Zoom
     * 
     * @return array Les détails de la réunion
     */
    public static function GetMeeting($account_id, $meeting_id) {
        M2Log::Log(M2Log::LEVEL_DEBUG, "Zoom/Api::GetMeeting($account_id, $meeting_id)");

        $token = self::GetToken($account_id);
        $url = self::_getUrl(self::GET_MEETING_URL, ['%%meeting_id%%' => $meeting_id]);

        $headers = [
            "Authorization: Bearer $token",
            'Content-Type: application/json',
        ];

        $ret = HTTP\Request::Get($url, null, $headers);

        if ($ret['httpCode'] == 200) {
            return json_decode($ret['content'], true);
        } else {
            M2Log::Log(M2Log::LEVEL_ERROR, "Zoom/Api::GetMeeting - Failed to retrieve meeting: " . $ret['content']);
            throw new \Exception("Failed to retrieve Zoom meeting");
        }
    }

    /**
     * Crée une nouvelle réunion Zoom
     * 
     * @param string $account_id L'ID du compte Zoom
     * @param string $username Le nom d'utilisateur du propriétaire de la réunion
     * @param array $meeting_data Les données de la réunion à créer
     * 
     * @return array Les détails de la réunion créée
     */
    public static function CreateMeeting($account_id, $username, $meeting_data) {
        M2Log::Log(M2Log::LEVEL_DEBUG, "Zoom/Api::CreateMeeting($account_id, $username)");

        $token = self::GetToken($account_id);
        $url = self::_getUrl(self::CREATE_MEETING_URL, ['%%username%%' => $username]);

        $headers = [
            "Authorization: Bearer $token",
            'Content-Type: application/json',
        ];

        $ret = HTTP\Request::Post($url, $meeting_data, $headers);

        if ($ret['httpCode'] == 201) {
            return json_decode($ret['content'], true);
        } else {
            M2Log::Log(M2Log::LEVEL_ERROR, "Zoom/Api::CreateMeeting - Failed to create meeting: " . $ret['content']);
            throw new \Exception("Failed to create Zoom meeting");
        }
    }

    /**
     * Met à jour une réunion Zoom existante
     * 
     * @param string $account_id L'ID du compte Zoom
     * @param string $meeting_id L'ID de la réunion Zoom à mettre à jour
     * @param array $meeting_data Les données de la réunion à mettre à jour
     * 
     * @return bool True si la mise à jour a réussi, sinon une exception est levée
     */
    public static function UpdateMeeting($account_id, $meeting_id, $meeting_data) {
        M2Log::Log(M2Log::LEVEL_DEBUG, "Zoom/Api::UpdateMeeting($account_id, $meeting_id)");

        $token = self::GetToken($account_id);
        $url = self::_getUrl(self::UPDATE_MEETING_URL, ['%%meeting_id%%' => $meeting_id]);

        $headers = [
            "Authorization: Bearer $token",
            'Content-Type: application/json',
        ];

        $ret = HTTP\Request::Patch($url, $meeting_data, $headers);

        if ($ret['httpCode'] == 204) {
            return true; // No content returned on success
        } else {
            M2Log::Log(M2Log::LEVEL_ERROR, "Zoom/Api::UpdateMeeting - Failed to update meeting: " . $ret['content']);
            throw new \Exception("Failed to update Zoom meeting");
        }
    }

    /**
     * Supprime une réunion Zoom
     * 
     * @param string $account_id L'ID du compte Zoom
     * @param string $meeting_id L'ID de la réunion Zoom à supprimer
     * 
     * @return bool True si la suppression a réussi, sinon une exception est levée
     */
    public static function DeleteMeeting($account_id, $meeting_id) {
        M2Log::Log(M2Log::LEVEL_DEBUG, "Zoom/Api::DeleteMeeting($account_id, $meeting_id)");

        $token = self::GetToken($account_id);
        $url = self::_getUrl(self::DELETE_MEETING_URL, ['%%meeting_id%%' => $meeting_id]);

        $headers = [
            "Authorization: Bearer $token",
            'Content-Type: application/json',
        ];

        $ret = HTTP\Request::Delete($url, null, $headers);

        if ($ret['httpCode'] == 204) {
            return true; // No content returned on success
        } else {
            M2Log::Log(M2Log::LEVEL_ERROR, "Zoom/Api::DeleteMeeting - Failed to delete meeting: " . $ret['content']);
            throw new \Exception("Failed to delete Zoom meeting");
        }
    }

    /**
     * Génère une URL en remplaçant les paramètres dans l'URL
     * 
     * @param string $url L'URL avec des paramètres à remplacer
     * @param array $params Tableau associatif des paramètres à remplacer dans l'URL
     * 
     * @return string L'URL avec les paramètres remplacés
     */
    protected static function _getUrl($url, $params) {
        return str_replace(array_keys($params), array_values($params), $url);
    }
}
