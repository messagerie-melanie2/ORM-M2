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

use LibMelanie\Lib\MceObject;
use LibMelanie\Objects\ExportMelanie;
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
abstract class Export extends MceObject
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

  /**
   * Configuration du mapping qui surcharge la conf
   */
  const MAPPING = [];
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
    $this->objectmelanie = new ExportMelanie($server, null, static::MAPPING, $this->_itemName);

    $this->_server = $server;
  }

  /**
   * Récupère les user d'un folder 
   * 
   * @param array $attributes attributs à récupérer
   * 
   * @return array Liste d'objet
   */
  protected function getExportChildren($attributes)
  {
    $user = $this->__getNamespace() . '\\User';

    $newData = [];

    $data = $this->objectmelanie->getExportChildren($attributes);
    foreach ($data as $key => $value) {
      if(is_array($value)){
        $tempUser = new $user();
        $tempUser->getObjectMelanie()->__set_data($value);
        $newData[$key] = $tempUser;
      }
    }
    return $newData;
  }
}
