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
 * Classe de gestion des logs pour les API
 * 
 * @package Lib
 */
class Objects {
    /**
     * Singleton
     * 
     * @var Objects
     */
    private static $instance;

    /**
     * @var string Namespace de l'objet
     */
    private $objectNS;

    /**
     * Récupération du singleton
     * 
     * @param array $config Configuration du script
     * 
     * @return Objects
     */
    public static function get_instance($config = null)
    {
        if (!isset($config)) {
            global $config;
        }
        
        if (!isset(self::$instance)) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Raccourcis pour get_instance
     * 
     * @param array $config Configuration du script
     * 
     * @return Objects
     */
    public static function gi($config = null) 
    {
        return self::get_instance($config);
    }

    /**
     * Constructeur par défaut de la classe
     * 
     * @param array $config Configuration du script
     */
    public function __construct($config)
    {
        $namespace = ucfirst($config['namespace']);
        $this->objectNS = "\\LibMelanie\\Api\\$namespace\\";
    }

    /**
     * Generate an object from the ORM with the right Namespace
     * 
     * @param string $objectName Object name (add sub namespace if needed, ex : Event, Users\Type)
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return staticClass object of the choosen type
     */
    protected function object($objectName, $params = []) 
    {
        $class = new \ReflectionClass($this->objectNS . $objectName);
        return $class->newInstanceArgs($params);
    }

    /**
     * Generate user object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\User
     */
    public function user($params = []) 
    {
        return $this->object('User', $params);
    }

    /**
     * Generate member object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Member
     */
    public function member($params = []) 
    {
        return $this->object('Member', $params);
    }

    /**
     * Generate users_outofoffice object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Users\Outofoffice
     */
    public function users_outofoffice($params = []) 
    {
        return $this->object('Users\\Outofoffice', $params);
    }

    /**
     * Generate users_type object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Users\Type
     */
    public function users_type($params = []) 
    {
        return $this->object('Users\\Type', $params);
    }

    /**
     * Generate users_share object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Users\Share
     */
    public function users_share($params = []) 
    {
        return $this->object('Users\\Share', $params);
    }

    /**
     * Generate group object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Group
     */
    public function group($params = []) 
    {
        return $this->object('Group', $params);
    }

    /**
     * Generate folder object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Folder
     */
    public function folder($params = []) 
    {
        return $this->object('Folder', $params);
    }

    /**
     * Generate share object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Share
     */
    public function share($params = []) 
    {
        return $this->object('Share', $params);
    }

    /**
     * Generate calendar object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Calendar
     */
    public function calendar($params = []) 
    {
        return $this->object('Calendar', $params);
    }

    /**
     * Generate event object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Event
     */
    public function event($params = []) 
    {
        return $this->object('Event', $params);
    }

    /**
     * Generate event object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Exception
     */
    public function exception($params = []) 
    {
        return $this->object('Exception', $params);
    }

    /**
     * Generate organizer object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Organizer
     */
    public function organizer($params = []) 
    {
        return $this->object('Organizer', $params);
    }

    /**
     * Generate attendee object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Attendee
     */
    public function attendee($params = []) 
    {
        return $this->object('Attendee', $params);
    }

    /**
     * Generate attachment object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Attachment
     */
    public function attachment($params = []) 
    {
        return $this->object('Attachment', $params);
    }

    /**
     * Generate recurrence object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Recurrence
     */
    public function recurrence($params = []) 
    {
        return $this->object('Recurrence', $params);
    }

    /**
     * Generate addressbook object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Addressbook
     */
    public function addressbook($params = []) 
    {
        return $this->object('Addressbook', $params);
    }

    /**
     * Generate contact object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Contact
     */
    public function contact($params = []) 
    {
        return $this->object('Contact', $params);
    }

    /**
     * Generate taskslist object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Taskslist
     */
    public function taskslist($params = []) 
    {
        return $this->object('Taskslist', $params);
    }

    /**
     * Generate task object from the ORM with the right Namespace
     * 
     * @param array $params [Optionnal] parameters of the constructor
     * 
     * @return \LibMelanie\Api\Defaut\Task
     */
    public function task($params = []) 
    {
        return $this->object('Task', $params);
    }

    /**
     * Return the constant class based on the namespace
     * 
     * @param string $contant Ex: ObjectShare::DELIMITER for DELIMITER of ObjectShare class
     * 
     * @return mixed Constant value
     */
    public function constant($constant) 
    {
        return constant($this->objectNS . $constant);
    }

    /**
     * Return the object share delimiter from ObjectShare ORM object
     * 
     * @return string DELIMITER
     */
    public function objectShareDelimiter() 
    {
        return constant($this->objectNS . 'ObjectShare::DELIMITER');
    }
}