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
use LibMelanie\Log\M2Log;
use LibMelanie\Config\MappingMce;

/**
 * Classe groupe LDAP pour Mel
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Mel
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
	 * Configuration du délimiteur pour le server host
	 * 
	 * @var string
	 */
    const SERVER_HOST_DELIMITER = '%';

    /**
     * Filtre pour la méthode load()
     * 
     * @ignore
     */
    const LOAD_FILTER = null;

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
    const LOAD_ATTRIBUTES = ['dn', 'fullname', 'email', 'members'];

    /**
     * Configuration du mapping qui surcharge la conf
     */
    const MAPPING = [
        "dn"                      => 'dn',                            // DN de la liste
        "rdn"                     => 'mineqrdn',                      // RDN de la liste
        "fullname"                => 'cn',                            // Nom complet de la liste
        "name"                    => 'displayname',                   // Nom de la liste
        "lastname"                => 'sn',                            // Last name de la liste
        "email"                   => 'mailpr',                        // Adresse e-mail principale de la liste en reception
        "email_list"              => [MappingMce::name => 'mail', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en reception pour l'utilisateur
        "service"                 => 'departmentnumber',              // Department Number
        "server_routage"          => [MappingMce::name => 'mineqmelroutage', MappingMce::type => MappingMce::arrayLdap], // Champ utilisé pour le routage des messages
        "type"                    => 'mineqtypeentree',               // Type d'entrée (boite individuelle, partagée, ressource, ...)
        "members"                 => [MappingMce::name => 'memberuid', MappingMce::type => MappingMce::arrayLdap], // Liste des membres du groupes
        "members_email"           => [MappingMce::name => 'mineqmelmembres', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses mails de la liste
        "owners"                  => [MappingMce::name => 'owner', MappingMce::type => MappingMce::arrayLdap], // Liste des propriétaires du groupes
        "is_dynamic"              => [MappingMce::name => 'objectclass', MappingMce::type => MappingMce::arrayLdap], // Est-ce qu'il s'agit d'une liste dynamique ?
        "restrictions"            => [MappingMce::name => 'mineqmelrestrictions', MappingMce::type => MappingMce::arrayLdap], // Restrictions de remise pour la liste
        "remise"                  => 'mineqmelremise',                // Type de remise pour la liste
        "liens_import"            => 'mineqliensimport',              // Lien d'import autres annuaires
        "unique_identifier"       => 'uniqueidentifier',              // Identifier unique pour le groupe
        "mdrive"                  => [MappingMce::name => 'info', MappingMce::prefixLdap => 'MDRIVE: ', MappingMce::type => MappingMce::booleanLdap, MappingMce::trueLdapValue => 'oui', MappingMce::falseLdapValue => 'non', MappingMce::emptyLdapValue => 'non'],
        "gestion"                 => [MappingMce::name => 'info', MappingMce::prefixLdap => 'GESTION: ', MappingMce::type => MappingMce::stringLdap],
        "reponse"                 => [MappingMce::name => 'info', MappingMce::prefixLdap => 'REPONSE:', MappingMce::type => MappingMce::stringLdap],
    ];

    /**
     * Récupération du champ server_host
     * 
     * @return mixed|NULL Valeur du serveur host, null si non trouvé
     */
    protected function getMapServer_host() {
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapServer_host()");
        foreach ($this->server_routage as $route) {
            if (strpos($route, self::SERVER_HOST_DELIMITER) !== false) {
                $route = explode('@', $route, 2);
                return $route[1];
            }
        }
        return null;
    }

    /**
     * Récupération du champ server_user
     * 
     * @return mixed|NULL Valeur du serveur user, null si non trouvé
     */
    protected function getMapServer_user() {
        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapServer_user()");
        foreach ($this->server_routage as $route) {
            if (strpos($route, self::SERVER_HOST_DELIMITER) !== false) {
                $route = explode('@', $route, 2);
                return $route[0];
            }
        }
        return null;
    }

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