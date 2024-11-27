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

namespace Controller;

/**
 * Classe de traitement pour les événements
 * 
 * @package Controller
 */
class Event extends Controller {
    /**
     * Récupération d'un événement
     */
    public static function get()
    {
        \Lib\Log::LogTrace("get(): " . var_export($_GET, 1));

        if (\Lib\Request::checkInputValues(['uid', 'calendar'], \Lib\Request::INPUT_GET)) {
            $user = \Lib\Utils::getCurrentUser();

            $calendar = \Lib\Objects::gi()->calendar([$user]);
            $calendar->id = \Lib\Request::getInputValue('calendar', \Lib\Request::INPUT_GET);

            $event = \Lib\Objects::gi()->event([$user, $calendar]);
            $event->uid = \Lib\Request::getInputValue('uid', \Lib\Request::INPUT_GET);

            if ($event->load()) {
                \Lib\Response::data(\Lib\Mapping::get('event', $event));
            }
            else {
                \Lib\Response::error("Event not found");
            }
        }
    }

    /**
     * Enregistrer/modifier un événement
     */
    public static function post()
    {
        $json = \Lib\Request::readJson();

        \Lib\Log::LogTrace("post(): " . var_export($json, 1));

        if (isset($json) && $json !== false) {

            if (\Lib\Request::checkInputValues(['uid', 'calendar'], null, $json)) {
                $user = \Lib\Utils::getCurrentUser('owner', null, $json);
                
                $calendar = \Lib\Objects::gi()->calendar([$user]);
                $calendar->id = $json['calendar'];

                $event = \Lib\Objects::gi()->event([$user, $calendar]);
                $event->uid = $json['uid'];
                $event->load();

                $event = \Lib\Mapping::set('event', $event, $json);
                $ret = $event->save();

                if (!is_null($ret)) {
                    \Lib\Response::success(true);
                }
                else {
                    \Lib\Response::error("Error when saving the event");
                }
            }
        }
        else {
            \Lib\Response::error("Invalid json parameter");
        }
    }

    /**
     * Suppression d'un événement
     */
    public static function delete()
    {
        \Lib\Log::LogTrace("delete(): " . var_export($_GET, 1));

        if (\Lib\Request::checkInputValues(['uid', 'calendar'], \Lib\Request::INPUT_GET)) {
            $user = \Lib\Utils::getCurrentUser();

            $calendar = \Lib\Objects::gi()->calendar([$user]);
            $calendar->id = \Lib\Request::getInputValue('calendar', \Lib\Request::INPUT_GET);

            $event = \Lib\Objects::gi()->event([$user, $calendar]);
            $event->uid = \Lib\Request::getInputValue('uid', \Lib\Request::INPUT_GET);

            if ($event->load()) {
                if ($event->delete()) {
                    \Lib\Response::success(true);
                }
                else {
                    \Lib\Response::error("Error when deleting event");
                }
            }
            else {
                \Lib\Response::error("Event not found");
            }
        }
    }

    /**
     * Mapping pour la recurrence
     */
    public function getRecurrence($recurrence)
    {
        if (isset($recurrence)) {
            $type = $recurrence->type;
            if (isset($type) && $type) {
                $data = \Lib\Mapping::get('recurrence', $recurrence);
            }
            else {
                $data = null;
            }
        }
        else {
            $data = null;
        }
        return $data;
    }
}