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
namespace LibMelanie\Lib;

use LibMelanie\Config\MappingMce;
use LibMelanie\Log\M2Log;
use Serializable;

/**
 * Objet magic pour les getter et setter en fonction des requêtes SQL
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Lib
 */
abstract class MagicObject implements Serializable {
	/**
	 * Stockage des données cachées
	 * @var array
	 */
	protected $data = [];
	/**
	 * Défini si les propriété ont changé pour les requêtes SQL
	 * @var array
	 */
	protected $haschanged = [];
	/**
	 * Est-ce que l'objet existe
	 * @var bool
	 */
	protected $isExist = null;
	/**
	 * Est-ce que l'objet est chargé
	 * @var bool
	 */
	protected $isLoaded = null;
	/**
	 * Type d'objet, lié au mapping
	 * @var string
	 */
	protected $objectType;
	/**
	 * Les clés primaires de l'objet
	 * @var mixed
	 */
	protected $primaryKeys;
	/**
	 * Classe courante
	 * @var string
	 */
  protected $get_class;
  
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
    }
	}

	/**
	 * Remet à 0 le haschanged
	 * @ignore
	 */
	protected function initializeHasChanged() {
		foreach ($this->haschanged as $key => $value) {
      		$this->haschanged[$key] = false;
    	}
	}
	
	/**
	 * Détermine si le champ a changé
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function fieldHasChanged($name) {
	  $lname = strtolower($name);
	  // Récupèration des données de mapping
	  if (isset(MappingMce::$Data_Mapping[$this->objectType])
        && isset(MappingMce::$Data_Mapping[$this->objectType][$lname])) {
      $lname = MappingMce::$Data_Mapping[$this->objectType][$lname][MappingMce::name];
    }
    if (isset($this->haschanged[$lname])) {
      return $this->haschanged[$lname];
    }
	  return false;
  }

  /**
	 * Positionne si le champ a changé
	 * 
	 * @param string $name
   * @param boolean $haschanged
	 */
	public function setFieldHasChanged($name, $haschanged = true) {
	  $lname = strtolower($name);
	  // Récupèration des données de mapping
	  if (isset(MappingMce::$Data_Mapping[$this->objectType])
        && isset(MappingMce::$Data_Mapping[$this->objectType][$lname])) {
      $lname = MappingMce::$Data_Mapping[$this->objectType][$lname][MappingMce::name];
    }
    $this->haschanged[$lname] = $haschanged;
  }
  
  /**
   * Détermine si au moins un champ a changé
   * 
   * @return boolean
   */
  public function anyFieldHasChanged() {
    $_hasChanged = false;
    foreach ($this->haschanged as $value) {
      $_hasChanged |= $value;
      if ($_hasChanged) {
        break;
      }
    }
    return $_hasChanged;
  }
	
	/**
	 * Récupère la valeur du champ dans data
	 * Nécessaire pour effectuer des comparaisons bruts
	 * @param string $name
	 * @return mixed
	 */
	public function getFieldValueFromData($name) {
	  $lname = strtolower($name);
	  // Récupèration des données de mapping
	  if (isset(MappingMce::$Data_Mapping[$this->objectType])
	      && isset(MappingMce::$Data_Mapping[$this->objectType][$lname])) {
      $lname = MappingMce::$Data_Mapping[$this->objectType][$lname][MappingMce::name];
    }
    if (isset($this->data[$lname])) {
      return $this->data[$lname];
    }
    return null;
	}

  /**
	 * Positionne la valeur du champ dans data
   * 
	 * @param string $name
   * @param string $value
	 */
	public function setFieldValueToData($name, $value) {
	  $lname = strtolower($name);
	  // Récupèration des données de mapping
	  if (isset(MappingMce::$Data_Mapping[$this->objectType])
	      && isset(MappingMce::$Data_Mapping[$this->objectType][$lname])) {
      $lname = MappingMce::$Data_Mapping[$this->objectType][$lname][MappingMce::name];
    }
    $this->data[$lname] = $value;
	}

  /**
	 * Positionne la valeur de isLoaded
   * 
	 * @param boolean $isLoaded
	 */
  public function setIsLoaded($isLoaded = true) {
    $this->isLoaded = $isLoaded;
  }
  /**
	 * Retourne la valeur de isLoaded
   * 
	 * @return boolean $isLoaded
	 */
  public function getIsLoaded() {
    return is_bool($this->isLoaded) ? $this->isLoaded : false;
  }

  /**
	 * Positionne la valeur de isExist
   * 
	 * @param boolean $isExist
	 */
	public function setIsExist($isExist = true) {
	  $this->isExist = $isExist;
	}
  /**
	 * Retourne la valeur de isExist
   * 
	 * @return boolean $isExist
	 */
	public function getIsExist() {
    return is_bool($this->isExist) ? $this->isExist : false;
	}

	/**
	 * Return data array
	 * @return array
	 */
	public function __get_data() {
	    return $this->data;
  }
  
  /**
	 * Set data array
	 * @param array
	 */
	public function __set_data($data) {
    $this->data = $data;
  }

	/**
	 * Copy l'objet depuis un autre
	 * @param MagicObject $object
	 * @return boolean
	 */
	public function __copy_from($object) {
	    if (method_exists($object, "__get_data")) {
	        $this->data = $object->__get_data();
	        return true;
	    }
	    return false;
	}

	/**
	 * PHP magic to set an instance variable
	 *
	 * @access public
	 * @return
	 * @ignore
	*/
	public function __set($name, $value) {
    $name = strtolower($name);
    $lname = $name;
    // Récupèration des données de mapping
    if (isset(MappingMce::$Data_Mapping[$this->objectType])
            && isset(MappingMce::$Data_Mapping[$this->objectType][$name])) {
        $lname = MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::name];
        // Typage
        if (!is_null($value) /* MANTIS 3642: Impossible de remettre à zéro le champ "event_recurenddate" */
                && isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::type])) {
            switch (MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::type]) {
                // INTEGER
                case MappingMce::integer:
                    if (!is_array($value)) { 
                      $value = intval($value);
                    }
                    break;
                // DOUBLE
                case MappingMce::double:
                    if (!is_array($value)) { 
                      $value = doubleval($value);
                    }
                    break;
                // STRING LDAP
                case MappingMce::stringLdap:
                    // Gestion d'un prefix ?
                    if (isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::prefixLdap])) {
                      $_prefix = MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::prefixLdap];
                      $_found = false;
                      $_value = isset($this->data[$lname]) ? $this->data[$lname] : [];
                      foreach ($_value as $k => $val) {
                        if (strpos($val, $_prefix) === 0) {
                          // Modification de la valeur prefixee
                          $_found = true;
                          $_value[$k] = $_prefix . $value;
                          break;
                        }
                      }
                      if (!$_found) {
                        // Gérer le cas ou la valeur n'existe pas mais n'a pas besoin d'être ajoutée
                        if (!isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::emptyLdapValue])
                            || MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::emptyLdapValue] != $value) {
                          // Ajoute la nouvelle valeur prefixee
                          $_value[] = $_prefix . $value;
                        }
                      }
                      $value = $_value;
                    }
                    else {
                      if (empty($value)) {
                        $value = [];
                      }
                      else if (is_array($value)) {
                        $value = [$value[0]];
                      }
                      else {
                        $value = [$value];
                      }
                    }
                    break;
                // ARRAY LDAP
                case MappingMce::arrayLdap:
                  if (is_array($value)) {
                    unset($value['count']);
                  }
                  else {
                    $value = [$value];
                  }
                  break;
                // BOOLEAN LDAP
                case MappingMce::booleanLdap:
                  if (is_array($value)) {
                    $value = isset($value[0]) ? $value[0] : null;
                  }
                  if ($value) {
                    $value = MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::trueLdapValue] ?: '1';
                  }
                  else {
                    $value = MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::falseLdapValue] ?: '0';
                  }
                  // Gestion d'un prefix ?
                  if (isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::prefixLdap])) {
                    $_prefix = MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::prefixLdap];
                    $_found = false;
                    $_value = isset($this->data[$lname]) ? $this->data[$lname] : [];
                    foreach ($_value as $k => $val) {
                      if (strpos($val, $_prefix) === 0) {
                        // Modification de la valeur prefixee
                        $_found = true;
                        $_value[$k] = $_prefix . $value;
                        break;
                      }
                    }
                    if (!$_found) {
                      // Gérer le cas ou la valeur n'existe pas mais n'a pas besoin d'être ajoutée
                      if (!isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::emptyLdapValue])
                          || MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::emptyLdapValue] != $value) {
                        // Ajoute la nouvelle valeur prefixee
                        $_value[] = $_prefix . $value;
                      }
                    }
                    $value = $_value;
                  }
                  else {
                    
                    $value = [$value];
                  }
                  break;
                // STRING
                case MappingMce::string:
                    // Gérer la taille des strings dans la BDD
                    if (!is_array($value) && isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::size])) {
                        $value = mb_substr($value, 0, MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::size]);
                    }
                    break;
                // DATE
                case MappingMce::date:
                    try {
                        if ($value instanceof \DateTime) {
                            if (isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::format]))
                                $value = $value->format(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::format]);
                            else
                                $value = $value->format('Y-m-d H:i:s');
                        } else if (!is_array($value)) {
                            if (isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::format]))
                                $value = date(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::format], strtotime($value));
                            else
                                $value = date('Y-m-d H:i:s', strtotime($value));
                        }
                    }
                    catch (\Exception $ex) {
                        M2Log::Log(M2Log::LEVEL_ERROR, "MagicObject->__set($name, $value) : Exception dans le format de date, utilisation de la valeur par defaut");
                        // Une erreur s'est produite, on met une valeur par défaut pour le pas bloquer la lecture des données
                        $value = "1970-01-01 00:00:00";
                    }

                    break;
                // TIMESTAMP
                case MappingMce::timestamp:
                    if ($value instanceof \DateTime) {
                        $value = $value->getTimestamp();
                    } else if (!is_array($value))  {
                        $value = intval($value);
                    }
                    // MANTIS 0006124: Problème avec les timestamp négatif
                    // Problème avec cet update
                    // if ($value < 0) {
                    //   $value = time();
                    // }
                    break;
            }
        }        
    }
    if (isset($this->data[$lname]) && is_scalar($value) && !is_array($value) && $this->data[$lname] === $value) {
      return false;
    }
    $this->data[$lname] = $value;
    $this->haschanged[$lname] = true;
    $this->isLoaded = false;
	}

	/**
	 * PHP magic to get an instance variable
	 * if the variable was not set previousely, the value of the
	 * Unsetdata array is returned
	 *
	 * @access public
	 * @return
	 * @ignore
	 */
	public function __get($name) {
	  $name = strtolower($name);
	  $lname = $name;
		// Récupèration des données de mapping
		if (isset(MappingMce::$Data_Mapping[$this->objectType])
		    && isset(MappingMce::$Data_Mapping[$this->objectType][$name])) {
      			$lname = MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::name];
		}
		if (isset($this->data[$lname])) {
		  $value = $this->data[$lname];
		  if (isset(MappingMce::$Data_Mapping[$this->objectType])
		      && isset(MappingMce::$Data_Mapping[$this->objectType][$name])
		      && isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::type])) {
        switch (MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::type]) {
		      // STRING LDAP
		      case MappingMce::stringLdap:
		        if (is_array($value)) {
              // Gestion d'un prefix
              if (isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::prefixLdap])) {
                $_prefix = MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::prefixLdap];
                $_found = false;
                foreach ($value as $val) {
                  if (strpos($val, $_prefix) === 0) {
                    $value = trim(str_replace($_prefix, '', $val));
                    $_found = true;
                    break;
                  }
                }
                // Valeur par défaut
                if (!$_found) {
                  if (isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::defaut])) {
                    $value = MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::defaut];
                  }
                  else {
                    $value = "";
                  }
                }
              }
              else {
                if (isset($value[0])) {
                  $value = $value[0];
                }
                else if (isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::defaut])) {
                  $value = MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::defaut];
                }
                else {
                  $value = "";
                }
              }
		        }
		        break;
	        // ARRAY LDAP
		      case MappingMce::arrayLdap:
		        if (is_array($value)) {
		          unset($value['count']);
		        }
		        else {
		          $value = [$value];
		        }
            break;
          // BOOLEAN LDAP
          case MappingMce::booleanLdap:
            // Gestion d'un prefix
            if (isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::prefixLdap])) {
              $_prefix = MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::prefixLdap];
              $_found = false;
              foreach ($value as $val) {
                if (strpos($val, $_prefix) === 0) {
                  $value = trim(str_replace($_prefix, '', $val));
                  $_found = true;
                  break;
                }
              }
              // Valeur par défaut
              if (!$_found) {
                if (isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::defaut])) {
                  $value = MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::defaut];
                }
                else {
                  $value = false;
                }
              }
            }
            // MANTIS 0006291: Permettre un type booleanLdap sur une entrée multivaluée
            if (is_array($value) && isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::trueLdapValue])) {
              $value = in_array(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::trueLdapValue], $value);
            }
            else if (is_array($value) && isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::falseLdapValue])) {
              $value = !in_array(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::falseLdapValue], $value);
            }
            else {
              if (is_array($value)) {
                $value = $value[0] ?: null;
              }
              if (isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::trueLdapValue])) {
                $value = $value === MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::trueLdapValue];
              }
              else {
                $value = $value == '1' || $value == 'oui' ? true : false;
              }
            }
		        break;
		    }
		  }		    
		  return $value;
		}
		// Récupération de la valeur par défaut
		if (isset(MappingMce::$Data_Mapping[$this->objectType])
		    && isset(MappingMce::$Data_Mapping[$this->objectType][$name])
		    && isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::defaut])) {
      return MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::defaut];
    }
		return null;
	}

	/**
	 * PHP magic to check if an instance variable is set
	 *
	 * @access public
	 * @return
	 * @ignore
	 */
	public function __isset($name) {
    $name = strtolower($name);
		$lname = $name;
		// Récupèration des données de mapping
		if (isset(MappingMce::$Data_Mapping[$this->objectType])
				&& isset(MappingMce::$Data_Mapping[$this->objectType][$name])) {
			$lname = MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::name];
    }
    // Gestion du cas du prefix ldap ?
    if (isset(MappingMce::$Data_Mapping[$this->objectType])
        && isset(MappingMce::$Data_Mapping[$this->objectType][$name])
        && isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::type])
        && MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::type] == MappingMce::stringLdap
        && isset($this->data[$lname])
        && is_array($this->data[$lname])) {
      if (isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::prefixLdap])) {
        $_prefix = MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::prefixLdap];
        $isset = false;
        foreach ($this->data[$lname] as $val) {
          if (strpos($val, $_prefix) === 0) {
            $isset = true;
            break;
          }
        }
      }
      else {
        $isset = isset($this->data[$lname]) && isset($this->data[$lname][0]);
      }
    }
    else if (isset(MappingMce::$Data_Mapping[$this->objectType])
        && isset(MappingMce::$Data_Mapping[$this->objectType][$name])
        && isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::type])
        && MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::type] == MappingMce::booleanLdap) {
      $isset = true;
    }
    else {
      $isset = isset($this->data[$lname]);
    }
    // Récupération de la valeur par défaut
		if (!$isset && isset(MappingMce::$Data_Mapping[$this->objectType])
        && isset(MappingMce::$Data_Mapping[$this->objectType][$name])
        && isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::defaut])) {
      $isset = true;
    }
		return $isset;
	}

	/**
	 * PHP magic to remove an instance variable
	 *
	 * @access public
	 * @return
	 * @ignore
	 */
	public function __unset($name) {
		$name = strtolower($name);
		$lname = $name;
		// Récupèration des données de mapping
		if (isset(MappingMce::$Data_Mapping[$this->objectType])
				&& isset(MappingMce::$Data_Mapping[$this->objectType][$name])) {
			$lname = MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::name];
		}
    // Gestion du cas du prefix ldap ?
    if (isset(MappingMce::$Data_Mapping[$this->objectType])
        && isset(MappingMce::$Data_Mapping[$this->objectType][$name])
        && isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::type])
        && MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::type] == MappingMce::stringLdap
        && isset($this->data[$lname])
        && is_array($this->data[$lname])) {
      if (isset(MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::prefixLdap])) {
        $_prefix = MappingMce::$Data_Mapping[$this->objectType][$name][MappingMce::prefixLdap];
        foreach ($this->data[$lname] as $k => $val) {
          if (strpos($val, $_prefix) === 0) {
            unset($this->data[$lname][$k]);
            $this->haschanged[$lname] = true;
            break;
          }
        }
        // Vider complétement si le tableau est vide
        if (!count($this->data[$lname])) {
          $this->data[$lname] = null;
        }
      }
      else {
        $this->data[$lname] = null;
        $this->haschanged[$lname] = true;
      }
    }
    else if (isset($this->data[$lname])) {
			unset($this->data[$lname]);
			$this->haschanged[$lname] = true;
		}
	}

	/**
	 * PHP magic to implement any getter, setter, has and delete operations
	 * on an instance variable.
	 * Methods like e.g. "SetVariableName($x)" and "GetVariableName()" are supported
	 *
	 * @access public
	 * @return mixed
	 * @ignore
	 */
	public function __call($name, $arguments) {
		$name = strtolower($name);
		$operator = substr($name, 0,3);
		$var = substr($name,3);

		// Récupèration des données de mapping
		if (isset(MappingMce::$Data_Mapping[$this->objectType])
				&& isset(MappingMce::$Data_Mapping[$this->objectType][$var])) {
			$var = MappingMce::$Data_Mapping[$this->objectType][$var][MappingMce::name];
		}
		if ($operator == "set" && count($arguments) == 1){
			$this->$var = $arguments[0];
			return true;
		}
		if ($operator == "set" && count($arguments) == 2 && $arguments[1] === false){
			$this->data[$var] = $arguments[0];
			return true;
		}
		// getter without argument = return variable, null if not set
		if ($operator == "get" && count($arguments) == 0) {
			return $this->$var;
		}
		// getter with one argument = return variable if set, else the argument
		else if ($operator == "get" && count($arguments) == 1) {
			if (isset($this->$var)) {
				return $this->$var;
			}
			else
				return $arguments[0];
		}
		if ($operator == "has" && count($arguments) == 0)
			return isset($this->$var);

		if ($operator == "del" && count($arguments) == 0) {
			unset($this->$var);
			return true;
		}
	}

  /**
	 * Méthode toString pour afficher le contenu des données de la classe
	 * 
	 * @return string
	 */
  public function __toString() {
    return $this->get_class . ": {\r\n\tobjectType: ".$this->objectType.",\r\n\tisExist: ".$this->isExist.",\r\n\tisLoaded: ".$this->isLoaded.",\r\n\tdata: " . str_replace("\n", "\n\t", var_export($this->data, true)) . ",\r\n\thaschanged: " . str_replace("\n", "\n\t", var_export($this->haschanged, true)) . "\r\n}";
  }
}
