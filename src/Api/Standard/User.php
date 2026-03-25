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
namespace LibMelanie\Api\Standard;

use LibMelanie\Log\M2Log;
use LibMelanie\Api\Standard\Users\Outofoffice;
use LibMelanie\Api\Standard\Users\Share;
use LibMelanie\Api\Defaut;
use LibMelanie\Config\MappingMce;
use LibMelanie\Objects\UserMelanie;

/**
 * Classe utilisateur pour un ldap standard
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Standard
 * @api
 * 
 * @property string $dn DN de l'utilisateur dans l'annuaire            
 * @property string $uid Identifiant unique de l'utilisateur
 * @property string $fullname Nom complet de l'utilisateur
 * @property string $name Nom de l'utilisateur
 * @property string $type Type de boite (voir Standard\Users\Type::*)
 * @property string $email Adresse email principale de l'utilisateur
 * @property array $email_list Liste de toutes les adresses email de l'utilisateur
 * @property string $email_send Adresse email d'émission principale de l'utilisateur
 * @property array $email_send_list Liste de toutes les adresses email d'émission de l'utilisateur
 * @property Share[] $shares Liste des partages de la boite
 * @property-read array $supported_shares Liste des droits supportés par cette boite
 * @property-read boolean $internet_access_enable Est-ce que l'accès Internet de l'utilisateur est activé
 * @property string $street Adresse - Rue de l'utilisateur
 * @property string $postalcode Adresse - Code postal de l'utilisateur
 * @property string $locality Adresse - Ville de l'utilisateur
 * @property string $title Titre de l'utilisateur
 * @property array $server_host Champ de routage pour le serveur de message de l'utilisateur
 * @property-read string $server_host Host du serveur de messagerie de l'utilisateur
 * @property-read string $server_user User du serveur de messagerie de l'utilisateur
 * @property Outofoffice[] $outofoffices Tableau de gestionnaire d'absence pour l'utilisateur
 * 
 * @property-read boolean $is_objectshare Est-ce que cet utilisateur est en fait un objet de partage
 * @property-read ObjectShare $objectshare Retourne l'objet de partage lié à cet utilisateur si s'en est un
 * 
 * @property-read boolean $is_synchronisation_enable Est-ce que la synchronisation est activée pour l'utilisateur ?
 * @property-read string $synchronisation_profile Profil de synchronisation positionné pour l'utilisateur (STANDARD ou SENSIBLE)
 * 
 * @method bool authentification($password, $master = false) Authentification de l'utilisateur sur l'annuaire MCE
 * @method bool save() Enregistrement de l'utilisateur dans l'annuaire
 */
class User extends Defaut\User {

  // **** Configuration des filtres et des attributs par défaut
  /**
   * Filtre pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_FILTER = "(uid=%%uid%%)";
  /**
   * Filtre pour la méthode load() avec un email
   * 
   * @ignore
   */
  const LOAD_FROM_EMAIL_FILTER = "(mail=%%email%%)";
  /**
   * Filtre pour la méthode load() si c'est un objet de partage
   * 
   * @ignore
   */
  const LOAD_OBJECTSHARE_FILTER = self::LOAD_FILTER;
  /**
   * Filtre pour la méthode load() avec un email si c'est un object de partage
   * 
   * @ignore
   */
  const LOAD_OBJECTSHARE_FROM_EMAIL_FILTER = self::LOAD_FROM_EMAIL_FILTER;
  /**
   * Filtre pour la méthode getBalp()
   * 
   * @ignore
   */
  const GET_BALP_FILTER = "(mcedelegation=%%uid%%:*)";
  /**
   * Filtre pour la méthode getBalpEmission()
   * 
   * @ignore
   */
  const GET_BALP_EMISSION_FILTER = self::GET_BALP_FILTER;
  /**
   * Filtre pour la méthode getBalpGestionnaire()
   * 
   * @ignore
   */
  const GET_BALP_GESTIONNAIRE_FILTER = self::GET_BALP_FILTER;
  /**
   * Filtre pour la méthode getGroups()
   * 
   * @ignore
   */
  const GET_GROUPS_FILTER = null;
  /**
   * Filtre pour la méthode getGroupsIsMember()
   * 
   * @ignore
   */
  const GET_GROUPS_IS_MEMBER_FILTER = null;
  /**
   * Filtre pour la méthode getListsIsMember()
   * 
   * @ignore
   */
  const GET_LISTS_IS_MEMBER_FILTER = null;

