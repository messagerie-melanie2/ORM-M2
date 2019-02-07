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
namespace LibMelanie\Lib;

use LibMelanie\Config\Config;
use LibMelanie\Log\M2Log;

/**
 * Classe de gestion des selaformes
 * Permet de limiter le nombre de connexion SQL simultanées par serveur
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Lib
 *
 */
class Selaforme {
    /**
     * Test la récupération d'un selaforme en se basant sur le lock des fichiers et les valeurs configurées
     * @param number $max
     * @param string $base
     * @return resource|boolean
     */
    public static function selaforme_acquire($max = 50, $base = "/tmp/_SeLaFoRmE_")
    {
        M2Log::Log(M2Log::LEVEL_DEBUG, "Selaforme->selaforme_acquire($max, $base)");
        for ($j = Config::get(Config::SEL_NB_ESSAI); $j > 0; $j--) {
            for ($i = 1; $i <= $max; $i++) {
                $fp = fopen($base.$i, "w+");
                if(flock($fp, LOCK_EX | LOCK_NB)) return $fp;
                fclose($fp);
            }
            M2Log::Log(M2Log::LEVEL_INFO, "Selaforme->selaforme_acquire() wait " . Config::get(Config::SEL_TEMPS_ATTENTE));
            usleep(Config::get(Config::SEL_TEMPS_ATTENTE));
        }
        M2Log::Log(M2Log::LEVEL_ERROR, "Selaforme->selaforme_acquire() pas de selaforme libre");
        return false;
    }
    /**
     * Libère un selaforme
     * @param resource $fp
     */
    public static function selaforme_release($fp)
    {
        M2Log::Log(M2Log::LEVEL_DEBUG, "Selaforme->selaforme_release()");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}