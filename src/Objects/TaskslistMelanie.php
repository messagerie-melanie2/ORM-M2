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
use LibMelanie\Config\DefaultConfig;

/**
 * Traitement des listes de tâches Melanie2
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage ORM
 */
class TaskslistMelanie extends MagicObject implements IObjectMelanie {
    /**
     * Nom de la table SQL liée à l'objet
     * @var string $tableName
     */
    public $tableName;

	/**
	 * Constructeur de l'objet, appelé par PDO
	 */
	public function __construct() {
	    // Défini la classe courante
	    $this->get_class = get_class($this);

		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->__construct()");

		// Récupération du type d'objet en fonction de la class
		$this->objectType = explode('\\',$this->get_class);
		$this->objectType = $this->objectType[count($this->objectType)-1];
		$this->tableName = MappingMce::$Table_Name[$this->objectType];

		if (isset(MappingMce::$Primary_Keys[$this->objectType])) {
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
	 * Chargement de l'objet
	 * need: $this->id
	 * optionnal: $this->user_uid
	 * @return boolean isExist
	 */
	public function load() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->load()");
		if (!isset($this->id)) return false;

		// Test si l'objet existe, pas besoin de load
		if (is_bool($this->isExist) && $this->isLoaded) {
		  return $this->isExist;
		}

		// Gérer le load si user n'est pas défini
		if (isset($this->user_uid)) {
			$query = Sql\SqlMelanieRequests::listObjectsByUidAndUser;
		}
		else {
			$query = Sql\SqlMelanieRequests::listObjectsByUid;
		}

		// Replace name
		$query = str_replace('{user_uid}', MappingMce::$Data_Mapping[$this->objectType]['owner'][MappingMce::name], $query);
		$query = str_replace('{datatree_name}', MappingMce::$Data_Mapping[$this->objectType]['id'][MappingMce::name], $query);
		$query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping[$this->objectType]['ctag'][MappingMce::name], $query);
		$query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping[$this->objectType]['synctoken'][MappingMce::name], $query);
		$query = str_replace('{attribute_value}', MappingMce::$Data_Mapping[$this->objectType]['name'][MappingMce::name], $query);
		$query = str_replace('{perm_object}', MappingMce::$Data_Mapping[$this->objectType]['perm'][MappingMce::name], $query);
		$query = str_replace('{datatree_id}', MappingMce::$Data_Mapping[$this->objectType]['object_id'][MappingMce::name], $query);

		// Params
		$params = [
		    "group_uid" => DefaultConfig::TASKSLIST_GROUP_UID,
			"datatree_name" => $this->id,
		    "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
		    "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
		    "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP,
		];

		// Gérer le load si user n'est pas défini
		if (isset($this->user_uid)) {
			$params["user_uid"] = $this->user_uid;
		}

		// Liste les calendriers de l'utilisateur
		$this->isExist = Sql\Sql::GetInstance()->executeQueryToObject($query, $params, $this);
		if ($this->isExist) {
			//$this->getCTag();
			$this->initializeHasChanged();
		}

