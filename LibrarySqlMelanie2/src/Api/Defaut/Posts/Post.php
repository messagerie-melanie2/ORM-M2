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
 * Classe de gestion des Posts
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property integer $id Identifiant unique entier du post
 * @property string $uid Identifiant unique string du post
 * @property string $title Titre donné au post
 * @property string $summary Résumé du post
 * @property string $content Contenu du post
 * @property string $created timestamp de création du post (format Y-m-d H:i:s)
 * @property string $modified timestamp de modification du post (format Y-m-d H:i:s)
 * @property string $creator uid de l'utilisateur qui a créé le post
 * @property string $workspace uid de l'espace de travail associé au post
 * @property array $settings Paramètres du post
 * @property array $history Historique en JSON du post
 * 
 * @method bool load() Charge les données du post depuis la base de données
 * @method bool exists() Est-ce que le post existe dans la base de données ?
 * @method bool save() Enregistre le post dans la base de données
 * @method bool delete() Supprime le post de la base de données
 */
class Post extends MceObject {

  /**
   * Nombre de réactions associées au Post
   * 
   * @var array
   */
  protected $_countReactions;

  /**
   * Constructeur de l'objet
   * 
   * @param \LibMelanie\Api\Defaut\Workspace $workspace Workspace
   * @param string $creator uid de l'utilisateur qui a créé le post
   */
  public function __construct($workspace = null, $creator = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->__construct()");
    // Définition de l'objet
    $this->objectmelanie = new ObjectMelanie('Post');

    if (isset($workspace)) {
      $this->workspace = $workspace->uid;
    }

    if (isset($creator)) {
      $this->creator = $creator;
    }
  }