  /**
   * Liste des attributs à récupérer depuis l'autre annuaire
   * 
   * @ignore
   */
  const OTHER_LDAP_ATTRIBUTES = null;

  /**
   * Configuration du mapping qui surcharge la conf
   */
  const MAPPING = [
    "dn"                      => 'dn',                            // DN de l'utilisateur
    "uid"                     => 'uid',                           // Identifiant de l'utilisateur
    "fullname"                => 'cn',                            // Nom complet de l'utilisateur
    "name"                    => 'cn',                            // Nom court de l'utilisateur
    "email"                   => 'mail',                          // Adresse e-mail principale de l'utilisateur en reception
    "email_list"              => [MappingMce::name => 'mail', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en reception pour l'utilisateur
    "email_send"              => 'mail',                          // Adresse e-mail principale de l'utilisateur en emission
    "email_send_list"         => [MappingMce::name => 'mail', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en émission pour l'utilisateur
    "street"                  => 'street',                        // Rue
    "postalcode"              => 'postalcode',                    // Code postal
    "locality"                => 'l',                             // Ville
    "title"                   => 'title',                         // Titre
  ];

  /**
   * Charge les données de l'utilisateur depuis l'annuaire (en fonction de l'uid ou l'email)
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   * 
   * @return boolean true si l'objet existe dans l'annuaire false sinon
   */
  public function load($attributes = null)
  {
    return false;
  } 

  /**
   * Récupère la liste des objets de partage accessibles à l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return ObjectShare[] Liste d'objets
   */
  public function getObjectsShared($attributes = null) {
    return [];
  }

  /**
   * Récupère la liste des objets de partage accessibles au moins en émission à l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return ObjectShare[] Liste d'objets
   */
  public function getObjectsSharedEmission($attributes = null) {
    return [];
  }

  /**
   * Récupère la liste des objets de partage accessibles en gestionnaire à l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return ObjectShare[] Liste d'objets
   */
  public function getObjectsSharedGestionnaire($attributes = null) {
    return [];
  }

  /**
   * ***************************************************
   * DATA MAPPING
   */
  
  /**
   * Récupération du champ internet_access_enable
   * 
   * @return boolean true si l'access internet de l'utilisateur est activé, false sinon
   */
  protected function getMapInternet_access_enable() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapInternet_access_enable()");
    // Toujours true pour la MCE
    return true;
  }

  /**
   * Récupération du champ server_host
   * 
   * @return mixed|NULL Valeur du serveur host, null si non trouvé
   */
  protected function getMapServer_host() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapServer_host()");
    return '';
  }

  /**
   * Récupération du champ server_routage
   * 
   * @return mixed|NULL Valeur du serveur routage, null si non trouvé
   */
  protected function getMapServer_routage() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapServer_host()");
    return '';
  }

  /**
   * Récupération du champ server_user
   * 
   * @return mixed|NULL Valeur du serveur user, null si non trouvé
   */
  protected function getMapServer_user() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapServer_user()");
    return null;
  }

  /**
   * Récupération du champ out of offices
   * 
   * @return Outofoffice[] Tableau de d'objets Outofoffice
   */
  protected function getMapOutofoffices() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapOutofoffices()");
    return [];
  }

  /**
   * Mapping shares field
   * 
   * @return Share[] Liste des partages de l'objet
   */
  protected function getMapShares() {
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->getMapShares()");
    return [];
  }

  /**
   * Mapping shares field
   * 
   * @return array Liste des partages supportés par cette boite ([Share::TYPE_*])
   */
  protected function getMapSupported_shares() {
    return [Share::TYPE_ADMIN];
  }

  /**
   * Mapping shares field
   * 
   * @return boolean false non supporté
   */
  protected function setMapSupported_shares() {
    return false;
  }

  /**
   * Mapping type field
   */
  protected function getMapType() {
    return Users\Type::INDIVIDUELLE;
  }
}
