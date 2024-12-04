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
 * Classe de gestion des Reactions sur les Posts
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property integer $id Identifiant unique entier de la réaction
 * @property string $type Type de réaction
 * @property integer $modified timestamp de modification du post
 * @property string $creator uid de l'utilisateur qui a créé la réaction
 * @property integer $post Identifiant unique entier du post associé
 * 
 * @method bool load() Charge les données de la réaction depuis la base de données
 * @method bool exists() Est-ce que la réaction existe dans la base de données ?
 * @method bool save() Enregistre la réaction dans la base de données
 * @method bool delete() Supprime la réaction de la base de données
 */
class Reaction extends MceObject {
  /**
   * Constructeur de l'objet
   * 
   * @param \LibMelanie\Api\Defaut\Posts\Post $post Post
   * @param string $type Type de réaction
   * @param string $creator uid de l'utilisateur qui a créé la réaction
   */
  public function __construct($post = null, $type = null, $creator = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->__construct()");
    // Définition de l'objet
    $this->objectmelanie = new ObjectMelanie('Post/Reaction');

    if (isset($post)) {
      $this->post = $post->id;
    }

    if (isset($type)) {
      $this->type = $type;
    }

    if (isset($creator)) {
      $this->creator = $creator;
    }
  }
}