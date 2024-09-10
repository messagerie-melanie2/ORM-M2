<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * 
 * ORM Mél Copyright © 2022 Groupe Messagerie/MTE
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace LibMelanie\Storage\SwiftStorage;

/**
 * Storage class
 * 
 * Cette classe est la classe dédiée au stockage des fichiers dans un container Swift
 * 
 * @package LibMelanie
 * @subpackage SwiftStorage
 */

use LibMelanie\Log\M2Log;
use LibMelanie\Storage\IStorage;
use OpenStack\OpenStack;
use LibMelanie\Lib\MceObject;

class SwiftStorage extends MceObject implements IStorage
{
    protected $container;
    protected $objectStore;
    private static $instance = null;

    // create a private constructor to prevent direct creation of object
    private function __construct(array $swiftConfig)
    {
        // Défini la classe courante
        $this->get_class = get_class($this);

        M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->__construct()");

        $openstack = new OpenStack($swiftConfig);

        $this->objectStore = $openstack->objectStoreV1();
        $this->container = $this->objectStore->getContainer('your-container-name');
    }

    public static function getInstance(array $swiftConfig)
    {
        if (self::$instance == null) {
            self::$instance = new SwiftStorage($swiftConfig);
        }

        return self::$instance;
    }

    public function write($path, $contents)
    {
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->write()");

        $response = false;
        try {
            $this->container->createObject(['name' => $path, 'content' => $contents]);
            $response = true;
        } catch (\Exception $exception) {
            M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->write(" . $path . ", fileContent) Exception: " . $exception);
        }

        return $response;
    }

    public function read($path)
    {
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->read()");

        $response = null;
        try {
            $object = $this->container->getObject($path);
            $response = $object->getContent();
        } catch (\Exception $exception) {
            M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->read() Exception: " . $exception);
        }

        return $response;
    }

    public function delete($path)
    {
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->delete()");

        $response = false;
        try {
            $this->container->getObject($path)->delete();
            $response = true;
        } catch (\Exception $exception) {
            M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->delete(" . $path . ") Exception: " . $exception);
        }

        return $response;
    }
}
