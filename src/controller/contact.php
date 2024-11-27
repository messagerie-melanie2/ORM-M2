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
 * Classe de traitement pour les contacts
 * 
 * @package Controller
 */
class Contact extends Controller {
    /**
     * Récupération d'un contact
     */
    public static function get()
    {
        \Lib\Log::LogTrace("get(): " . var_export($_GET, 1));

        if (\Lib\Request::checkInputValues(['uid', 'addressbook'], \Lib\Request::INPUT_GET)) {
            $user = \Lib\Utils::getCurrentUser();

            $addressbook = \Lib\Objects::gi()->addressbook([$user]);
            $addressbook->id = \Lib\Request::getInputValue('addressbook', \Lib\Request::INPUT_GET);

            $contact = \Lib\Objects::gi()->contact([$user, $addressbook]);
            $contact->uid = \Lib\Request::getInputValue('uid', \Lib\Request::INPUT_GET);

            if ($contact->load()) {
                \Lib\Response::data(\Lib\Mapping::get('contact', $contact));
            }
            else {
                \Lib\Response::error("Contact not found");
            }
        }
    }

    /**
     * Enregistrer/modifier un contact
     */
    public static function post()
    {
        $json = \Lib\Request::readJson();

        \Lib\Log::LogTrace("post(): " . var_export($json, 1));

        if (isset($json) && $json !== false) {

            if (\Lib\Request::checkInputValues(['uid', 'addressbook'], null, $json)) {
                $user = \Lib\Utils::getCurrentUser('owner', null, $json);

                $addressbook = \Lib\Objects::gi()->addressbook([$user]);
                $addressbook->id = $json['addressbook'];

                $contact = \Lib\Objects::gi()->contact([$user, $addressbook]);
                $contact->uid = $json['uid'];
                
                if (!$contact->load()) {
                    // Gérer l'id
                    $contact->id = hash('sha256', $contact->uid . $contact->calendar . uniqid(), false);
                    $contact->type = \LibMelanie\Api\Defaut\Contact::TYPE_CONTACT;
                }

                $contact = \Lib\Mapping::set('contact', $contact, $json);

                // Gérer le modified
                if (!isset($json['modified'])) {
                    $contact->modified = time();
                }
                $ret = $contact->save();

                if (!is_null($ret)) {
                    \Lib\Response::success(true);
                }
                else {
                    \Lib\Response::error("Error when saving the contact");
                }
            }
        }
        else {
            \Lib\Response::error("Invalid json parameter");
        }
    }

    /**
     * Suppression d'un contact
     */
    public static function delete()
    {
        \Lib\Log::LogTrace("delete(): " . var_export($_GET, 1));
        
        if (\Lib\Request::checkInputValues(['uid', 'addressbook'], \Lib\Request::INPUT_GET)) {
            $user = \Lib\Utils::getCurrentUser();

            $addressbook = \Lib\Objects::gi()->addressbook([$user]);
            $addressbook->id = \Lib\Request::getInputValue('addressbook', \Lib\Request::INPUT_GET);

            $contact = \Lib\Objects::gi()->contact([$user, $addressbook]);
            $contact->uid = \Lib\Request::getInputValue('uid', \Lib\Request::INPUT_GET);

            if ($contact->load()) {
                if ($contact->delete()) {
                    \Lib\Response::success(true);
                }
                else {
                    \Lib\Response::error("Error when deleting contact");
                }
            }
            else {
                \Lib\Response::error("Contact not found");
            }
        }
    }
}