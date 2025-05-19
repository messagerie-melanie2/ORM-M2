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
namespace LibMelanie\Api\Mel;

use LibMelanie\Api\Defaut;
use LibMelanie\Config\MappingMce;

/**
 * Classe utilisateur pour Mel
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Mel
 * @api
 * 
 * @property string $dn                 DN de la ressource dans l'annuaire            
 * @property string $uid                Identifiant unique de la ressource
 * @property string $fullname           Nom complet de la ressource
 * @property string $name               Nom de la ressource
 * @property string $email              Adresse email principale de la ressource
 * @property array  $email_list         Liste de toutes les adresses email de la ressource
 * @property string $type               Type de ressource (voir Resource::TYPE_*)
 * @property string $service            Service de la ressource
 * @property string $bal                Type de boite aux lettres
 * @property string $street             Rue de la ressource
 * @property string $postalcode         Code postal de la ressource
 * @property string $locality           Ville de la ressource
 * @property string $description        Description de la ressource
 * @property string $roomnumber         Numéro de bureau de la ressource
 * @property string $title              Titre de la ressource
 * @property string $batiment           Batiment de la ressource
 * @property string $etage              Etage de la ressource
 * @property string $capacite           Capacité de la ressource
 * @property string $caracteristiques   Caractéristiques de la ressource
 * 
 * @method bool save() Enregistrement de la ressource dans l'annuaire
 * @method bool load() Chargement de la ressource dans l'annuaire
 * @method bool delete() Suppression de la ressource dans l'annuaire
 */
class Resource extends Defaut\Resource {

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
   * Attributs par défauts pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_ATTRIBUTES = ['fullname', 'uid', 'name', 'email', 'email_list', 'shares', 'type', 'bal', 'roomnumber', 'batiment', 'etage', 'capacite', 'caracteristiques', 'street', 'postalcode', 'locality', 'description', 'title'];

  /**
   * DN a utiliser comme base pour les requetes
   */
  const DN = 'ou=Ressources,ou=BNUM,ou=applications,ou=ressources,dc=equipement,dc=gouv,dc=fr';
  
  /**
   * Configuration du mapping qui surcharge la conf
   */
  const MAPPING = [
    "dn"                      => 'dn',                            // DN de la ressource
    "uid"                     => 'uid',                           // Identifiant de la ressource
    "fullname"                => 'cn',                            // Nom complet de la ressource
    "name"                    => 'givenname',                     // Nom de la ressource
    "type"                    => 'sn',                            // Type de la ressource
    "email"                   => 'mailpr',                        // Adresse e-mail principale de la ressource en reception
    "email_list"              => [MappingMce::name => 'mail', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en reception pour la ressource
    "service"                 => 'departmentnumber',              // Department Number
    "bal"                     => 'mineqtypeentree',               // Type d'entrée (boite individuelle, partagée, ressource, ...)
    "street"                  => 'street',                        // Rue
    "postalcode"              => 'postalcode',                    // Code postal
    "locality"                => 'l',                             // Ville
    "description"             => 'description',                   // Description
    "roomnumber"              => 'roomnumber',                    // Numéro de bureau
    "title"                   => 'title',                         // Titre
    "batiment"                => [MappingMce::name => 'info', MappingMce::prefixLdap => 'Ressource.Batiment: ', MappingMce::type => MappingMce::stringLdap],
    "etage"                   => [MappingMce::name => 'info', MappingMce::prefixLdap => 'Ressource.Etage: ', MappingMce::type => MappingMce::stringLdap],
    "capacite"                => [MappingMce::name => 'info', MappingMce::prefixLdap => 'Ressource.Capacite: ', MappingMce::type => MappingMce::stringLdap],
    "caracteristiques"        => [MappingMce::name => 'info', MappingMce::prefixLdap => 'Ressource.Caracteristiques: ', MappingMce::type => MappingMce::stringLdap],
    "batiment"                => [MappingMce::name => 'info', MappingMce::prefixLdap => 'Ressource.Batiment: ', MappingMce::type => MappingMce::stringLdap],

    // Zoom Room
    "is_zoom_room"            => [MappingMce::name => 'info', MappingMce::prefixLdap => 'Ressource.Type: ', MappingMce::type => MappingMce::booleanLdap, MappingMce::trueLdapValue => 'Zoom Room'],
    "zoom_account_id"         => [MappingMce::name => 'info', MappingMce::prefixLdap => 'Ressource.ZoomRoom.AccountID: ', MappingMce::type => MappingMce::stringLdap],
    "zoom_client_id"          => [MappingMce::name => 'info', MappingMce::prefixLdap => 'Ressource.ZoomRoom.ClientID: ', MappingMce::type => MappingMce::stringLdap],
    "zoom_internal_email"     => [MappingMce::name => 'info', MappingMce::prefixLdap => 'Ressource.ZoomRoom.InternalEmail: ', MappingMce::type => MappingMce::stringLdap],

    "modifiedtime"            => 'mineqmodifiedtimestamp',
  ];
}
