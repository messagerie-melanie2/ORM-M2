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

use LibMelanie\Lib\MagicObject;
use LibMelanie\Interfaces\IObjectMelanie;
use LibMelanie\Sql;
use LibMelanie\Config\ConfigSQL;
use LibMelanie\Config\MappingMce;
use LibMelanie\Log\M2Log;

/**
 * Traitement de l'historique dans Horde
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage ORM
 *
 */
class HistoryMelanie extends MagicObject implements IObjectMelanie {
	/**
	 * Constructeur par défaut, appelé par PDO
	 */
	public function __construct() {
	    // Défini la classe courante
	    $this->get_class = get_class($this);

		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->__construct()");

		// Récupération du type d'objet en fonction de la class
		$this->objectType = explode('\\',$this->get_class);
		$this->objectType = $this->objectType[count($this->objectType)-1];

		if (isset(MappingMce::$Primary_Keys[$this->objectType])) {
			if (is_array(MappingMce::$Primary_Keys[$this->objectType])) $this->primaryKeys = MappingMce::$Primary_Keys[$this->objectType];
			else $this->primaryKeys = [MappingMce::$Primary_Keys[$this->objectType]];
		}
	}

	/**
	 * charger l'objet
	 */
	public function load() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->load()");
		// Si les clés primaires ne sont pas définis, impossible de charger l'objet
		if (!isset($this->primaryKeys)) return false;
		// Test si l'objet existe, pas besoin de load
		if (is_bool($this->isExist) && $this->isLoaded) {
		  return $this->isExist;
		}
		// Paramètres de la requête
		$params = [];
		// Test si les clés primaires sont bien instanciées et les ajoute en paramètres
		foreach ($this->primaryKeys as $key) {
			if (!isset($this->$key)) return false;
			// Récupèration des données de mapping
			if (isset(MappingMce::$Data_Mapping[$this->objectType])
						&& isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
				$mapKey = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
			} else {
				$mapKey = $key;
			}
			$params[$mapKey] = $this->$key;
		}

		// Liste les history
		$this->isExist = Sql\Sql::GetInstance()->executeQueryToObject(Sql\SqlHistoryRequests::getHistory, $params, $this);
		if ($this->isExist) {
		  $this->initializeHasChanged();
		}
		$this->isLoaded = true;
		return $this->isExist;
	}

	/**
	 * Sauvegarder l'objet
	 * @return boolean True si c'est une command Insert, False si c'est un Update
	 */
	public function save() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->save()");
		$insert = false;
		// Si les clés primaires ne sont pas définis, impossible de charger l'objet
		if (!isset($this->primaryKeys)) return null;

		// Ne rien sauvegarder si rien n'a changé
		$haschanged = false;
		foreach ($this->haschanged as $value) {
			$haschanged = $haschanged || $value;
			if ($haschanged) break;
		}
		if (!$haschanged) return null;
		// Si isExist est à null c'est qu'on n'a pas encore testé
		if (!is_bool($this->isExist)) {
		  $this->isExist = $this->exists();
		}
		// Si l'objet existe on fait un UPDATE
		if ($this->isExist) {
			// Paramètres de la requête
			$params = [];
			// Test si les clés primaires sont bien instanciées et les ajoute en paramètres
			foreach ($this->primaryKeys as $key) {
				if (!isset($this->$key)) return null;
				// Récupèration des données de mapping
				if (isset(MappingMce::$Data_Mapping[$this->objectType])
							&& isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
					$mapKey = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
				} else {
					$mapKey = $key;
				}
				$params[$mapKey] = $this->$key;
			}

			// Liste les modification à faire
			$update = "";
			foreach ($this->haschanged as $key => $value) {
				if ($value && !isset($params[$key])) {
					if ($update != "") $update .= ", ";
					$update .= "$key = :$key";
					$params[$key] = $this->$key;
				}
			}
			// Pas d'update
			if ($update == "") return null;

			// Replace
			$query = str_replace("{history_set}", $update, Sql\SqlHistoryRequests::updateHistory);

			// Execute
			$this->isExist = Sql\Sql::GetInstance()->executeQuery($query, $params);
		} else {
			// C'est une Insertion
			$insert = true;
			// Test si les clés primaires sont bien instanciées
			foreach ($this->primaryKeys as $key) {
				if (!isset($this->$key)) return null;
			}

			// Gestion de history_id
			if (!isset($this->id)) Sql\Sql::GetInstance()->executeQueryToObject(Sql\SqlHistoryRequests::getNextHistory, null, $this);

			// Si l'objet n'existe pas, on fait un INSERT
			// Liste les insertion à faire
			$data_fields = "";
			$data_values = "";
			$params = [];
			foreach ($this->haschanged as $key => $value) {
				if ($value) {
					if ($data_fields != "") $data_fields .= ", ";
					if ($data_values != "") $data_values .= ", ";
					$data_fields .= $key;
					$data_values .= ":".$key;
					$params[$key] = $this->$key;
				}
			}
			// Pas d'insert
			if ($data_fields == "") return null;

			// Replace
			$query = str_replace("{data_fields}", $data_fields, Sql\SqlHistoryRequests::insertHistory);
			$query = str_replace("{data_values}", $data_values, $query);

			// Execute
			$this->isExist = Sql\Sql::GetInstance()->executeQuery($query, $params);
		}
		if ($this->isExist) $this->initializeHasChanged();
		return $insert;
	}

	/**
	 * Suppression de l'objet
	 * @return boolean
	 */
	public function delete() {
		return false;
	}

	/**
	 * Est-ce que l'objet existe déjà
	 */
	public function exists() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->exists()");
		// Si les clés primaires ne sont pas définis, impossible de charger l'objet
		if (!isset($this->primaryKeys)) return false;
		// Test si l'objet existe, pas besoin de load
		if (is_bool($this->isExist)) {
		  return $this->isExist;
		}
		// Paramètres de la requête
		$params = [];
		// Test si les clés primaires sont bien instanciées et les ajoute en paramètres
		foreach ($this->primaryKeys as $key) {
			if (!isset($this->$key)) return false;
			// Récupèration des données de mapping
			if (isset(MappingMce::$Data_Mapping[$this->objectType])
						&& isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
				$mapKey = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
			} else {
				$mapKey = $key;
			}
			$params[$mapKey] = $this->$key;
		}

		// Liste les history
		$res = Sql\Sql::GetInstance()->executeQuery(Sql\SqlHistoryRequests::getHistory, $params);
		$this->isExist = (count($res) >= 1);
		return $this->isExist;
	}
}