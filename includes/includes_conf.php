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

/* Chargement de l'environnement */
require_once(__DIR__ . '/../src/Config/confenv.php');
if (@file_exists(CONFIGURATION_PATH_LIBM2. '/env.php')) {
  require_once(CONFIGURATION_PATH_LIBM2. '/env.php');
}
else if (@file_exists(__DIR__ . '/../env.php')) {
  require_once(__DIR__ . '/../env.php');
}

// Chargement de la configuration de l'application
if (CONFIGURATION_TYPE_LIBM2 == TYPE_EXTERNAL) {
    // Type de configuration externe, a aller chercher dans /etc
    if (CONFIGURATION_MODE_LIBM2 == MODE_SIMPLE) {
        // Chargement de la configuration dans le répertoire de conf de l'ORM
        require_once(CONFIGURATION_PATH_LIBM2.'/includes.php');
    }
    else if (CONFIGURATION_MODE_LIBM2 == MODE_MULTIPLE) {
        require_once(CONFIGURATION_PATH_LIBM2.'/'.CONFIGURATION_APP_LIBM2.'/includes.php');
    }
}
else if (CONFIGURATION_TYPE_LIBM2 == TYPE_INTERNAL) {
    // Type de configuration interne à l'ORM
    /* Chargement de la configuration de l'application en fonction de l'environnement */
    require_once(__DIR__ . '/../config/'.ENVIRONNEMENT_LIBM2.'/includes.php');
}
/* Chargement de la configuration de mapping */
require_once(__DIR__ . '/../src/Config/MappingMelanie.php');