<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * ORM M2 Copyright © 2017 PNE Annuaire et Messagerie/MEDDE
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
namespace LibMelanie\Ldap;

use LibMelanie;
use LibMelanie\Log\M2Log;
use LibMelanie\Exceptions;

/**
 * Gestion de la connexion LDAP
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage LDAP
 */
class Ldap {
  /**
   * Instances LDAP
   * 
   * @var Ldap
   */
  private static $instances = [];
  /**
   * Connexion vers le serveur LDAP
   * 
   * @var resource
   */
  private $connection = null;
  /**
   * Configuration de connexion
   * 
   * @var array
   */
  private $config = [];
  /**
   * Utilisateur connecté
   * 
   * @var string
   */
  private $username = null;
  /**
   * Stockage des données retournées en cache
   * 
   * @var array
   */
  private $cache = [];
  /**
   * Permet de savoir si on est en connexion anonyme
   * 
   * @var bool
   */
  private $isAnonymous = false;
  /**
   * Permet de savoir si on est en connexion authentifiée
   * 
   * @var bool
   */
  private $isAuthenticate = false;
  /**
   * Derniere requete utilisee, sert pour les logs shutdown
   *
   * @var string
   */
  private static $last_request;
  
  /**
   * ************ SINGLETON **
   */
  /**
   * Récupèration de l'instance lié au serveur
   * 
   * @param string $server
   *          Nom du serveur, l'instance sera liée à ce nom qui correspond à la configuration du serveur
   * @return Ldap
   */
  public static function GetInstance($server) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap::GetInstance($server)");
    if (!isset(self::$instances[$server])) {
      if (!isset(LibMelanie\Config\Ldap::$SERVERS[$server])) {
        M2Log::Log(M2Log::LEVEL_ERROR, "Ldap->GetInstance() Erreur la configuration du serveur '$server' n'existe pas");
        return false;
      }
      self::$instances[$server] = new self(LibMelanie\Config\Ldap::$SERVERS[$server]);
    }
    return self::$instances[$server];
  }
  
  /**
   * * Constructeurs *
   */
  /**
   * Constructeur par défaut
   * 
   * @param string $config
   */
  public function __construct($config) {
    // Assigner la configuration
    $this->config = $config;
    // Lancer la connexion au LDAP
    if (is_null($this->connection))
      $this->connect();
  }
  
  /**
   * Destructeur par défaut : appel à disconnect
   */
  function __destruct() {
    $this->disconnect();
  }
  
  /**
   * **************** Authentification ***
   */
  /**
   * Authentification sur le serveur LDAP
   * 
   * @param string $dn
   * @param string $password
   * @return boolean
   */
  public function authenticate($dn, $password) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap->authentification($dn)");
    if (is_null($this->connection))
      $this->connect();
    
    // Authentification sur le seveur LDAP
    if (isset($this->config['tls']) && $this->config['tls'])
      ldap_start_tls($this->connection);
    $this->isAuthenticate = @ldap_bind($this->connection, $dn, $password);
    $this->isAnonymous = false;
    return $this->isAuthenticate;
  }
  
  /**
   * Se connecte en faisant un bind anonyme sur la connexion LDAP
   * 
   * @param boolean $force
   *
   * @return boolean
   */
  public function anonymous($force = false) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap->anonymous()");
    if (is_null($this->connection))
      $this->connect();
    if (!$force && $this->isAuthenticate)
      return $this->isAuthenticate;
    if ($this->isAnonymous)
      return $this->isAnonymous;
    
    // Authentification sur le seveur LDAP
    if (isset($this->config['tls']) && $this->config['tls'])
      ldap_start_tls($this->connection);
    $this->isAnonymous = @ldap_bind($this->connection);
    $this->isAuthenticate = false;
    return $this->isAnonymous;
  }
  
  /**
   * ************* Statics methods **
   */
  /**
   * Authentification sur le serveur LDAP associé
   * 
   * @param string $username
   * @param string $password
   * @param string $server
   *          [Optionnel] Server LDAP utilisé pour la requête
   * @param boolean $useUserInfos
   *          [Optionnel] Utiliser la méthode GetUserInfos pour chercher le DN (intéressant si les données sont en cache)
   * @return boolean
   */
  public static function Authentification($username, $password, $server = null, $useUserInfos = false) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap::Authentification($username, $useUserInfos)");
    if (!isset($server)) {
      $server = LibMelanie\Config\Ldap::$AUTH_LDAP;
    }
    // Récupération de l'instance LDAP en fonction du serveur
    $ldap = self::GetInstance($server);
    // Récupération des données en cache
    $infos = $ldap->getCache("Authentification:$server:$username");
    if (isset($infos)) {
      $dn = $infos['dn'];
    } else {
      // Connexion anonymous pour lire les données
      if ($ldap->anonymous()) {
        // Génération du filtre
        $filter = $ldap->getConfig("authentification_filter");
        if (isset($filter)) {
          $filter = str_replace('%%username%%', $username, $filter);
        } else {
          $filter = "(uid=$username)";
        }
        if ($useUserInfos) {
          $infos = self::GetUserInfos($username);
          if (isset($infos) && $infos['dn']) {
            $dn = $infos['dn'];
          } else {
            return false;
          }
        } else {
          // Lancement de la recherche
          $sr = $ldap->search($ldap->getConfig("base_dn"), $filter, [
              'dn'
          ], 0, 1);
          if ($sr && $ldap->count_entries($sr) == 1) {
            $infos = $ldap->get_entries($sr);
            $dn = $infos[0]['dn'];
          } else {
            return false;
          }
        }
      }
      else {
        throw new Exceptions\Melanie2LdapException('Connexion anonyme impossible au serveur LDAP. Erreur : ' . $ldap->getError());
      }
    }
    // Authentification
    return $ldap->authenticate($dn, $password);
  }
  /**
   * Retourne les données sur l'utilisateur lues depuis le Ldap
   * Ne retourne qu'une seule entrée
   * 
   * @param string $username
   *          Identifiant de l'utilisateur recherché
   * @param string $filter
   *          [Optionnel] Filtre ldap à utiliser pour la recherche
   * @param array $ldap_attr
   *          [Optionnel] Liste des attributs ldap à retourner
   * @param string $server
   *          [Optionnel] Server LDAP utilisé pour la requête
   * @return array
   */
  public static function GetUserInfos($username, $filter = null, $ldap_attr = null, $server = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap::GetUserInfos($username)");
    if (!isset($server)) {
      $server = LibMelanie\Config\Ldap::$SEARCH_LDAP;
    }
    // Récupération de l'instance LDAP en fonction du serveur
    $ldap = self::GetInstance($server);
    // Filtre ldap
    if (!isset($filter)) {
      // Génération du filtre
      $filter = $ldap->getConfig("get_user_infos_filter");
      if (isset($filter)) {
        $filter = str_replace('%%username%%', $username, $filter);
      } else {
        $filter = "(uid=$username)";
      }
    }
    // Liste des attributes
    if (!isset($ldap_attr)) {
      $ldap_attr = $ldap->getConfig("get_user_infos_attributes");
    }
    // Récupération des données en cache
    $keycache = "GetUserInfos:$server:" . md5($filter) . ":" . md5(serialize($ldap_attr)) . ":$username";
    $infos = $ldap->getCache($keycache);
    if (!isset($infos)) {
      // Connexion anonymous pour lire les données
      if ($ldap->anonymous()) {
        // Lancement de la recherche
        $sr = $ldap->search($ldap->getConfig("base_dn"), $filter, $ldap_attr, 0, 1);
        if ($sr && $ldap->count_entries($sr) == 1) {
          $infos = $ldap->get_entries($sr);
          $infos = $infos[0];
          $ldap->setCache($keycache, $infos);
        } else {
          $ldap->deleteCache($keycache);
        }
      }
      else {
        throw new Exceptions\Melanie2LdapException('Connexion anonyme impossible au serveur LDAP. Erreur : ' . $ldap->getError());
      }
    }
    // Retourne les données, null si vide
    return $infos;
  }
  
  /**
   * Retourne les boites partagées accessible pour un utilisateur depuis le LDAP
   * 
   * @param string $username
   *          Identifiant de l'utilisateur recherché
   * @param string $filter
   *          [Optionnel] Filtre ldap à utiliser pour la recherche
   * @param array $ldap_attr
   *          [Optionnel] Liste des attributs ldap à retourner
   * @param string $server
   *          [Optionnel] Server LDAP utilisé pour la requête
   * @return array
   */
  public static function GetUserBalPartagees($username, $filter = null, $ldap_attr = null, $server = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap::GetUserBalPartagees($username)");
    if (!isset($server)) {
      $server = LibMelanie\Config\Ldap::$SEARCH_LDAP;
    }
    // Récupération de l'instance LDAP en fonction du serveur
    $ldap = self::GetInstance($server);
    // Filtre ldap
    if (!isset($filter)) {
      // Génération du filtre
      $filter = $ldap->getConfig("get_user_bal_partagees_filter");
      if (isset($filter)) {
        $filter = str_replace('%%username%%', $username, $filter);
      } else {
        $filter = "(uid=$username.-.*)";
      }
    }
    // Liste des attributes
    if (!isset($ldap_attr)) {
      $ldap_attr = $ldap->getConfig("get_user_bal_partagees_attributes");
    }
    // Récupération des données en cache
    $keycache = "GetUserBalPartagees:$server:" . md5($filter) . ":" . md5(serialize($ldap_attr)) . ":$username";
    $infos = $ldap->getCache($keycache);
    if (!isset($infos)) {
      // Connexion anonymous pour lire les données
      if ($ldap->anonymous()) {
        // Lancement de la recherche
        $sr = $ldap->search($ldap->getConfig("shared_base_dn"), $filter, $ldap_attr);
        if ($sr && $ldap->count_entries($sr) > 0) {
          $infos = $ldap->get_entries($sr);
          $ldap->setCache($keycache, $infos);
        } else {
          $ldap->deleteCache($keycache);
        }
      }
      else {
        throw new Exceptions\Melanie2LdapException('Connexion anonyme impossible au serveur LDAP. Erreur : ' . $ldap->getError());
      }
    }
    // Retourne les données, null si vide
    return $infos;
  }
  /**
   * Retourne les boites partagées accessible en Emission ou Gestionnaire pour un utilisateur depuis le LDAP
   * 
   * @param string $username
   *          Identifiant de l'utilisateur recherché
   * @param string $filter
   *          [Optionnel] Filtre ldap à utiliser pour la recherche
   * @param array $ldap_attr
   *          [Optionnel] Liste des attributs ldap à retourner
   * @param string $server
   *          [Optionnel] Server LDAP utilisé pour la requête
   * @return array
   */
  public static function GetUserBalEmission($username, $filter = null, $ldap_attr = null, $server = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap::GetUserBalEmission($username)");
    if (!isset($server)) {
      $server = LibMelanie\Config\Ldap::$SEARCH_LDAP;
    }
    // Récupération de l'instance LDAP en fonction du serveur
    $ldap = self::GetInstance($server);
    // Filtre ldap
    if (!isset($filter)) {
      // Génération du filtre
      $filter = $ldap->getConfig("get_user_bal_emission_filter");
      if (isset($filter)) {
        $filter = str_replace('%%username%%', $username, $filter);
      } else {
        $filter = "(|(mineqmelpartages=$username:C)(mineqmelpartages=$username:G))";
      }
    }
    // Liste des attributes
    if (!isset($ldap_attr)) {
      $ldap_attr = $ldap->getConfig("get_user_bal_emission_attributes");
    }
    // Récupération des données en cache
    $keycache = "GetUserBalEmission:$server:" . md5($filter) . ":" . md5(serialize($ldap_attr)) . ":$username";
    $infos = $ldap->getCache($keycache);
    if (!isset($infos)) {
      // Connexion anonymous pour lire les données
      if ($ldap->anonymous()) {
        // Lancement de la recherche
        $sr = $ldap->search($ldap->getConfig("shared_base_dn"), $filter, $ldap_attr);
        if ($sr && $ldap->count_entries($sr) > 0) {
          $infos = $ldap->get_entries($sr);
          $ldap->setCache($keycache, $infos);
        } else {
          $ldap->deleteCache($keycache);
        }
      }
      else {
        throw new Exceptions\Melanie2LdapException('Connexion anonyme impossible au serveur LDAP. Erreur : ' . $ldap->getError());
      }
    }
    // Retourne les données, null si vide
    return $infos;
  }
  /**
   * Retourne les boites partagées dont l'utilisateur est gestionnaire
   * 
   * @param string $username
   *          Identifiant de l'utilisateur recherché
   * @param string $filter
   *          [Optionnel] Filtre ldap à utiliser pour la recherche
   * @param array $ldap_attr
   *          [Optionnel] Liste des attributs ldap à retourner
   * @param string $server
   *          [Optionnel] Server LDAP utilisé pour la requête
   * @return array
   */
  public static function GetUserBalGestionnaire($username, $filter = null, $ldap_attr = null, $server = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap::GetUserBalGestionnaire($username)");
    if (!isset($server)) {
      $server = LibMelanie\Config\Ldap::$SEARCH_LDAP;
    }
    // Récupération de l'instance LDAP en fonction du serveur
    $ldap = self::GetInstance($server);
    // Filtre ldap
    if (!isset($filter)) {
      // Génération du filtre
      $filter = $ldap->getConfig("get_user_bal_gestionnaire_filter");
      if (isset($filter)) {
        $filter = str_replace('%%username%%', $username, $filter);
      } else {
        $filter = "(mineqmelpartages=$username:G)";
      }
    }
    // Liste des attributes
    if (!isset($ldap_attr)) {
      $ldap_attr = $ldap->getConfig("get_user_bal_gestionnaire_attributes");
    }
    // Récupération des données en cache
    $keycache = "GetUserBalGestionnaire:$server:" . md5($filter) . ":" . md5(serialize($ldap_attr)) . ":$username";
    $infos = $ldap->getCache($keycache);
    if (!isset($infos)) {
      // Connexion anonymous pour lire les données
      if ($ldap->anonymous()) {
        // Lancement de la recherche
        $sr = $ldap->search($ldap->getConfig("shared_base_dn"), $filter, $ldap_attr);
        if ($sr && $ldap->count_entries($sr) > 0) {
          $infos = $ldap->get_entries($sr);
          $ldap->setCache($keycache, $infos);
        } else {
          $ldap->deleteCache($keycache);
        }
      }
      else {
        throw new Exceptions\Melanie2LdapException('Connexion anonyme impossible au serveur LDAP. Erreur : ' . $ldap->getError());
      }
    }
    // Retourne les données, null si vide
    return $infos;
  }
  
  /**
   * Retourne les informations sur un utilisateur depuis son adresse email depuis le LDAP
   * Ne retourne qu'une seule entrée
   * 
   * @param string $email
   *          Adresse email de l'utilisateur
   * @param string $filter
   *          [Optionnel] Filtre ldap à utiliser pour la recherche
   * @param array $ldap_attr
   *          [Optionnel] Liste des attributs ldap à retourner
   * @param string $server
   *          [Optionnel] Server LDAP utilisé pour la requête
   * @return mixed dn cn uid
   */
  public static function GetUserInfosFromEmail($email, $filter = null, $ldap_attr = null, $server = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap::GetUserInfosFromEmail($email)");
    if (!isset($server)) {
      $server = LibMelanie\Config\Ldap::$SEARCH_LDAP;
    }
    // Récupération de l'instance LDAP en fonction du serveur
    $ldap = self::GetInstance($server);
    // Filtre ldap
    if (!isset($filter)) {
      // Génération du filtre
      $filter = $ldap->getConfig("get_user_infos_from_email_filter");
      if (isset($filter)) {
        $filter = str_replace('%%email%%', $email, $filter);
      } else {
        $filter = "(mineqmelmailemission=$email)";
      }
    }
    // Liste des attributes
    if (!isset($ldap_attr)) {
      $ldap_attr = $ldap->getConfig("get_user_infos_from_email_attributes");
    }
    // Récupération des données en cache
    $keycache = "GetUserInfosFromEmail:" . md5($filter) . ":" . md5(serialize($ldap_attr)) . ":$server:$email";
    $infos = $ldap->getCache($keycache);
    if (!isset($infos)) {
      // Connexion anonymous pour lire les données
      if ($ldap->anonymous()) {
        // Lancement de la recherche
        $sr = $ldap->search($ldap->getConfig("base_dn"), $filter, $ldap_attr, 0, 1);
        if ($sr && $ldap->count_entries($sr) == 1) {
          $infos = $ldap->get_entries($sr);
          $infos = $infos[0];
          $ldap->setCache($keycache, $infos);
        } else {
          $ldap->deleteCache($keycache);
        }
      }
      else {
        throw new Exceptions\Melanie2LdapException('Connexion anonyme impossible au serveur LDAP. Erreur : ' . $ldap->getError());
      }
    }
    // Retourne les données, null si vide
    return $infos;
  }
  /**
   * Retourne le nom du champ mappé configuré pour le serveur LDAP (par défault SEARCH)
   * 
   * @param string $name
   * @param string $defaultValue
   *          [Optionnel] valeur par défaut si le mapping n'existe pas
   * @param string $server
   *          [Optionnel] Server LDAP utilisé pour la requête
   * @return NULL|string Nom du champ mappé
   */
  public static function GetMap($name, $defaultValue = null, $server = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap::GetMap($name, $defaultValue, $server)");
    if (!isset($server)) {
      $server = LibMelanie\Config\Ldap::$SEARCH_LDAP;
    }
    // Récupération de l'instance LDAP en fonction du serveur
    $ldap = self::GetInstance($server);
    
    return $ldap->getMapping($name, $defaultValue);
  }
  
  /**
   * Retourne la valeur (1ere si plusieurs) correspondant au nom en fonction des infos
   *
   * @param array $infos Données venant du LDAP
   * @param string $name
   * @param string $defaultValue
   *          [Optionnel] valeur par défaut si le mapping n'existe pas
   * @param string $server
   *          [Optionnel] Server LDAP utilisé pour la requête
   * @return NULL|string Nom du champ mappé
   */
  public static function GetMapValue($infos, $name, $defaultValue = null, $server = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap::GetMapValue($name, $defaultValue, $server)");
    if (!isset($server)) {
      $server = LibMelanie\Config\Ldap::$SEARCH_LDAP;
    }
    // Récupération de l'instance LDAP en fonction du serveur
    $ldap = self::GetInstance($server);
    
    $value = null;
    if (isset($infos[$ldap->getMapping($name, $defaultValue)])) {
      if (is_array($infos[$ldap->getMapping($name, $defaultValue)])
          && isset($infos[$ldap->getMapping($name, $defaultValue)][0])) {
        $value = $infos[$ldap->getMapping($name, $defaultValue)][0];
      }
      else {
        $value = $infos[$ldap->getMapping($name, $defaultValue)];
      }
    }
    
    return $value;
  }
  /**
   * Retourne les valeurs (tableau) correspondant au nom en fonction des infos
   *
   * @param array $infos Données venant du LDAP
   * @param string $name
   * @param string $defaultValue
   *          [Optionnel] valeur par défaut si le mapping n'existe pas
   * @param string $server
   *          [Optionnel] Server LDAP utilisé pour la requête
   * @return NULL|string Nom du champ mappé
   */
  public static function GetMapValues($infos, $name, $defaultValue = null, $server = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap::GetMapValues($name, $defaultValue, $server)");
    if (!isset($server)) {
      $server = LibMelanie\Config\Ldap::$SEARCH_LDAP;
    }
    // Récupération de l'instance LDAP en fonction du serveur
    $ldap = self::GetInstance($server);
    
    $values = null;
    if (isset($infos[$ldap->getMapping($name, $defaultValue)])) {
      $values = $infos[$ldap->getMapping($name, $defaultValue)];
    }
    
    return $values;
  }
  /**
   * Retourne la derniere requete
   * @return string
   */
  public static function getLastRequest() {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap::getLastRequest()");
    return self::$last_request;
  }
  
  /**
   * ************** Cache store *****
   */
  /**
   * Mise en cache des données
   * 
   * @param string $key
   * @param \multitype $value
   */
  public function setCache($key, $value) {
    // Création du stockage en cache
    if (!is_array($this->cache))
      $this->cache = [];
    // Stockage en cache de la donnée
    $this->cache[$key] = $value;
  }
  /**
   * Récupération des données depuis le cache
   * 
   * @param string $key
   * @return \multitype:
   */
  public function getCache($key) {
    // test si les données existes
    if (!isset($this->cache[$key]))
      return null;
    // Retourne les données du cache
    return $this->cache[$key];
  }
  /**
   * Suppression de la donnée en cache
   * 
   * @param string $key
   */
  public function deleteCache($key) {
    // Delete les données du cache
    unset($this->cache[$key]);
  }
  
  /**
   * **************** Generic LDAP Methods ***
   */
  /**
   * Connection au serveur LDAP
   */
  public function connect() {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap->connect()");
    $this->connection = @ldap_connect($this->config['hostname'], isset($this->config['port']) ? $this->config['port'] : '389');
    if (defined('LDAP_OPT_REFERRALS')) ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);
    if (defined('LDAP_OPT_TIMELIMIT')) ldap_set_option($this->connection, LDAP_OPT_TIMELIMIT, 20);
    if (defined('LDAP_OPT_TIMEOUT')) ldap_set_option($this->connection, LDAP_OPT_TIMEOUT, 15);
    if (defined('LDAP_OPT_NETWORK_TIMEOUT')) ldap_set_option($this->connection, LDAP_OPT_NETWORK_TIMEOUT, 10);
    if (isset($this->config['version']))
      @ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, $this->config['version']);
    $this->isAnonymous = false;
  }
  /**
   * Deconnection du serveur LDAP
   * 
   * @return boolean
   */
  public function disconnect() {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap->disconnect()");
    $ret = @ldap_unbind($this->connection);
    $this->connection = null;
    $this->isAnonymous = false;
    return $ret;
  }
  /**
   * Recherche dans le LDAP
   * Effectue une recherche avec le filtre filter dans le dossier base_dn avec le paramétrage LDAP_SCOPE_SUBTREE.
   * C'est l'équivalent d'une recherche dans le dossier.
   * 
   * @param string $base_dn
   *          Base DN de recherche
   * @param string $filter
   *          Filtre de recherche
   * @param array $attributes
   *          Attributs à rechercher
   * @param int $attrsonly
   *          Doit être défini à 1 si seuls les types des attributs sont demandés. S'il est défini à 0, les types et les valeurs des attributs sont récupérés, ce qui correspond au comportement par défaut.
   * @param int $sizelimit
   *          Vous permet de limiter le nombre d'entrées à récupérer. Le fait de définir ce paramètre à 0 signifie qu'il n'y aura aucune limite.
   * @return resource a search result identifier or false on error.
   */
  public function search($base_dn, $filter, $attributes = null, $attrsonly = 0, $sizelimit = 0) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap->search($base_dn, $filter)");
    self::$last_request = "ldap_search($base_dn, $filter, attributes, $attrsonly, $sizelimit)";
    return @ldap_search($this->connection, $base_dn, $filter, $this->getMappingAttributes($attributes), $attrsonly, $sizelimit);
  }
  /**
   * Recherche dans le LDAP
   * Effectue une recherche avec le filtre filter dans le dossier base_dn avec la configuration LDAP_SCOPE_BASE.
   * C'est équivalent à lire une entrée dans un dossier.
   * 
   * @param string $base_dn
   *          Base DN de recherche
   * @param string $filter
   *          Filtre de recherche
   * @param array $attributes
   *          Attributs à rechercher
   * @param int $attrsonly
   *          Doit être défini à 1 si seuls les types des attributs sont demandés. S'il est défini à 0, les types et les valeurs des attributs sont récupérés, ce qui correspond au comportement par défaut.
   * @param int $sizelimit
   *          Vous permet de limiter le nombre d'entrées à récupérer. Le fait de définir ce paramètre à 0 signifie qu'il n'y aura aucune limite.
   * @return resource a search result identifier or false on error.
   */
  public function read($base_dn, $filter, $attributes = null, $attrsonly = 0, $sizelimit = 0) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap->read($base_dn, $filter)");
    self::$last_request = "ldap_read($base_dn, $filter, attributes, $attrsonly, $sizelimit)";
    return @ldap_read($this->connection, $base_dn, $filter, $this->getMappingAttributes($attributes), $attrsonly, $sizelimit);
  }
  /**
   * Recherche dans le LDAP
   * Effectue une recherche avec le filtre filter dans le dossier base_dn avec l'option LDAP_SCOPE_ONELEVEL.
   * LDAP_SCOPE_ONELEVEL signifie que la recherche ne peut retourner des entrées que dans le niveau qui est immédiatement sous le niveau base_dn
   * (c'est l'équivalent de la commande ls, pour obtenir la liste des fichiers et dossiers du dossier courant).
   * 
   * @param string $base_dn
   *          Base DN de recherche
   * @param string $filter
   *          Filtre de recherche
   * @param array $attributes
   *          Attributs à rechercher
   * @param int $attrsonly
   *          Doit être défini à 1 si seuls les types des attributs sont demandés. S'il est défini à 0, les types et les valeurs des attributs sont récupérés, ce qui correspond au comportement par défaut.
   * @param int $sizelimit
   *          Vous permet de limiter le nombre d'entrées à récupérer. Le fait de définir ce paramètre à 0 signifie qu'il n'y aura aucune limite.
   * @return resource a search result identifier or false on error.
   */
  public function ldap_list($base_dn, $filter, $attributes = null, $attrsonly = 0, $sizelimit = 0) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap->ldap_list($base_dn, $filter)");
    self::$last_request = "ldap_list($base_dn, $filter, attributes, $attrsonly, $sizelimit)";
    return @ldap_list($this->connection, $base_dn, $filter, $this->getMappingAttributes($attributes), $attrsonly, $sizelimit);
  }
  /**
   * Retourne les entrées trouvées via le Ldap search
   * 
   * @param resource $search
   *          Resource retournée par le search
   * @return array a complete result information in a multi-dimensional array on success and false on error.
   */
  public function get_entries($search) {
    return @ldap_get_entries($this->connection, $search);
  }
  /**
   * Retourne le nombre d'entrées trouvé via le Ldap search
   * 
   * @param resource $search
   *          Resource retournée par le search
   * @return int number of entries in the result or false on error.
   */
  public function count_entries($search) {
    return @ldap_count_entries($this->connection, $search);
  }
  /**
   * Retourne la premiere entrée trouvée
   * 
   * @param resource $search
   *          Resource retournée par le search
   * @return resource the result entry identifier for the first entry on success and false on error.
   */
  public function first_entry($search) {
    if (is_null($this->connection))
      $this->connect();
    return @ldap_first_entry($this->connection, $search);
  }
  /**
   * Retourne les entrées suivantes de la recherche
   * 
   * @param resource $search
   *          Resource retournée par le search
   * @return resource entry identifier for the next entry in the result whose entries are being read starting with ldap_first_entry. If there are no more entries in the result then it returns false.
   */
  public function next_entry($search) {
    if (is_null($this->connection))
      $this->connect();
    return @ldap_next_entry($this->connection, $search);
  }
  /**
   * Retourne le dn associé à une entrée de l'annuaire
   * 
   * @param resource $entry
   *          l'entrée dans laquelle on récupère les infos
   * @return string the DN of the result entry and false on error.
   */
  public function get_dn($entry) {
    if (is_null($this->connection))
      $this->connect();
    return @ldap_get_dn($this->connection, $entry);
  }
  /**
   * Ajoute l'attribut entry à l'entrée dn.
   * Elle effectue la modification au niveau attribut, par opposition au niveau objet.
   * Les additions au niveau objet sont réalisées par ldap_add().
   * 
   * @param string $dn
   *          Le nom DN de l'entrée LDAP.
   * @param array $entry
   *          Entrée à remplacer dans l'annuaire
   * @return bool Cette fonction retourne TRUE en cas de succès ou FALSE si une erreur survient.
   */
  public function mod_add($dn, $entry) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap->mod_add($dn)");
    self::$last_request = "ldap_mod_add($dn)";
    return @ldap_mod_add($this->connection, $dn, $entry);
  }
  /**
   * Remplace l'attribut entry de l'entrée dn.
   * Elle effectue le remplacement au niveau attribut, par opposition au niveau objet.
   * Les additions au niveau objet sont réalisées par ldap_modify().
   * 
   * @param string $dn
   *          Le nom DN de l'entrée LDAP.
   * @param array $entry
   *          Entrée à remplacer dans l'annuaire
   * @return bool Cette fonction retourne TRUE en cas de succès ou FALSE si une erreur survient.
   */
  public function mod_replace($dn, $entry) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap->mod_replace($dn)");
    self::$last_request = "ldap_mod_replace($dn)";
    return @ldap_mod_replace($this->connection, $dn, $entry);
  }
  /**
   * Efface l'attribut entry de l'entrée dn.
   * Elle effectue la modification au niveau attribut, par opposition au niveau objet.
   * Les additions au niveau objet sont réalisées par ldap_delete().
   * 
   * @param string $dn
   *          Le nom DN de l'entrée LDAP.
   * @param array $entry
   *          Entrée à remplacer dans l'annuaire
   * @return bool Cette fonction retourne TRUE en cas de succès ou FALSE si une erreur survient.
   */
  public function mod_del($dn, $entry) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap->mod_del($dn)");
    self::$last_request = "ldap_mod_del($dn)";
    return @ldap_mod_del($this->connection, $dn, $entry);
  }
  /**
   * Ajoute une entrée dans un dossier LDAP.
   * 
   * @param string $dn
   *          Le nom DN de l'entrée LDAP.
   * @param array $entry
   *          Entrée à remplacer dans l'annuaire
   * @return bool Cette fonction retourne TRUE en cas de succès ou FALSE si une erreur survient.
   */
  public function add($dn, $entry) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap->add($dn)");
    self::$last_request = "ldap_add($dn)";
    return @ldap_add($this->connection, $dn, $entry);
  }
  /**
   * Modifie l'entrée identifiée par dn, avec les valeurs fournies dans entry.
   * La structure de entry est la même que détaillée dans ldap_add().
   * 
   * @param string $dn
   *          Le nom DN de l'entrée LDAP.
   * @param array $entry
   *          Entrée à remplacer dans l'annuaire
   * @return bool Cette fonction retourne TRUE en cas de succès ou FALSE si une erreur survient.
   */
  public function modify($dn, $entry) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap->modify($dn)");
    self::$last_request = "ldap_modify($dn)";
    return @ldap_modify($this->connection, $dn, $entry);
  }
  /**
   * Efface une entrée spécifique d'un dossier LDAP.
   * 
   * @param string $dn
   *          Le nom DN de l'entrée LDAP.
   * @return bool Cette fonction retourne TRUE en cas de succès ou FALSE si une erreur survient.
   */
  public function delete($dn) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap->delete($dn)");
    self::$last_request = "ldap_delete($dn)";
    return @ldap_delete($this->connection, $dn);
  }
  /**
   * Renomme une entrée pour déplacer l'objet dans l'annuaire
   * 
   * @param string $dn
   *          Le nom DN de l'entrée LDAP.
   * @param string $newrdn
   *          The new RDN.
   * @param string $newparent
   *          The new parent/superior entry.
   * @param bool $deleteoldrdn
   *          If TRUE the old RDN value(s) is removed, else the old RDN value(s) is retained as non-distinguished values of the entry.
   * @return bool Cette fonction retourne TRUE en cas de succès ou FALSE si une erreur survient.
   */
  public function rename($dn, $newrdn, $newparent, $deleteoldrdn) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Ldap->rename($dn)");
    self::$last_request = "ldap_rename($dn, $newrdn)";
    return @ldap_rename($this->connection, $dn, $newrdn, $newparent, $deleteoldrdn);
  }
  /**
   * Retourne la précédente erreur pour la commande LDAP
   * 
   * @return string Errno: Errmsg
   */
  public function getError() {
    $errno = ldap_errno($this->connection);
    return "$errno: " . ldap_err2str($errno);
  }
  
  /**
   * **************** CONFIGURATION ***
   */
  /**
   * Retourne la configuration associée
   * 
   * @param string $name
   *          Nom de la propriété à retourner
   * @return string|array Retourne la valeur
   */
  public function getConfig($name) {
    if (!isset($this->config[$name]))
      return null;
    return $this->config[$name];
  }
  /**
   * Modifie ou ajoute la configuration associée
   * 
   * @param string $name
   *          Nom de la propriété à modifier
   * @param string|array $value
   *          Valeur de la proriété à définir
   */
  public function setConfig($name, $value) {
    $this->config[$name] = $value;
  }
  /**
   * Retourne si la configuration associée existe
   * 
   * @param string $name
   *          Nom de la propriété à retourner
   * @return bool True si la valeur existe, false sinon
   */
  public function issetConfig($name) {
    return isset($this->config[$name]);
  }
  /**
   * Retourne si un mapping du champ existe pour le serveur LDAP
   * 
   * @param string $name
   * @return boolean
   */
  public function issetMapping($name) {
    return isset($this->config['mapping'][$name]);
  }
  /**
   * Retourne le nom du champ mappé configuré pour le serveur LDAP
   * 
   * @param string $name
   * @param string $defaultValue
   * @return NULL|string Nom du champ mappé
   */
  public function getMapping($name, $defaultValue = null) {
    if (!isset($this->config['mapping']) || !isset($this->config['mapping'][$name])) {
      if (isset($defaultValue))
        return $defaultValue;
      else
        return $name;
    }
    return $this->config['mapping'][$name];
  }
  /**
   * Retourne les champs mappés
   * 
   * @param array $attributes
   * @return NULL|array
   */
  public function getMappingAttributes($attributes) {
    if (is_null($attributes))
      return null;
    $mapAttributes = array();
    foreach ($attributes as $attribute) {
      if (!isset($this->config['mapping']) || !isset($this->config['mapping'][$attribute]))
        $mapAttributes[] = $attribute;
      $mapAttributes[] = $this->config['mapping'][$attribute];
    }
    return $mapAttributes;
  }
}
?>