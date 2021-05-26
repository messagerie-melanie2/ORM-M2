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
namespace LibMelanie\Api\Defaut\Users;

/**
 * Classe utilisateur par defaut
 * pour la gestion du type de boite
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut/Users
 * @api
 */
class Type {
  // *** Type de boite
  /**
   * Boite individuelle
   */
  const INDIVIDUELLE = 'BALI';
  /**
   * Boite partagée
   */
  const PARTAGEE = 'BALP';
  /**
   * Boite fonctionnelle
   */
  const FONCTIONNELLE = 'BALF';
  /**
   * Boite de ressource
   */
  const RESSOURCE = 'BALR';
  /**
   * Boite d'unité
   */
  const UNITE = 'BALU';
  /**
   * Boite de service
   */
  const SERVICE = 'BALS';
  /**
   * Boite applicative
   */
  const APPLICATIVE = 'BALA';
  /**
   * Personne sans boite
   */
  const PERSONNE = 'PERS';
  /**
   * Liste serveur de DIStribution
   */
  const LIST = 'LDIS';
  /**
   * Liste serveur de Distribution à ABonnement
   */
  const LISTAB = 'LDAB';
}