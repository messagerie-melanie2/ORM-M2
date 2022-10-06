<?php

/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 *
 * ORM M2 Copyright © 2017  PNE Annuaire et Messagerie/MEDDE
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
namespace LibMelanie\Sql;

use LibMelanie\Cache\Cache;
use LibMelanie\Log\M2Log;
use LibMelanie\Lib\Selaforme;
use LibMelanie\Exceptions;
use LibMelanie\Config\Config;
use LibMelanie\Config\MappingMce;

/**
 * Gestion de la connexion Sql
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage SQL
 */
class Sql {
  /**
   * Instances LDAP
   * 
   * @var Sql
   */
  private static $instances = [];
  /**
   * Connexion PDO en cours
   *
   * @var PDO
   */
  private $connection;
  /**
   * Est-ce que la connexion courante est le serveur par défaut ?
   * 
   * @var boolean
   */
  private $is_default;
  /**
   * Type de base de données utilisée
   * 
   * @var string
   */
  private $db_type;
  /**
   * String de connexion
   *
   * @var string
   */
  private $cnxstring;
  /**
   * Utilisateur SQL
   *
   * @var string
   */
  private $username;
  /**
   * Mot de passe SQL
   *
   * @var string
   */
  private $password;
  /**
   * Connexion persistante
   *
   * @var bool
   */
  private $persistent;
  /**
   * Connexion PDO en cours pour la lecture
   *
   * @var PDO
   */
  private $connection_read;
  /**
   * String de connexion pour la lecture
   *
   * @var string
   */
  private $cnxstring_read;
  /**
   * Utilisateur SQL pour la lecture
   *
   * @var string
   */
  private $username_read;
  /**
   * Mot de passe SQL pour la lecture
   *
   * @var string
   */
  private $password_read;
  /**
   * Connexion persistante pour la lecture
   *
   * @var bool
   */
  private $persistent_read;
  /**
   * Mise en cache des statements par requete SQL
   * Voir MANTIS 3547: Réutiliser les prepare statements pour les requêtes identiques
   *
   * @var array
   */
  private $PreparedStatementCache;
  /**
   * Classe courante
   *
   * @var string
   */
  protected $get_class;
  /**
   * Resource vers les selaformes
   *
   * @var resource
   */
  private $ret_sel = false;
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
   *          Optionnel, si non renseigné utilise la valeur de ConfigSQL::$SGBD_SERVER
   * @return Sql
   */
  public static function GetInstance($server = null) {
    if (!isset($server)) {
      $server = \LibMelanie\Config\ConfigSQL::$SGBD_SERVER;
    }
    if (!isset(self::$instances[$server])) {
      if (!isset(\LibMelanie\Config\ConfigSQL::$SERVERS[$server])) {
        M2Log::Log(M2Log::LEVEL_ERROR, "Sql->GetInstance() Erreur la configuration du serveur '$server' n'existe pas");
        return false;
      }
      $conf = \LibMelanie\Config\ConfigSQL::$SERVERS[$server];
      // Lecture du read
      $conf_read = null;
      if (isset(\LibMelanie\Config\ConfigSQL::$SERVERS[$server."_read"])) {
        $conf_read = \LibMelanie\Config\ConfigSQL::$SERVERS[$server."_read"];
      }
      else if (isset($conf['read'])) {
        $conf_read = $conf['read'];
      }
      self::$instances[$server] = new self($server === \LibMelanie\Config\ConfigSQL::$SGBD_SERVER, $conf, $conf_read);
    }
    return self::$instances[$server];
  }

  /**
   * Forcer la déconnexion de toutes les instances de base
   * Méthode a utiliser dans z-push pour libérer les ressources
   * Parcours toutes les instances existantes et les déconnectes
   */
  public function ForceDisconnectAllInstances() {
    // Rechercher toutes les instances existantes
    foreach (\LibMelanie\Config\ConfigSQL::$SERVERS as $server => $conf) {
      // si l'instance existe, on la déconnecte
      if (isset(self::$instances[$server])) {
        self::$instances[$server]->disconnect();
        unset(self::$instances[$server]);
      }
    }
  }

  /**
   * Raccourcis pour la méthode GetInstance
   * 
   * Récupèration de l'instance lié au serveur
   * 
   * @param string $server
   *          Nom du serveur, l'instance sera liée à ce nom qui correspond à la configuration du serveur
   *          Optionnel, si non renseigné utilise la valeur de ConfigSQL::$SGBD_SERVER
   * @return Sql
   */
  public static function i($server = null) {
    return self::GetInstance();
  }

