<?php
/**
 * Ce fichier est développé pour la gestion des API de la librairie Mélanie2
 * Ces API permettent d'accéder à la librairie en REST
 *
 * ORM API Copyright © 2022  Groupe MCD/MTE
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

/**
 * Configuration par défaut des API
 * 
 * @var array
 */
$default = [
   /**
    * Niveau de logs
    * 
    * 0 -> pas de log
    * 1 -> logs erreur
    * 2 -> logs information
    * 3 -> logs debug
    * 4 -> logs trace (+ debug ORM)
    * 
    * @var integer
    */
   'log_level' => 2,
   
   /**
    * Dans quel fichier doivent être envoyé les logs
    * 
    * Plusieurs possibilités de configuration :
    *  - 'standard' pour la sortie standard
    *  - 'syslog' pour syslog
    *  - <path_to_file> chemin complet vers le fichier (ATTENTION: le fichier doit être créé et l'utilisateur web doit avoir les droits d'écriture)
    * 
    * @var string
    */
   'log_file' => '/var/log/orm-api/api.log',
   
   /**
    * Configuration du namespace pour la librairie ORM
    * 
    * Valeurs possibles :
    * - Mce, Mel, Mi, Dgfip, Gn
    * 
    * @var string
    */
   'namespace' => 'Mce',
   
   /**
    * URL de base utilisée pour les API
    * 
    * Doit correspondre exactement à ce qui se trouve derrière l'url racine
    * Exemple: pour une url "https://api.exemple.com/api/api.php" la base sera "/api/api.php"
    * 
    * @var string
    */
   'base_url' => '/api/api.php/',
   
   /**
    * Possibilité de se connecter aux API sans authentification ?
    * 
    * ATTENTION: Passer cette valeur à true peut constituer une faille de sécurité
    * A n'utiliser que pour les tests ou associé avec un filtre sur les adresses IP
    * 
    * @var boolean
    */
   'auth_type_none' => false,
   
   /**
    * Possibilité de se connecter aux API via une authentification Basic ?
    * 
    * L'authentification de l'utilisateur se fait via l'ORM
    * 
    * Authorization: Basic <base64("username:password")>
    * 
    * @var boolean
    */
   'auth_type_basic' => false,
   
   /**
    * Authentification possible via une clé d'API ?
    * 
    * La liste des clés d'API utilisables se trouve dans "api_keys"
    * 
    * Authorization: Apikey <clé d'API>
    * 
    * @var boolean
    */
   'auth_type_apikey' => false,
   
   /**
    * Authentification possible via un token Bearer ?
    * 
    * Possibilité d'utiliser un token JWT ou un token OpenID Connect/OAuth2
    * Actuellement non implémenté retourne toujours false
    * 
    * Authorization: Bearer <Token>
    * 
    * @var boolean
    */
   'auth_type_bearer' => false,
   
   /**
    * Liste des clés d'API utilisables
    * 
    * Tableau contenant une clé d'API par ligne
    * 
    * @var array
    */
   'api_keys' => [],
   
   /**
    * Limiter les connexions possibles à certaines adresse IP
    * 
    * @var boolean
    */
   'ip_address_filter' => false,
   
   /**
    * Liste des adresses IP autorisées
    * 
    * Tableau contenant une adresse IP par ligne
    * 
    * @var array
    */
   'valid_ip_addresses_list' => [],
   
   /**
    * Configuration d'un mapping personnalisé
    * 
    * Permet de surcharger le mapping du fichier "config/mapping.inc.php"
    * Voir ce fichier pour le format de la syntaxe
    * 
    * @var array
    */
   'mapping' => [],
   
   /**
    * Configuration d'un routing personnalisé
    * 
    * Permet de surcharger le routing du fichier "config/rouging.inc.php"
    * Voir ce fichier pour le format de la syntaxe
    * 
    * @var array
    */
   'routing' => [],
];