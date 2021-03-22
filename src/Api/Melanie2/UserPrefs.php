<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * ORM Mél Copyright © 2020 Groupe Messagerie/MTES
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
namespace LibMelanie\Api\Melanie2;

use LibMelanie\Lib\Melanie2Object;
use LibMelanie\Objects\ObjectMelanie;
use LibMelanie\Log\M2Log;

/**
 * Classe pour la gestion des propriétés des évènements
 * Permet d'ajouter de nouvelles options aux évènements
 * implémente les API de la librairie pour aller chercher les données dans la base de données
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage API Mélanie2
 *             @api
 * @property string $user Utilisateur lié à la preference
 * @property string $scope Scope lié à la preference
 * @property string $name Nom de la preference
 * @property string $value Valeur de la preference
 * @method bool load() Chargement la preference, en fonction de l'utilisateur, du scope et du nom
 * @method bool exists() Test si la preference existe, en fonction de l'utilisateur, du scope et du nom
 * @method bool save() Sauvegarde la preference dans la base de données
 * @method bool delete() Supprime la preference, en fonction de l'utilisateur, du scope et du nom
 */
class UserPrefs extends Melanie2Object {
  /**
   * Constructeur de l'objet
   * 
   * @param UserMelanie $usermelanie          
   */
  function __construct($usermelanie = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    // M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->__construct()");
    // Définition de la propriété de l'évènement
    $this->objectmelanie = new ObjectMelanie('UserPrefs');
    
    // Définition des objets associés
    if (isset($usermelanie)) {
      $this->objectmelanie->user = $usermelanie->uid;
    }
  }
  
  /**
   * Défini l'utilisateur Melanie
   *
   * @param UserMelanie $usermelanie
   * @ignore
   *
   */
  function setUserMelanie($usermelanie) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setUserMelanie()");
    $this->objectmelanie->user = $usermelanie->uid;
  }
  
  /**
   * ***************************************************
   * METHOD MAPPING
   */
  /**
   * Permet de récupérer la liste d'objet en utilisant les données passées
   * (la clause where s'adapte aux données)
   * Il faut donc peut être sauvegarder l'objet avant d'appeler cette méthode
   * pour réinitialiser les données modifiées (propriété haschanged)
   *
   * @param String[] $fields
   *          Liste les champs à récupérer depuis les données
   * @param String $filter
   *          Filtre pour la lecture des données en fonction des valeurs déjà passé, exemple de filtre : "((#description# OR #title#) AND #start#)"
   * @param String[] $operators
   *          Liste les propriétés par operateur (MappingMelanie::like, MappingMelanie::supp, MappingMelanie::inf, MappingMelanie::diff)
   * @param String $orderby
   *          Tri par le champ
   * @param bool $asc
   *          Tri ascendant ou non
   * @param int $limit
   *          Limite le nombre de résultat (utile pour la pagination)
   * @param int $offset
   *          Offset de début pour les résultats (utile pour la pagination)
   * @param String[] $case_unsensitive_fields
   *          Liste des champs pour lesquels on ne sera pas sensible à la casse
   * @return UserPrefs[] Array
   */
  function getList($fields = [], $filter = "", $operators = [], $orderby = "", $asc = true, $limit = null, $offset = null, $case_unsensitive_fields = []) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getList()");
    $_userprefs = $this->objectmelanie->getList($fields, $filter, $operators, $orderby, $asc, $limit, $offset, $case_unsensitive_fields);
    if (!isset($_userprefs))
      return null;
    $userprefs = [];
    foreach ($_userprefs as $_userpref) {
      $userpref = new UserPrefs();
      $userpref->setObjectMelanie($_userpref);
      $userprefs[] = $userpref;
    }
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    return $userprefs;
  }
}