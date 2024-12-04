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
namespace LibMelanie\Ldap;

/**
 * Singleton de connexion à l'annuaire
 * TODO: n'est plus utile, on la conserve en attendant que les applis passe à la nouvelle utilisation de l'appli ldap
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage LDAP
 *
 */
class LDAPMelanie {
	/**
	 *  Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() { }

	/**
	 * Athentification sur le serveur LDAP
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	public static function Authentification ($username, $password) {
		return Ldap::Authentification($username, $password);
	}

	/**
	 * Return les boites partagées accessible pour un utilisateur depuis le LDAP
	 * @param string $username
	 * @return mixed cn mineqmelmailemission uid
	 */
	public static function GetInformations($username) {
		return Ldap::GetUserInfos($username);
	}

	/**
	 * Return les informations sur un utilisateur depuis le LDAP
	 * @param string $username
	 * @return dn cn mail
	 */
	public static function GetBalp($username) {
		return Ldap::GetUserBalPartagees($username);
	}

	/**
	 * Return les informations sur un utilisateur depuis le LDAP
	 * @param string $username
	 * @return dn cn mail
	 */
	public static function GetEmissionBal($username) {
	    return Ldap::GetUserBalEmission($username);
	}

	/**
	 * Return les informations sur un utilisateur depuis son adresse email depuis le LDAP
	 * @param string $email
	 * @return dn cn uid
	 */
	public static function GetInformationsFromMail ($email) {
		return Ldap::GetUserInfosFromEmail($email);
	}

	/**
	 * Return l'uid de l'utilisateur depuis son adresse email depuis le LDAP
	 * @param string $email
	 * @return string $uid
	 */
	public static function GetUidFromMail($email) {
		$infos = Ldap::GetUserInfosFromEmail($email);
		if (is_null($infos)) return null;
		$ldap = Ldap::GetInstance(\LibMelanie\Config\Ldap::$SEARCH_LDAP);
		return isset($infos[$ldap->getMapping('uid')]) ? $infos[$ldap->getMapping('uid')][0] : null;
	}

	/**
	 * Return l'email de l'utilisateur depuis son uid depuis le LDAP
	 * @param string $uid
	 * @return string $email
	 */
	public static function GetMailFromUid ($uid) {
		$infos = Ldap::GetUserInfos($uid);
		if (is_null($infos)) return null;
		$ldap = Ldap::GetInstance(\LibMelanie\Config\Ldap::$SEARCH_LDAP);
		return $infos[$ldap->getMapping('mail', 'mineqmelmailemission')][0];
	}

	/**
	 * Return le nom de l'utilisateur depuis son uid depuis le LDAP
	 * @param string $uid
	 * @return string $email
	 */
	public static function GetNameFromUid ($uid) {
		$infos = Ldap::GetUserInfos($uid);
		if (is_null($infos)) return null;
		$ldap = Ldap::GetInstance(\LibMelanie\Config\Ldap::$SEARCH_LDAP);
		return $infos[$ldap->getMapping('cn')][0];
	}
}
?>