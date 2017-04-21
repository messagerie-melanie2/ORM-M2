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
namespace LibMelanie\Config;

/**
 * Configuration de la connexion SQL vers la base de données Mélanie2
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Config
 */
class ConfigSQL {
  /**
   * Configuration du serveur de base de données
   */
  public static $SGBD_SERVER = 'sgbd.test';
  /**
   * Backend courant utilisé
   */
  public static $CURRENT_BACKEND = 'sgbd.test';
  /**
   * Défini le backend courant
   * 
   * @param string $backend          
   */
  public static function setCurrentBackend($backend) {
    if (isset(self::$SERVERS[$backend])) {
      self::$CURRENT_BACKEND = $backend;
    }
  }
  /**
   * Configuration des serveurs SQL
   * Chaque clé indique le nom du serveur sql et sa configuration de connexion
   */
  public static $SERVERS = array(
      // Configuration de la base de données original
      'sgbd.test' => array(
          /**
           * Connexion persistante
           */
          'persistent' => 'false',
          /**
           * Hostname ou IP vers le serveur SGBD
           */
          'hostspec' => 'sgbd.test',
          /**
           * Mot de passe pour l'utilisateur
           */
          'password' => 'P4ss.',
          /**
           * Base de données
           */
          'database' => 'mybase',
          /**
           * Port de connexion
           */
          'port' => 5432,
          /**
           * Utilisateur pour la connexion à la base
           */
          'username' => 'myuser',
          /**
           * Protocole de connexion
           */
          'protocol' => 'tcp',
          /**
           * Encodage de la base de données
           */
          'charset' => 'utf-8',
          /**
           * Type de base : pgsql, mysql
           */
          'phptype' => 'pgsql'
      )
  );
}