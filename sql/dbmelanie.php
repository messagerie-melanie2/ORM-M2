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

use LibMelanie\Config\ConfigSQL;

/**
 * Singleton de connexion à la base de données
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage SQL
 */
class DBMelanie {
  /**
   * Singleton vers la connexion SQL
   *
   * @var Sql
   */
  private static $connexion;
  /**
   * Configuration de la base
   *
   * @var array
   */
  private static $db;
  /**
   * Configuration de la base en lecture
   *
   * @var array
   */
  private static $db_read = null;
  /**
   * Définition du backend courant utilisé pour la connexion
   *
   * @var unknown
   */
  private static $current_backend = null;
  /**
   * Transation en cours
   *
   * @var mixed
   */
  private static $transaction;

  /**
   * Constructeur privé pour ne pas instancier la classe
   */
  private function __construct() {
  }

  /**
   * Initialise les paramètres de connexion à la base de données
   *
   * @param $backend Configuration du backend SQL
   */
  public static function Initialize($backend = null) {
    if (isset($backend) && isset(ConfigSQL::$SERVERS[$backend])) {
      if (self::$current_backend != $backend) {
        // Définition des paramètres de connexion à la base de données, en fonction du backend
        self::$db = ConfigSQL::$SERVERS[$backend];
        if (isset(self::$db['read'])) {
          // Définition des paramètres de connexion pour la base de données en lecture
          self::$db_read = self::$db['read'];
          unset(self::$db['read']);
        }
      }
      if (isset(self::$db) && (self::$current_backend != $backend || ! isset(self::$connexion))) {
        // Si la connexion n'est pas instanciée mais que la classe est initialisée
        // OU si le backend courant est différent du backend défini
        self::$connexion = new Sql(self::$db, self::$db_read);
        self::$current_backend = $backend;
      }
    }
    if (self::$transaction !== true)
      self::$transaction = false;
  }

  /**
   * Débute une transaction PDO
   */
  public static function BeginTransaction() {
    // Si la connexion existe
    if (isset(self::$connexion)) {
      self::$connexion->getConnection();
      self::$connexion->beginTransaction();
      self::$transaction = true;
    }
  }

  /**
   * Commit une transaction PDO après un BeginTransaction
   */
  public static function Commit() {
    // Si la connexion existe
    if (isset(self::$connexion)) {
      self::$connexion->getConnection();
      self::$connexion->commit();
      self::$transaction = false;
    }
  }

  /**
   * Rollback une transaction PDO après un BeginTransaction
   */
  public static function Rollback() {
    // Si la connexion existe
    if (isset(self::$connexion)) {
      self::$connexion->getConnection();
      self::$connexion->rollBack();
      self::$transaction = false;
    }
  }

  /**
   * Execute la requête grâce à l'instance de Sql
   * Se connecte et se déconnecte automatiquement à la ressource sql
   *
   * @param string $query
   * @param array $params
   * @param string $class
   * @param string $objectType
   * @return mixed result/bool
   */
  public static function ExecuteQuery($query, $params = null, $class = null, $objectType = null) {
    // Si la connexion existe, se connecte s'il faut, execute la requête et se déconnecte
    if (isset(self::$connexion)) {
      self::$connexion->getConnection();
      $result = self::$connexion->executeQuery($query, $params, $class, $objectType);
      // if (!self::$transaction) self::$connexion->disconnect();
      return $result;
    }
    // Return null en cas d'erreur
    return null;
  }

  /**
   * Execute la requête grâce à l'instance de Sql
   * Se connecte et se déconnecte automatiquement à la ressource sql
   *
   * @param string $query
   * @param array $params
   * @param mixed $object
   * @return mixed result/bool
   */
  public static function ExecuteQueryToObject($query, $params = null, $object) {
    // Si la connexion existe, se connecte s'il faut, execute la requête et se déconnecte
    if (isset(self::$connexion)) {
      self::$connexion->getConnection();
      $result = self::$connexion->executeQueryToObject($query, $params, $object);
      // if (!self::$transaction) self::$connexion->disconnect();
      return $result;
    }
    // Return null en cas d'erreur
    return null;
  }

  /**
   * Disconnect from SQL database
   */
  public static function Disconnect() {
    // Deconnexion de la base de données
    if (isset(self::$connexion))
      self::$connexion->disconnect();
  }
}
?>