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
use LibMelanie\Config\MappingMce;
use LibMelanie\Exceptions\UndefinedMappingException;
use LibMelanie\Log\M2Log;

/**
* Classe de gestion d'un objet Melanie2
* Penser à configurer le MappingMce pour les clés et le mapping
*
* @author PNE Messagerie/Apitech
* @package Librairie Mélanie2
* @subpackage ORM
*
*/
class ObjectMelanie extends MagicObject implements IObjectMelanie {
	/**
	* Nom de la table SQL liée à l'objet
	* @var string $tableName
	*/
	public $tableName;
	
	/**
	* Requête SQL pour récupérer un objet
	* 
	* @var string $sqlGetObject
	*/
	protected static $sqlGetObject = Sql\SqlObjectRequests::getObject;
	
	/**
	* Requête SQL pour mettre à jour un objet
	* 
	* @var string $sqlUpdateObject
	*/
	protected static $sqlUpdateObject = Sql\SqlObjectRequests::updateObject;
	
	/**
	* Requête SQL pour insérer un objet
	* 
	* @var string $sqlInsertObject
	*/
	protected static $sqlInsertObject = Sql\SqlObjectRequests::insertObject;
	
	/**
	* Requête SQL pour supprimer un objet
	* 
	* @var string $sqlDeleteObject
	*/
	protected static $sqlDeleteObject = Sql\SqlObjectRequests::deleteObject;
	
	/**
	* Constructeur par défaut, appelé par PDO
	* @param string $objectType Type de l'objet (optionnel)
	* @param string/array $primaryKeys La ou les clé primaire pour la gestion de l'objet (optionnel)
	*/
	public function __construct($objectType = null, $primaryKeys = null) {
		// Défini la classe courante
		$this->get_class = get_class($this);
		
		// Construteur appelé par PDO, ne fait rien pour l'instant
		// L'appel a pdoConstruct sera fait ensuite
		if (!isset($objectType)) return;
		
		// Intialisation du type d'objet
		$this->objectType = $objectType;
		
		// Intialisation du nom de la table
		if (!isset(MappingMce::$Table_Name[$this->objectType])) throw new UndefinedMappingException($this->objectType . ":TableName");
		$this->tableName = MappingMce::$Table_Name[$this->objectType];
		
		// Intialisation des clés primaires de la table
		if (isset($primaryKeys)) {
			if (is_array($primaryKeys)) $this->primaryKeys = $primaryKeys;
			else $this->primaryKeys = [$primaryKeys];
		} elseif (isset(MappingMce::$Primary_Keys[$this->objectType])) {
			if (is_array(MappingMce::$Primary_Keys[$this->objectType])) $this->primaryKeys = MappingMce::$Primary_Keys[$this->objectType];
			else $this->primaryKeys = [MappingMce::$Primary_Keys[$this->objectType]];
		}
	}
	
	/**
	* String representation of object
	*
	* @return string
	*/
	public function serialize() {
		return serialize([
			'data'        => $this->data,
			'isExist'     => $this->isExist,
			'isLoaded'    => $this->isLoaded,
			'objectType'  => $this->objectType,
			'primaryKeys' => $this->primaryKeys,
			'get_class'   => $this->get_class,
			'tableName'   => $this->tableName,
		]);
	}
	
	/**
	* Constructs the object
	*
	* @param string $serialized
	* @return void
	*/
	public function unserialize($serialized) {
		$array = unserialize($serialized);
		if ($array) {
			$this->data = $array['data'];
			$this->isExist = $array['isExist'];
			$this->isLoaded = $array['isLoaded'];
			$this->objectType = $array['objectType'];
			$this->primaryKeys = $array['primaryKeys'];
			$this->get_class = $array['get_class'];
			$this->tableName = $array['tableName'];
		}
	}
	