		// Les données sont chargées
		$this->isLoaded = true;
		return $this->isExist;
	}

	/**
	 * (non-PHPdoc)
	 * @see IObjectMelanie::save()
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
			if (isset($this->haschanged[MappingMce::$Data_Mapping[$this->objectType]['name'][MappingMce::name]])
					&& $this->haschanged[MappingMce::$Data_Mapping[$this->objectType]['name'][MappingMce::name]]) {
				$this->saveName();
			}
		} else {
			if (!isset($this->user_uid)) return false;
			// C'est une Insertion
			$insert = true;
			Sql\Sql::GetInstance()->beginTransaction();
			$query = Sql\SqlMelanieRequests::insertObject;
			$res = Sql\Sql::GetInstance()->executeQuery(Sql\SqlMelanieRequests::getNextObject);
			$datatree_id = $res[0][0];
			$datatree_name = isset($this->id) ? $this->id : md5(time() . $datatree_id);
			$params = [
					'datatree_id' => $datatree_id,
					'datatree_name' => $datatree_name,
					'datatree_ctag' => md5($datatree_name),
					'user_uid' => $this->user_uid,
			    'group_uid' => isset($this->group) ?  $this->group : DefaultConfig::TASKSLIST_GROUP_UID,
			];
			if (Sql\Sql::GetInstance()->executeQuery($query, $params)) {
				$this->isExist = true;
				// Name
				$query = Sql\SqlObjectPropertyRequests::insertProperty;
				$params = [
						'datatree_id' => $datatree_id,
				    'attribute_name' => DefaultConfig::ATTRIBUTE_NAME_NAME,
						'attribute_key' => '',
						'attribute_value' => isset($this->name) ?  $this->name : $datatree_name,
				];
				if (!Sql\Sql::GetInstance()->executeQuery($query, $params)) {
			        Sql\Sql::GetInstance()->rollBack();
			        return null;
				}
				// owner
				$query = Sql\SqlObjectPropertyRequests::insertProperty;
				$params = [
						'datatree_id' => $datatree_id,
				    'attribute_name' => DefaultConfig::ATTRIBUTE_OWNER,
						'attribute_key' => '',
						'attribute_value' => $this->user_uid,
				];
			    if (!Sql\Sql::GetInstance()->executeQuery($query, $params)) {
			        Sql\Sql::GetInstance()->rollBack();
			        return null;
				}
				// perm
				$query = Sql\SqlObjectPropertyRequests::insertProperty;
				$params = [
						'datatree_id' => $datatree_id,
				    'attribute_name' => DefaultConfig::ATTRIBUTE_NAME_PERM,
						'attribute_key' => $this->user_uid,
						'attribute_value' => '30',
				];
			    if (!Sql\Sql::GetInstance()->executeQuery($query, $params)) {
			        Sql\Sql::GetInstance()->rollBack();
			        return null;
				}
				Sql\Sql::GetInstance()->commit();
			} else {
			    Sql\Sql::GetInstance()->rollBack();
			    return null;
			}
		}
		if ($this->isExist) $this->initializeHasChanged();
		return $insert;
	}

	/**
	 * (non-PHPdoc)
	 * @see IObjectMelanie::delete()
	 */
	public function delete() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->exists()");
		if (!isset($this->tableName)) return false;

		// Si l'objet existe on fait un UPDATE
		if ($this->isExist
				&& isset($this->object_id)) {
			// Params
			$params = [
					"datatree_id" => $this->object_id,
			];
			$ok = true;
			Sql\Sql::GetInstance()->beginTransaction();
			$query = Sql\SqlMelanieRequests::deleteObject1;
			// Supprimer l'objet
			$ok &= Sql\Sql::GetInstance()->executeQuery($query, $params);
			$query = Sql\SqlMelanieRequests::deleteObject2;
			// Supprimer l'objet
			$ok &= Sql\Sql::GetInstance()->executeQuery($query, $params);
			$query = Sql\SqlMelanieRequests::deleteObject3;
			$query = str_replace("{objects_table}", "nag_tasks", $query);
			$query = str_replace("{datatree_name}", MappingMce::$Data_Mapping[$this->objectType]['id'][MappingMce::name], $query);
			// Params
			$params = [
			    "datatree_name" => $this->id,
			];
			// Supprimer l'objet
			$ok &= Sql\Sql::GetInstance()->executeQuery($query, $params);
			// Ne pas supprimer du horde_histories qui part en timeout sur la prod
			// TODO: Trouver une solution
//  			$query = Sql\SqlMelanieRequests::deleteObject4;
//  			// Params
//  			$params = [
//  					"object_uid" => DefaultConfig::TASKSLIST_PREF_SCOPE.":".$this->id.":%",
//  			];
//  			// Supprimer l'objet
//  			$ok &= Sql\Sql::GetInstance()->executeQuery($query, $params);
      if ($ok) {
        Sql\Sql::GetInstance()->commit();
        $this->initializeHasChanged();
        $this->isExist = false;
      }
      else Sql\Sql::GetInstance()->rollBack();
			return $ok;
		}
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see IObjectMelanie::exists()
	 */
	public function exists() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->exists()");
		// Si les clés primaires et la table ne sont pas définies, impossible de charger l'objet
		if (!isset($this->tableName)) return false;
		// Test si l'objet existe, pas besoin de load
		if (is_bool($this->isExist)) {
		  return $this->isExist;
		}
		// Paramètres de la requête
		$params = ['id' => $this->id, 'group' => $this->group];
		$whereClause = "datatree_name = :id AND group_uid = :group";

		$query = Sql\SqlObjectRequests::getObject;
		// Liste des champs
		$query = str_replace("{fields_list}", MappingMce::$Data_Mapping[$this->objectType]['object_id'][MappingMce::name], $query);
		// Nom de la table
		$query = str_replace("{table_name}", $this->tableName, $query);
		// Clause where
		$query = str_replace("{where_clause}", ' WHERE ' . $whereClause, $query);

		// Liste les objets
		$res = Sql\Sql::GetInstance()->executeQuery($query, $params);
		$this->isExist = (count($res) >= 1);
		return $this->isExist;
	}

	/**
	 * Fonction appelé après la génération de l'objet par PDO
	 * Cette fonction est normalement auto appelée par le getList
	 * Elle permet de définir les bon paramètres de l'objet
	 * L'appel externe n'est donc pas nécessaire (mais cette méthode doit rester public)
	 * @param bool $isExist si l'objet existe
	 */
	public function pdoConstruct($isExist) {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->pdoConstruct($isExist)");
		$this->initializeHasChanged();
		$this->isExist = $isExist;
	}

	/**
	 * Récupère la liste de toutes les tâches
	 * need: $this->id
	 * @return Task[]
	 */
	public function getAllTasks() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getAllTasks()");
		if (!isset($this->id)) return false;

		// Params
		$params = [MappingMce::$Data_Mapping[$this->objectType]['id'][MappingMce::name] => $this->id];

		// Liste les tâches de la liste
		return Sql\Sql::GetInstance()->executeQuery(Sql\SqlTaskRequests::listAllTasks, $params, 'LibMelanie\Objects\ObjectMelanie', 'TaskMelanie');
	}

	/**
	 * Recupère le Tag associé à la liste de tâches
	 * need: $this->id
	 * @return string
	 */
	public function getCTag() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getCTag()");
		if (!isset($this->id)) return false;
		if (!isset($this->ctag)) {
		  // Params
		  $params = [MappingMce::$Data_Mapping[$this->objectType]['id'][MappingMce::name] => $this->id];

		  // Récupération du tag
		  Sql\Sql::GetInstance()->executeQueryToObject(Sql\SqlTaskRequests::getCTag, $params, $this);
		  if (!isset($this->ctag)) $this->ctag = md5($this->id);
		  M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getCTag() this->ctag: " . $this->ctag);
		}

		return $this->ctag;
	}

  	/**
	 * Recupère le timezone par défaut pour le
	 * need: $this->user_uid
	 * 
	 * @deprecated
	 */
	public function getTimezone() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getTimezone()");
		if (!isset($this->user_uid)) return DefaultConfig::CALENDAR_DEFAULT_TIMEZONE;

		if (!isset($this->timezone)) {
			// Replace name
			$query = str_replace('{pref_name}', 'timezone', Sql\SqlMelanieRequests::getUserPref);

			// Params
			$params = [
					"user_uid" => $this->user_uid,
			    "pref_scope" => DefaultConfig::PREF_SCOPE,
			    "pref_name" => DefaultConfig::TZ_PREF_NAME
			];

			// Récupération du timezone
			$res = Sql\Sql::GetInstance()->executeQueryToObject($query, $params, $this);
			// Test si le timezone est valide en PHP
			try {
				$tz = new \DateTimeZone($this->timezone);
			} catch (\Exception $ex) {
			  $this->timezone = DefaultConfig::CALENDAR_DEFAULT_TIMEZONE;
			}
			M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getTimezone() this->timezone: " . $this->timezone);
		}
		return $this->timezone;
	}

	/**
	 * Sauvegarde le nom de l'objet
	 */
	private function saveName() {
	  // Si l'objet existe on fait un UPDATE
	  if ($this->isExist
	      && isset($this->object_id)
	      && isset($this->name)) {
	    $query = Sql\SqlObjectPropertyRequests::updateProperty;
	    // Params
	    $params = [
	        "datatree_id" => $this->object_id,
	        "attribute_value" => $this->name,
	        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
	    ];
	    Sql\Sql::GetInstance()->executeQuery($query, $params);
	  }
	}

	/**
	 * Gestion des droits
	 * @param string $action
	 * @return boolean
	 */
	public function asRight($action) {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->asRight($action)");
		return (DefaultConfig::$PERMS[$action] & $this->perm_taskslist) === DefaultConfig::$PERMS[$action];
	}
}