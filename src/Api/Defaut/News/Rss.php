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
namespace LibMelanie\Api\Defaut\News;

use LibMelanie\Log\M2Log;
use LibMelanie\Objects\ObjectMelanie;

/**
 * Classe de gestion des partages pour les News
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $uid Identifiant unique du flux rss
 * @property string $title Titre donné au flux rss
 * @property string $url URL vers le flux rss
 * @property string $source Nom de la source
 * @property string $service Service associé au flux rss
 * @property string $creator Identifiant du créateur
 * @property boolean $publisher Est-ce que l'utilisateur est un publisher de cette news ?
 * 
 * @method bool load() Charge les données du flux rss depuis la base de données
 * @method bool exists() Est-ce que le flux rss existe dans la base de données ?
 * @method bool save() Enregistre le flux rss dans la base de données
 * @method bool delete() Supprime le flux rss de la base de données
 */
class Rss extends News {
  /**
   * Accès aux objets associés
   * Utilisateur associé à l'objet
   * 
   * @var \LibMelanie\Api\Defaut\User
   * @ignore
   */
  protected $_user;

  /**
   * Constructeur de l'objet
   * 
   * @param \LibMelanie\Api\Defaut\User $user Utilisateur
   */
  public function __construct($user = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->__construct()");
    // Définition de l'objet
    $this->objectmelanie = new ObjectMelanie('Rss');

    if (isset($user)) {
      $this->_user = $user;
    }
  }

  /**
   * ***************************************************
   * METHOD MAPPING
   */
  /**
   * Chargement de l'objet
   */
  public function load() {
    // Load l'objet
    $ret = $this->objectmelanie->load();

    // Test si le user est un publisher
    if ($ret && isset($this->_user)) {
      $this->_user->isNewsPublisher($this);
    }

    return $ret;
  }

  /**
   * Enregistrement de l'objet
   * Nettoie le cache du user
   * 
   * @return null si erreur, boolean sinon (true insert, false update)
   */
  public function save() {
    // Sauvegarder le rss
    $ret = $this->objectmelanie->save();
    if (!is_null($ret) && isset($this->_user)) {
      $this->_user->cleanRss();
    }
    return $ret;
  }

  /**
   * Suppression de l'objet
   * Nettoie le cache du user
   * 
   * @return boolean
   */
  public function delete() {
    $ret = $this->objectmelanie->delete();
    if ($ret && isset($this->_user)) {
      $this->_user->cleanRss();
    }
    return $ret;
  }
}