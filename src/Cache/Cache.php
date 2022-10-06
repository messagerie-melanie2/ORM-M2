<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * ORM Mél Copyright © 2021 Groupe Messagerie/MTE
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
namespace LibMelanie\Cache;

use LibMelanie\Log\M2Log;
use LibMelanie\Config\Config;

/**
 * Classe de gestion du cache pour les objets Melanie2
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage CACHE
 */
class Cache {
  // Tableau de stockage des données SQL
  private static $cache_sql = [];
  /**
   * Connexion vers memcache
   *
   * @var \Memcache
   */
  private static $memcache_cnx = null;

  // Constantes
  const OBJECT = "object";
  const OBJECTLISTID = "objectlistid";
  const OBJECTSTYPE = "objectstype";
  const TIME = "time";
  const QUERY = "0x01";
  const PARAMS = "0x02";
  const TABLE = "0x03";
  const FIELDS = "0x04";
  const RESULTS = "0x05";

  /**
   * Connexion à memcache
   */
  private static function memcache_connect() {
    self::$memcache_cnx = new \Memcache();
    if (strpos(Config::get(Config::CACHE_MEMCACHE_HOST), ',') !== false) {
      $memcache_hosts = explode(',', Config::get(Config::CACHE_MEMCACHE_HOST));
      foreach ($memcache_hosts as $memcache_host) {
        if (isset($memcache_host) && $memcache_host != "") {
          $url = explode(':', $memcache_host);
          $host = $url[0];
          if (isset($url[1]))
            $port = $url[1];
          self::$memcache_cnx->addserver($host, intval($port));
        }
      }
    } else {
      $url = explode(':', Config::get(Config::CACHE_MEMCACHE_HOST));
      $host = $url[0];
      if (isset($url[1]))
        $port = $url[1];
      self::$memcache_cnx->connect($host, intval($port));
    }
  }

