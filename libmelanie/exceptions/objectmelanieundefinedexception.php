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
namespace LibMelanie\Exceptions;

use LibMelanie\Log\M2Log;

/**
 * Exception ObjectMelanieUndefined pour la librairie Melanie2
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Exceptions
 *
 */
class ObjectMelanieUndefinedException extends \Exception {
	/**
	 * Message d'erreur de l'exception
	 * @var string
	 */
	protected $errorMessage = "L'objet melanie n'est pas défini";
	/**
	 * Code erreur de l 'exception
	 * @var int
	 */
	protected $errorCode = 11;

	/**
	 * Constructeur de l'exception
	 * @param string $message
	 * @param int $code
	 */
	function __construct($message = "", $code = NULL) {
		// Error message
	    $message = $this->errorMessage . $message;

		// Error code
		if (!$code)
			$code = $this->errorCode;

		M2Log::Log(M2Log::LEVEL_ERROR, "LibMelanie\Exceptions\ObjectMelanieUndefinedException->__construct($code, $message)");
		parent::__construct($message, (int) $code);
	}
}