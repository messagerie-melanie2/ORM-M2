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
namespace LibMelanie\Objects;

use LibMelanie\Sql;
use LibMelanie\Ldap\Ldap;
use LibMelanie\Config;
use LibMelanie\Config\MappingMce;
use LibMelanie\Log\M2Log;
use LibMelanie\Lib\MagicObject;
use LibMelanie\Interfaces\IObjectMelanie;
use LibMelanie\Config\DefaultConfig;

/**
 * Gestion de l'utilisateur Melanie2
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage ORM
 */
class UserMelanie extends MagicObject implements IObjectMelanie {
  /**
   * Timezone de l'utilisateur
   * 
   * @var string
   */
  public $timezone;
  /**
   * Quel serveur LDAP utiliser pour la lecture des données
   * 
   * @var string
   */
  private $server;
  /**
   * Mapping des données
   * 
   * @var array
   */
  private $mapping;
  /**
   * Liste des propriétés qui ne peuvent pas être modifiées
   * 
   * @var array Valeur static
   */
  private static $unchangeableProperties = [
      'dn',
      'uid',
  ];
  /**
   * Liste des propriétés qui ne peuvent pas être modifiées
   * 
   * @var array Valeur non static
   */
  private $_unchangeableProperties;
  /**
   * Est-ce que l'objet a déjà été initialisé
   * 
   * @var boolean
   */
  private static $isInit = false;

  /**
   * Supporter la création d'un objet ?
   * 
   * @var boolean
   */
  private $_supportCreation = false;

  /**
   * Configuration de l'objet dans le ldap
   * 
   * @var array
   */
  private $_itemConfiguration;
  
  /**
   * Constructeur de la class
   * 
   * @param string $server Serveur d'annuaire a utiliser en fonction de la configuration
   * @param array $unchangeableProperties Liste des propriétés qui ne peut pas être modifiées
   * @param array $mapping Données de mapping
   * @param string $itemName Nom de l'objet associé dans la configuration LDAP
   */
  public function __construct($server = null, $unchangeableProperties = null, $mapping = null, $itemName = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");
    
    // Récupération du type d'objet en fonction de la class
    $this->objectType = explode('\\', $this->get_class);
    $this->objectType = $this->objectType[count($this->objectType) - 1];

    // Init du serveur
    if (isset($server)) {
      $this->server = $server;
    }
    if (isset($mapping)) {
      $this->mapping = $mapping;
    }

    // Gestion des proprietes non modifiables
    if (isset($unchangeableProperties)) {
      $this->_unchangeableProperties = $unchangeableProperties;
    }
    else if (!isset($this->_unchangeableProperties)) {
      $this->_unchangeableProperties = self::$unchangeableProperties;
    }

    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    
    // Gestion du mapping des clés primaires
    if (isset(MappingMce::$Primary_Keys[$this->objectType])) {
      if (is_array(MappingMce::$Primary_Keys[$this->objectType]))
        $this->primaryKeys = MappingMce::$Primary_Keys[$this->objectType];
      else
        $this->primaryKeys = [
            MappingMce::$Primary_Keys[$this->objectType]
        ];
    }

    // Charger la configuration de l'objet dans la configuration LDAP
    $server = $server ?: Config\Ldap::$SEARCH_LDAP;
    if (isset($itemName)) {
      $this->readItemConfiguration($itemName, $server);
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
      '_unchangeableProperties'   => $this->_unchangeableProperties,
      'mapping'     => $this->mapping,
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
      $this->_unchangeableProperties = $array['_unchangeableProperties'];
      $this->mapping = $array['mapping'];
      if (isset($this->mapping)) {
        self::Init($this->mapping, null);
      }
    }
	}

