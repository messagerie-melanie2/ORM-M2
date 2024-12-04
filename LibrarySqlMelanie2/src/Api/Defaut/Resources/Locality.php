<?php
/**
 * Ce fichier est développé pour la gestion de la lib MCE
 * 
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * 
 * ORM Mél Copyright © 2021 Groupe Messagerie/MTE
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
namespace LibMelanie\Api\Defaut\Resources;

use LibMelanie\Ldap\Ldap;
use LibMelanie\Lib\MceObject;
use LibMelanie\Log\M2Log;
use LibMelanie\Objects\LocalityMelanie;

/**
 * Classe locality pour les ressources par defaut
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $dn     DN de la localite dans l'annuaire            
 * @property string $uid    Identifiant de la localite
 * @property string $name   Nom de la localite
 * 
 * @method bool save()      Enregistrement de la localite dans l'annuaire
 * @method bool load()      Chargement de la localite dans l'annuaire
 * @method bool delete()    Suppression de la localite dans l'annuaire
 */
abstract class Locality extends MceObject {
  /**
   * Nom de la conf serveur utilisé pour le LDAP
   * 
   * @var string
   * @ignore
   */
  protected $_server;

  /**
   * Est-ce que l'objet est déjà chargé depuis l'annuaire ?
   * 
   * @var boolean
   * @ignore
   */
  protected $_isLoaded;

  /**
   * Est-ce que l'objet existe dans l'annuaire ?
   * 
   * @var boolean
   * @ignore
   */
  protected $_isExist;

  /**
   * Liste des propriétés à sérialiser pour le cache
   */
  protected $serializedProperties = [
    'otherldapobject',
    '_server',
    '_isLoaded',
    '_isExist',
    '_preferences',
  ];

  /**
   * Configuration de l'item name associé à l'objet courant
   * 
   * @var string
   * @ignore
   */
  protected $_itemName;

  // **** Constantes pour les preferences
  /**
   * Scope de preference par defaut pour la ressource
   */
  const PREF_SCOPE_DEFAULT = \LibMelanie\Config\ConfigMelanie::GENERAL_PREF_SCOPE;
  /**
   * Scope de preference pour les calendriers de la ressource
   */
  const PREF_SCOPE_CALENDAR = \LibMelanie\Config\ConfigMelanie::CALENDAR_PREF_SCOPE;

  /**
   * Droit de lecture
   */
  const RIGHT_READ = 'read';
  /**
   * Droit d'écriture
   */
  const RIGHT_WRITE = 'write';
  /**
   * Droit d'émission
   */
  const RIGHT_SEND = 'send';
  /**
   * Droit de gestion
   */
  const RIGHT_ADMIN = 'admin';

  // **** Configuration des filtres et des attributs par défaut
  /**
   * Filtre pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_FILTER = null;

  /**
   * Filtre pour la méthode listAllLocalities()
   * 
   * @ignore
   */
  const LIST_LOCALITIES_FILTER = null;
  /**
   * Filtre pour la méthode listResources()
   * 
   * @ignore
   */
  const LIST_RESOURCES_FILTER = null;
  /**
   * Filtre pour la méthode listResources() par type
   * 
   * @ignore
   */
  const LIST_RESOURCES_BY_TYPE_FILTER = null;
  /**
   * Filtre pour la méthode listResources() par uids
   * 
   * @ignore
   */
  const LIST_RESOURCES_BY_UIDS_FILTER = null;
  /**
   * Filtre pour la méthode listResources() par emails
   * 
   * @ignore
   */
  const LIST_RESOURCES_BY_EMAILS_FILTER = null;
  /**
   * Filtre pour la méthode load() avec un email
   * 
   * @ignore
   */
  const LOAD_FROM_EMAIL_FILTER = null;
  /**
   * Attributs par défauts pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_ATTRIBUTES = ['uid', 'name'];

  /**
   * Configuration du mapping qui surcharge la conf
   */
  const MAPPING = [];

  /**
   * DN a utiliser comme base pour les requetes
   */
  const DN = null;

  /**
   * Constructeur de l'objet
   * 
   * @param string $server Serveur d'annuaire a utiliser en fonction de la configuration
   * @param string $itemName Nom de l'objet associé dans la configuration LDAP
   */
  public function __construct($server = null, $itemName = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct($server)");

    // Récupération de l'itemName
    $this->_itemName = $itemName;

    // Définition de la ressource
    $this->objectmelanie = new LocalityMelanie($server, null, static::MAPPING, $this->_itemName, static::DN);
    // Gestion d'un second serveur d'annuaire dans le cas ou les informations sont répartis
    if (isset(\LibMelanie\Config\Ldap::$OTHER_LDAP)) {
      $this->otherldapobject = new LocalityMelanie(\LibMelanie\Config\Ldap::$OTHER_LDAP, null, static::MAPPING, $this->_itemName, static::DN);
    }
    $this->_server = $server ?: \LibMelanie\Config\Ldap::$SEARCH_LDAP;
  }
   
  /**
   * ***************************************************
   * METHOD MAPPING
   */
  /**
   * Enregistrement de l'objet
   * Nettoie le cache du user
   * 
   * @return null si erreur, boolean sinon (true insert, false update)
   */
  public function save() {
    $ret = $this->objectmelanie->save();
    $this->executeCache();
    return $ret;
  }