	/**
	* Charge l'objet
	*/
	public function load() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."\\".$this->objectType."->load()");

		// Si les clés primaires et la table ne sont pas définies, impossible de charger l'objet
		if (!isset($this->primaryKeys)) return false;
		if (!isset($this->tableName)) return false;

		// Test si l'objet existe, pas besoin de load
		if (is_bool($this->isExist) && $this->isLoaded) {
			return $this->isExist;
		}

		// Paramètres de la requête
		$params = [];
		$whereClause = "";

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
			if ($whereClause != "") $whereClause .= " AND ";
			$whereClause .= "$mapKey = :$mapKey";
		}

		// Chargement de la requête
		$query = static::$sqlGetObject;
		// Liste des champs
		$query = str_replace("{fields_list}", $this->_getAllFields(), $query);
		// Nom de la table
    $query = str_replace("{table_name}", $this->_getTableName(), $query);
		// Clause where
		$query = str_replace("{where_clause}", ' WHERE ' . $whereClause, $query);
		
		// Récupération
		$this->isExist = Sql\Sql::GetInstance()->executeQueryToObject($query, $params, $this);
		if ($this->isExist) {
			$this->initializeHasChanged();
		}
		$this->isLoaded = true;

		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->load() isExist: ".$this->isExist);
		return $this->isExist;
	}
	
	/**
	* Sauvegarde l'objet
	* @return boolean True si c'est une command Insert, False si c'est un Update
	*/
	public function save() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."\\".$this->objectType."->save()");
		$insert = false;

		// Si les clés primaires et la table ne sont pas définies, impossible de charger l'objet
		if (!isset($this->primaryKeys)) {
			M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->save() No primaryKeys");
			return null;
		}

		if (!isset($this->tableName)) {
			M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->save() No tableName");
			return null;
		}
		
		// Ne rien sauvegarder si rien n'a changé
		$haschanged = false;
		foreach ($this->haschanged as $value) {
			$haschanged = $haschanged || $value;
			if ($haschanged) break;
		}

		if (!$haschanged) {
			M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->save() Nothing has changed");
			return null;
		}

		// Si isExist est à null c'est qu'on n'a pas encore testé
		if (!is_bool($this->isExist)) {
			$this->isExist = $this->exists();
		}

		// Si l'objet existe on fait un UPDATE
		if ($this->isExist) {
			// Paramètres de la requête
			$params = [];
			$whereClause = "";
			// Test si les clés primaires sont bien instanciées et les ajoute en paramètres
			foreach ($this->primaryKeys as $key) {
				if (!isset($this->$key)) {
					M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->save() Update $key is null");
					return null;
				}

				// Récupèration des données de mapping
				if (isset(MappingMce::$Data_Mapping[$this->objectType])
				    && isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
					$mapKey = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
				} else {
					$mapKey = $key;
				}

				if ($this->haschanged[$mapKey]) {
					$params["where_$mapKey"] = $this->oldData[$mapKey];
				}
				else {
					$params["where_$mapKey"] = $this->$key;
				}
				
				if ($whereClause != "") $whereClause .= " AND ";
				$whereClause .= "$mapKey = :where_$mapKey";
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
			if ($update == "") {
				M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->save() Update is null");
				return null;
			}
			
			// Replace
			$query = static::$sqlUpdateObject;
			$query = str_replace("{table_name}", $this->tableName, $query);
			$query = str_replace("{object_set}", $update, $query);
			$query = str_replace("{where_clause}", $whereClause, $query);
			
			// Execute
			$this->isExist = Sql\Sql::GetInstance()->executeQuery($query, $params);
		} else {
			// C'est une Insertion
			$insert = true;
			// Test si les clés primaires sont bien instanciées
			foreach ($this->primaryKeys as $key) {
				if (!isset($this->$key)) {
					M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->save() Insert $key is null");
					return null;
				}
			}
			
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
			if ($data_fields == "") {
				M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->save() Insert is null");
				return null;
			}
			
			// Replace
			$query = static::$sqlInsertObject;
			$query = str_replace("{table_name}", $this->tableName, $query);
			$query = str_replace("{data_fields}", $data_fields, $query);
			$query = str_replace("{data_values}", $data_values, $query);
			
			// Execute
			$this->isExist = Sql\Sql::GetInstance()->executeQuery($query, $params);
		}
		if ($this->isExist) $this->initializeHasChanged();
		return $insert;
	}
	
	/**
	* Supprime l'objet
	* @return boolean
	*/
	public function delete() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."\\".$this->objectType."->delete()");

		// Si les clés primaires et la table ne sont pas définies, impossible de supprimer l'objet
		if (!isset($this->primaryKeys)) return false;
		if (!isset($this->tableName)) return false;

		// Paramètres de la requête
		$params = [];
		$whereClause = "";
		// Test si les clés primaires sont bien instanciées et les ajoute en paramètres
		foreach ($this->primaryKeys as $key) {
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
		
		$query = static::$sqlDeleteObject;
		// Nom de la table
		$query = str_replace("{table_name}", $this->_getTableName(), $query);
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
	
	/**
	* Si l'objet existe
	* @return boolean
	*/
	public function exists() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."\\".$this->objectType."->exists()");

		// Si les clés primaires et la table ne sont pas définies, impossible de charger l'objet
		if (!isset($this->primaryKeys)) return false;
		if (!isset($this->tableName)) return false;

		// Test si l'objet existe, pas besoin de load
		if (is_bool($this->isExist)) {
			return $this->isExist;
		}

		// Paramètres de la requête
		$params = [];
		$whereClause = "";
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
			if ($whereClause != "") $whereClause .= " AND ";
			$whereClause .= "$mapKey = :$mapKey";
		}

		$query = static::$sqlGetObject;
		// Liste des champs
		$query = str_replace("{fields_list}", $this->_getAllFields(), $query);
		// Nom de la table
		$query = str_replace("{table_name}", $this->_getTableName(), $query);
		// Clause where
		$query = str_replace("{where_clause}", ' WHERE ' . $whereClause, $query);
		// Liste les objets
		$res = Sql\Sql::GetInstance()->executeQuery($query, $params);
		$this->isExist = (count($res) >= 1);
		return $this->isExist;
	}
	
	/**
	* Permet de récupérer la liste d'objet en utilisant les données passées
	* (la clause where s'adapte aux données)
	* Il faut donc peut être sauvegarder l'objet avant d'appeler cette méthode
	* pour réinitialiser les données modifiées (propriété haschanged)

	* @param string[] $fields Liste les champs à récupérer depuis les données
	* @param string $filter Filtre pour la lecture des données en fonction des valeurs déjà passé, exemple de filtre : "((#description# OR #title#) AND #start#)"
	* @param string[] $operators Liste les propriétés par operateur (MappingMce::like, MappingMce::supp, MappingMce::inf, MappingMce::diff)
	* @param string $orderby Tri par le champ
	* @param bool $asc Tri ascendant ou non
	* @param int $limit Limite le nombre de résultat (utile pour la pagination)
	* @param int $offset Offset de début pour les résultats (utile pour la pagination)
	* @param string[] $case_unsensitive_fields Liste des champs pour lesquels on ne sera pas sensible à la casse
	* @param string $join Nom de la table à joindre
	* @param string $type_join Type de jointure
	* @param string $using Clause using
	* @param string $prefix Prefix des champs
	* @param array $groupby Sur quel champ on fait le group by
	* @param string $groupby_count Champ utilisé pour compter le group by
	* @param array $subqueries (tableau de tableaux) Liste des sous requêtes array(name, fields, object, filter)
	*
	* @return ObjectMelanie[] Array
	*/
	public function getList($fields = [], $filter = "", $operators = [], $orderby = "", $asc = true, $limit = null, $offset = null, $case_unsensitive_fields = [], $join = null, $type_join = 'INNER', $using = null, $prefix = null, $groupby = [], $groupby_count = null, $subqueries = []) {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."\\".$this->objectType."->getList()");
		if (!isset($this->tableName)) return false;

		// Mapping pour les operateurs
		$opmapping = [];
		// Test si les clés primaires sont bien instanciées et les ajoute en paramètres
		if (is_array($operators)) {
			foreach ($operators as $key => $operator) {
				// Récupèration des données de mapping
				if (isset(MappingMce::$Data_Mapping[$this->objectType])
						&& isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
					$key = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
				}
				$opmapping[$key] = $operator;
			}
		}
		
		// Mapping pour les champs
		$fieldsmapping = [];
		if (is_array($fields)) {
			// Test si les clés primaires sont bien instanciées et les ajoute en paramètres
			foreach ($fields as $key) {
        		$prefix = '';
				// Récupèration des données de mapping
				if (isset(MappingMce::$Data_Mapping[$this->objectType])
				    	&& isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
					if (isset(MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::prefix])) {
						$prefix = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::prefix] . '.';
					}

					$key = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
				}
				$fieldsmapping[] = $prefix.$key;
			}

			// Ajout du count
			if (isset($groupby_count) && count($fieldsmapping) > 0) {
				$count = "COUNT(*)";
				if (isset($join) && !is_array($using) && isset(MappingMce::$Table_Name[$join]) && isset(MappingMce::$Data_Mapping[$join][$using])) {
					$count = "COUNT(" . MappingMce::$Table_Name[$join]. "." . MappingMce::$Data_Mapping[$join][$using][MappingMce::name] . ")";
				}
				$fieldsmapping[] = "$count as \"$groupby_count\"";
			}

			// Ajout des sous requêtes
			if (!empty($subqueries) && count($fieldsmapping) > 0) {
				foreach ($subqueries as $subquery) {
					$fieldsmapping[] = "(" . $this->_getSubquery($subquery[1], $subquery[2], $subquery[3], $join) . ") as \"$subquery[0]\"";
				}
			}
		}

		// Mapping pour les champs non case sensitive
		foreach ($case_unsensitive_fields as $i => $key) {
			// Récupèration des données de mapping
			if (isset(MappingMce::$Data_Mapping[$this->objectType])
					&& isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
				$key = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
			}
			$case_unsensitive_fields[$i] = $key;
		}

		// Paramètres de la requête
		$whereClause = "";
		$params = [];

		// Prefix de champ ?
		$keyprefix = '';

		if (isset($join) && isset(MappingMce::$Table_Name[$join]) && MappingMce::$Table_Name[$join] == $this->tableName) {
			$keyprefix = "table1.";
		}

		// Est-ce qu'un filtre est activé
		if ($filter != "") {
			// Recherche toutes les entrées du filtre
			// TODO: Attention la regex ne prend que a-z ce qui correspond au mapping actuel
			preg_match_all("/#([a-z0-9]*)#/",
        strtolower($filter),
        $matches, PREG_PATTERN_ORDER);
			if (isset($matches[1])) {
				foreach ($matches[1] as $key) {
					// Récupèration des données de mapping
					if (isset(MappingMce::$Data_Mapping[$this->objectType])
					    	&& isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
						$mapKey = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];

						if (isset(MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::prefix])) {
							$keyprefix = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::prefix] . ".";
						}
					} else {
						$mapKey = $key;
					}

          			// Est-ce que le champ courant est non case sensitive
					$is_case_unsensitive = in_array($mapKey, $case_unsensitive_fields);

					if (isset($opmapping[$mapKey])) {
						if (is_array($this->$mapKey)) {
							if ($opmapping[$mapKey] == MappingMce::in 
							    	|| $opmapping[$mapKey] == MappingMce::notin) {

								// Filtre personnalisé, valeur multiple, pas de like, on utilise IN
								if ($is_case_unsensitive)
								  $clause = "LOWER($keyprefix$mapKey) " . $opmapping[$mapKey] . " (";
								else
								  $clause = "$keyprefix$mapKey " . $opmapping[$mapKey] . " (";

								$i = 1;
								foreach ($this->$mapKey as $val) {
									if ($i > 1) $clause .= ", ";

									$clause .= ":$mapKey$i";

									if ($is_case_unsensitive)
									  $params[$mapKey.$i] = strtolower($val);
									else
									  $params[$mapKey.$i] = $val;

									$i++;
								}
								$clause .= ")";
								$filter = str_replace("#$key#", $clause, $filter);
							} else if ($opmapping[$mapKey] == MappingMce::between
							    || $opmapping[$mapKey] == MappingMce::notbetween) {
								$value = $this->$mapKey;

								// Filtre personnalisé, avec between
								if ($is_case_unsensitive) {
									$clause = "(LOWER($keyprefix$mapKey) " . $opmapping[$mapKey] . " :" . $mapKey . "0 AND :" . $mapKey . "1)";
									$params[$mapKey."0"] = strtolower($value[0]);
									$params[$mapKey."1"] = strtolower($value[1]);
								}
								else {
									$clause = "($keyprefix$mapKey " . $opmapping[$mapKey] . " :" . $mapKey . "0 AND :" . $mapKey . "1)";
									$params[$mapKey."0"] = $value[0];
									$params[$mapKey."1"] = $value[1];
								}
								$filter = str_replace("#$key#", $clause, $filter);

							} else {
								// Filtre personnalisé, valeur multiple, avec like
								$clause = "(";
								$i = 1;
								foreach ($this->$mapKey as $val) {
									if ($i > 1) {
										if ($opmapping[$mapKey] == MappingMce::diff) $clause .= " AND ";
										else $clause .= " OR ";
									}
									if ($is_case_unsensitive) {
										$clause .= "LOWER($keyprefix$mapKey) " . $opmapping[$mapKey] . " ";
										$clause .= ":$mapKey$i";
										$params[$mapKey.$i] = strtolower($val);
									} else {
										$clause .= "$keyprefix$mapKey " . $opmapping[$mapKey] . " ";
										$clause .= ":$mapKey$i";
										$params[$mapKey.$i] = $val;
									}
									$i++;
								}
								$clause .= ")";
								$filter = str_replace("#$key#", $clause, $filter);
							}
						} else {
							// Filtre personnalisé, valeur simple avec LIKE
							if ($is_case_unsensitive) {
								$clause = "LOWER($keyprefix$mapKey) " . $opmapping[$mapKey] . " :$mapKey";
								$params[$mapKey] = strtolower($this->$mapKey);
							} else {
								$clause = "$keyprefix$mapKey " . $opmapping[$mapKey] . " :$mapKey";
								$params[$mapKey] = $this->$mapKey;
							}
							$filter = str_replace("#$key#", $clause, $filter);
						}
					} else {
						// Filtre personnalise, on ne met que le nom du champ
						if ($is_case_unsensitive)
						  $clause = "LOWER($keyprefix$mapKey)";
						else
						  $clause = "$keyprefix$mapKey";

						$filter = str_replace("#$key#", $clause, $filter);
					}
				}
			}
			$whereClause = $filter;
		} else {
			// Gestion du where clause en fonction du haschanged
			// N'ajoute que les paramètres qui ont changé
			foreach ($this->haschanged as $key => $value) {
				if ($value) {
					// Est-ce que le champ courant est non case sensitive
					$is_case_unsensitive = in_array($key, $case_unsensitive_fields);

					if (isset($opmapping[$key])) {
						if (is_array($this->$key)) {
							// On est dans un tableau et il nous faut utiliser LIKE
							if ($whereClause != "") $whereClause .= " AND ";
							$i = 1;

							foreach ($this->$key as $val) {
								if ($i > 1) {
									if ($opmapping[$key] == MappingMce::diff) $whereClause .= " AND ";
									else $whereClause .= " OR ";
								} else $whereClause .= "(";

								if ($is_case_unsensitive) {
									$whereClause .= "LOWER($keyprefix$key) " . $opmapping[$key] . " ";
									$whereClause .= ":$key$i";
									$params[$key.$i] = strtolower($val);
								} else {
									$whereClause .= "$keyprefix$key " . $opmapping[$key] . " ";
									$whereClause .= ":$key$i";
									$params[$key.$i] = $val;
								}
								$i++;
							}
							$whereClause .= ")";
						} else {
							// Valeur simple avec LIKE
							if ($whereClause != "") $whereClause .= " AND ";
							if ($is_case_unsensitive) {
								$whereClause .= "LOWER($keyprefix$key) " . $opmapping[$key] . " :$key";
								$params[$key] = strtolower($this->$key);
							} else {
								$whereClause .= "$keyprefix$key " . $opmapping[$key] . " :$key";
								$params[$key] = $this->$key;
							}
						}
					} else {
						if (is_array($this->$key)) {
							// On est dans un tableau, pas de like, on utilise IN
							if ($whereClause != "") $whereClause .= " AND ";

							if ($is_case_unsensitive)
							  $whereClause .= "LOWER($keyprefix$key) IN (";
							else
							  $whereClause .= "$keyprefix$key IN (";

							$i = 1;
							foreach ($this->$key as $val) {
								if ($i > 1) $whereClause .= ", ";
								$whereClause .= ":$key$i";

								if ($is_case_unsensitive)
								  $params[$key.$i] = strtolower($val);
								else
								  $params[$key.$i] = $val;
								$i++;
							}
							$whereClause .= ")";
						} else {
							// Valeur simple, pas de like, on utilise l'égalité
							if ($whereClause != "") $whereClause .= " AND ";
							if ($is_case_unsensitive) {
								$whereClause .= "LOWER($keyprefix$key) = :$key";
								$params[$key] = strtolower($this->$key);
							} else {
								$whereClause .= "$keyprefix$key = :$key";
								$params[$key] = $this->$key;
							}
						}
					}
				}
			}
		}
		// Gestion d'une clause where vide
		if (!empty($whereClause)) {
			$whereClause = ' WHERE ' . $whereClause;
		}

    // Group By
    $whereClause .= Sql\Sql::GetGroupByClause($this->objectType, $groupby, $join);
		// Tri
		$whereClause .= Sql\Sql::GetOrderByClause($this->objectType, $orderby, $asc);
		// Limit & offset 
		$whereClause .= Sql\Sql::GetLimitClause($limit, $offset);
		// Chargement de la requête
		$query = static::$sqlGetObject;

		// Liste des champs
		if (!is_array($fields) && strtolower($fields) == 'count') {
			// On fait un count(*)
			$query = str_replace("{fields_list}", "count(*)", $query);
		} elseif (count($fieldsmapping) > 0) {
			$query = str_replace("{fields_list}", implode(", ", $fieldsmapping), $query);
		} else {
			$query = str_replace("{fields_list}", $this->_getAllFields($prefix, $groupby_count, $join, $using, $subqueries), $query);
		}

		// Nom de la table
		$query = str_replace("{table_name}", $this->_getTableName($join, $type_join, $using), $query);
		// Clause where
		$query = str_replace("{where_clause}", $whereClause, $query);
		// Récupération
		return Sql\Sql::GetInstance()->executeQuery($query, $params, $this->get_class, $this->objectType);
	}
	
	/**
	* Fonction appelé après la génération de l'objet par PDO
	* Cette fonction est normalement auto appelée par le getList
	* Elle permet de définir les bon paramètres de l'objet
	* L'appel externe n'est donc pas nécessaire (mais cette méthode doit rester public)
	* @param bool $isExist si l'objet existe
	* @param string $objectType Type de l'objet
	*/
	public function pdoConstruct($isExist, $objectType) {
		$this->initializeHasChanged();
		$this->__construct($objectType);
		$this->isExist = $isExist;
	}

  /**
   * Récupère le nom de la table
   * permet de gérer les jointures si besoin
   * 
   * @param string $join Nom de la table à joindre
   * @param string $type_join Type de jointure (INNER, LEFT, RIGHT)
   * @param string $using Nom du champ à utiliser pour la jointure
   * 
   * @return string
   * 
   * @ignore
   */
  protected function _getTableName($join = null, $type_join = 'INNER', $using = null) {
    // Nom de la table
    $tableName = $this->tableName;
    
    // Jointure
    if (isset($join)) {
      $type_join = strtoupper($type_join);
      if (isset(MappingMce::$Table_Name[$join])) {
        if (is_array($using)) {
          if (isset(MappingMce::$Data_Mapping[$join][$using[0]])) {
            $using[0] = MappingMce::$Data_Mapping[$join][$using[0]][MappingMce::name];
          }
          if (isset(MappingMce::$Data_Mapping[$join][$using[1]])) {
            $using[1] = MappingMce::$Data_Mapping[$join][$using[1]][MappingMce::name];
          }
        }
        else {
          if (!is_array($using) && isset(MappingMce::$Data_Mapping[$join][$using])) {
            $using = MappingMce::$Data_Mapping[$join][$using][MappingMce::name];
          }
        }

        $join = MappingMce::$Table_Name[$join];
      }
      
      if (is_array($using)) {
        $tableName .= " table1";
        $join .= " table2";
        $tableName = "$tableName $type_join JOIN $join ON table1.$using[0] = table2.$using[1]";
      }
      else {
        $tableName = "$tableName $type_join JOIN $join USING ($using)";
      }
    }
    else if (isset(MappingMce::$Joins[$this->objectType])) {
      $tableName = "$tableName INNER JOIN " . MappingMce::$Joins[$this->objectType][MappingMce::table_join];
      if (isset(MappingMce::$Joins[$this->objectType][MappingMce::using])) {
        $tableName .= " USING (" . MappingMce::$Joins[$this->objectType][MappingMce::using] . ")";
      }
    }
    
    return $tableName;
  }

  /**
   * Récupère la sous requête pour récupérer les données
   * 
   * @param string[] $fields Liste des champs à récupérer depuis les données
   * @param string $object Nom de l'objet
   * @param string $filter Filtre pour la lecture des données en fonction des valeurs déjà passé
   * @param string $join Nom de la table à joindre
   * @param int $i Numéro de la sous requête
   * 
   * @return string
   */
  protected function _getSubquery($fields, $object, $filter, $join = null, $i = 1) {
    $query = self::$sqlGetObject;
    $tableName = MappingMce::$Table_Name[$object];
    $prefix = "";
    
    if ($tableName == $this->tableName) {
      $prefix = "query$i.";
      $tableName .= " query$i";
    }

    // Mapping pour les champs
	if (is_array($fields)) {
      	$fieldsmapping = [];
		// Test si les clés primaires sont bien instanciées et les ajoute en paramètres
		foreach ($fields as $key) {
			// Récupèration des données de mapping
			if (isset(MappingMce::$Data_Mapping[$object])
					&& isset(MappingMce::$Data_Mapping[$object][$key])) {
				$key = MappingMce::$Data_Mapping[$object][$key][MappingMce::name];
			}
			$fieldsmapping[] = $prefix.$key;
		}
      	$fields = implode(', ', $fieldsmapping);
	}
    else if ($fields == 'count') {
      $fields = "count($prefix*)";
    }
    else {
      $fields = "$prefix*";
    }
    $query = str_replace("{fields_list}", $fields, $query);

    // Nom de la table
		$query = str_replace("{table_name}", $tableName, $query);

    // Clause where
    // Récupèration des données de mapping
	$filter3 = '';
    if (is_array($filter)) {
      if (isset(MappingMce::$Data_Mapping[$object])
          && isset(MappingMce::$Data_Mapping[$object][$filter[0]])) {
        $filter[0] = MappingMce::$Data_Mapping[$object][$filter[0]][MappingMce::name];
      }

      if (isset(MappingMce::$Data_Mapping[$object])
          && isset(MappingMce::$Data_Mapping[$object][$filter[1]])) {
        $filter[1] = MappingMce::$Data_Mapping[$object][$filter[1]][MappingMce::name];
      }
      
      $filter1 = $filter[0];
      $filter2 = $filter[1];

	  // Pouvoir filtrer sur des valeurs
	  if (isset($filter[2]) && is_array($filter[2])) {
		foreach ($filter[2] as $key => $value) {
			if (isset(MappingMce::$Data_Mapping[$object])
					&& isset(MappingMce::$Data_Mapping[$object][$key])) {
				$key = MappingMce::$Data_Mapping[$object][$key][MappingMce::name];
			}

			if (is_int($value)) {
				$filter3 .= " AND $key = $value";
			}
			else {
				$filter3 .= " AND $key = '$value'";
			}
		}
	  }
    }
    else {
      if (isset(MappingMce::$Data_Mapping[$object])
          && isset(MappingMce::$Data_Mapping[$object][$filter])) {
        $filter = MappingMce::$Data_Mapping[$object][$filter][MappingMce::name];
      }

      $filter1 = $filter;
      $filter2 = $filter;
    }

    if (isset($join) && isset(MappingMce::$Table_Name[$join]) && MappingMce::$Table_Name[$join] == $this->tableName) {
      $query = str_replace("{where_clause}", " WHERE $filter1 = table1.$filter2$filter3", $query);
    }
    else {
      $query = str_replace("{where_clause}", " WHERE $filter1 = " . MappingMce::$Table_Name[$this->objectType] . ".$filter2$filter3", $query);
    }

    return $query;
  }

  /**
   * Comment lister tous les champs
   * 
   * @param string $prefix Prefix des champs
   * @param string $groupby_count Nom du champ pour le count
   * @param string $join Nom de la table à joindre
   * @param string $using Nom du champ à utiliser pour la jointure
   * @param array $subqueries Liste des sous requêtes
   * 
   * @return string
   * 
   * @ignore
   */
  protected function _getAllFields($prefix = null, $groupby_count = null, $join = null, $using = null, $subqueries = []) {
    $fields = "*";

    if (isset($join) && isset(MappingMce::$Table_Name[$join]) && MappingMce::$Table_Name[$join] == $this->tableName) {
      $fields = "table1.*";
    }
    else if (isset($prefix)) {
      if (isset(MappingMce::$Table_Name[$prefix])) {
        $prefix = MappingMce::$Table_Name[$prefix];
      }

      $fields = $prefix . ".*";
    }
    else if (isset(MappingMce::$Joins[$this->objectType]) 
        && isset(MappingMce::$Joins[$this->objectType][MappingMce::prefix])) {
      $fields = MappingMce::$Joins[$this->objectType][MappingMce::prefix] . ".*";
    }

    // Ajouter un count pour le group by
    if (isset($groupby_count)) {
      if (isset($join) && !is_array($using) && isset(MappingMce::$Table_Name[$join]) && isset(MappingMce::$Data_Mapping[$join][$using])) {
        $count = "COUNT(" . MappingMce::$Table_Name[$join]. "." . MappingMce::$Data_Mapping[$join][$using][MappingMce::name] . ")";
      }
      else if (isset($join) && isset(MappingMce::$Table_Name[$join]) && MappingMce::$Table_Name[$join] == $this->tableName) {
        $count = "COUNT(table2.*)";
      }
      else  {
        $count = "COUNT(*)";
      }
      $fields .= ", $count as \"$groupby_count\"";
    }

    // Ajout des sous requêtes
    if (!empty($subqueries)) {
      foreach ($subqueries as $subquery) {
        $fields .=  ", (" . $this->_getSubquery($subquery[1], $subquery[2], $subquery[3], $join) . ") as \"$subquery[0]\"";
      }
    }

    return $fields;
  }
}