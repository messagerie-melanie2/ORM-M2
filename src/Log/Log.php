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
 * Classe de log
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage LOG
 */
class Log {
	/**
	 * Function callback de debug
	 * @var Callback $debuglog
	 */
	private $debuglog;
	/**
	 * Function callback d'erreur
	 * @var Callback $errorlog
	 */
	private $errorlog;
	/**
	 * Function callback d'info
	 * @var Callback $infolog
	 */
	private $infolog;

	/**
	 * Constructeur par défaut
	 */
	public function __construct() {
		$this->debuglog = null;
		$this->errorlog = null;
		$this->infolog = null;
	}

	/**
	 * Intialisation de la methode de log error
	 *
	 * @param mixed $errorlog function appelé pour logger les erreurs
	 * doit prendre en paramètre le message
	 */
	public function setErrorLog($errorlog) {
		$this->errorlog = $errorlog;
	}

	/**
	 * Intialisation de la methode de log debug
	 *
	 * @param mixed $debuglog function appelé pour logger le debug
	 * doit prendre en paramètre le message
	 */
	public function setDebugLog($debuglog) {
		$this->debuglog = $debuglog;
	}

	/**
	 * Intialisation de la methode de log info
	 *
	 * @param mixed $infolog function appelé pour logger le debug
	 * doit prendre en paramètre le message
	 */
	public function setInfoLog($infolog) {
		$this->infolog = $infolog;
	}

	/**
	 * Appel les logs associé au level
	 *
	 * @param M2Log::LEVEL_* $level
	 * @param string $message message to show
	 */
	public function log($level, $message) {
		switch ($level) {
			case M2Log::LEVEL_ERROR:
				if (isset($this->errorlog)) {
					$errorlog = $this->errorlog;
					$errorlog($message);
				}
				break;
			case M2Log::LEVEL_DEBUG:
				if (isset($this->debuglog)) {
					$debuglog = $this->debuglog;
					$debuglog($message);
				}
				break;
			case M2Log::LEVEL_INFO:
				if (isset($this->infolog)) {
					$info = $this->infolog;
					$info($message);
				}
				break;
		}
	}
}
