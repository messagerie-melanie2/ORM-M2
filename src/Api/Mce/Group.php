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
namespace LibMelanie\Api\Mce;

use LibMelanie\Api\Defaut;
use LibMelanie\Config\MappingMce;

/**
 * Classe groupe LDAP pour Mel
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/MCE
 * @api
 * 
 * @property string $dn DN du groupe l'annuaire 
 * @property string $fullname Nom complet du groupe LDAP
 * @property string $type Type de groupe (voir Mce\Users\Type::*)
 * @property string $email Adresse email principale de l'utilisateur
 * @property array $email_list Liste de toutes les adresses email de l'utilisateur
 * @property User[] $members Liste des membres appartenant au groupe
 * @property array $members_email Liste des adresses e-mail de la liste
 * @property array $owners Liste des propriétaires du groupe LDAP
 * @property string $service Service du groupe dans l'annuaire
 * @property-read boolean $is_dynamic Est-ce qu'il s'agit d'une liste dynamique ?
 */
class Group extends Defaut\Group {
    /**
     * Attributs par défauts pour la méthode load()
     * 
     * @ignore
     */
    const LOAD_ATTRIBUTES = [];

    /**
     * Configuration du mapping qui surcharge la conf
     */
    const MAPPING = [
        "dn"                      => 'dn',                              // DN de la liste
        "rdn"                     => 'mcerdn',                          // RDN de la liste
        "gid"                     => 'gidnumber',                       // Group ID
        "fullname"                => 'cn',                              // Nom complet de la liste
        "name"                    => 'displayname',                     // Nom de la liste
        "lastname"                => 'sn',                              // Last name de la liste
        "email"                   => 'mail',                          // Adresse e-mail principale de la liste en reception
        "email_list"              => [MappingMce::name => 'mailalternateaddress', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en reception pour l'utilisateur
        "service"                 => 'departmentnumber',                // Department Number
        "type"                    => 'mcetypeentree',                   // Type d'entrée (boite individuelle, partagée, ressource, ...)
        "members"                 => [MappingMce::name => 'memberuid', MappingMce::type => MappingMce::arrayLdap], // Liste des membres du groupes
        "members_email"           => [MappingMce::name => 'mcemelmembres', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses mails de la liste
        "owners"                  => [MappingMce::name => 'owner', MappingMce::type => MappingMce::arrayLdap], // Liste des propriétaires du groupes
        "unique_identifier"       => 'uniqueidentifier',              // Identifier unique pour le groupe
        "is_dynamic"              => [MappingMce::name => 'objectclass', MappingMce::type => MappingMce::arrayLdap], // Est-ce qu'il s'agit d'une liste dynamique ?
    ];

    /**
     * Mapping is_dynamic field
     * 
     * @return boolean Est-ce qu'il s'agit d'une liste dynamique ?
     */
    protected function getMapIs_dynamic() {
        return is_array($this->objectmelanie->objectclass) 
                && in_array('labeledURIObject', $this->objectmelanie->objectclass);
    }
    /**
     * Mapping is_dynamic field
     * 
     * @param boolean $is_dynamic Est-ce qu'il s'agit d'une liste dynamique ?
     * 
     * @return boolean false non supporté
     */
    protected function setMapIs_dynamic($is_dynamic) {
        return false;
    }
}