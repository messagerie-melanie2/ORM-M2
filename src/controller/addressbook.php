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
 * Classe de traitement pour les carnets d'adresses
 * 
 * @package Controller
 */
class Addressbook extends Controller {
    /**
     * Récupération d'un carnet d'adresses
     */
    public static function get()
    {
        \Lib\Log::LogTrace("get(): " . var_export($_GET, 1));

        if (\Lib\Request::checkInputValues(['id'], \Lib\Request::INPUT_GET)) {
            $user = \Lib\Utils::getCurrentUser();

            $addressbook = \Lib\Objects::gi()->addressbook([$user]);
            $addressbook->id = \Lib\Request::getInputValue('id', \Lib\Request::INPUT_GET);

            if ($addressbook->load()) {
                \Lib\Response::data(\Lib\Mapping::get('addressbook', $addressbook));
            }
            else {
                \Lib\Response::error("Addressbook not found");
            }
        }
    }

    /**
     * Récupération des contacts d'un carnet d'adresses
     */
    public static function contacts()
    {
        \Lib\Log::LogTrace("contacts(): " . var_export($_GET, 1));

        if (\Lib\Request::checkInputValues(['id'], \Lib\Request::INPUT_GET)) {
            $user = \Lib\Utils::getCurrentUser();

            $addressbook = \Lib\Objects::gi()->addressbook([$user]);
            $addressbook->id = \Lib\Request::getInputValue('id', \Lib\Request::INPUT_GET);

            if ($addressbook->load()) {
                $contacts = $addressbook->getAllContacts();
                $data = [];

                foreach ($contacts as $contact) {
                    $data[] = \Lib\Mapping::get('contact', $contact);
                }

                \Lib\Response::data($data);
            }
            else {
                \Lib\Response::error("Addressbook not found");
            }
        }
    }

    /**
     * Récupération des groupes d'un carnet d'adresses
     */
    public static function groups()
    {
        \Lib\Log::LogTrace("groups(): " . var_export($_GET, 1));

        if (\Lib\Request::checkInputValues(['id'], \Lib\Request::INPUT_GET)) {
            $user = \Lib\Utils::getCurrentUser();

            $addressbook = \Lib\Objects::gi()->addressbook([$user]);
            $addressbook->id = \Lib\Request::getInputValue('id', \Lib\Request::INPUT_GET);

            if ($addressbook->load()) {
                $groups = $addressbook->getAllGroups();
                $data = [];

                foreach ($groups as $group) {
                    $data[] = \Lib\Mapping::get('group', $group);
                }

                \Lib\Response::data($data);
            }
            else {
                \Lib\Response::error("Addressbook not found");
            }
        }
    }

    /**
     * Récupération des partages d'un carnet d'adresses
     */
    public static function shares()
    {
        \Lib\Log::LogTrace("shares(): " . var_export($_GET, 1));

        if (\Lib\Request::checkInputValues(['id'], \Lib\Request::INPUT_GET)) {
            $user = \Lib\Utils::getCurrentUser();

            $addressbook = \Lib\Objects::gi()->addressbook([$user]);
            $addressbook->id = \Lib\Request::getInputValue('id', \Lib\Request::INPUT_GET);

            if ($addressbook->load()) {
                $shares = \Lib\Objects::gi()->share([$addressbook]);
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
                \Lib\Response::error("Addressbook not found");
            }
        }
    }

    /**
     * Enregistrer/modifier un carnet d'adresses
     */
    public static function post()
    {
        $json = \Lib\Request::readJson();

        \Lib\Log::LogTrace("post(): " . var_export($json, 1));

        if (isset($json) && $json !== false) {

            if (\Lib\Request::checkInputValues(['id', 'name'], null, $json)) {
                $user = \Lib\Utils::getCurrentUser('owner', null, $json);

                $addressbook = \Lib\Objects::gi()->addressbook([$user]);
                $addressbook->id = $json['id'];

                if (!$addressbook->load()) {
                    $addressbook->owner = $user->uid;
                }
                
                $addressbook->name = $json['name'];

                $ret = $addressbook->save();

                if (!is_null($ret)) {
                    \Lib\Response::success(true);
                }
                else {
                    \Lib\Response::error("Error when saving the addressbook");
                }
            }
        }
        else {
            \Lib\Response::error("Invalid json parameter");
        }
    }

    /**
     * Suppression d'un carnet d'adresses
     */
    public static function delete()
    {
        \Lib\Log::LogTrace("delete(): " . var_export($_GET, 1));

        if (\Lib\Request::checkInputValues(['id'], \Lib\Request::INPUT_GET)) {
            $user = \Lib\Utils::getCurrentUser();

            $addressbook = \Lib\Objects::gi()->addressbook([$user]);
            $addressbook->id = \Lib\Request::getInputValue('id', \Lib\Request::INPUT_GET);

            if ($addressbook->load()) {
                if ($addressbook->delete()) {
                    \Lib\Response::success(true);
                }
                else {
                    \Lib\Response::error("Error when deleting addressbook");
                }
            }
            else {
                \Lib\Response::error("Addressbook not found");
            }
        }
    }
}