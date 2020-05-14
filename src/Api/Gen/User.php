<?php
/**
 * Ce fichier est développé pour la gestion de la lib MCE
 * 
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * 
 * ORM Mél Copyright © 2020 Groupe Messagerie/MTES
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
namespace LibMelanie\Api\Gen;

use LibMelanie\Api\Defaut;
use LibMelanie\Api\Gen\Users\Outofoffice;
use LibMelanie\Api\Gen\Users\Share;
use LibMelanie\Log\M2Log;

/**
 * Classe utilisateur pour Gen
 * 
 * @author Groupe Messagerie/MTES - Apitech
 * @package LibMCE
 * @subpackage API/Gen
 * @api
 * 
 * @property string $dn DN de l'utilisateur dans l'annuaire            
 * @property string $uid Identifiant unique de l'utilisateur
 * @property string $fullname Nom complet de l'utilisateur
 * @property string $name Nom de l'utilisateur
 * @property string $type Type de boite (voir Mce\Users\Type::*)
 * @property string $email Adresse email principale de l'utilisateur
 * @property array $email_list Liste de toutes les adresses email de l'utilisateur
 * @property string $email_send Adresse email d'émission principale de l'utilisateur
 * @property array $email_send_list Liste de toutes les adresses email d'émission de l'utilisateur
 * @property Share[] $shares Liste des partages de la boite
 * @property-read array $supported_shares Liste des droits supportés par cette boite
 * @property string $away_response Message d'absence de l'utilisateur (TODO: Objet pour traiter la syntaxe)
 * @property integer $internet_access_admin Accés Internet positionné par l'administrateur
 * @property integer $internet_access_user Accés Internet positionné par l'utilisateur
 * @property-read boolean $internet_access_enable Est-ce que l'accès Internet de l'utilisateur est activé
 * @property array $server_routage Champ de routage pour le serveur de message de l'utilisateur
 * @property-read string $server_host Host du serveur de messagerie de l'utilisateur
 * @property-read string $server_user User du serveur de messagerie de l'utilisateur
 * 
 * @property-read boolean $is_objectshare Est-ce que cet utilisateur est en fait un objet de partage
 * @property-read ObjectShare $objectshare Retourne l'objet de partage lié à cet utilisateur si s'en est un
 * 
 * @method string getTimezone() [OSOLETE] Chargement du timezone de l'utilisateur
 * @method bool authentification($password, $master = false) Authentification de l'utilisateur sur l'annuaire Mélanie2
 * @method bool save() Enregistrement de l'utilisateur dans l'annuaire
 * @method bool load() Charge les données de l'utilisateur depuis l'annuaire Mélanie2 (en fonction de l'uid ou l'email)
 * @method bool exists() Est-ce que l'utilisateur existe dans l'annuaire Mélanie2 (en fonction de l'uid ou l'email)
 */
class User extends Defaut\User {
	/**
	 * Configuration du délimiteur pour le server host
	 * 
	 * @var string
	 */
  const SERVER_HOST_DELIMITER = '%';

  // **** Configuration des filtres et des attributs par défaut
  /**
   * Filtre pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_FILTER = "";
  /**
   * Filtre pour la méthode load() avec un email
   * 
   * @ignore
   */
  const LOAD_FROM_EMAIL_FILTER = "";
  /**
   * Filtre pour la méthode getBalp()
   * 
   * @ignore
   */
  const GET_BALP_FILTER = "";
  /**
   * Filtre pour la méthode getBalpEmission()
   * 
   * @ignore
   */
  const GET_BALP_EMISSION_FILTER = "";
  /**
   * Filtre pour la méthode getBalpGestionnaire()
   * 
   * @ignore
   */
  const GET_BALP_GESTIONNAIRE_FILTER = "";
  /**
   * Filtre pour la méthode getGroups()
   * 
   * @ignore
   */
  const GET_GROUPS_FILTER = "";
  /**
   * Filtre pour la méthode getGroupsIsMember()
   * 
   * @ignore
   */
  const GET_GROUPS_IS_MEMBER_FILTER = null;
  /**
   * Filtre pour la méthode getListsIsMember()
   * 
   * @ignore
   */
  const GET_LISTS_IS_MEMBER_FILTER = null;

