<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * 
 * ORM Mél Copyright © 2022 Groupe Messagerie/MTE
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

namespace LibMelanie\Storage;

/**
 * Storage class
 * 
 * Cette classe est la classe de base pour les classes de stockage (s3, local, postgresql et Swift)
 * 
 * @package LibMelanie
 * @subpackage Storage
 */

interface IStorage
{
    /**
     * Create a new file at the specified path with the given contents, if the file already exists, it will be overwritten.
     * @param string $path The path where the file should be created.
     * @param string $contents The contents of the file to be created.
     * @return bool True on success, false on failure.
     */
    public function write(string $path, string $contents);

    /**
     * Read the contents of a file at the specified path.
     * @param string $path The path of the file to be read.
     * @return string | null The contents of the file, or null on failure.
     */
    public function read(string $path);

    /**
     * Delete a file at the specified path.
     * @param string $path The path of the file to be deleted.
     * @return bool True on success, false on failure.
     */
    public function delete(string $path);
}



