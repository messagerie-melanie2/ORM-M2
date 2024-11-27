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
 * Classe de fonctions utilitaires pour les API
 * 
 * @package Lib
 */
class Utils {
    /**
     * Retourne l'utilisateur courant basé sur l'auth Basic ou la requête
     * 
     * @param string $param Nom du paramètre pour récupérer l'uid depuis la requête ou le json
     * @param string $source Type de requête (GET ou POST)
     * @param array $json Si les données doivent plutôt être récupérees du json
     * 
     * @return \LibMelanie\Api\Defaut\User|null Null si non trouvé
     */
    public static function getCurrentUser($param = 'user', $source = Request::INPUT_GET, $json = null)
    {
        $user = null;

        // Forcer l'uid dans le cas d'un user Basic
        if (Request::issetUser()) {
            $user = Objects::gi()->user();
            $user->uid = Request::getUser();
        }
        else if (isset($json)) {
            if (isset($json[$param])) {
                $user = Objects::gi()->user();
                $user->uid = $json[$param];
            }
        }
        else {
            $uid = Request::getInputValue($param, $source);
            if (isset($uid)) {
                $user = Objects::gi()->user();
                $user->uid = $uid;
            }
        }

        return $user;
    }
}