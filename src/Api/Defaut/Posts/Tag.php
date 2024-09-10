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
 * Classe de gestion des Tags de Post
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property integer $id Identifiant unique entier du tag
 * @property string $name Nom du tag
 * @property string $workspace uid de l'espace de travail associé au post
 * 
 * @method bool load() Charge les données du tag depuis la base de données
 * @method bool exists() Est-ce que le tag existe dans la base de données ?
 * @method bool save() Enregistre le tag dans la base de données
 * @method bool delete() Supprime le tag de la base de données
 */
class Tag extends MceObject {
  /**
   * Constructeur de l'objet
   * 
   * @param \LibMelanie\Api\Defaut\Workspace $workspace Workspace
   * @param string $name Nom du tag
   */
  public function __construct($workspace = null, $name = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_TRACE, $this->get_class . "->__construct()");
    // Définition de l'objet
    $this->objectmelanie = new ObjectMelanie('Post/Tag');

    if (isset($workspace)) {
      $this->workspace = $workspace->uid;
    }

    if (isset($name)) {
      $this->name = $name;
    }
  }

  /**
   * Récupère la liste des posts associés au tag
   * 
   * @param Tag[] $tagsList Liste des tags
   * @param string[] $fields Liste des champs à récupérer
   * 
   * @return Post[] Liste des posts
   */
  public function listPosts($tagsList = [], $fields = []) {
    $object = new ObjectMelanie('Post/PostsByTag');
    
    if (!empty($tagsList)) {
      $ids = [];
      foreach ($tagsList as $tag) {
        $ids[] = $tag->id;
      }
      $object->tag = $ids;
    }
    else {
      $object->tag = $this->id;
    }
    
    $_objects = $object->getList($fields);

    if (!isset($_objects)) {
			return null;
		}

		$objects = [];
		foreach ($_objects as $_object) {
			$_object->setIsExist();
			$_object->setIsLoaded();
			$object = new Post();
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
   * Associer un post au tag courant
   * 
   * @param Post $post Post à associer
   * 
   * @return bool Vrai si l'association a été faite
   */
  public function addPost($post) {
    $object = new ObjectMelanie('Post/TagByPost');
    $object->tag = $this->id;
    $object->post = $post->id;
    $ret = $object->save();

    return !is_null($ret);
  }

  /**
   * Dissocier un post du tag courant
   * 
   * @param Post $post Post à dissocier
   * 
   * @return bool Vrai si la dissociation a été faite
   */
  public function removePost($post) {
    $object = new ObjectMelanie('Post/TagByPost');
    $object->tag = $this->id;
    $object->post = $post->id;
    return $object->delete();
  }

  /**
   * Compte le nombre de posts associés au tag
   * 
   * @return integer Nombre de posts
   */
  public function countPosts() {
    $object = new ObjectMelanie('Post/TagByPost');
    $object->tag = $this->id;
    $res = $object->getList('count');
    return isset($res[0]) ? $res[0]->count : 0;
  }

  /**
   * Récupère la liste des tags
   * 
   * @param string $search Recherche
   * 
   * @return Tag[] Liste des tags
   */
  public function listTags($search = null) {
    $tag = new static();
    $tag->workspace = $this->workspace;

    $orderby = 'name';
    $fields = [];
    $filter = "";
    $operators = [];
    $case_unsensitive_fields = [];

    // Gestion de la recherche
    if (isset($search)) {
      $tag->name = '%'.$search.'%';
      $operators = [
        'name' => \LibMelanie\Config\MappingMce::like
      ];
      $case_unsensitive_fields = ['name'];
    }

    return $tag->getList($fields, $filter, $operators, $orderby, true, null, null, $case_unsensitive_fields);
  }
}