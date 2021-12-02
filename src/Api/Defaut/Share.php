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
use LibMelanie\Objects\ObjectMelanie;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\DefaultConfig;

/**
 * Classe pour la gestion des droits
 * Permet d'ajouter de nouveaux partages sur la lib MCE
 * implémente les API de la librairie pour aller chercher les données dans la base de données
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $object_id Identifiant de l'objet utilisé pour le partage
 * @property string $name Utilisateur ou groupe auquel est associé le partage
 * @property Share::TYPE_* $type Type de partage
 * @property Share::ACL_* $acl Niveau d'acl, utilisé sous forme ACL_WRITE | ACL_FREEBUSY
 * @method bool load() Chargement du partage, en fonction de l'object_id et du nom
 * @method bool exists() Test si le partage existe, en fonction de l'object_id et du nom
 * @method bool save() Sauvegarde la priopriété dans la base de données
 * @method bool delete() Supprime le partage, en fonction de l'object_id et du nom
 */
class Share extends MceObject {
  /**
   * **
   * CONSTANTES
   */
  // TYPE
  /**
   * Partage pour un groupe
   */
  const TYPE_GROUP = 'perm_groups';
  /**
   * Partage pour un utilisateur
   */
  const TYPE_USER = 'perm_users';
  // ACL
  /**
   * ACL Ecriture
   */
  const ACL_WRITE = 16;
  /**
   * ACL Suppression
   */
  const ACL_DELETE = 8;
  /**
   * ACL Lecture
   */
  const ACL_READ = 4;
  /**
   * ACL Accès au dispos
   */
  const ACL_FREEBUSY = 2;
  /**
   * ACL Visibilité sur les évènement privés
   */
  const ACL_PRIVATE = 1;
  
  /**
   * Constructeur de l'objet
   * 
   * @param misc $object          
   */
  function __construct($object = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    // M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->__construct()");
    // Définition du partage
    $this->objectmelanie = new ObjectMelanie('Share');
    
    // Définition des objets associés
    if (isset($object) && isset($object->object_id)) {
      $this->objectmelanie->object_id = $object->object_id;
    }
  }
  
  /**
   * Défini l'objet lié
   * 
   * @param misc $object          
   * @ignore
   *
   */
  function setObject($object) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setObject()");
    if (isset($object->object_id)) {
      $this->objectmelanie->object_id = $object->object_id;
    }
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
   *          Liste les propriétés par operateur (MappingMce::like, MappingMce::supp, MappingMce::inf, MappingMce::diff)
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
   * @return Share[] Array
   */
  function getList($fields = [], $filter = "", $operators = [], $orderby = "", $asc = true, $limit = null, $offset = null, $case_unsensitive_fields = []) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getList()");
    $_shares = $this->objectmelanie->getList($fields, $filter, $operators, $orderby, $asc, $limit, $offset, $case_unsensitive_fields);
    if (!isset($_shares))
      return null;
    $shares = [];
    foreach ($_shares as $_share) {
      $_share->setIsExist();
      $_share->setIsLoaded();
      $share = new static();
      $share->setObjectMelanie($_share);
      $shares[] = $share;
    }
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    return $shares;
  }
  
  /**
   * Gestion des droits
   * 
   * @param string $action          
   * @return boolean
   */
  function asRight($acl) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->asRight($acl)");
    return (DefaultConfig::$PERMS[$acl] & intval($this->objectmelanie->acl)) === DefaultConfig::$PERMS[$acl];
  }

/**
 * ***************************************************
 * DATA MAPPING
 */
}