  /**
   * Mise en cache des données SQL
   * Permet de ne pas charger la base de données avec plusieurs fois la même requête
   * TODO: Mettre en memcache les données
   *
   * @param string $table_name
   * @param array $fields
   * @param string $query
   * @param array $params
   * @param array $results
   * @return boolean
   */
  static function setSQLToCache($table_name = null, $fields = [], $query = "", $params = [], $results = []) {
    // Le cache est il activé
    if (!Config::get(Config::CACHE_ENABLED))
      return false;
    if (empty($table_name)) {
      // On essaye de récupérer le nom de table depuis la query
      if (strpos(strtoupper($query), "SELECT ") === 0 || strpos(strtoupper($query), "DELETE ") === 0) {
        if (preg_match_all('/\b(from|join)\b\s*(\w+)/', strtolower($query), $matches) && isset($matches[2])) {
          if (count($matches[2]) == 1) {
            // Récupération de la table
            $table_name = $matches[2][0];
          } else {
            $table_name = [];
            // On trouve plusieurs tables dans la requête
            foreach ($matches[2] as $match) {
              // Suppression des doublons
              if (!in_array($match, $table_name)) {
                $table_name[] = $match;
              }
            }
          }
        } else {
          return null;
        }
      } else if (strpos(strtoupper($query), "INSERT ") === 0) {
        if (preg_match('/\binto\b\s*(\w+)/i', strtolower($query), $matches) && isset($matches[2])) {
          $table_name = $matches[2];
        } else {
          return null;
        }
      } else if (strpos(strtoupper($query), "UPDATE ") === 0) {
        if (preg_match('/\bupdate\b\s*(\w+)/i', strtolower($query), $matches) && isset($matches[1])) {
          $table_name = $matches[1];
        } else {
          return null;
        }
      } else {
        return null;
      }
      return self::setSQLToCache($table_name, $fields, $query, $params, $results);
    }
    M2Log::Log(M2Log::LEVEL_DEBUG, "Cache->setSQLToCache()");
    // Cache mémoire php
    if (Config::get(Config::CACHE_TYPE) == 'php') {
      if (is_array($table_name)) {
        $table_name = $table_name[0];
      }
      // Création du cache pour la table
      if (!isset(self::$cache_sql[$table_name]))
        self::$cache_sql[$table_name] = [];
      // Création du cache pour les champs
      $hash_fields = md5(serialize($fields));
      if (!isset(self::$cache_sql[$table_name][$hash_fields]))
        self::$cache_sql[$table_name][$hash_fields] = [];
      // Création du cache pour la requête
      $hash_query = md5($query);
      if (!isset(self::$cache_sql[$table_name][$hash_fields][$hash_query]))
        self::$cache_sql[$table_name][$hash_fields][$hash_query] = array();
      // Création du cache pour les paramètres
      $hash_params = md5(serialize($params));
      self::$cache_sql[$table_name][$hash_fields][$hash_query][$hash_params] = [];
      // Ajout des resultats dans le cache
      self::$cache_sql[$table_name][$hash_fields][$hash_query][$hash_params][self::RESULTS] = $results;
      self::$cache_sql[$table_name][$hash_fields][$hash_query][$hash_params][self::TIME] = time();
    } else if (Config::get(Config::CACHE_TYPE) == 'memcache') {
      // Cache memcache
      if (!isset(self::$memcache_cnx)) {
        self::memcache_connect();
      }
      $hash = md5($query) . ":" . base64_encode(serialize($params));
      if (self::$memcache_cnx->get($hash) !== false) {
        self::$memcache_cnx->delete($hash);
      } else {
        // Gestion de la liste des hash pour la table
        if (is_array($table_name)) {
          foreach ($table_name as $table) {
            $table_name_hashs = self::$memcache_cnx->get(md5(strtolower($table)));
            if ($table_name_hashs !== false) {
              $table_name_hashs = unserialize($table_name_hashs);
              if (!in_array($hash, $table_name_hashs)) {
                $table_name_hashs[] = $hash;
                self::$memcache_cnx->replace(md5(strtolower($table)), serialize($table_name_hashs), false, Config::get(Config::CACHE_DELAY));
              }
            } else {
              $table_name_hashs = [];
              $table_name_hashs[] = $hash;
              self::$memcache_cnx->add(md5(strtolower($table)), serialize($table_name_hashs), false, Config::get(Config::CACHE_DELAY));
            }
          }
        } else {
          $table_name_hashs = self::$memcache_cnx->get(md5(strtolower($table_name)));
          if ($table_name_hashs !== false) {
            $table_name_hashs = unserialize($table_name_hashs);
            if (!in_array($hash, $table_name_hashs)) {
              $table_name_hashs[] = $hash;
              self::$memcache_cnx->replace(md5(strtolower($table_name)), serialize($table_name_hashs), false, Config::get(Config::CACHE_DELAY));
            }
          } else {
            $table_name_hashs = [];
            $table_name_hashs[] = $hash;
            self::$memcache_cnx->add(md5(strtolower($table_name)), serialize($table_name_hashs), false, Config::get(Config::CACHE_DELAY));
          }
        }
      }
      self::$memcache_cnx->add($hash, serialize($results), false, Config::get(Config::CACHE_DELAY));
    }
    return true;
  }

