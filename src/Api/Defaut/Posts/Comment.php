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
   * Nombre de réactions associées au Post
   * 
   * @var array
   */
  protected $_countLikes;

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
   * @param string $type Type de réaction
   * 
   * @return Comments\Like[] Liste des réactions
   */
  public function listLikes($type = null) {
    $like = new Comments\Like($this);

    if (isset($type)) {
      $like->type = $type;        
    }

    $likes = $like->getList();
    $this->objectmelanie->likes = count($likes);
    $this->objectmelanie->setFieldHasChanged('likes', false);

    // Gérer les countLikes
    foreach ($likes as $like) {
      if (!isset($this->_countLikes)) {
        $this->_countLikes = [];
      }

      if (!isset($this->_countLikes[$like->type])) {
        $this->_countLikes[$like->type] = 0;
      }

      $this->_countLikes[$like->type]++;
    }

    return $likes;
  }

  /**
   * Compte le nombre de réactions associées au post
   * 
   * @param string $type Type de réaction
   * 
   * @return integer Nombre de réactions
   */
  public function countLikes($type = null) {
    if (!isset($this->objectmelanie->likes) || isset($type)) {
      $like = new Comments\Like($this);

      if (isset($type)) {
        if (!isset($this->_countLikes)) {
          $this->_countLikes = [];
        }

        if (isset($this->_countLikes[$type])) {
          return $this->_countLikes[$type];
        }

        $like->type = $type;
      }

      $res = $like->getList('count');
      $this->objectmelanie->likes = isset($res[0]) ? $res[0]->count : 0;
      $this->objectmelanie->setFieldHasChanged('likes', false);
    }
    return $this->objectmelanie->likes;
  }

  /**
   * Récupère la liste des enfants du commentaire
   * 
   * @return Comment[] Liste des commentaires
   */
  public function listChildren($search = null, $orderby = 'created', $asc = true) {
    $comment = new Comment();
    $filter = '';
    $fields = [];
    $operators = [];
    $case_unsensitive_fields = [];

    // Gestion de la recherche
    if (isset($search)) {
      // Creator ?
      if (strpos($search, 'creator:') !== false) {
        preg_match('/creator:(.*)/', $search, $matches);
        if (!empty($matches[1])) {
          $comment->creator = $matches[1];
          $operators = [
            'creator' => \LibMelanie\Config\MappingMce::eq
          ];
        }
        $search = preg_replace('/creator:(.*)/', '', $search);
      }

      $comment->content = '%'.$search.'%';
      $operators = [
        'content' => \LibMelanie\Config\MappingMce::like
      ];
      $case_unsensitive_fields = ['content'];
    }

    // N'afficher que les commentaires parents ?
    $comment->parent = $this->id;
  
    switch ($orderby) {
      case 'children':
        $comments = $comment->getList($fields, $filter, $operators, $orderby, $asc, null, null, $case_unsensitive_fields, 'Post/Comment', 'LEFT', ['id', 'parent'], 'Post/Comment', ['id'], 'children', [
          // Subrequest likes
          ['likes', 'count', 'Post/Comment/Like', 'comment']
        ]);
        break;
      case 'likes':
        $comments = $comment->getList($fields, $filter, $operators, $orderby, $asc, null, null, $case_unsensitive_fields, 'Post/Comment/Like', 'LEFT', 'comment', 'Post/Comment', ['id'], 'likes', [
          // Subrequest children
          ['children', 'count', 'Post/Comment', ['parent', 'id']]
        ]);
        break;
      default:
        $comments = $comment->getList($fields, $filter, $operators, $orderby, $asc, null, null, $case_unsensitive_fields, null, null, null, null, null, null, [
          // Subrequest likes
          ['likes', 'count', 'Post/Comment/Like', 'comment'],
          // Subrequest children
          ['children', 'count', 'Post/Comment', ['parent', 'id']]
        ]);
        break;
    }

    return $comments;
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
      $this->objectmelanie->setFieldHasChanged('children', false);
    }
    return $this->objectmelanie->children;
  }

  /**
   * children en lecture seule
   * 
   * @ignore
   */
  public function setMapChildren($children) {
    return;
  }

  /**
   * likes en lecture seule
   * 
   * @ignore
   */
  public function setMapLikes($likes) {
    return;
  }
}