  /**
   * Appel l'initialisation du mapping
   * 
   * @param array $mapping Données de mapping
   * @return boolean
   */
  protected static function Init($mapping = [], $server = null) {
    if (!self::$isInit) {
      if (isset($server) && isset(Config\Ldap::$SERVERS[$server]['mapping'])) {
        $mapping = array_merge($mapping, Config\Ldap::$SERVERS[$server]['mapping']);
      }
      else if (isset(Config\Ldap::$SERVERS[Config\Ldap::$SEARCH_LDAP]['mapping'])) {
        $mapping = array_merge($mapping, Config\Ldap::$SERVERS[Config\Ldap::$SEARCH_LDAP]['mapping']);
      }
      // Traitement du mapping
      foreach ($mapping as $key => $map) {
        if (is_array($map)) {
          if (!isset($map[MappingMce::type])) {
            $mapping[$key][MappingMce::type] = MappingMce::stringLdap;
            if (!isset($map[MappingMce::name])) {
              // Si pas de type ni de nom c'est surement un tableau de champs
              $mapping[$key][MappingMce::name] = $map;
            }
          }
        }
        else {
          $mapping[$key] = [MappingMce::name => $map, MappingMce::type => MappingMce::stringLdap];
        }
      }
      self::$isInit = MappingMce::UpdateDataMapping('UserMelanie', $mapping);
    }
    return self::$isInit;
  }
  
  /**
   * Chargement de l'objet UserMelanie
   * need: $this->dn
   * need: $this->uid
   * need: $this->email
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   * @param string $filter [Optionnal] Filter to load data
   * @param string $filterFromEmail [Optionnal] Filter to load data
   * 
   * @see IObjectMelanie::load()
   *
   * @return boolean isExist
   */
  public function load($attributes = null, $filter = null, $filterFromEmail = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->load()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    // Si les clés primaires et la table ne sont pas définies, impossible de charger l'objet
    if (!isset($this->uid) && !isset($this->email) && !isset($this->dn)) {
      return false;
    }
    // Test si l'objet existe, pas besoin de load
    if (is_bool($this->isExist) && $this->isLoaded && !isset($attributes)) {
      return $this->isExist;
    }
    // Mapping pour les champs
		$attributesmapping = [];
		if (is_array($attributes)) {
    		// Recherche l'attribut dans la conf de mapping
    		foreach ($attributes as $key) {
    			// Récupèration des données de mapping
    			if (isset(MappingMce::$Data_Mapping[$this->objectType])
    					&& isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
    				$key = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
          }
          if (is_array($key)) {
            foreach ($key as $k) {
              if (!isset($this->data[$k]) && !in_array($k, $attributesmapping)) {
                $attributesmapping[] = $k;
              }
            }
          }
          else {
            if (!isset($this->data[$key]) && !in_array($key, $attributesmapping)) {
              $attributesmapping[] = $key;
            }
          }
        }
        if (!empty($attributes) && empty($attributesmapping)) {
          return true;
        }
		}
    // Récupération des données depuis le LDAP avec le dn, l'uid ou l'email
    if (isset($this->dn)) {
      $data = Ldap::GetUserInfosFromDn($this->dn, $attributesmapping, $this->server);
    } else if (isset($this->uid)) {
      $data = Ldap::GetUserInfos($this->uid, $this->generateFilter($filter), $attributesmapping, $this->server);
    } else if (isset($this->email)) {
      $data = Ldap::GetUserInfosFromEmail($this->email, $this->generateFilter($filterFromEmail), $attributesmapping, $this->server);
    }
    if (isset($data)) {
      $this->setData($data);
    } else {
      $this->isExist = false;      
    }
    $this->isLoaded = true;
    return $this->isExist;
  }
  
  /**
   * Détermine si l'objet existe dans Melanie2
   * need: $this->dn
   * need: $this->uid
   * need: $this->email
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   * @param string $filter [Optionnal] Filter to load data
   * @param string $filterFromEmail [Optionnal] Filter to load data
   * 
   * @see IObjectMelanie::exists()
   */
  public function exists($attributes = null, $filter = null, $filterFromEmail = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->exists()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    // Si les clés primaires et la table ne sont pas définies, impossible de charger l'objet
    if (!isset($this->uid) && !isset($this->email) && !isset($this->dn)) {
      return false;
    }
    // Test si l'objet existe, pas besoin de load
    if (is_bool($this->isExist)) {
      return $this->isExist;
    }
    // Mapping pour les champs
		$attributesmapping = [];
		if (is_array($attributes)) {
    	$attributesmapping = $this->_get_mapping_attributes($attributes);
		}
    // Récupération des données depuis le LDAP avec le dn, l'uid ou l'email
    if (isset($this->dn)) {
      $data = Ldap::GetUserInfosFromDn($this->dn, $attributesmapping, $this->server);
    } else if (isset($this->uid)) {
      $data = Ldap::GetUserInfos(null, $this->generateFilter($filter), $attributesmapping, $this->server);
    } else if (isset($this->email)) {
      $data = Ldap::GetUserInfosFromEmail(null, $this->generateFilter($filterFromEmail), $attributesmapping, $this->server);
    }
    $this->isExist = isset($data);
    return $this->isExist;
  }
  
