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
namespace LibMelanie\Log;

/**
 * Classe de log Melanie2
 * Peut être initialisé avec une méthode de log debug/info
 * Singleton
 *
 * (TODO: pas très utile de passer par un singleton pour les logs)
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage LOG
 *
 */
class M2Log {
	/**
	 * Static log class
	 * @var Log $log
	 */
	private static $log = null;

	/**
	 * Log level const
	 */
	const LEVEL_ERROR = "error";
	const LEVEL_DEBUG = "debug";
	const LEVEL_INFO = "info";

	/**
	 * Intialisation de la methode de log error
	 *
	 * @param mixed $errorlog function appelé pour logger les erreurs
	 * doit prendre en paramètre le message
	 */
	public static function InitErrorLog($errorlog) {
		if (!isset(self::$log)) self::$log = new Log();
		self::$log->setErrorLog($errorlog);
	}

	/**
	 * Intialisation de la methode de log debug
	 *
	 * @param mixed $debuglog function appelé pour logger le debug
	 * doit prendre en paramètre le message
	 */
	public static function InitDebugLog($debuglog) {
		if (!isset(self::$log)) self::$log = new Log();
		self::$log->setDebugLog($debuglog);
	}

	/**
	 * Intialisation de la methode de log info
	 *
	 * @param mixed $infolog function appelé pour logger le debug
	 * doit prendre en paramètre le message
	 */
	public static function InitInfoLog($infolog) {
		if (!isset(self::$log)) self::$log = new Log();
		self::$log->setInfoLog($infolog);
	}

	/**
	 * Fonction de log
	 *
	 * @param M2Log::LEVEL_* $level
	 * @param string $message message to show
	 */
	public static function Log($level, $message) {
		if (!isset(self::$log)) self::$log = new Log();
		self::$log->log($level, $message);
	}
}