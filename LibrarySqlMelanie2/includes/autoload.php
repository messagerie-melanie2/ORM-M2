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
// ----------------------------------------------------------------------------
// Enregistrement de l'autoloader
// Les classes étant dans des namespaces respectant l'arborescence du projet
// elles sont chargées automatiquement
// ----------------------------------------------------------------------------

/**
 * Méthode de chargement automatique des classes
 * @param string $pClassName
 */
function libm2_autoload($pClassName) {
    // Ne charger que les classe de la librairie LibMelanie
    if (strpos($pClassName, 'LibMelanie') === false)
        return;
    // Définition du nom du fichier et du chemin
    $dir_class = $pClassName . '.php';
    // Remplace les \ du namespace par /
    $dir_class = str_replace('\\', '/', $dir_class);
    // Enleve le LibMelanie\ du namespace
    $dir_class = str_replace('LibMelanie/', '', $dir_class);
    // Charge la classe
    include_once(__DIR__ . '/../src/'.$dir_class);
}

// Appel l'autoload register qui va utiliser notre méthode autoload
spl_autoload_register("libm2_autoload", true, true);