  /**
   * Récupère la liste des commentaires associés au post
   * 
   * @param bool $onlyParent Récupérer uniquement les commentaires de premier niveau
   * @param string $search Recherche
   * @param string $orderby Champ de tri
   * @param bool $asc Tri ascendant ou descendant
   * 
   * @return Comment[] Liste des commentaires
   */
  public function listComments($onlyParent = false, $search = null, $orderby = 'created', $asc = true) {
    $comment = new Comment($this);
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
    if ($onlyParent) {
      $filter = "#post# AND #parent# IS NULL";
      $comment->parent = null;
      $operators['post'] = \LibMelanie\Config\MappingMce::eq;
    }
    
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
   * Compte le nombre de commentaires associés au post
   * 
   * @return integer Nombre de commentaires
   */
  public function countComments() {
    if (!isset($this->objectmelanie->comments)) {
      $comment = new Comment($this);
      $res = $comment->getList('count');
      $this->objectmelanie->comments = isset($res[0]) ? $res[0]->count : 0;
      $this->objectmelanie->setFieldHasChanged('comments', false);
    }
    return $this->objectmelanie->comments;
  }

  /**
   * Récupère la liste des réactions associées au post
   * 
   * @param string $type Type de réaction
   * 
   * @return Reaction[] Liste des réactions
   */
  public function listReactions($type = null) {
    $reaction = new Reaction($this);

    if (isset($type)) {
      $reaction->type = $type;        
    }

    $reactions = $reaction->getList();
    $this->objectmelanie->reactions = count($reactions);
    $this->objectmelanie->setFieldHasChanged('reactions', false);

    // Gérer les countReactions
    foreach ($reactions as $reaction) {
      if (!isset($this->_countReactions)) {
        $this->_countReactions = [];
      }

      if (!isset($this->_countReactions[$reaction->type])) {
        $this->_countReactions[$reaction->type] = 0;
      }

      $this->_countReactions[$reaction->type]++;
    }

    // Compter les likes et les dislikes
    $this->objectmelanie->likes = $this->_countReactions['like'] ?? 0;
    $this->objectmelanie->setFieldHasChanged('likes', false);
    $this->objectmelanie->dislikes = $this->_countReactions['dislike'] ?? 0;
    $this->objectmelanie->setFieldHasChanged('dislikes', false);

    return $reactions;

  }

  /**
   * Récupère la liste des images associées au post
   * 
   * @return Image[] Liste des images
   */
  public function listImages() {
    $image = new Image($this);
    return $image->getList(['id', 'uid', 'post']);
  }

  /**
   * Récupère la première image associée au post
   * 
   * @return Image Première image
   */
  public function firstImage() {
    $image = new Image($this);
    $images = $image->getList(['id', 'uid', 'post'], '', [], 'id', false, 1);
    return count($images) ? array_pop($images) : null;
  }

  /**
   * Compte le nombre de réactions associées au post
   * 
   * @param string $type Type de réaction
   * 
   * @return integer Nombre de réactions
   */
  public function countReactions($type = null) {
    if (!isset($this->objectmelanie->reactions) || isset($type)) {
      $reaction = new Reaction($this);

      if (isset($type)) {
        if (!isset($this->_countReactions)) {
          $this->_countReactions = [];
        }

        if (isset($this->_countReactions[$type])) {
          return $this->_countReactions[$type];
        }

        $reaction->type = $type;
      }
      
      $res = $reaction->getList('count');

      if (!isset($type)) {
        $this->objectmelanie->reactions = isset($res[0]) ? $res[0]->count : 0;
        $this->objectmelanie->setFieldHasChanged('reactions', false);
      }
      else if ($type == 'like') {
        $this->objectmelanie->likes = $this->objectmelanie->reactions;
        $this->objectmelanie->setFieldHasChanged('likes', false);
      }
      else if ($type == 'dislike') {
        $this->objectmelanie->dislikes = $this->objectmelanie->reactions;
        $this->objectmelanie->setFieldHasChanged('dislikes', false);
      }
    }
    return $this->objectmelanie->reactions;
  }

  /**
   * Récupère la liste des tags associés au post
   * 
   * @param Post[] $postsList Liste des posts
   * 
   * @return Tag[] Liste des tags
   */
  public function listTags($postsList = []) {
    $object = new ObjectMelanie('Post/TagsByPost');
    
    if (!empty($postsList)) {
      $ids = [];
      foreach ($postsList as $post) {
        $ids[] = $post->id;
      }
      $object->post = $ids;
    }
    else {
      $object->post = $this->id;
    }
    
    $_objects = $object->getList();

    if (!isset($_objects)) {
			return null;
		}

		$objects = [];
		foreach ($_objects as $_object) {
			$_object->setIsExist();
			$_object->setIsLoaded();
			$object = new Tag();
			$object->setObjectMelanie($_object);
			
			if (isset($_object->id)) {
				$objects[$_object->id] = $object;
			}
			else {
				$objects[] = $object;
			}
		}
		return $objects;
  }

  /**
   * Associer un tag au post courant
   * 
   * @param Tag $tag Tag à associer
   * 
   * @return bool Vrai si l'association a été faite
   */
  public function addTag($tag) {
    $object = new ObjectMelanie('Post/TagByPost');
    $object->post = $this->id;
    $object->tag = $tag->id;
    $ret = $object->save();

    return !is_null($ret);
  }

  /**
   * Dissocier un tag du post courant
   * 
   * @param Tag $tag Tag à dissocier
   * 
   * @return bool Vrai si la dissociation a été faite
   */
  public function removeTag($tag) {
    $object = new ObjectMelanie('Post/TagByPost');
    $object->post = $this->id;
    $object->tag = $tag->id;
    return $object->delete();
  }

  /**
   * Compte le nombre de tags associés au post
   * 
   * @return integer Nombre de tags
   */
  public function countTags() {
    $object = new ObjectMelanie('Post/TagByPost');
    $object->post = $this->id;
    $res = $object->getList('count');
    return isset($res[0]) ? $res[0]->count : 0;
  }

  /**
   * Récupère la liste des posts
   * 
   * @param string $search Recherche
   * @param Tag[] $tagsList Liste des tags
   * @param string $orderby Champ de tri
   * @param bool $asc Tri ascendant ou descendant
   * @param integer $limit Limite de résultats
   * @param integer $offset Offset de résultats
   * @param array $uids Liste des uids
   * 
   * @return Post[] Liste des posts
   */
  public function listPosts($search = null, $tags = [], $orderby = 'created', $asc = true, $limit = null, $offset = null, $uids = null) {
    $post = new static();
    $post->workspace = $this->workspace;
    $fields = ['id', 'uid', 'title', 'summary', 'created', 'modified', 'creator', 'workspace'];
    $filter = "";
    $operators = [];
    $case_unsensitive_fields = [];

    // Gestion de la recherche
    if (isset($search)) {
      $search = strtolower($search);

      // HashTags ?
      if (strpos($search, '#') !== false) {
        preg_match_all('/#(\w+)/', $search, $matches);
        if (!empty($matches[1])) {
          $_posts = (new Tag())->listPostsByTagsName($matches[1], $this->workspace, ['post']);

          $ids = [];
          foreach ($_posts as $_post) {
            $ids[] = $_post->post;
          }

          $post->id = $ids;

          // Gestion du filtre
          if (!empty($filter)) {
            $filter .= " AND ";
          }
          $filter .= "#id#";
          $operators['id'] = \LibMelanie\Config\MappingMce::in;
        }
        $search = preg_replace('/#(\w+)/', '', $search);
        $search = trim($search);
      }

      // Creator ?
      if (strpos($search, 'creator:') !== false) {
        preg_match('/creator:(.*)/', $search, $matches);
        if (!empty($matches[1])) {
          $post->creator = strtolower($matches[1]);
          $operators['creator'] = \LibMelanie\Config\MappingMce::eq;
          $case_unsensitive_fields[] = 'creator';
        }
        $search = preg_replace('/creator:(.*)/', '', $search);

        // Gestion du filtre
        if (!empty($filter)) {
          $filter .= " AND ";
        }
        $filter .= "#creator#";
      }

      // cleaned search
      if (!empty($search)) {
        $search = trim($search);
        
        // Recherche dans le title et summary
        $operators['title'] = \LibMelanie\Config\MappingMce::like;
        $operators['summary'] = \LibMelanie\Config\MappingMce::like;
        $case_unsensitive_fields[] = 'title';
        $case_unsensitive_fields[] = 'summary';        
        $post->title = '%'.$search.'%';
        $post->summary = '%'.$search.'%';

        // Gestion du filtre
        if (!empty($filter)) {
          $filter .= " AND ";
        }
        $filter .= "(#title# OR #summary#)";
      }
    }

    // Gestion des tags
    if (!empty($tags)) {
      $tag = new Tag();
      $ids = [];
      $_posts = $tag->listPosts($tags, ['id']);
      foreach ($_posts as $_post) {
        $ids[] = $_post->id;
      }
      $post->id = $ids;

      // Gestion du filtre
      if (!empty($filter)) {
        $filter .= " AND ";
      }
      $filter .= "#id#";
      $operators['id'] = \LibMelanie\Config\MappingMce::in;
    }

    // Lister par uid
    if (isset($uids)) {
      $post->uid = $uids;

      // Gestion du filtre
      if (!empty($filter)) {
        $filter .= " AND ";
      }
      $filter .= "#uid#";
      $operators['uid'] = \LibMelanie\Config\MappingMce::in;
    }

    // Gestion du tri
    switch ($orderby) {
      case 'comments':
        $posts = $post->getList($fields, $filter, $operators, "comments", $asc, $limit, $offset, $case_unsensitive_fields, 'Post/Comment', 'LEFT', 'post', 'Post', ['id'], "comments", [
          // Subrequest reactions
          ['reactions', 'count', 'Post/Reaction', 'post'],
          ['likes', 'count', 'Post/Reaction', ['post', 'post', ['type' => 'like']]],
          ['dislikes', 'count', 'Post/Reaction', ['post', 'post', ['type' => 'dislike']]]]);
        break;
      case 'reactions':
        $posts = $post->getList($fields, $filter, $operators, "reactions", $asc, $limit, $offset, $case_unsensitive_fields, 'Post/Reaction', 'LEFT', 'post', 'Post', ['id'], "reactions", [
          // Subrequest comments
          ['comments', 'count', 'Post/Comment', 'post'],
          ['likes', 'count', 'Post/Reaction', ['post', 'post', ['type' => 'like']]],
          ['dislikes', 'count', 'Post/Reaction', ['post', 'post', ['type' => 'dislike']]]]);
        break;
      default:
        $posts = $post->getList($fields, $filter, $operators, $orderby, $asc, $limit, $offset, $case_unsensitive_fields, null, null, null, null, null, null, [
          // Subrequest comments
          ['comments', 'count', 'Post/Comment', 'post'], 
          // Subrequest reactions
          ['reactions', 'count', 'Post/Reaction', 'post'],
          ['likes', 'count', 'Post/Reaction', ['post', 'post', ['type' => 'like']]],
          ['dislikes', 'count', 'Post/Reaction', ['post', 'post', ['type' => 'dislike']]]]);
        break;
    }

    return $posts;
  }

  /**
   * reactions en lecture seule
   * 
   * @ignore
   */
  public function setMapReactions($reactions) {
    return;
  }

  /**
   * comments en lecture seule
   * 
   * @ignore
   */
  public function setMapComments($comments) {
    return;
  }
}