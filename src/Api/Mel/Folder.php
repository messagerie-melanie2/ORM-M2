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
class Folder extends Defaut\Folder
{
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
     * Attributs par défauts pour la méthode load()
     * 
     * @ignore
     */
    const LOAD_ATTRIBUTES = ['dn', 'fullname', 'email', 'description'];


    /**
     * Configuration du mapping pour récupérer bon type de boite
     */
    const FOLDER_TYPE_MAPPING = ['NUNI' => 'BALU', 'NSER' => 'BALS'];

    /**
     * Configuration du mapping pour determiner le type d'enfant
     */
    const CHILDREN_TYPE_MAPPING = ['folder' => 'organizationalUnit', 'user' => 'mineqMelBoite', 'group' => 'mineqMelListe'];

    /**
     * Configuration du mapping pour l'ordre de l'annuaire
     */
    const CHILDREN_ORDER_TYPE_MAPPING = ['folder' => '0', 'user' => '1', 'fonction' => '2', 'group' => '3', 'other' => '4'];

    /**
     * Configuration du mapping qui surcharge la conf
     */
    const MAPPING =
    [
        'ou'                      => [MappingMce::name => 'ou', MappingMce::regexp => "/^[a-zA-Z0-9ÀÂÈÉÊËÎÏÑÔÙÛÇàâèéêëîñïôùûç '\.\-]{1,25}$/"], // Nom de l'Entité
        'smtp'                    => [MappingMce::name => 'mineqdomainessmtp', MappingMce::type => MappingMce::arrayLdap], 
        "dn"                      => 'dn',                                                                                         // DN de l'utilisateur
        "uid"                     => [MappingMce::name => 'uid', MappingMce::regexp => "/^[[a-z0-9.\-]{1,34}$/"],                  // Identifiant de l'utilisateur
        "fullname"                => 'cn',                                                                                         // Nom complet de l'utilisateur
        "name"                    => 'displayname',                                                                                // Display name de l'utilisateur
        "lastname"                => 'sn',                                                                                         // Last name de l'utilisateur
        "firstname"               => 'givenname',                                                                                  // First name de l'utilisateur
        "email"                   => 'mailpr',                                                                                     // Adresse e-mail principale de l'utilisateur en reception
        "email_list"              => [MappingMce::name => 'mail', MappingMce::type => MappingMce::arrayLdap],                      // Liste d'adresses e-mail en reception pour l'utilisateur
        "email_send"              => 'mineqmelmailemissionpr',                                                                     // Adresse e-mail principale de l'utilisateur en emission
        "email_send_list"         => [MappingMce::name => 'mineqmelmailemission', MappingMce::type => MappingMce::arrayLdap],      // Liste d'adresses e-mail en émission pour l'utilisateur
        "service"                 => 'departmentnumber',                                                                           // Department Number
        "password_need_change"    => 'mineqpassworddoitchanger',                                                                   // Message pour indiquer que le mot de passe de l'utilisateur doit changer
        "shares"                  => [MappingMce::name => 'mineqmelpartages', MappingMce::type => MappingMce::arrayLdap],          // Liste des partages pour cette boite
        "server_routage"          => [MappingMce::name => 'mineqmelroutage', MappingMce::type => MappingMce::arrayLdap],           // Champ utilisé pour le routage des messages
        "server_host"             => [MappingMce::name => 'mineqmelroutage', MappingMce::type => MappingMce::arrayLdap],           // Champ utilisé pour le routage des messages
        "type"                    => 'mineqtypeentree',                                                                            // Type d'entrée (boite individuelle, partagée, ressource, ...)
        "internet_access_admin"   => [MappingMce::name => 'mineqmelaccesinterneta', MappingMce::defaut => false, MappingMce::type => MappingMce::booleanLdap], // Droit d'accès depuis internet donné par l'administrateur
        "internet_access_user"    => [MappingMce::name => 'mineqmelaccesinternetu', MappingMce::defaut => false, MappingMce::type => MappingMce::booleanLdap], // Droit d'accès depuis internet accepté par l'utilisateur
        "internet_access_enable"  => ['mineqmelaccesinterneta', 'mineqmelaccesinternetu'],
        "use_photo_ader"          => 'mineqpublicationphotoader',                                                                  // Flag publier la photo de l'utilisateur sur Ader
        "use_photo_intranet"      => 'mineqpublicationphotointranet',                                                              // Flag publier la photo de l'utilisateur sur l'intranet
        "employee_number"         => [MappingMce::name => 'employeenumber', MappingMce::defaut => 'non renseigné'],                // Matricule de l'utilisateur
        "ministere"               => 'mineqzone',                                                                                  // Zone de l'utilisateur
        "street"                  => [MappingMce::name => 'street', MappingMce::regexp => "/^[a-zA-ZÀÂÈÉÊËÎÏÑÔÙÛÇàâèéêëîïñôùûç\s]*(?:$\n?^[a-zA-Zàâèéêëîïñôùûç\s]*)*$/um"], // Rue
        "postalcode"              => [MappingMce::name => 'postalcode', MappingMce::regexp => "/^[0-9]*$/"],                       // Code postal
        "locality"                => [MappingMce::name => 'l', MappingMce::regexp => "/^[a-zA-ZÀÂÈÉÊËÎÏÑÔÙÛÇàâèéêëîïñôùûç0-9 _-]*$/u"],// Ville
        "description"             => 'description',                                                                                // Description
        "phonenumber"             => [MappingMce::name => 'telephonenumber', MappingMce::regexp => "/^[^a-zA-ZÀÂÈÉÊËÎÏÑÔÙÛÇàâèéêëîïñôùûç]*$/"], // Numéro de téléphone
        "phoneformat"             => [MappingMce::name => 'telephonenumber', MappingMce::regexp => "/^[^a-zA-ZÀÂÈÉÊËÎÏÑÔÙÛÇàâèéêëîïñôùûç]*$/"], // Numéro de téléphone
        "faxnumber"               => [MappingMce::name => 'facsimiletelephonenumber', MappingMce::regexp => "/^[^a-zA-ZÀÂÈÉÊËÎÏÑÔÙÛÇàâèéêëîïñôùûç]$/"], // Numéro de fax
        "mobilephone"             => 'mobile',                                                                                     // Numéro de mobile
        "mobileformat"            => 'mobile',                                                                                     // Numéro de mobile
        "roomnumber"              => 'roomnumber',                                                                                 // Numéro de bureau
        "title"                   => 'title',                                                                                      // Titre
        "business_category"       => [MappingMce::name => 'businesscategory', MappingMce::type => MappingMce::arrayLdap],          // Cetégorie
        "vpn_profile_name"        => 'mineqvpnprofil',                                                                             // Nom du profil VPN associé
        "has_bureautique"         => 'sambasid',                                                                                   // SID Samba
        "update_personnal_info"   => [MappingMce::name => 'mineqmajinfoperso', MappingMce::defaut => true, MappingMce::trueLdapValue => '1', MappingMce::falseLdapValue => '0', MappingMce::type => MappingMce::booleanLdap],             // Droit pour l'utilisateur de modifier ses infos perso dans l'annuaire
        "remise"                  => 'mineqmelremise',                                                                             // Remise
        "mission"                 => [MappingMce::name => 'mineqmission', MappingMce::type => MappingMce::arrayLdap],              // Mission
        "photo_src"               => 'jpegphoto',                                                                                  // Photo de l'utilisateur
        "gender"                  => 'gender',                                                                                     // Genre
        "liens_import"            => 'mineqliensimport',                                                                           // Lien d'import autres annuaires
        "is_agriculture"          => 'mineqliensimport',                                                                           // Calcul si l'utilisateur appartient à l'agriculture
        "supported_shares"        => 'mineqliensimport',                                                                           // Liste des droits supportés en fonction du lien import
        "observation"             => [MappingMce::name => 'info', MappingMce::prefixLdap => 'OBSERVATION:', MappingMce::type => MappingMce::stringLdap],
        "acces_internet_profil"   => [MappingMce::name => 'info', MappingMce::prefixLdap => 'AccesInternet.Profil: ', MappingMce::type => MappingMce::stringLdap],
        "acces_internet_ts"       => [MappingMce::name => 'info', MappingMce::prefixLdap => 'AccesInternet.AcceptationCGUts: ', MappingMce::type => MappingMce::stringLdap],
        "mdrive"                  => [MappingMce::name => 'info', MappingMce::prefixLdap => 'MDRIVE: ', MappingMce::type => MappingMce::booleanLdap, MappingMce::trueLdapValue => 'oui', MappingMce::falseLdapValue => 'non'],
        "mdrive_quota"            => 'mineqmelmdrivequota',                                                                        // Quota du MDrive
        "outofoffices"            => [MappingMce::name => 'mineqmelreponse', MappingMce::type => MappingMce::arrayLdap],           // Affichage du message d'absence de l'utilisateur
        "acces_synchro_admin_profil"    => 'mineqmelaccessynchroa',                                                                // Profil de synchro administrateur
        "acces_synchro_admin_datetime"  => 'mineqmelaccessynchroa',                                                                // Date de synchro administrateur
        "acces_synchro_user_profil"     => 'mineqmelaccessynchrou',                                                                // Profil de synchro administrateur
        "acces_synchro_user_datetime"   => 'mineqmelaccessynchrou',                                                                // Profil de synchro administrateur
        "is_synchronisation_enable"     => 'mineqmelaccessynchrou',                                                                // Est-ce que la synchronisation de l'utilisateur est activée ?
        "synchronisation_profile"       => 'mineqmelaccessynchrou',                                                                // Retourne le profil de synchronisation
        "cerbere"                   => [MappingMce::name => 'info', MappingMce::prefixLdap => 'AUTH.Cerbere:', MappingMce::type => MappingMce::stringLdap],
        "double_authentification"   => [MappingMce::name => 'mineqinfosec', MappingMce::prefixLdap => 'DoubleAuth.Obligatoire: ', MappingMce::type => MappingMce::booleanLdap, MappingMce::trueLdapValue => 'oui', MappingMce::falseLdapValue => 'non'],
        "is_mailbox"                => [MappingMce::name => 'objectclass', MappingMce::trueLdapValue => 'mineqMelBoite', MappingMce::type => MappingMce::booleanLdap],// Est-ce qu'il s'agit bien d'une boite Mél
        "modifiedtime"              => 'mineqmodifiedtimestamp',
        "double_authentification_forcee"        => [MappingMce::name => 'mineqinfosec', MappingMce::prefixLdap => '2FA.Forcee: ', MappingMce::type => MappingMce::booleanLdap, MappingMce::trueLdapValue => '1', MappingMce::falseLdapValue => '0'],
        "double_authentification_date_butoir"   => [MappingMce::name => 'mineqinfosec', MappingMce::prefixLdap => '2FA.DateButoir: ', MappingMce::type => MappingMce::dateLdap],
        "double_authentification_date_grace"    => [MappingMce::name => 'mineqinfosec', MappingMce::prefixLdap => '2FA.DateDeGrace: ', MappingMce::type => MappingMce::dateLdap],
        "is_external"               => [MappingMce::name => 'dn', MappingMce::type => MappingMce::booleanLdap],
        "uuid"                      => "uniqueidentifier",
        "password"                  => "userpassword",
        "order"                   => [MappingMce::name => "mineqordreaffichage", MappingMce::regexp => "/^[a-zA-Z0-9]{1,4}$/"],    // Ordre d'affichage des personnes
        "alias"                     => [MappingMce::name => 'seeAlso', MappingMce::type => MappingMce::arrayLdap],
        "portee"                    => [MappingMce::name => 'mineqportee', MappingMce::regexp => "/^[0-9]*$/"],
        "work"                      => "observation",
        "manager"                   => "manager",
        "wifi"                      => "mineqAccesWifiA"
    ];
}
