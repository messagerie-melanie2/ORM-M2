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

namespace LibMelanie\Api\Defaut;

use LibMelanie\Config\Config;
use LibMelanie\Objects\FolderMelanie;
use LibMelanie\Log\M2Log;

/**
 * Classe ou LDAP par defaut
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $dn DN du groupe l'annuaire 
 * @property string $fullname Nom complet du groupe LDAP
 * @property string $type Type de groupe (voir Mce\Users\Type::*)
 * @property string $email Adresse email principale de l'utilisateur
 * @property array $email_list Liste de toutes les adresses email de l'utilisateur
 * @property User[] $members Liste des membres appartenant au groupe
 * @property array $owners Liste des propriétaires du groupe LDAP
 * 
 * @method bool save() Enregistrement du groupe dans l'annuaire
 */
abstract class Folder extends User
{

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
   * Liste des membres du groupe
   * 
   * @var User[]
   * @ignore
   */
  protected $_members;

  /**
   * Liste des propriétés à sérialiser pour le cache
   */
  protected $serializedProperties = [
    '_server',
    '_isLoaded',
    '_isExist',
  ];

  /**
   * Attributs par défauts pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_ATTRIBUTES = ['dn', 'fullname', 'email', 'description'];

  const LOAD_CHILDREN_ATTRIBUTES = ['uid', 'dn', 'fullname', 'name', 'gender', 'email', 'description', 'phonenumber', 'roomnumber', 'mobilephone', 'order', 'objectclass'];

  /**
   * Configuration du mapping qui surcharge la conf
   */
  const MAPPING = [];

  const FOLDER_TYPE_MAPPING = [];

  const CHILDREN_TYPE_MAPPING = [];

  const CHILDREN_ORDER_TYPE_MAPPING = [];

  /**
   * Constructeur de l'objet
   * 
   * @param string $server Serveur d'annuaire a utiliser en fonction de la configuration
   * @param string $itemName Nom de l'objet associé dans la configuration LDAP
   */
  public function __construct($server = null, $itemName = null)
  {
    // Défini la classe courante
    $this->get_class = get_class($this);

    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->__construct()");

    // Récupération de l'itemName
    $this->_itemName = $itemName;

    // Définition de l'utilisateur
    $this->objectmelanie = new FolderMelanie($server, null, static::MAPPING, $this->_itemName);

    $this->_server = $server;
  }

  /**
   * Récupère les objets dans le Ldap correspondant à la recherche
   * 
   * @param string $query Input de la recherche
   * @param string $attributes Attributs à retourner
   * 
   * @return array Liste d'objet
   */
  protected function search($query, $attributes)
  {
    $data = $this->objectmelanie->search($query, $attributes);

    return $this->sortByAlphaAndQuery($this->formatDataType($data), $query);
  }
  
  /**
   * Récupère les enfants du dossier
   * 
   * @param string $type Type d'enfant à récupérer (user,folder,group)
   * @param string $attributes Attributs à retourner
   * 
   * @return array Liste d'objet
   */
  protected function getFolderChildren($type, $attributes)
  {
    $data = $this->objectmelanie->getFolderChildren(static::CHILDREN_TYPE_MAPPING[$type] ?? null, $attributes);

    return $this->sortFolderChildren($this->formatDataType($data));
  }

  /**
   * Filtre les enfants du dossier
   * 
   * @param string $type Type d'enfant à récupérer (user,folder,group)
   * @param string $attributes Attributs à retourner
   * 
   * @return array Liste d'objet
   */
  protected function filterDataType($type, $attributes)
  {
    $data = $this->objectmelanie->getFolderChildren(static::CHILDREN_TYPE_MAPPING[$type] ?? null, $attributes);

    return $this->sortFolderChildren($this->formatDataType($data));
  }

  /**
   * Format les données en fonction du type pour le retourner dans le bon objet
   * 
   * @param array $data Données à formater
   * @param string $otherUserType Exception a retourner
   * 
   * @return array Liste d'objet
   */
  protected function formatDataType($data)
  {
    $newData = [];

    foreach ($data as $value) {
      if (is_array($value)) {
        $class = $this->__getNamespace() . '\\User';
        $user = new $class();
        $user->getObjectMelanie()->__set_data($value);
        $newData[] = $user;
      }
    }

    return $newData;
  }

  /**
   * Trie les enfants du dossier en fonction des critères du ldap
   * 
   * @param array $newData Données à trier
   * 
   * @return array Liste d'objets
   */
  protected function sortFolderChildren($newData)
  {
    usort($newData, function ($a, $b) {
      return strcmp(static::CHILDREN_ORDER_TYPE_MAPPING[$a->object_type] . $a->order . $a->fullname, static::CHILDREN_ORDER_TYPE_MAPPING[$b->object_type] .  $b->order . $b->fullname);
    });

    return $newData;
  }

  /**
   * Trie les données par ordre alphabétique + Met les noms recherchés en premier
   * 
   * @param array $newData Données à trier
   * @param $query Recherche
   * 
   * @return array Liste d'objets triés
   */
  protected function sortByAlphaAndQuery($newData, $query) {
    usort($newData, function ($a, $b) use ($query) {
      $ordera = 0;
      $orderb = 0;
        if ($a->lastname === strtoupper($query)) {
          $ordera = -1;
        }
        if ($b->lastname === strtoupper($query)) {
          $orderb = -1;
        }
        return strcmp($ordera . $a->lastname . $a->firstname,  $orderb . $b->lastname . $b->firstname);
    });

    return $newData;
}


  /**
   * Retourne l'email d'un folder en allant récupérer les informations de la boite liée
   * 
   * @return string email du folder
   */
  protected function getMapEmail()
  {
    $class = $this->__getNamespace() . '\\User';
    $user = new $class();
    $data = $this->objectmelanie->getFolderUser(static::FOLDER_TYPE_MAPPING, $class::LOAD_ATTRIBUTES);
    $user->getObjectMelanie()->__set_data($data);
    return $user->email;
  }

  /**
   * Retourne l'email d'un folder en allant récupérer les informations de la boite liée
   * 
   * @return string email du folder
   */
  protected function getMapCan_Export()
  {
    if (isset($this->objectmelanie->type) && array_key_exists($this->objectmelanie->type, static::FOLDER_TYPE_MAPPING)) {
      return true;
    } else {
      return false;
    }
  }

  protected function getDefaultLdapDn() {
    return Config::get(Config::DEFAULT_LDAP_DN);
  }
}
