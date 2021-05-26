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
use LibMelanie\Config\ConfigSQL;
use LibMelanie\Config\MappingMce;
use LibMelanie\Log\M2Log;
use LibMelanie\Lib\MagicObject;
use LibMelanie\Interfaces\IObjectMelanie;
use LibMelanie\Config\DefaultConfig;

/**
 * Gestion de groupe MCE
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage ORM
 */
class GroupMelanie extends UserMelanie {
  /**
   * Est-ce que l'objet a déjà été initialisé
   * 
   * @var boolean
   */
  private static $isInit = false;

  /**
   * Appel l'initialisation du mapping
   * 
   * @param array $mapping Données de mapping
   * @return boolean
   */
  protected static function Init($mapping = [], $server = null) {
    if (!self::$isInit) {
      if (isset($server) && isset(Config\Ldap::$SERVERS[$server]['mapping_group'])) {
        $mapping = array_merge($mapping, Config\Ldap::$SERVERS[$server]['mapping_group']);
      }
      else if (isset(Config\Ldap::$SERVERS[Config\Ldap::$SEARCH_LDAP]['mapping_group'])) {
        $mapping = array_merge($mapping, Config\Ldap::$SERVERS[Config\Ldap::$SEARCH_LDAP]['mapping_group']);
      }
      // Traitement du mapping
      foreach ($mapping as $key => $map) {
        if (is_array($map)) {
          if (!isset($map[MappingMce::type])) {
            $mapping[$key][MappingMce::type] = MappingMce::stringLdap;
          }
        }
        else {
          $mapping[$key] = [MappingMce::name => $map, MappingMce::type => MappingMce::stringLdap];
        }
      }
      self::$isInit = MappingMce::UpdateDataMapping('GroupMelanie', $mapping);
    }
    return self::$isInit;
  }
}