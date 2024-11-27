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
 * Classe de gestion du mapping des API
 * 
 * @package Lib
 */
class Mapping
{
    const NAME = 'name';
    const MAPPING = 'mapping';
    const LIST = 'list';
    const KEY = 'key';
    const GET = 'get';
    const SET = 'set';

    /**
     *  Constructeur privé pour ne pas instancier la classe
     */
    private function __construct() {}

    /**
     * Mapping objet vers json
     */
    public static function get($itemName, $item)
    {
        $mapping = Config::get('mapping', []);
        $data = [];
        if (!is_array($item)) {
            $item = [$item];
        }

        if (isset($mapping[$itemName])) {
            if (is_array($item)) {
                foreach ($item as $itemKey => $item) {
                    if ($item->type) {
                        $itemType = $item->type;
                    }
                    if(isset($mapping[$itemType])) {
                        $data[$itemKey] = self::mapItem($mapping[$itemType], $item);
                    } else {
                        $data[$itemKey] = self::mapItem($mapping[$itemName], $item);
                    }
                   
                }
            } else {
                $data[] = self::mapItem($mapping[$itemName], $item);
            }
        }
        return $data;
    }

    /**
     * Mapping json vers objet
     */
    public static function set($itemName, $item, $data)
    {
        $mapping = Config::get('mapping', []);

        if (isset($mapping[$itemName])) {
            foreach ($mapping[$itemName] as $name) {
                $method = null;
                $refMap = null;
                $isList = false;

                if (is_array($name)) {
                    $method = isset($name[self::SET]) ? $name[self::SET] : null;
                    $refMap = isset($name[self::MAPPING]) ? $name[self::MAPPING] : null;
                    $isList = isset($name[self::LIST]) ? $name[self::LIST] : false;
                    $name = isset($name[self::NAME]) ? $name[self::NAME] : null;
                    $key = isset($name[self::KEY]) ? $name[self::KEY] : $name;
                } else {
                    $key = $name;
                }
                $value = isset($data[$key]) ? $data[$key] : null;

                // Appel une méthode de mapping
                if (isset($method)) {
                    $file = strtolower(str_replace("\\", "/", $method[0]));
                    require_once __DIR__ . "/../$file.php";
                    $value = call_user_func($method, $value);
                }
                // Référence vers un mapping automatique
                else if (isset($refMap)) {
                    if (is_array($value) && $isList) {
                        $t = [];
                        foreach ($value as $k => $v) {
                            $t[$k] = self::set($refMap, call_user_func([Objects::gi(), $refMap], [$item]), $v);
                        }
                        $value = $t;
                    } else if (isset($value)) {
                        $value = self::set($refMap, call_user_func([Objects::gi(), $refMap], [$item]), $value);
                    }
                }

                // Positionne la valeur
                if (isset($value)) {
                    $item->$name = $value;
                }
            }
        }
        return $item;
    }

    public static function get_mapping($itemName)
    {
        $mapping = Config::get('mapping', []);

        if (isset($mapping[$itemName])) {
            return $mapping[$itemName];
        } else {
            return null;
        }
    }

    protected function mapItem($mapping, $item)
    {
        $data = [];
        foreach ($mapping as $name) {
            $method = null;
            $refMap = null;
            $isList = false;

            // Traitement de l'enregistrement
            if (is_array($name)) {
                $method = isset($name[self::GET]) ? $name[self::GET] : null;
                $refMap = isset($name[self::MAPPING]) ? $name[self::MAPPING] : null;
                $isList = isset($name[self::LIST]) ? $name[self::LIST] : false;
                $name = isset($name[self::NAME]) ? $name[self::NAME] : null;
                $key = isset($name[self::KEY]) ? $name[self::KEY] : $name;
            } else {
                $key = $name;
            }
            $value = $item->$name;

            // Appel une méthode de mapping
            if (isset($method)) {
                $file = strtolower(str_replace("\\", "/", $method[0]));
                require_once __DIR__ . "/../$file.php";
                $value = call_user_func($method, $value);
            }
            // Référence vers un mapping automatique
            else if (isset($refMap)) {
                if (is_array($value) && $isList) {
                    $t = [];
                    foreach ($value as $k => $v) {
                        $t[$k] = self::get($refMap, $v);
                    }
                    $value = $t;
                } else {
                    $value = self::get($refMap, $value);
                }
            }

            // Positionne la valeur si elle n'est pas vide
            if (isset($value) && !empty($value)) {
                $data[$key] = $value;
            }
        }
        return $data;
    }
}
