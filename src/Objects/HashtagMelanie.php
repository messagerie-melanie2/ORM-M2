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
namespace LibMelanie\Objects;

use LibMelanie\Sql;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\MappingMce;

/**
 * Traitement des hashtags Melanie2
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage ORM
 */
class HashtagMelanie extends ObjectMelanie {
    /**
	 * Constructeur par défaut, appelé par PDO
	 */
	public function __construct() {
	    parent::__construct('WorkspaceHashtag');
	}

    /**
	 * Supprime l'objet
	 * @return boolean
	 */
	public function delete() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->delete()");
		// Si les clés primaires et la table ne sont pas définies, impossible de supprimer l'objet
		if (!isset($this->primaryKeys)) return false;
        if (!isset($this->tableName)) return false;
        // Gestion de la requête
        if (isset($this->id)) {
            $primaryKeys = ['id'];
        }
        else if (isset($this->label)) {
            $primaryKeys = ['label'];
        }
        else {
            return false;
        }
		// Paramètres de la requête
		$params = [];
		$whereClause = "";
		// Test si les clés primaires sont bien instanciées et les ajoute en paramètres
		foreach ($primaryKeys as $key) {
			if (!isset($key)) return false;
			// Récupèration des données de mapping
			if (isset(MappingMce::$Data_Mapping[$this->objectType])
					&& isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
				$mapKey = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
			} else {
				$mapKey = $key;
			}
			$params[$mapKey] = $this->$key;
			if ($whereClause != "") $whereClause .= " AND ";
			$whereClause .= "$mapKey = :$mapKey";
		}

		$query = Sql\SqlObjectRequests::deleteObject;
		// Nom de la table
		$query = str_replace("{table_name}", $this->tableName, $query);
		// Clause where
		$query = str_replace("{where_clause}", $whereClause, $query);

		// Supprimer l'évènement
		$ret = Sql\Sql::GetInstance()->executeQuery($query, $params);
		if ($ret) {
		  $this->initializeHasChanged();
		  $this->isExist = false;
		}
		return $ret;
	}
}