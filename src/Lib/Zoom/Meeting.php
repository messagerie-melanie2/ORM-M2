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

/**
 * Classe de gestion des meetings de Zoom via les API
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Lib Mélanie2
 *
 */
class Meeting {

    /**
     * Description à mettre à jour pour une réunion Zoom
     */
    const DESCRIPTION = "Pour rejoindre la réunion Zoom : %%url%%\r\nCode secret : %%password%%";

    /**
     * Enregistre un événement Zoom
     * 
     * @param object $event L'événement à enregistrer, doit contenir les propriétés zoom_meeting_id et zoom_json
     * @param string $username Le nom d'utilisateur de l'organisateur de la réunion
     * @param string $account_id L'ID du compte Zoom
     * 
     * @return bool Retourne true si l'enregistrement a réussi, false sinon
     */
    public static function save($event, $username, $account_id) {
        M2Log::Log(M2Log::LEVEL_DEBUG, "LibMelanie\Lib\Zoom\Meeting::save()");

        $exist = false;

        try {
            // Vérification des paramètres requis
            if (isset($event->zoom_meeting_id) && !empty($event->zoom_meeting_id)) {
                // Recherche la réunion chez Zoom pour être sûr qu'elle existe
                $result = Api::GetMeeting($account_id, $event->zoom_meeting_id);

                $exist = isset($result['id']) && !empty($result['id']) && $result['id'] == $event->zoom_meeting_id;
            }

            if ($exist) {
                // Mise à jour de l'événement existant
                $result = Api::UpdateMeeting($account_id, $event->zoom_meeting_id, $event->zoom_json);
            } else {
                // Création d'un nouvel événement
                $result = Api::CreateMeeting($account_id, $username, $event->zoom_json);
            }

            if (isset($result['id']) && !empty($result['id'])) {
                // Mise à jour de l'ID de la réunion Zoom dans l'événement
                $event->zoom_meeting_id = $result['id'];
                $event->zoom_meeting_url = $result['join_url'] ?? '';
                $event->zoom_meeting_password = $result['password'] ?? '';

                // Pour l'instant, pas de recurrence
                $event->recurrence->type = \LibMelanie\Api\Defaut\Recurrence::RECURTYPE_NORECUR;

                if (strpos($event->location, $event->zoom_meeting_url) === false) {
                    $event->location .= ' ' . $event->zoom_meeting_url;
                }

                if (strpos($event->description, $event->zoom_meeting_url) === false) {
                    if (empty($event->description)) {
                        $event->description = str_replace(['%%url%%', '%%password%%'], [$event->zoom_meeting_url, $event->zoom_meeting_password], self::DESCRIPTION);
                    }
                    else {
                        $event->description = str_replace(['%%url%%', '%%password%%'], [$event->zoom_meeting_url, $event->zoom_meeting_password], self::DESCRIPTION) . "\r\n\r\n" . $event->description;
                    }
                }

                return true;
            } else {
                M2Log::Log(M2Log::LEVEL_ERROR, "LibMelanie\Lib\Zoom\Meeting::save() - Erreur lors de la création ou mise à jour de la réunion Zoom");

                return false;
            }
        }
        catch (\Exception $e) {
            M2Log::Log(M2Log::LEVEL_ERROR, "LibMelanie\Lib\Zoom\Meeting::save() - Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un événement Zoom
     * 
     * @param object $event L'événement à enregistrer, doit contenir les propriétés zoom_meeting_id et zoom_json
     * @param string $account_id L'ID du compte Zoom
     * 
     * @return bool Retourne true si la suppression a réussi, false sinon
     */
    public static function delete($event, $account_id) {
        M2Log::Log(M2Log::LEVEL_DEBUG, "LibMelanie\Lib\Zoom\Meeting::delete()");

        try {
            if (isset($event->zoom_meeting_id) && !empty($event->zoom_meeting_id)) {
                // Suppression de la réunion Zoom
                $result = Api::DeleteMeeting($account_id, $event->zoom_meeting_id);

                if ($result) {
                    // Réinitialisation des propriétés de l'événement
                    $event->location = str_replace($event->zoom_meeting_url, '', $event->location);
                    $event->description = str_replace(str_replace(['%%url%%', '%%password%%'], [$event->zoom_meeting_url, $event->zoom_meeting_password], self::DESCRIPTION), '', $event->description);
                    $event->zoom_meeting_id = '';
                    $event->zoom_meeting_url = '';
                    return true;
                } else {
                    M2Log::Log(M2Log::LEVEL_ERROR, "LibMelanie\Lib\Zoom\Meeting::delete() - Erreur lors de la suppression de la réunion Zoom");
                    return false;
                }
            } else {
                M2Log::Log(M2Log::LEVEL_ERROR, "LibMelanie\Lib\Zoom\Meeting::delete() - Aucun ID de réunion Zoom fourni");
                return false;
            }
        }
        catch (\Exception $e) {
            M2Log::Log(M2Log::LEVEL_ERROR, "LibMelanie\Lib\Zoom\Meeting::delete() - Exception: " . $e->getMessage());
            return false;
        }
    }
}
