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
 * Classe de traitement pour les calendriers
 * 
 * @package Controller
 */
class Calendar extends Controller {
    /**
     * Récupération d'un calendrier
     */
    public static function get()
    {
        \Lib\Log::LogTrace("get(): " . var_export($_GET, 1));

        if (\Lib\Request::checkInputValues(['id'], \Lib\Request::INPUT_GET)) {
            $user = \Lib\Utils::getCurrentUser();

            $calendar = \Lib\Objects::gi()->calendar([$user]);
            $calendar->id = \Lib\Request::getInputValue('id', \Lib\Request::INPUT_GET);

            if ($calendar->load()) {
                \Lib\Response::data(\Lib\Mapping::get('calendar', $calendar));
            }
            else {
                \Lib\Response::error("Calendar not found");
            }
        }
    }

    /**
     * Récupération des événements d'un calendrier
     */
    public static function events()
    {
        \Lib\Log::LogTrace("events(): " . var_export($_GET, 1));

        if (\Lib\Request::checkInputValues(['id'], \Lib\Request::INPUT_GET)) {
            $user = \Lib\Utils::getCurrentUser();

            $calendar = \Lib\Objects::gi()->calendar([$user]);
            $calendar->id = \Lib\Request::getInputValue('id', \Lib\Request::INPUT_GET);

            if ($calendar->load()) {
                $events = $calendar->getAllEvents();
                $data = [];

                foreach ($events as $event) {
                    $data[] = \Lib\Mapping::get('event', $event);
                }

                \Lib\Response::data($data);
            }
            else {
                \Lib\Response::error("Calendar not found");
            }
        }
    }

    /**
     * Récupération des partages d'un calendrier
     */
    public static function shares()
    {
        \Lib\Log::LogTrace("shares(): " . var_export($_GET, 1));

        if (\Lib\Request::checkInputValues(['id'], \Lib\Request::INPUT_GET)) {
            $user = \Lib\Utils::getCurrentUser();

            $calendar = \Lib\Objects::gi()->calendar([$user]);
            $calendar->id = \Lib\Request::getInputValue('id', \Lib\Request::INPUT_GET);

            if ($calendar->load()) {
                $shares = \Lib\Objects::gi()->share([$calendar]);
                $is_group = \Lib\Request::getInputValue('is_group', \Lib\Request::INPUT_GET);

                $shares->type = isset($is_group) && $is_group ? \LibMelanie\Api\Defaut\Share::TYPE_GROUP : \LibMelanie\Api\Defaut\Share::TYPE_USER;
                $data = [];
                foreach ($shares->getList() as $share) {
                    $data[] = [
                        'name' => $share->name,
                        'acl' => $share->acl,
                    ];
                }
                \Lib\Response::data($data);
            }
            else {
                \Lib\Response::error("Calendar not found");
            }
        }
    }

    /**
     * Enregistrer/modifier un calendrier
     */
    public static function post()
    {
        $json = \Lib\Request::readJson();

        \Lib\Log::LogTrace("post(): " . var_export($json, 1));

        if (isset($json) && $json !== false) {

            if (\Lib\Request::checkInputValues(['id', 'name'], null, $json)) {
                $user = \Lib\Utils::getCurrentUser('owner', null, $json);

                $calendar = \Lib\Objects::gi()->calendar([$user]);
                $calendar->id = $json['id'];

                if (!$calendar->load()) {
                    $calendar->owner = $user->uid;
                }
                
                $calendar->name = $json['name'];

                $ret = $calendar->save();

                if (!is_null($ret)) {
                    \Lib\Response::success(true);
                }
                else {
                    \Lib\Response::error("Error when saving the calendar");
                }
            }
        }
        else {
            \Lib\Response::error("Invalid json parameter");
        }
    }

    /**
     * Suppression d'un calendrier
     */
    public static function delete()
    {
        \Lib\Log::LogTrace("delete(): " . var_export($_GET, 1));

        if (\Lib\Request::checkInputValues(['id'], \Lib\Request::INPUT_GET)) {
            $user = \Lib\Utils::getCurrentUser();

            $calendar = \Lib\Objects::gi()->calendar([$user]);
            $calendar->id = \Lib\Request::getInputValue('id', \Lib\Request::INPUT_GET);

            if ($calendar->load()) {
                if ($calendar->delete()) {
                    \Lib\Response::success(true);
                }
                else {
                    \Lib\Response::error("Error when deleting calendar");
                }
            }
            else {
                \Lib\Response::error("Calendar not found");
            }
        }
    }
}