  /**
   * Récupération depuis le cache des données
   * Permet de ne pas charger la base de données avec plusieurs fois la même requête
   * TODO: Mettre en memcache les données
   *
   * @param string $table_name
   * @param array $fields
   * @param string $query
   * @param array $params
   * @return array|null Résultats cachés
   */
  static function getFromSQLCache($table_name = null, $fields = [], $query = "", $params = []) {
    // Le cache est il activé
    if (!Config::get(Config::CACHE_ENABLED))
      return null;
    if (empty($table_name)) {
      // On essaye de récupérer le nom de table depuis la query
      if (strpos(strtoupper($query), "SELECT ") === 0 || strpos(strtoupper($query), "DELETE ") === 0) {
        if (preg_match_all('/\b(from|join)\b\s*(\w+)/', strtolower($query), $matches) && isset($matches[2])) {
          if (count($matches[2]) == 1) {
            // Récupération de la table
            $table_name = $matches[2][0];
          } else {
            $table_name = [];
            // On trouve plusieurs tables dans la requête
            foreach ($matches[2] as $match) {
              // Suppression des doublons
              if (!in_array($match, $table_name)) {
                $table_name[] = $match;
              }
            }
          }
        } else {
          return null;
        }
      } else if (strpos(strtoupper($query), "INSERT ") === 0) {
        if (preg_match('/\binto\b\s*(\w+)/i', strtolower($query), $matches) && isset($matches[2])) {
          $table_name = $matches[2];
        } else {
          return null;
        }
      } else if (strpos(strtoupper($query), "UPDATE ") === 0) {
        if (preg_match('/\bupdate\b\s*(\w+)/i', strtolower($query), $matches) && isset($matches[1])) {
          $table_name = $matches[1];
        } else {
          return null;
        }
      } else {
        return null;
      }
      return self::getFromSQLCache($table_name, $fields, $query, $params);
    }
    M2Log::Log(M2Log::LEVEL_DEBUG, "Cache->getFromSQLCache()");
    // Cache mémoire php
    if (Config::get(Config::CACHE_TYPE) == 'php') {
      if (is_array($table_name)) {
        $table_name = $table_name[0];
      }
      // Création du cache pour les champs
      $hash_fields = md5(serialize($fields));
      // Création du cache pour la requête
      $hash_query = md5($query);
      // Création du cache pour les paramètres
      $hash_params = md5(serialize($params));
      // Récupération du cache
      if (isset(self::$cache_sql[$table_name]) && isset(self::$cache_sql[$table_name][$hash_fields]) && isset(self::$cache_sql[$table_name][$hash_fields][$hash_query]) && isset(self::$cache_sql[$table_name][$hash_fields][$hash_query][$hash_params])) {
        // Delai de rétention du cache
        if ((time() - self::$cache_sql[$table_name][$hash_fields][$hash_query][$hash_params][self::TIME]) > Config::get(Config::CACHE_DELAY)) {
          // Suppression des données en cache
          unset(self::$cache_sql[$table_name][$hash_fields][$hash_query][$hash_params]);
          // Suppression des données vides
          if (count(self::$cache_sql[$table_name][$hash_fields][$hash_query]) === 0) {
            unset(self::$cache_sql[$table_name][$hash_fields][$hash_query]);
            // Suppression des données vides
            if (count(self::$cache_sql[$table_name][$hash_fields]) === 0) {
              unset(self::$cache_sql[$table_name][$hash_fields]);
              // Suppression des données vides
              if (count(self::$cache_sql[$table_name]) === 0) {
                unset(self::$cache_sql[$table_name]);
              }
            }
          }
          return null;
        } else
          return self::$cache_sql[$table_name][$hash_fields][$hash_query][$hash_params][self::RESULTS];
      }
    } else if (Config::get(Config::CACHE_TYPE) == 'memcache') {
      // Cache memcache
      // Cache memcache
      if (!isset(self::$memcache_cnx)) {
        self::memcache_connect();
      }
      $hash = md5($query) . ":" . base64_encode(serialize($params));
      $results = self::$memcache_cnx->get($hash);
      if ($results !== false) {
        $results = unserialize($results);
        if ($results !== false) {
          return $results;
        }
      }
    }
    return null;
  }