  /**
   * Constructor SQL
   *
   * @param boolean $is_default Est-ce que cette connexion est celle par defaut ?
   * @param array $db configuration vers la base de données
   * @param array $db_read configuration vers la base de données en lecture
   * @access public
   */
  public function __construct($is_default, $db, $db_read = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);

    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");
    
    // Connexion par defaut ?
    $this->is_default = $is_default;
    // Définition des données de connexion
    $this->db_type = isset($db['phptype']) ? $db['phptype'] : 'pgsql';
    $this->cnxstring = "$this->db_type:dbname=$db[database];host=$db[hostspec];port=$db[port]";
    $this->username = $db['username'];
    $this->password = $db['password'];
    $this->persistent = $db['persistent'];
    // Définition des données de connexion pour la lecture
    if (isset($db_read)) {
      $this->cnxstring_read = "$this->db_type:dbname=$db_read[database];host=$db_read[hostspec];port=$db_read[port]";
      $this->username_read = $db_read['username'];
      $this->password_read = $db_read['password'];
      $this->persistent_read = $db_read['persistent'];
    }
    // Mise en cache des statements
    // MANTIS 3547: Réutiliser les prepare statements pour les requêtes identiques
    $this->PreparedStatementCache = [];
    $this->getConnection();
  }

  /**
   * Getter for Database Type
   * 
   * @return string Type de base (pgsql, mysql, ...)
   */
  public function databaseType() {
    return $this->db_type;
  }

  /**
   * Getter for Is Default value
   * 
   * @return boolean
   */
  public function isDefault() {
    return $this->is_default;
  }

  /**
   * Destructor SQL
   *
   * @access public
   */
  public function __destruct() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__destruct()");
    $this->disconnect();
  }

  /**
   * Connect to sql database
   *
   * @throws Melanie2DatabaseException
   *
   * @access private
   */
  private function connect() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->connect()");
    // Utilisation des selaformes
    if (Config::get(Config::SEL_ENABLED)) {
      $this->ret_sel = Selaforme::selaforme_acquire(Config::get(Config::SEL_MAX_ACQUIRE), Config::get(Config::SEL_FILE_NAME));
      if ($this->ret_sel === false) {
        throw new Exceptions\Melanie2DatabaseException("Erreur de base de données Mélanie2 : Selaforme maximum atteint : " . Config::get(Config::SEL_MAX_ACQUIRE), 11);
      }
    }
    // Connexion persistante ?
    $options = [\PDO::ATTR_PERSISTENT => ($this->persistent == 'true'),\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];
    try {
      $this->connection = new \PDO($this->cnxstring, $this->username, $this->password, $options);
    }
    catch (\PDOException $e) {
      // Erreur de connexion
      M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->connect(): Erreur de connexion à la base de données\n" . $e->getMessage());
      throw new Exceptions\Melanie2DatabaseException("Erreur de base de données Mélanie2 : Erreur de connexion", 21);
    }
    // Connexion à la base de données en lecture
    if (isset($this->cnxstring_read)) {
      // Connexion persistante ?
      $options_read = [\PDO::ATTR_PERSISTENT => ($this->persistent_read == 'true'),\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];
      try {
        $this->connection_read = new \PDO($this->cnxstring_read, $this->username_read, $this->password_read, $options_read);
      }
      catch (\PDOException $e) {
        // Erreur de connexion
        M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->connect(): Erreur de connexion à la base de données en lecture\n" . $e->getMessage());
        $this->connection_read = null;
      }
    }
    return true;
  }

  /**
   * Disconnect from SQL database
   *
   * @access public
   */
  public function disconnect() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->disconnect()");
    // Fermer tous les statements
    $this->PreparedStatementCache = [];
    // Deconnexion de la bdd
    if (isset($this->connection)) {
      $this->connection = null;
      M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->disconnect() Close connection");
    }
    // Deconnexion de la bdd pour la lecture
    if (isset($this->connection_read)) {
      $this->connection_read = null;
      M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->disconnect() Close connection read");
    }
    // Utilisation des selaformes
    if (Config::get(Config::SEL_ENABLED) && $this->ret_sel !== false) {
      Selaforme::selaforme_release($this->ret_sel);
      $this->ret_sel = false;
    }
  }

  /**
   * Get the active connection to the sql database
   *
   * @access private
   */
  public function getConnection() {
    // Si la connexion n'existe pas, on se connecte
    if (!isset($this->connection)) {
      if (!$this->connect()) {
        $this->connection = null;
      }
    }
  }

  /**
   * Execute a sql query to the active database connection in PDO
   * If query start by SELECT
   * return an array of array of data
   *
   * @param string $query
   * @param array $params
   * @param string $class
   * @param string $objectType
   * @param boolean $cached_statement Utiliser le cache pour les statements
   * @return mixed array de resultat, true
   * @throws Melanie2DatabaseException
   *
   * @access public
   */
  public function executeQuery($query, $params = null, $class = null, $objectType = null, $cached_statement = true) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->executeQuery($query, $class)");
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->executeQuery() params : " . print_r($params, true));
    // Sauvegarde de la derniere requete
    self::$last_request = ['query' => $query, 'params' => $params];
    // Si la connexion n'est pas instanciée
    if (!isset($this->connection)) {
      // Throw exception, erreur
      M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->executeQueryToObject(): Problème de connexion à la base de données");
      throw new Exceptions\Melanie2DatabaseException("Erreur de base de données Mélanie2 : Erreur de connexion", 21);
    }
    // Configuration de la réutilisation des prepares statements
    $cached_statement = $cached_statement & Config::get(Config::REUSE_PREPARE_STATEMENT);
    // Si la requête demarre par SELECT on retourne les resultats
    // Sinon on retourne true (UPDATE/DELETE pas de resultat)
    // Récupération des données du cache
    if (strpos($query, "SELECT") === 0) {
      // Récupération du cache
      $cache = Cache::getFromSQLCache(null, is_array($params) ? array_keys($params) : $params, $query, $params);
      if (!is_null($cache) && $cache !== false) {
        return $cache;
      }
    }
    try {
      if ($cached_statement && isset($this->PreparedStatementCache[$query])) {
        // Récupérer le statement depuis le cache
        $sth = $this->PreparedStatementCache[$query];
      }
      else {
        // Choix de la connexion lecture/ecriture
        if (strpos($query, "SELECT") === 0 && isset($this->connection_read) && !$this->connection->inTransaction) {
          if (!isset($this->connection_read)) {
            return null;
          }
          $sth = $this->connection_read->prepare($query);
        }
        else {
          if (!isset($this->connection)) {
            return null;
          }
          $sth = $this->connection->prepare($query);
        }
        if ($cached_statement) {
          // Mise en cache du statement
          $this->PreparedStatementCache[$query] = $sth;
        }
      }
      if (isset($class)) {
        $sth->setFetchMode(\PDO::FETCH_CLASS, $class);
      }
      else {
        $sth->setFetchMode(\PDO::FETCH_BOTH);
      }
      if (isset($params)) {
        $res = $sth->execute($params);
      }
      else {
        $res = $sth->execute();
      }
    }
    catch (\PDOException $ex) {
      // Throw exception, erreur
      M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->executeQuery(): Exception $ex");
      throw new Exceptions\Melanie2DatabaseException("Erreur de base de données Mélanie2 : Erreur d'execution de la requête", 22);
    }
    // Tableau de stockage des données sql
    $arrayData = Array();
    // Si la requête demarre par SELECT on retourne les resultats
    // Sinon on retourne true (UPDATE/DELETE pas de resultat)
    if (strpos($query, "SELECT") === 0) {
      while ($object = $sth->fetch()) {
        if (isset($class) && method_exists($object, "pdoConstruct")) {
          if (isset($objectType)) {
            $object->pdoConstruct(true, $objectType);
          }
          else {
            $object->pdoConstruct(true);
          }
        }
        $arrayData[] = $object;
      }
      Cache::setSQLToCache(null, is_array($params) ? array_keys($params) : $params, $query, $params, $arrayData);
      $sth->closeCursor();
      return $arrayData;
    }
    else {
      // Suppression dans le cache
      Cache::deleteFromSQLCache(null, null, $query);
      // Retourne le resultat de l'execution
      return $res;
    }
    // Retourne null, pas de resultat
    return false;
  }

  /**
   * Execute a sql query to the active database connection in PDO
   * If query start by SELECT
   *
   * @param string $query
   * @param array $params
   * @param mixed $object
   * @param boolean $cached_statement Utiliser le cache pour les statements
   * @return boolean
   *
   * @throws Melanie2DatabaseException
   *
   * @access public
   */
  public function executeQueryToObject($query, $params = null, $object = null, $cached_statement = true) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->executeQueryToObject($query, " . get_class($object) . ")");
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->executeQueryToObject() params : " . print_r($params, true));
    // Sauvegarde de la derniere requete
    self::$last_request = ['query' => $query, 'params' => $params];
    // Si la connexion n'est pas instanciée
    if (!isset($this->connection)) {
      // Throw exception, erreur
      M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->executeQueryToObject(): Problème de connexion à la base de données");
      throw new Exceptions\Melanie2DatabaseException("Erreur de base de données Mélanie2 : Erreur de connexion", 21);
    }
    // Configuration de la réutilisation des prepares statements
    $cached_statement = $cached_statement & Config::get(Config::REUSE_PREPARE_STATEMENT);
    // Si la requête demarre par SELECT on retourne les resultats
    // Sinon on retourne null (UPDATE/DELETE pas de resultat)
    // Récupération des données du cache
    if (strpos($query, "SELECT") == 0) {
      // Récupération du cache
      $cache = Cache::getFromSQLCache(null, is_array($params) ? array_keys($params) : $params, $query, $params, $object);
      if (!is_null($cache) && $cache !== false) {
        if (method_exists($object, "__copy_from")) {
          if ($object->__copy_from($cache)) {
            return true;
          }
        }
      }
    }
    try {
      if ($cached_statement && isset($this->PreparedStatementCache[$query])) {
        // Récupérer le statement depuis le cache
        $sth = $this->PreparedStatementCache[$query];
      }
      else {
        // Choix de la connexion lecture/ecriture
        if (strpos($query, "SELECT") === 0 && !is_null($this->connection_read) && !$this->connection->inTransaction) {
          if (!isset($this->connection_read)) {
            return false;
          }
          $sth = $this->connection_read->prepare($query);
        }
        else {
          if (!isset($this->connection)) {
            return false;
          }
          $sth = $this->connection->prepare($query);
        }
        if ($cached_statement) {
          // Mise en cache du statement
          $this->PreparedStatementCache[$query] = $sth;
        }
      }
      $sth->setFetchMode(\PDO::FETCH_INTO, $object);
      if (isset($params)) {
        $res = $sth->execute($params);
      }
      else {
        $res = $sth->execute();
      }
    }
    catch (\PDOException $ex) {
      // Throw exception, erreur
      M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->executeQueryToObject(): Exception $ex");
      throw new Exceptions\Melanie2DatabaseException("Erreur de base de données Mélanie2 : Erreur d'execution de la requête", 23);
    }
    // Si la requête demarre par SELECT on retourne les resultats
    // Sinon on retourne null (UPDATE/DELETE pas de resultat)
    if (strpos($query, "SELECT") == 0) {
      if ($sth->fetch(\PDO::FETCH_INTO)) {
        Cache::setSQLToCache(null, is_array($params) ? array_keys($params) : $params, $query, $params, $object);
        $sth->closeCursor();
        // Retourne true, l'objet est trouvé
        return true;
      }
      else {
        // Retourne false, l'objet n'est pas trouvé
        return false;
      }
    }
    else {
      // Suppression dans le cache
      Cache::deleteFromSQLCache(null, null, $query);
      return $res;
    }
    // Retourne null, pas de resultat
    return false;
  }

  /**
   * Begin a PDO transaction
   */
  public function beginTransaction() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->beginTransaction()");
    $this->connection->beginTransaction();
  }

  /**
   * Commit a PDO transaction
   */
  public function commit() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->commit()");
    $this->connection->commit();
  }

  /**
   * Rollback a PDO transaction
   */
  public function rollBack() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->rollBack()");
    $this->connection->rollBack();
  }
  /**
   * Retourne la derniere requete
   * @return string
   */
  public static function getLastRequest() {
    M2Log::Log(M2Log::LEVEL_DEBUG, "Sql::getLastRequest()");
    return self::$last_request;
  }

  /**
   * Récupérer la clause de limit et de offset
   * 
   * @param integer $limit [Optionnel] limite du nombre de résultats à retourner
   * @param integer $offset [Optionnel] offset pour la pagination
   * 
   * @return string $limit_clause
   */
  public static function GetLimitClause($limit = null, $offset = null) {
    $limit_clause = '';
    // Gestion de la limite
    if (isset($limit) && is_int($limit)) {
        $limit_clause .= ' LIMIT '.$limit;
    }
    // Gestion de l'offset
    if (isset($offset) && is_int($offset)) {
        $limit_clause .= ' OFFSET '.$offset;
    }
    return $limit_clause;
  }

  /**
   * Récupérer la clause de order by
   * 
   * @param string $objectType Type d'objet pour le mapping
   * @param string $orderby Nom du champ pour le tri
   * @param boolean $asc Tri ascendant ou non
   * 
   * @return string $orderby_clause
   */
  public static function GetOrderByClause($objectType = null, $orderby = null, $asc = true) {
    $orderby_clause = '';
    // Tri
		if (!empty($orderby)) {
      // Récupèration des données de mapping
      if (isset(MappingMce::$Data_Mapping[$objectType])
              && isset(MappingMce::$Data_Mapping[$objectType][$orderby])) {
          $orderby = MappingMce::$Data_Mapping[$objectType][$orderby][MappingMce::name];
      }
      $orderby_clause .= " ORDER BY $orderby" . ($asc ? " ASC" : " DESC");
    }
    return $orderby_clause;
  }
}
?>