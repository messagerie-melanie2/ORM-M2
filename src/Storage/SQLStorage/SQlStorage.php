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

namespace LibMelanie\Storage\SQLStorage;

/**
 * Storage class
 * 
 * Cette classe est la classe dédiée au stockage des fichiers dans une base de données SQL
 * 
 * @package LibMelanie
 * @subpackage SQLStorage
 */

use LibMelanie\Lib\MceObject;
use LibMelanie\Log\M2Log;
use LibMelanie\Storage\IStorage;
use LibMelanie\Sql;
use LibMelanie\Config\MappingMce;

class SQLStorage extends MceObject implements IStorage
{
    private static $instance = null;

    private function __construct()
    {
        // Défini la classe courante
        $this->get_class = get_class($this);

        M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->__construct()");
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new SQLStorage();
        }

        return self::$instance;
    }

    public function write($path, $contents)
    {
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->write()");

        $response = false;
        try {
            // Replace
            // $query = Sql\SqlAttachmentRequests::updateAttachment;
            // $query = str_replace("{attachment_set}", $update, $query);
            // $query = str_replace("{where_clause}", $whereClause, $query);

            // Replace
            // $query = str_replace("{data_fields}", $data_fields, Sql\SqlAttachmentRequests::insertAttachment);
            // $query = str_replace("{data_values}", $data_values, $query);

            // // Execute
            // $this->isExist = Sql\Sql::GetInstance()->executeQuery($query, $params);

            $response = true;
        } catch (\Exception $exception) {
            M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->write(" . $path . ", fileContent) Exception: " . $exception);
        }

        return $response;
    }

    public function read($path)
    {
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->read()");

        $response = null;
        try {
            // Paramètres de la requête
            $params = [];
            $whereClause = "";
            // Test si les clés primaires sont bien instanciées et les ajoute en paramètres
            foreach ($this->primaryKeys as $key) {
                if (!isset($this->$key))
                    return false;
                // Récupèration des données de mapping
                if (
                    isset(MappingMce::$Data_Mapping[$this->objectType])
                    && isset(MappingMce::$Data_Mapping[$this->objectType][$key])
                ) {
                    $mapKey = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
                } else {
                    $mapKey = $key;
                }
                $params[$mapKey] = $this->$key;
                if ($whereClause != "")
                    $whereClause .= " AND ";
                $whereClause .= "$mapKey = :$mapKey";
            }
            // Chargement de la requête
            $query = Sql\SqlAttachmentRequests::getAttachmentData;
            // Clause where
            $query = str_replace("{where_clause}", $whereClause, $query);

            Sql\Sql::GetInstance()->executeQueryToObject($query, $params, $response);
        } catch (\Exception $exception) {
            M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->read() Exception: " . $exception);
        }

        return $response;
    }

    public function delete($path)
    {
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->delete()");

        $response = false;
        try {
            // $query = Sql\SqlAttachmentRequests::deleteAttachment;
            // // Clause where
            // $query = str_replace("{where_clause}", $path, $query);

            // // Supprimer l'évènement
            // $response = Sql\Sql::GetInstance()->executeQuery($query, $params);
        } catch (\Exception $exception) {
            M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->delete(" . $path . ") Exception: " . $exception);
        }

        return $response;
    }
}