  /**
   * Suppression des données dans le cache
   * Permet de ne pas charger la base de données avec plusieurs fois la même requête
   * TODO: Mettre en memcache les données
   *
   * @param string $table_name
   * @param array $fields
   * @param string $query
   * @param array $params
   * @return boolean
   */
  static function deleteFromSQLCache($table_name = null, $fields = [], $query = null, $params = []) {
    // Le cache est il activé
    if (!Config::get(Config::CACHE_ENABLED))
      return null;
    if (empty($table_name)) {
      if (!empty($query)) {
        // On essaye de récupérer le nom de table depuis la query
        if (strpos(strtoupper($query), "SELECT ") === 0 || strpos(strtoupper($query), "DELETE ") === 0) {
          if (preg_match_all('/\b(from|join)\b\s*(\w+)/', strtolower($query), $matches) && isset($matches[2])) {
            if (count($matches[2]) == 1) {
              // Récupération de la table
              $table_name = $matches[2][0];
            } else {
              $table_name = [];
              // On trouve plusieurs tables dans la requête
              foreach ($matches[2] as $match) {
                // Suppression des doublons
                if (!in_array($match, $table_name)) {
                  $table_name[] = $match;
                }
              }
            }
          } else {
            return false;
          }
        } else if (strpos(strtoupper($query), "INSERT ") === 0) {
          if (preg_match('/\binto\b\s*(\w+)/i', strtolower($query), $matches) && isset($matches[2])) {
            $table_name = $matches[2];
          } else {
            return false;
          }
        } else if (strpos(strtoupper($query), "UPDATE ") === 0) {
          if (preg_match('/\bupdate\b\s*(\w+)/i', strtolower($query), $matches) && isset($matches[1])) {
            $table_name = $matches[1];
          } else {
            return false;
          }
        } else {
          return false;
        }
        return self::deleteFromSQLCache($table_name, $fields, $query, $params);
      } else {
        // Pas de table on vide toutes les données en cache
        unset(self::$cache_sql);
        self::$cache_sql = [];
      }
    } else {
      M2Log::Log(M2Log::LEVEL_DEBUG, "Cache->deleteFromSQLCache()");
      // Cache mémoire php
      if (Config::get(Config::CACHE_TYPE) == 'php') {
        if (is_array($table_name)) {
          $table_name = $table_name[0];
        }
        // Suppression du cache
        if (isset(self::$cache_sql[$table_name])) {
          if (empty($fields)) {
            unset(self::$cache_sql[$table_name]);
          } else {
            // Création du hash pour les champs
            $hash_fields = md5(serialize($fields));
            if (isset(self::$cache_sql[$table_name][$hash_fields])) {
              if (empty($query)) {
                unset(self::$cache_sql[$table_name][$hash_fields]);
              } else {
                // Création du hash pour la requête
                $hash_query = md5($query);
                if (isset(self::$cache_sql[$table_name][$hash_fields][$hash_query])) {
                  if (empty($params)) {
                    unset(self::$cache_sql[$table_name][$hash_fields][$hash_query]);
                  } else {
                    // Création du hash pour les paramètres
                    $hash_params = md5(serialize($params));
                    if (isset(self::$cache_sql[$table_name][$hash_fields][$hash_query][$hash_params])) {
                      unset(self::$cache_sql[$table_name][$hash_fields][$hash_query][$hash_params]);
                    }
                    if (count(self::$cache_sql[$table_name][$hash_fields][$hash_query]) === 0) {
                      unset(self::$cache_sql[$table_name][$hash_fields][$hash_query]);
                    }
                  }
                }
                if (count(self::$cache_sql[$table_name][$hash_fields]) === 0) {
                  unset(self::$cache_sql[$table_name][$hash_fields]);
                }
              }
            }
            if (count(self::$cache_sql[$table_name])) {
              unset(self::$cache_sql[$table_name]);
            }
          }
        }
      } else if (Config::get(Config::CACHE_TYPE) == 'memcache') {
        // Cache memcache
        if (!isset(self::$memcache_cnx)) {
          self::memcache_connect();
        }
        // Gestion de la liste des hash pour la table
        if (is_array($table_name)) {
          foreach ($table_name as $table) {
            // Gestion de la liste des hash pour la table
            $table_name_hashs = self::$memcache_cnx->get(md5(strtolower($table)));
            if ($table_name_hashs !== false) {
              foreach (unserialize($table_name_hashs) as $hash) {
                self::$memcache_cnx->delete($hash);
              }
              self::$memcache_cnx->delete(md5(strtolower($table)));
            }
          }
        } else {
          // Gestion de la liste des hash pour la table
          $table_name_hashs = self::$memcache_cnx->get(md5(strtolower($table_name)));
          if ($table_name_hashs !== false) {
            foreach (unserialize($table_name_hashs) as $hash) {
              self::$memcache_cnx->delete($hash);
            }
            self::$memcache_cnx->delete(md5(strtolower($table_name)));
          }
        }
      }
    }
    return true;
  }
}