  /**
   * Suppression de l'objet
   * Nettoie le cache du user
   * 
   * @return boolean
   */
  public function delete() {
    $ret = $this->objectmelanie->delete();
    $this->executeCache();
    return $ret;
  }

  /**
   * Charge les données de la ressource depuis l'annuaire (en fonction de l'uid ou l'email)
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   * 
   * @return boolean true si l'objet existe dans l'annuaire false sinon
   */
  public function load($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->load() [" . $this->_server . "]");
    if (isset($attributes) && is_string($attributes)) {
      $attributes = [$attributes];
    }
    if (isset($this->_isLoaded) && !isset($attributes)) {
      return $this->_isLoaded;
    }
    $useIsLoaded = !isset($attributes);

    if (!isset($attributes)) {
      $attributes = static::LOAD_ATTRIBUTES;
    }

    $ret = $this->objectmelanie->load($attributes, static::LOAD_FILTER);
    if ($useIsLoaded) {
      $this->_isLoaded = $ret;
    }
    $this->executeCache();
    return $ret;
  }
  /**
   * Est-ce que la ressource existe dans l'annuaire (en fonction de l'uid ou l'email)
   * Effectue un load cette méthode a donc peu d'intéret dans cet objet
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   * 
   * @return boolean true si l'objet existe dans l'annuaire false sinon
   */
  public function exists($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->exists() [" . $this->_server . "]");
    if (!isset($this->_isExist)) {
      if (!isset($attributes)) {
        $attributes = static::LOAD_ATTRIBUTES;
      }
      $this->_isExist = $this->objectmelanie->exists($attributes, static::LOAD_FILTER);
    }
    return $this->_isExist;
  }

  /**
   * Récupération de la liste des locality
   * 
   * @return array Liste des localités
   */
  public function listAllLocalities() {
    // Récupération de l'instance LDAP en fonction du serveur
    $ldap = Ldap::GetInstance($this->_server);

    // Lister les localités directement sous le DN
    $sr = $ldap->ldap_list(static::DN, static::LIST_LOCALITIES_FILTER, $this->get_mapping_attributes(static::LOAD_ATTRIBUTES));
    $list = [];

    if ($sr && $ldap->count_entries($sr) > 0) {
      $entries = $ldap->get_entries($sr);

      foreach ($entries as $key => $entry) {
        if ($key === 'count') {
            continue;
        }

        // Initialisation de l'objet Locality
        $locality = new static();
        $locality->getObjectMelanie()->__set_data($entry);
        $list[] = $locality;
      }
    }

    // Retourne la liste des localité trouvées
    return $list;
  }

  /**
   * Récupération de la liste des resources appartenant a une localité
   * 
   * @param string $type Type de la resource
   * @param array $listUids Liste des uids des resources
   * @param array $listEmails Liste des emails des resources
   * 
   * @return array Liste des resources
   */
  public function listResources($type = null, $listUids = null, $listEmails = null) {
    // Récupération de l'instance LDAP en fonction du serveur
    $ldap = Ldap::GetInstance($this->_server);

    $list = true;

    // Configuration du dn
    if (isset($this->dn)) {
      $dn = $this->dn;
    } else if (isset($this->uid)) {
      $dn = "ou=$this->uid," . static::DN;
    } else {
      $dn = static::DN;
      $list = false;
    }

    // Ressource dans le bon namespace
    $Resource = $this->__getNamespace(true) . '\\Resource';

    if (isset($type)) {
      $filter = str_replace('%%type%%', $type, static::LIST_RESOURCES_BY_TYPE_FILTER);
    } else if (isset($listUids)) {
      $filter = "(|";
      foreach ($listUids as $uid) {
        $filter .= "(uid=$uid)";
      }
      $filter .= ")";
      $filter = str_replace('%%uids%%', $filter, static::LIST_RESOURCES_BY_UIDS_FILTER);
    } else if (isset($listEmails)) {
      $filter = "(|";
      foreach ($listEmails as $email) {
        $filter .= "(mail=$email)";
      }
      $filter .= ")";
      $filter = str_replace('%%emails%%', $filter, static::LIST_RESOURCES_BY_EMAILS_FILTER);
    } else {
      $filter = static::LIST_RESOURCES_FILTER;
    }

    // Lister les localités directement sous le DN
    if ($list) {
      $sr = $ldap->ldap_list($dn, $filter, (new $Resource())->get_mapping_attributes($Resource::LOAD_ATTRIBUTES));
    }
    else {
      $sr = $ldap->search($dn, $filter, (new $Resource())->get_mapping_attributes($Resource::LOAD_ATTRIBUTES));
    }
    
    $list = [];

    if ($sr && $ldap->count_entries($sr) > 0) {
      $entries = $ldap->get_entries($sr);

      foreach ($entries as $key => $entry) {
        if ($key === 'count') {
            continue;
        }

        // Initialisation de l'objet Locality
        $res = new $Resource();
        $res->getObjectMelanie()->__set_data($entry);
        $list[] = $res;
      }
    }

    // Retourne la liste des localité trouvées
    return $list;
  }
}
