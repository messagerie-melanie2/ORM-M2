<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * ORM Mél Copyright © 2021 Groupe Messagerie/MTE
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

/**
 * *** NE PAS MODIFIER CETTE PARTIE ****
 */
/* Définition des environnements pour les configurations externes */
/**
 * Configuration externe vers un répertoire
 */
define('TYPE_EXTERNAL', 'external');
/**
 * Configuration dans le répertoire config/ de l'ORM
 */
define('TYPE_INTERNAL', 'internal');

/**
 * Configuration externe pour une seule application
 */
define('MODE_SIMPLE', 'simple');
/**
 * Configuration externe pour plusieurs applications
 */
define('MODE_MULTIPLE', 'multiple');

/**
 * Configuration externe ou interne
 * La configuration TYPE_INTERNAL va lire les données dans le répertoire /config de l'ORM
 * Dans ce cas la configuration chargée sera fonction du ENVIRONNEMENT_LIBM2
 * La configuration TYPE_EXTERNAL va les lire les données dans un répertoire configuré dans CONFIGURATION_PATH_LIBM2
 */
define('DEFAULT_CONFIGURATION_TYPE_LIBM2', TYPE_EXTERNAL);

/**
 * **** CONFIGURATION INTERNE *****
 */
/**
 * Choix de l'environnement à configurer, si utilisation de la configuration interne
 */
define('DEFAULT_ENVIRONNEMENT_LIBM2', '');

/**
 * *** CONFIGURATION EXTERNE ******
 */
/**
 * Chemin vers la configuration externe
 */
define('DEFAULT_CONFIGURATION_PATH_LIBM2', '/etc/LibM2');

/**
 * MODE_SIMPLE ou MODE_MULTIPLE pour la configuration TYPE_EXTERNAL
 * Le MODE_SIMPLE va lire les données directement dans le CONFIGURATION_PATH
 * Le MODE_MULTIPLE permet de gérer plusieurs configuration dans le CONFIGURATION_PATH_LIBM2
 * Dans ce cas la configuration va être lu dans le répertoire correspondant au CONFIGURATION_APP_LIBM2
 * qui doit être configuré dans l'application
 */
define('DEFAULT_CONFIGURATION_MODE_LIBM2', MODE_SIMPLE);