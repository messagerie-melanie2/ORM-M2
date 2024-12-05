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

use LibMelanie\Ldap\Ldap;

/**
 * Gestion des ou MCE
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage ORM
 */
class FolderMelanie extends UserMelanie
{
  /**
   * Est-ce que l'objet a déjà été initialisé
   * 
   * @var boolean
   */
  protected static $isInit = false;

  /**
   * Nom de la configuration du mapping dans les preferences serveur
   */
  const SERVER_MAPPING_PREF = 'mapping_folder';

  /**
   * Type d'objet pour la gestion du mapping
   */
  const OBJECT_TYPE = 'FolderMelanie';

  public function getFolderUser($type_mapping, $attributes)
  {
    if (isset($this->type) && array_key_exists($this->type, $type_mapping)) {
      $data = Ldap::getFolderBalInfos($this->dn, $type_mapping[$this->type], $this->get_mapping_attributes($attributes));
    }
    if (isset($data[0])) {
      return $data[0];
    } else {
      return null;
    }
  }


  /**
   * Récupère les enfants du dossier
   * 
   * @param string $type Type d'enfant à récupérer (user,folder,group)
   * @param string $attributes Attributs à retourner
   * 
   * @return array Liste d'objet
   */
  public function getFolderChildren($type, $attributes)
  {
    $data = Ldap::getFolderChildrenInfos($this->dn, $type, $this->get_mapping_attributes($attributes));

    if (isset($data)) {
      return $data;
    } else {
      return null;
    }
  }

  /**
   * Récupère les objets dans le Ldap correspondant à la recherche
   * 
   * @param string $query Input de la recherche
   * @param string $attributes Attributs à retourner
   * 
   * @return array Liste d'objet
   */
  public function search($query, $attributes)
  {
    $data = Ldap::getFolderSearch($query, $this->get_mapping_attributes($attributes));

    if (isset($data)) {
      return $data;
    } else {
      return null;
    }
  }
}
