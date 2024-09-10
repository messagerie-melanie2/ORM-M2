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
 * Classe de gestion des Comments de Post
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property integer $id Identifiant unique entier du commentaire
 * @property string $uid Identifiant unique string du commentaire
 * @property string $content Contenu du commentaire
 * @property integer $created timestamp de création du commentaire
 * @property integer $modified timestamp de modification du commentaire
 * @property integer $post Identifiant unique entier du post associé
 * @property string $creator uid de l'utilisateur qui a créé le commentaire
 * @property integer $parent Identifiant unique entier du commentaire parent
 * 
 * @method bool load() Charge les données du commentaire depuis la base de données
 * @method bool exists() Est-ce que le commentaire existe dans la base de données ?
 * @method bool save() Enregistre le commentaire dans la base de données
 * @method bool delete() Supprime le commentaire de la base de données
 */
class Comment extends MceObject {
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
    $this->objectmelanie = new ObjectMelanie('Post/Comment');

    if (isset($post)) {
      $this->post = $post->id;
    }
  }

  /**
   * Récupère la liste des likes associées au commentaire du post
   * 
   * @return Comments\Like[] Liste des réactions
   */
  public function listLikes() {
    $like = new Comments\Like($this);
    return $like->getList();
  }

  /**
   * Compte le nombre de réactions associées au post
   * 
   * @return integer Nombre de réactions
   */
  public function countLikes() {
    if (!isset($this->objectmelanie->likes)) {
      $like = new Comments\Like($this);
      $res = $like->getList('count');
      $this->objectmelanie->likes = isset($res[0]) ? $res[0]->count : 0;
    }
    return $this->objectmelanie->likes;
  }

  /**
   * Récupère la liste des enfants du commentaire
   * 
   * @return Comment[] Liste des commentaires
   */
  public function listChildren() {
    $comment = new Comment($this);
    $comment->parent = $this->id;
    return $comment->getList();
  }

  /**
   * Compte le nombre d'enfants du commentaire
   * 
   * @return integer Nombre d'enfants
   */
  public function countChildren() {
    if (!isset($this->objectmelanie->children)) {
      $comment = new Comment($this);
      $comment->parent = $this->id;
      $res = $comment->getList('count');
      $this->objectmelanie->children = isset($res[0]) ? $res[0]->count : 0;
    }
    return $this->objectmelanie->children;
  }
}