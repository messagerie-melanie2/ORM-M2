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
namespace LibMelanie\Api\Defaut\Posts;

use LibMelanie\Lib\MceObject;
use LibMelanie\Log\M2Log;
use LibMelanie\Objects\ObjectMelanie;

/**
 * Classe de gestion des Images de Post
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property integer $id Identifiant unique entier de l'image
 * @property string $uid Identifiant unique string de l'image
 * @property integer $post Identifiant unique entier du post associé
 * @property string $data Données de l'image
 * 
 * @method bool load() Charge les données de l'image depuis la base de données
 * @method bool exists() Est-ce que l'image existe dans la base de données ?
 * @method bool save() Enregistre l'image dans la base de données
 * @method bool delete() Supprime l'image de la base de données
 */
class Image extends MceObject {
  /**
   * Constructeur de l'objet
   * 
   * @param \LibMelanie\Api\Defaut\Posts\Post $post Post
   */
  public function __construct($post = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->__construct()");
    // Définition de l'objet
    $this->objectmelanie = new ObjectMelanie('Post/Image');

    if (isset($post)) {
      $this->post = $post->id;
    }
  }
}