  /**
   * Enregistrement de l'utilisateur modifié dans l'annuaire
   * 
   * @see IObjectMelanie::save()
   * @ignore
   */
  public function save() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->save()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    // Result
    $ret = true;
    // Est-ce que le dn est bien défini ?
    if (!isset($this->dn)) {
      return false;
    }
    // MANTIS 0006136: Gérer la création d'un objet LDAP
    if ($this->_supportCreation && !$this->exists()) {
      $entry = $this->getCreationEntry();
      $ldap = Ldap::GetInstance(\LibMelanie\Config\Ldap::$MASTER_LDAP);
      // Gérer une authentification externe
      if (isset($this->_itemConfiguration['bind_dn'])) {
        $ret = $ldap->authenticate($this->_itemConfiguration['bind_dn'], $this->_itemConfiguration['bind_password']);
      }
      return $ret && $ldap->add($this->dn, $entry);
    }
    else {
      // Modification de l'entrée si on est pas en création
      $entry = $this->getEntry();
      if (!empty($entry)) {
        $ldap = Ldap::GetInstance(\LibMelanie\Config\Ldap::$MASTER_LDAP);
        // Gérer une authentification externe
        if (isset($this->_itemConfiguration['bind_dn'])) {
          $ret = $ldap->authenticate($this->_itemConfiguration['bind_dn'], $this->_itemConfiguration['bind_password']);
        }
        return $ret && $ldap->modify($this->dn, $entry);
      }
    }
    return $ret;
  }
  
  /**
   * Not implemented
   * 
   * @see IObjectMelanie::delete()
   * @ignore
   */
  public function delete() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->delete()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    throw new \Exception('Not implemented');
  }
  
  /**
   * Positionne les data pour le UserMelanie
   * 
   * @param array $data
   */
  public function setData($data) {
    foreach ($data as $key => $value) {
      if ($key == 'count') {
        continue;
      }
      if (!is_numeric($key)) {
        if (is_array($value)
            && isset($value['count'])) {
          unset($value['count']);
        }
        $this->data[$key] = $value;
      }
    }
    $this->isExist = true;
    $this->isLoaded = true;
    $this->initializeHasChanged();
  }

  /**
   * Récupère l'entrée générée suite au changement de valeur
   * 
   * @return array
   */
  private function getEntry() {
    $entry = [];
    foreach ($this->haschanged as $key => $changed) {
      if ($changed) {
        if (!in_array($key, $this->_unchangeableProperties)) {
          $entry[$key] = $this->data[$key];
        }
      }
    }
    return $entry;
  }

  /**
   * Récupère l'entrée à créer à partir de toutes les données
   * 
   * @return array
   */
  private function getCreationEntry() {
    $entry = [];
    // Récuperer les valeurs par défaut ?
    if (isset($this->_itemConfiguration['default']) && is_array($this->_itemConfiguration['default'])) {
      foreach ($this->_itemConfiguration['default'] as $key => $value) {
        $entry[$key] = $value;
      }
    }
    // Récupérer les données
    foreach ($this->data as $key => $value) {
      $entry[$key] = $value;
    }
    // Ne pas mettre le dn
    unset($entry['dn']);
    return $entry;
  }

  /**
   * Génération du filtre ldap en fonction des attributs du user
   * 
   * @param string $filter Filtre à traiter
   * 
   * @return string Filtre généré
   */
  private function generateFilter($filter) {
    $matches = [];
    // Gestion du %%username%%
    if (strpos($filter, '%%username%%') !== false) {
      $filter = str_replace('%%username%%', $this->uid, $filter);
    }
    if (strpos($filter, '%%') !== false 
        && preg_match_all('/%%([\w]+)%%/', $filter, $matches, PREG_PATTERN_ORDER) !== false) {
      foreach ($matches[1] as $attr) {
        $filter = str_replace('%%'.$attr.'%%', $this->$attr, $filter);
      }
    }
    return $filter;
  }
  
  // -- LDAP
  /**
   * Authentification sur le serveur LDAP
   *
   * @param string $password
   * @param boolean $master Utiliser le serveur maitre (nécessaire pour faire des modifications)
   * @param string $user_dn DN de l'utilisateur si ce n'est pas le courant a utiliser
   * @param boolean $gssapi Utiliser une authentification GSSAPI sans mot de passe
   * @param string $itemName Nom de l'objet associé dans la configuration LDAP
   * 
   * @return boolean
   */
  public function authentification($password, $master = false, $user_dn = null, $gssapi = false, $itemName = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->authentification()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if ($master) {
      $this->server = \LibMelanie\Config\Ldap::$MASTER_LDAP;
    }
    // Gérer l'itemName pour l'authentification également
    if (isset($itemName)) {
      $this->readItemConfiguration($itemName, $this->server);
    }
    // Authentification en direct ?
    if (isset($this->_itemConfiguration) && isset($this->_itemConfiguration['bind_dn'])) {
      return Ldap::AuthentificationDirect($this->_itemConfiguration['bind_dn'], $this->_itemConfiguration['bind_password'], $this->server);
    }
    else if ($gssapi) {
      return Ldap::AuthentificationGSSAPI($this->server);
    }
    else if (isset($user_dn)) {
      return Ldap::AuthentificationDirect($user_dn, $password, $this->server);
    }
    else if (isset($this->dn)) {
      return Ldap::AuthentificationDirect($this->dn, $password, $this->server);
    }
    else {
      return Ldap::Authentification($this->uid, $password, $this->server, true);
    }
  }

  /**
   * Récupère la liste des BALP accessibles à l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   * @param string $filter [Optionnal] Filter to load data
   * 
   * @return UserMelanie[] Liste d'objet UserMelanie
   */
  public function getBalp($attributes = null, $filter = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getBalpList()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return [];
    }
    // Mapping pour les champs
		$attributesmapping = [];
		if (is_array($attributes)) {
    	$attributesmapping = $this->_get_mapping_attributes($attributes);
		}
    // Récupération des Balp depuis le LDAP
    $result = Ldap::GetUserBalPartagees(null, $this->generateFilter($filter), $attributesmapping, $this->server);
    $balps = [];
    if (isset($result)) {
      // Parcours la list des balp pour générer les objet UserMelanie
      foreach ($result as $data) {
        if (is_array($data)) {
          $balp = new UserMelanie($this->server);
          $balp->setData($data);
          $balps[$balp->uid] = $balp;
        }
      }
    }
    return $balps;
  }

  /**
   * Récupère la liste des BALP accessibles au moins en émission à l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   * @param string $filter [Optionnal] Filter to load data
   *
   * @return UserMelanie[] Liste d'objet UserMelanie
   */
  public function getBalpEmission($attributes = null, $filter = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getBalpListEmission()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return [];
    }
    // Mapping pour les champs
		$attributesmapping = [];
		if (is_array($attributes)) {
    	$attributesmapping = $this->_get_mapping_attributes($attributes);
		}
    // Récupération des Balp depuis le LDAP
    $result = Ldap::GetUserBalEmission(null, $this->generateFilter($filter), $attributesmapping, $this->server);
    $balps = [];
    if (isset($result)) {
      // Parcours la list des balp pour générer les objet UserMelanie
      foreach ($result as $data) {
        if (is_array($data)) {
          $balp = new UserMelanie($this->server);
          $balp->setData($data);
          $balps[$balp->uid] = $balp;
        }
      }
    }
    return $balps;
  }

  /**
   * Récupère la liste des BALP accessibles en gestionnaire à l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   * @param string $filter [Optionnal] Filter to load data
   *
   * @return UserMelanie[] Liste d'objet UserMelanie
   */
  public function getBalpGestionnaire($attributes = null, $filter = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getBalpListGestionnaire()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return false;
    }
    // Mapping pour les champs
		$attributesmapping = [];
		if (is_array($attributes)) {
      $attributesmapping = $this->_get_mapping_attributes($attributes);
		}
    // Récupération des Balp depuis le LDAP
    $result = Ldap::GetUserBalGestionnaire(null, $this->generateFilter($filter), $attributesmapping, $this->server);
    $balps = [];
    if (isset($result)) {
      // Parcours la list des balp pour générer les objet UserMelanie
      foreach ($result as $data) {
        if (is_array($data)) {
          $balp = new UserMelanie($this->server);
          $balp->setData($data);
          $balps[$balp->uid] = $balp;
        }
      }
    }
    return $balps;
  }

  /**
   * Récupère la liste des groupes dont l'utilisateur est propriétaire
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   * @param string $filter [Optionnal] Filter to load data
   *
   * @return UserMelanie[] Liste d'objet UserMelanie
   */
  public function getGroups($attributes = null, $filter = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getGroups()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->dn)) {
      return false;
    }
    // Mapping pour les champs
		$attributesmapping = [];
		if (is_array($attributes)) {
    	$attributesmapping = $this->_get_mapping_attributes($attributes);
		}
    // Récupération des Balp depuis le LDAP
    $result = Ldap::GetUserGroups(null, $this->generateFilter($filter), $attributesmapping, $this->server);
    $lists = [];
    if (isset($result)) {
      // Parcours la liste des listes pour générer les objet UserMelanie
      foreach ($result as $data) {
        if (is_array($data)) {
          $list = new UserMelanie($this->server);
          $list->setData($data);
          $lists[] = $list;
        }
      }
    }
    return $lists;
  }

  /**
   * Récupère la liste des groupes dont l'utilisateur est membre
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   * @param string $filter [Optionnal] Filter to load data
   *
   * @return UserMelanie[] Liste d'objet UserMelanie
   */
  public function getGroupsIsMember($attributes = null, $filter = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getGroupsIsMember()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return false;
    }
    // Mapping pour les champs
		$attributesmapping = [];
		if (is_array($attributes)) {
    	$attributesmapping = $this->_get_mapping_attributes($attributes);
		}
    // Récupération des Balp depuis le LDAP
    $result = Ldap::GetGroupsUserIsMember(null, $this->generateFilter($filter), $attributesmapping, $this->server);
    $lists = [];
    if (isset($result)) {
      // Parcours la liste des listes pour générer les objet UserMelanie
      foreach ($result as $data) {
        if (is_array($data)) {
          $list = new UserMelanie($this->server);
          $list->setData($data);
          $lists[] = $list;
        }
      }
    }
    return $lists;
  }

  /**
   * Récupère la liste des listes de diffusion dont l'utilisateur est membre
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   * @param string $filter [Optionnal] Filter to load data
   *
   * @return UserMelanie[] Liste d'objet UserMelanie
   */
  public function getListsIsMember($attributes = null, $filter = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getListsIsMember()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    // Mapping pour les champs
		$attributesmapping = [];
		if (is_array($attributes)) {
      $attributesmapping = $this->_get_mapping_attributes($attributes);
		}
    // Récupération des Balp depuis le LDAP
    $result = Ldap::GetListsUserIsMember(null, $this->generateFilter($filter), $attributesmapping, $this->server);
    $lists = [];
    if (isset($result)) {
      // Parcours la liste des listes pour générer les objet UserMelanie
      foreach ($result as $data) {
        if (is_array($data)) {
          $list = new UserMelanie($this->server);
          $list->setData($data);
          $lists[] = $list;
        }
      }
    }
    return $lists;
  }

  /**
   * Récupère la liste des workspaces dont l'utilisateur est owner
   * 
   * @param string $orderby [Optionnel] nom du champ a trier
   * @param boolean $asc [Optionnel] tri ascendant ?
   * @param integer $limit [Optionnel] limite du nombre de résultats à retourner
   * @param integer $offset [Optionnel] offset pour la pagination
   * 
   * @return WorkspaceMelanie[]
   */
  public function getUserWorkspaces($orderby = null, $asc = true, $limit = null, $offset = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserWorkspaces()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return false;
    }
    $query = Sql\SqlWorkspaceRequests::listUserWorkspaces;
    $query = str_replace('{order_by}', Sql\Sql::GetOrderByClause('Workspace', $orderby, $asc), $query);
    $query = str_replace('{limit}', Sql\Sql::GetLimitClause($limit, $offset), $query);
    // Params
    $params = [
        "user_uid" => $this->uid,
    ];
    // Liste les calendriers de l'utilisateur
    return Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\WorkspaceMelanie', 'Workspace');
  }

  /**
   * Récupère la liste des workspaces auxquels l'utilisateur accède
   * 
   * @param string $orderby [Optionnel] nom du champ a trier
   * @param boolean $asc [Optionnel] tri ascendant ?
   * @param integer $limit [Optionnel] limite du nombre de résultats à retourner
   * @param integer $offset [Optionnel] offset pour la pagination
   * 
   * @return WorkspaceMelanie[]
   */
  public function getSharedWorkspaces($orderby = null, $asc = true, $limit = null, $offset = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedWorkspaces()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return false;
    }
    $query = Sql\SqlWorkspaceRequests::listSharedWorkspaces;
    $query = str_replace('{order_by}', Sql\Sql::GetOrderByClause('Workspace', $orderby, $asc), $query);
    $query = str_replace('{limit}', Sql\Sql::GetLimitClause($limit, $offset), $query);
    // Params
    $params = [
        "user_uid" => $this->uid,
    ];
    // Liste les calendriers de l'utilisateur
    return Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\WorkspaceMelanie', 'Workspace');
  }
  
  // -- CALENDAR
  /**
   * Retour le calendrier par défaut
   * 
   * @return CalendarMelanie
   */
  public function getDefaultCalendar() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDefaultCalendar()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return false;
    }
    $query = Sql\SqlMelanieRequests::getDefaultObject;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['CalendarMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['CalendarMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['CalendarMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['CalendarMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['CalendarMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['CalendarMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    // Params
    $params = [
        "group_uid" => DefaultConfig::CALENDAR_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "pref_scope" => DefaultConfig::CALENDAR_PREF_SCOPE,
        "pref_name" => DefaultConfig::CALENDAR_PREF_DEFAULT_NAME,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    // Calendrier par défaut de l'utilisateur
    $calendars = Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\CalendarMelanie');
    if (isset($calendars) && is_array($calendars) && count($calendars)) {
      $calendars[0]->pdoConstruct(true);
      return $calendars[0];
    } else {
      $calendars = $this->getUserCalendars();
      return isset($calendars[0]) ? $calendars[0] : null;
    }
    return null;
  }
  
  /**
   * Récupère la liste des calendriers appartenant à l'utilisateur
   * 
   * @return CalendarMelanie[]
   */
  public function getUserCalendars() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserCalendars()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return false;
    }
    $query = Sql\SqlMelanieRequests::listUserObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['CalendarMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['CalendarMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['CalendarMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['CalendarMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['CalendarMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['CalendarMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    // Params
    $params = [
        "group_uid" => DefaultConfig::CALENDAR_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME
    ];
    // Liste les calendriers de l'utilisateur
    return Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\CalendarMelanie');
  }
  
  /**
   * Récupère la liste des calendriers appartenant à l'utilisateur
   * ainsi que ceux qui lui sont partagés
   * 
   * @return CalendarMelanie[]
   */
  public function getSharedCalendars() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedCalendars()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return false;
    }
    $query = Sql\SqlMelanieRequests::listSharedObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['CalendarMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['CalendarMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['CalendarMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['CalendarMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['CalendarMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['CalendarMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    // Params
    $params = [
        "group_uid" => DefaultConfig::CALENDAR_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    // Liste les calendriers de l'utilisateur
    return Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\CalendarMelanie');
  }
  
  // -- TASKSLIST
  /**
   * Retour la liste de tâches par défaut
   * 
   * @return TaskslistMelanie
   */
  public function getDefaultTaskslist() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDefaultTaskslist()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return false;
    }
    $query = Sql\SqlMelanieRequests::getDefaultObject;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['TaskslistMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['TaskslistMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['TaskslistMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['TaskslistMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['TaskslistMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['TaskslistMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    // Params
    $params = [
        "group_uid" => DefaultConfig::TASKSLIST_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "pref_scope" => DefaultConfig::TASKSLIST_PREF_SCOPE,
        "pref_name" => DefaultConfig::TASKSLIST_PREF_DEFAULT_NAME,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    // Liste de tâches par défaut de l'utilisateur
    $taskslists = Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\TaskslistMelanie');
    if (isset($taskslists) && is_array($taskslists) && count($taskslists)) {
      $taskslists[0]->pdoConstruct(true);
      return $taskslists[0];
    } else {
      $taskslists = $this->getUserTaskslists();
      return isset($taskslists[0]) ? $taskslists[0] : null;
    }
    return null;
  }
  
  /**
   * Récupère la liste des listes de tâches appartenant à l'utilisateur
   * 
   * @return TaskslistMelanie[]
   */
  public function getUserTaskslists() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserTaskslists()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return false;
    }
    $query = Sql\SqlMelanieRequests::listUserObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['TaskslistMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['TaskslistMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['TaskslistMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['TaskslistMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['TaskslistMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['TaskslistMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    // Params
    $params = [
        "group_uid" => DefaultConfig::TASKSLIST_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME
    ];
    // Liste les listes de tâches de l'utilisateur
    return Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\TaskslistMelanie');
  }
  
  /**
   * Récupère la liste des listes de tâches appartenant à l'utilisateur
   * ainsi que ceux qui lui sont partagés
   * 
   * @return TaskslistMelanie[]
   */
  public function getSharedTaskslists() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedTaskslists()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return false;
    }
    $query = Sql\SqlMelanieRequests::listSharedObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['TaskslistMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['TaskslistMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['TaskslistMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['TaskslistMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['TaskslistMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['TaskslistMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    // Params
    $params = [
        "group_uid" => DefaultConfig::TASKSLIST_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    // Liste les listes de tâches de l'utilisateur
    return Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\TaskslistMelanie');
  }
  
  // -- ADDRESSBOOK
  /**
   * Retour la liste de contacts par défaut
   * 
   * @return AddressbookMelanie
   */
  public function getDefaultAddressbook() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDefaultAddressbook()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return false;
    }
    $query = Sql\SqlMelanieRequests::getDefaultObject;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['AddressbookMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['AddressbookMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['AddressbookMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['AddressbookMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['AddressbookMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['AddressbookMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    // Params
    $params = [
        "group_uid" => DefaultConfig::ADDRESSBOOK_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "pref_scope" => DefaultConfig::ADDRESSBOOK_PREF_SCOPE,
        "pref_name" => DefaultConfig::ADDRESSBOOK_PREF_DEFAULT_NAME,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    // Liste de tâches par défaut de l'utilisateur
    $addressbooks = Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\AddressbookMelanie');
    if (isset($addressbooks) && is_array($addressbooks) && count($addressbooks)) {
      $addressbooks[0]->pdoConstruct(true);
      return $addressbooks[0];
    } else {
      $addressbooks = $this->getUserAddressbooks();
      return isset($addressbooks[0]) ? $addressbooks[0] : null;
    }
    return null;
  }
  
  /**
   * Récupère la liste des listes de contacts appartenant à l'utilisateur
   * 
   * @return AddressbookMelanie[]
   */
  public function getUserAddressbooks() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserAddressbooks()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return false;
    }
    $query = Sql\SqlMelanieRequests::listUserObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['AddressbookMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['AddressbookMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['AddressbookMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['AddressbookMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['AddressbookMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['AddressbookMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    // Params
    $params = [
        "group_uid" => DefaultConfig::ADDRESSBOOK_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME
    ];
    // Liste les listes de contacts de l'utilisateur
    return Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\AddressbookMelanie');
  }
  
  /**
   * Récupère la liste des listes de contacts appartenant à l'utilisateur
   * ainsi que ceux qui lui sont partagés
   * 
   * @return AddressbookMelanie[]
   */
  public function getSharedAddressbooks() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedAddressbooks()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return false;
    }
    $query = Sql\SqlMelanieRequests::listSharedObjects;
    // Replace name
    $query = str_replace('{user_uid}', MappingMce::$Data_Mapping['AddressbookMelanie']['owner'][MappingMce::name], $query);
    $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping['AddressbookMelanie']['id'][MappingMce::name], $query);
    $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping['AddressbookMelanie']['ctag'][MappingMce::name], $query);
    $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping['AddressbookMelanie']['synctoken'][MappingMce::name], $query);
    $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping['AddressbookMelanie']['name'][MappingMce::name], $query);
    $query = str_replace('{perm_object}', MappingMce::$Data_Mapping['AddressbookMelanie']['perm'][MappingMce::name], $query);
    $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping['CalendarMelanie']['object_id'][MappingMce::name], $query);
    // Params
    $params = [
        "group_uid" => DefaultConfig::ADDRESSBOOK_GROUP_UID,
        "user_uid" => $this->uid,
        "attribute_name" => DefaultConfig::ATTRIBUTE_NAME_NAME,
        "attribute_perm" => DefaultConfig::ATTRIBUTE_NAME_PERM,
        "attribute_permfg" => DefaultConfig::ATTRIBUTE_NAME_PERMGROUP
    ];
    // Liste les listes de contacts de l'utilisateur
    return Sql\Sql::GetInstance()->executeQuery($query, $params, 'LibMelanie\\Objects\\AddressbookMelanie');
  }
  
  /**
   * Recupère le timezone par défaut pour le
   * need: $this->uid
   * 
   * @deprecated
   */
  public function getTimezone() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getTimezone()");
    // Gestion du mapping global
    static::Init($this->mapping, $this->server);
    if (!isset($this->uid)) {
      return DefaultConfig::CALENDAR_DEFAULT_TIMEZONE;
    }
    if (!isset($this->timezone)) {
      // Replace name
      $query = str_replace('{pref_name}', 'timezone', Sql\SqlMelanieRequests::getUserPref);
      // Params
      $params = [
          "user_uid" => $this->uid,
          "pref_scope" => DefaultConfig::PREF_SCOPE,
          "pref_name" => DefaultConfig::TZ_PREF_NAME
      ];
      // Récupération du timezone
      $res = Sql\Sql::GetInstance()->executeQueryToObject($query, $params, $this);
      // Test si le timezone est valide en PHP
      try {
        $tz = new \DateTimeZone($this->timezone);
      } catch ( \Exception $ex ) {
        $this->timezone = DefaultConfig::CALENDAR_DEFAULT_TIMEZONE;
      }
      M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getTimezone() this->timezone: " . $this->timezone);
    }
    return $this->timezone;
  }


  /**
   * Génère un tableau d'attribut mappés
   * 
   * @param array $attributes Liste des attributs a mapper
   * 
   * @return array $attributesmapping
   */
  private function _get_mapping_attributes($attributes) {
    // Mapping pour les champs
		$attributesmapping = [];
    // Recherche l'attribut dans la conf de mapping
    foreach ($attributes as $key) {
      // Récupèration des données de mapping
      if (isset(MappingMce::$Data_Mapping[$this->objectType])
          && isset(MappingMce::$Data_Mapping[$this->objectType][$key])) {
        $key = MappingMce::$Data_Mapping[$this->objectType][$key][MappingMce::name];
      }
      if (is_array($key)) {
        foreach ($key as $k) {
          if (!in_array($k, $attributesmapping)) {
            $attributesmapping[] = $k;
          }
        }
      }
      else if (!in_array($key, $attributesmapping)) {
        $attributesmapping[] = $key;
      }
    }
    return $attributesmapping;
  }

  /**
   * Lire la configuration LDAP de l'item si elle existe
   * supporte également des configurations par partie : objet.item.name
   * 
   * @param string $itemName Nom de l'objet dans la configuration
   * @param string $server Serveur ou chercher la configuration
   */
  private function readItemConfiguration($itemName, $server) {
    if (isset(Config\Ldap::$SERVERS[$server]) && isset(Config\Ldap::$SERVERS[$server]["items"])) {
      $itemsConf = Config\Ldap::$SERVERS[$server]["items"];
      // Si c'est un itemName par partie on cherche chaque partie dans la conf
      foreach ($this->explodeParts($itemName) as $part) {
        if (isset($itemsConf[$part])) {
          // Si l'itemName est directement dans la configuration
          $this->_itemConfiguration = $itemsConf[$part];
          $this->_supportCreation = isset($itemsConf[$part]['creation']) ? $itemsConf[$part]['creation'] : false;
          return;
        }
      }
    }
  }

  /**
   * Transforme un itemName en parts pour les retrouver dans la conf
   * 
   * @param string $itemName
   * 
   * @return array
   */
  private function explodeParts($itemName) {
    $parts = [$itemName];
    if (strpos($itemName, '.') !== false) {
      $pItem = $itemName;
      while($p = strrchr($pItem, '.')) {
          $pItem = str_replace($p, "", $pItem);
          $parts[] = $pItem;
      }
    }
    return $parts;
  }
}