  /**
   * Configuration du mapping qui surcharge la conf
   */
  protected static $mapping = [];

  /**
   * ***************************************************
   * DATA MAPPING
   */
  /**
   * Mapping uid field
   *
   * @param string $uid
   */
  protected function setMapUid($uid) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapUid(" . (is_string($uid) ? $uid : "") . ")");
    if (!isset($this->objectmelanie)) {
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    }
    $this->objectmelanie->uid = $uid;
  }

  /**
   * Récupération du champ internet_access_enable
   * 
   * @return boolean true si l'access internet de l'utilisateur est activé, false sinon
   */
  protected function getMapInternet_access_enable() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapInternet_access_enable()");
    return true;
  }

  /**
   * Mapping shares field
   *
   * @param Share[] $shares
   */
  protected function setMapShares($shares) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapShares()");
    if (!isset($this->objectmelanie)) {
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    }
    $this->_shares = $shares;
    $_shares = [];
    foreach ($shares as $share) {
      $right = '';
      switch ($share->type) {
        case Share::TYPE_ADMIN:
          $right = 'G';
          break;
        case Share::TYPE_SEND:
          $right = 'C';
          break;
        case Share::TYPE_WRITE:
          $right = 'E';
          break;
        case Share::TYPE_READ:
          $right = 'L';
          break;
      }
      $_shares[] = $share->user . ':' . $right;
    }
    $this->objectmelanie->shares = $_shares;
  }

  /**
   * Mapping shares field
   * 
   * @return Share[] Liste des partages positionnés sur cette boite
   */
  protected function getMapShares() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapShares()");
    if (!isset($this->_shares)) {
      $_shares = $this->objectmelanie->shares;
      $this->_shares = [];
      foreach ($_shares as $_share) {
        $share = new Share();
        list($share->user, $right) = \explode(':', $_share, 2);
        switch (\strtoupper($right)) {
          case 'G':
            $share->type = Share::TYPE_ADMIN;
            break;
          case 'C':
            $share->type = Share::TYPE_SEND;
            break;
          case 'E':
            $share->type = Share::TYPE_WRITE;
            break;
          case 'L':
            $share->type = Share::TYPE_READ;
            break;
        }
        $this->_shares[$share->user] = $share;
      }
    }
    return $this->_shares;
  }

  /**
   * Mapping shares field
   * 
   * @return array Liste des partages supportés par cette boite ([Share::TYPE_*])
   */
  protected function getMapSupported_shares() {
    return [Share::TYPE_ADMIN, Share::TYPE_SEND, Share::TYPE_WRITE, Share::TYPE_READ];
  }

  /**
   * Mapping shares field
   * 
   * @return boolean false non supporté
   */
  protected function setMapSupported_shares() {
    return false;
  }

  /**
   * Récupération du champ server_host
   * 
   * @return mixed|NULL Valeur du serveur host, null si non trouvé
   */
  protected function getMapServer_host() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapServer_host()");
    foreach ($this->server_routage as $route) {
			if (strpos($route, self::SERVER_HOST_DELIMITER) !== false) {
				$route = explode('@', $route, 2);
				return $route[1];
			}
    }
    return null;
  }

  /**
   * Récupération du champ server_user
   * 
   * @return mixed|NULL Valeur du serveur user, null si non trouvé
   */
  protected function getMapServer_user() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapServer_user()");
    foreach ($this->server_routage as $route) {
			if (strpos($route, self::SERVER_HOST_DELIMITER) !== false) {
				$route = explode('@', $route, 2);
				return $route[0];
			}
    }
    return null;
  }

  /**
   * Récupération du champ out of offices
   * 
   * @return Outofoffice[] Tableau de d'objets Outofoffice
   */
  protected function getMapOutofoffices() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapOutofoffices()");
    return [];
	}
	
  /**
   * Positionnement du champ out of offices
   * 
   */
  protected function setMapOutofoffices($OofObjects) {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapOutofoffices()");
		return null;
	}
}
