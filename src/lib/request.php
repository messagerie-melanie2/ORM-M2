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
 * Classe de gestion de la requête vers les API
 * 
 * @package Lib
 */
class Request
{
    /**
     * Valeur de $_GET
     */
    const INPUT_GET = 'get';

    /**
     * Valeur de $_POST
     */
    const INPUT_POST = 'post';

    /**
     * Valeur de $_COOKIE
     */
    const INPUT_COOKIE = 'cookie';

    /**
     * Valeur de $_GET, $_POST, $_COOKIE
     */
    const INPUT_GPC = 'gpc';

	/**
	 * Utilisateur courant de la requête (en cas d'auth Basic)
	 * 
	 * @var string
	 */
	private static $user;

    /**
	 *  Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() {}

	/**
	 * Positionne le user de la requête
	 * 
	 * @param string
	 */
	public static function setUser($user)
	{
		self::$user = $user;
	}

	/**
	 * Récupère le user de la requête
	 * 
	 * @return string
	 */
	public static function getUser()
	{
		return self::$user;
	}

	/**
	 * Est-ce que le user de la requête est positionné
	 * 
	 * @return boolean
	 */
	public static function issetUser()
	{
		return isset(self::$user);
	}

    /**
	 * Retourne l'url filtrée de l'application
     * 
	 * @return string
	 */
	public static function getURL()
	{
		$base_url = Config::get('base_url', '');
		$uri = str_replace($base_url, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
		$uri = self::parseInputValue($uri);

		return $uri;
	}

	/**
	 * Retourne le path complet filtré de l'application
	 * 
	 * @return string
	 */
	public static function getPath() 
	{
		$base_url = Config::get('base_url', '');
		$uri = str_replace($base_url, '', $_SERVER['REQUEST_URI']);
		$uri = self::parseInputValue($uri);

		return $uri;
	}

    /**
     * Retourne la liste des URIs utilisées pour les API
     * 
     * @return array
     */
    public static function getUris() 
    {
        $uri = trim(self::getURL(), '/');

        return explode('/', $uri);
    }

	/**
	 * Retourne le header filtré de la requête
     * 
	 * @return array
	 */
	public static function getHeaders()
	{
		$headers = apache_request_headers();

		return self::parseInputValue($headers);
	}

	/**
	 * Retourne la méthode de la requête
     * 
	 * @return string
	 */
	public static function getMethod()
	{
		return self::parseInputValue($_SERVER['REQUEST_METHOD']);
	}

    /**
	 * Read input value and convert it for internal use
	 * Performs stripslashes() and charset conversion if necessary
	 *
	 * @param  string   Field name to read
	 * @param  int      Source to get value from (GPC)
	 * @param  boolean  Allow HTML tags in field value
	 * @param  string   Charset to convert into
	 * @return string   Field value or NULL if not available
	 */
	public static function getInputValue($fname, $source, $allow_html = FALSE, $charset = NULL)
	{
		$value = NULL;

		if ($source == self::INPUT_GET) {
			if (isset($_GET[$fname]))
				$value = $_GET[$fname];
		} else if ($source == self::INPUT_POST) {
			if (isset($_POST[$fname]))
				$value = $_POST[$fname];
        } else if ($source == self::INPUT_COOKIE) {
            if (isset($_COOKIE[$fname]))
                $value = $_COOKIE[$fname];
		} else if ($source == self::INPUT_GPC) {
			if (isset($_POST[$fname]))
				$value = $_POST[$fname];
			else if (isset($_GET[$fname]))
				$value = $_GET[$fname];
			else if (isset($_COOKIE[$fname]))
				$value = $_COOKIE[$fname];
		}

		return self::parseInputValue($value, $allow_html, $charset);
	}

	/**
	 * Check if input value isset
	 *
	 * @param  string   Field name to read
	 * @param  int      Source to get value from (GPC)
	 * 
	 * @return boolean
	 */
	public static function issetInputValue($fname, $source)
	{
		if ($source == self::INPUT_GET) {
			return isset($_GET[$fname]);
		} else if ($source == self::INPUT_POST) {
			return isset($_POST[$fname]);
        } else if ($source == self::INPUT_COOKIE) {
			return isset($_COOKIE[$fname]);
		} else if ($source == self::INPUT_GPC) {
			return isset($_POST[$fname]) || isset($_GET[$fname]) || isset($_COOKIE[$fname]);
		}
		return false;
	}

	/**
	 * Vérifie si les inputs sont présents
	 * 
	 * @param array $inputs Liste des inputs à vérifier
	 * @param int $source Source to get value from (GPC)
	 * @param array $data Si on ne passe pas par la source on fourni les data
	 * @param boolean $error Envoyer une erreur à la response
	 * 
	 * @return boolean
	 */
	public static function checkInputValues($inputs, $source = null, $data = null, $error = true)
	{
		foreach ($inputs as $input) {
			$isset = false;
			// Vérifier si la valeur existe
			if (isset($source)) {
				$isset = self::issetInputValue($input, $source);
			}
			else if (isset($data)) {
				$isset = isset($data[$input]);
			}

			// Si la valeur n'existe pas erreur
			if (!$isset) {
				if ($error) {
					Response::error("Missing parameter '$input'");
				}
				return false;
			}
		}
		return true;
	}

	/**
	 * Read Json value and convert it for internal use
	 * Performs stripslashes() and charset conversion if necessary
     * 
	 * @return string	Field value or NULL if not available
	 */
	public static function getJsonValue()
	{
		$json = file_get_contents('php://input');

		return json_decode(self::parseInputValue($json));
	}

	/**
	 * Parse/validate input value. See get_input_value()
	 * Performs stripslashes() and charset conversion if necessary
	 *
	 * @param  string   Input value
	 * @param  boolean  Allow HTML tags in field value
	 * @return string   Parsed value
	 */
	private static function parseInputValue($value, $allow_html = FALSE)
	{
		if (empty($value))
			return $value;

		if (is_array($value)) {
			foreach ($value as $idx => $val)
				$value[$idx] = self::parseInputValue($val, $allow_html);
			return $value;
		}

		// strip single quotes if magic_quotes_sybase is enabled
		if (ini_get('magic_quotes_sybase'))
			$value = str_replace("''", "'", $value);
		// strip slashes if magic_quotes enabled
		else if (get_magic_quotes_gpc() || get_magic_quotes_runtime())
			$value = stripslashes($value);

		// remove HTML tags if not allowed
		if (!$allow_html)
			$value = strip_tags($value);

		return $value;
	}

	/**
	 * Retourne l'adresse IP du client
	 * 
	 * @return string
	 */
	public static function ipAddress() 
	{
		return isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Récupération du JSON envoyé en entrée
	 * 
	 * @return mixed false si échec sinon array
	 */
	public static function readJson()
	{
		return json_decode(file_get_contents('php://input'), true);
	}
}