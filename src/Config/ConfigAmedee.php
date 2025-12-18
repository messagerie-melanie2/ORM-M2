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

namespace LibMelanie\Config;

/**
 * Configuration par défaut à surcharger dans 
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Config
 */
class ConfigAmedee
{
  const DEFAULT_QUOTA=1000;
  const DEFAULT_QUOTA_POLE_MIN=5000;

  /**
   * Récupère le chemin LDAP par défaut
   * 
   * @var string
   */
  const DEFAULT_LDAP_DN = 'ou=organisation,dc=equipement,dc=gouv,dc=fr';

 
   /**
   * Récupère le chemin LDAP des ressources
   * 
   * @var string
   */
  const DEFAULT_RESSOURCES_LDAP_DN = 'ou=nomenclatures,ou=ressources,dc=equipement,dc=gouv,dc=fr';

  /**
   * Récupère la valeur maximale par défaut du nombre de résultats d'une recherche
   * 
   * @var string
   */
  const DEFAULT_ADVANCED_SEARCH_MAX_RESULTS = 300;

  /**
   * Mapping de la portée pour le ldap
   * 
   * @var string
   */
  const MINEQ_PORTEE_MAPPING = ['00' => 'Cachée (00)', '20' => 'Service (20)', '40' => 'Ministère (40)', '50' => 'Administration française (50)'];
}
