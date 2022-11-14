<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 *
 * ORM M2 Copyright © 2020  Groupe MCD/MTES
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
namespace LibMelanie;

/**
 * Cette classe permet d'avoir le numéro de version de l'ORM
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 */
class Version {

    /**
     * Numéro de version
     */
    const VERSION = '0.6.4.1';

    /**
     * Numéro de version normalisé
     */
    const NORMALIZED_VERSION = '0.6.4.1';

    /**
     * Build
     */
    const BUILD = '20221114162052';

}
