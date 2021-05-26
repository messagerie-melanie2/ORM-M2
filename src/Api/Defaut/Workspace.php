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
namespace LibMelanie\Api\Defaut;

use DateTime;
use LibMelanie\Api\Gen\Group;
use LibMelanie\Api\Gen\User;
use LibMelanie\Lib\MceObject;
use LibMelanie\Objects\WorkspaceMelanie;
use LibMelanie\Log\M2Log;

/**
 * Classe workspace par defaut
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $id Identifiant numérique du workspace
 * @property string $uid Identifiant unique du workspace
 * @property int $created Timestamp de creation du workspace
 * @property int $modified Timestamp de modification du workspace
 * @property string $creator Uid utilisateur du createur
 * @property string $title Titre du workspace
 * @property string $description Description du workspace
 * @property string $logo Logo du workspace
 * @property boolean $ispublic Est-ce que le workspace est public ?
 * @property boolean $isarchived Est-ce que le workspace est archivé ?
 * @property string $objects JSON des objets du workspace
 * @property string $links JSON des liens utiles du workspace
 * @property string $flux JSON des flux rss du workspace
 * @property string $settings JSON des paramètres du workspace
 * @property Workspaces\Share[] $shares Liste des partages du workspaces
 * @property string[] $hashtags Liste des hashtags du workspaces
 * 
 * @method bool load() Charge les données du workspace depuis la base de données
 * @method bool exists() Recherche si le workspace existe dans la base de données
 * @method bool save() Enregistre le workspace dans la base de données
 * @method bool delete() Supprime le workspace de la base de données
 */
class Workspace extends MceObject {
  /**
   * Accès aux objets associés
   * Utilisateur associé à l'objet
   * 
   * @var User
   * @ignore
   */
  protected $user;

  /**
   * Liste des hashtags associés au workspace
   * 
   * @var Workspaces\Hashtag[]
   * @ignore
   */
  protected $hashtags;

  /**
   * Liste des hashtags à supprimer du workspace
   * 
   * @var Workspaces\Hashtag
   * @ignore
   */
  protected $deletedHashtags;

  /**
   * Liste des partages associés au workspace
   * 
   * @var Workspaces\Share[]
   * @ignore
   */
  protected $shares;

  /**
   * Liste des partages à supprimer du workspace
   * 
   * @var Workspaces\Share[]
   * @ignore
   */
  protected $deletedShares;

  /**
   * Liste de diffusion associée au workspace
   * 
   * @var Group
   * @ignore
   */
  protected $_emailList;
  
  /**
   * Constructeur de l'objet
   * 
   * @param User $user          
   */
  public function __construct($user = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");
    // Définition du calendrier melanie2
    $this->objectmelanie = new WorkspaceMelanie();
    
    // Définition des objets associés
    if (isset($user)) {
      $this->user = $user;
    }
  }
  
  /**
   * Défini l'utilisateur MCE
   * 
   * @param User $user          
   * @ignore
   */
  public function setUserMelanie($user) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setUserMelanie()");
    $this->user = $user;
  }
  
  /**
   * ***************************************************
   * METHOD MAPPING
   */
  /**
   * Enregistrement de l'objet
   * Nettoie le cache du user
   * 
   * @return null si erreur, boolean sinon (true insert, false update)
   */
  public function save() {
    // sauvegarder les hashtags
    $this->saveHashtags();
    // sauvegarder les shares
    $this->saveShares();
    // Sauvegarder le workspace
    $ret = $this->objectmelanie->save();
    if (!is_null($ret) && isset($this->user)) {
      $this->user->cleanWorkspaces();
    }
    return $ret;
  }

  /**
   * Enregistrer les partages modifiés dans la base de données
   */
  protected function saveShares() {
    $hasChanged = false;
    if (is_array($this->shares)) {
      // Enregistrer les shares dans la base de données
      foreach ($this->shares as $share) {
        $ret = $share->save();
        $hasChanged |= !is_null($ret);
      }
    }
    // Gérer les suppressions
    if (is_array($this->deletedShares)) {
      foreach ($this->deletedShares as $share) {
        $hasChanged |= $share->delete();
      }
    }
    // Si un partage a changé on change le timestamp modified
    if ($hasChanged) {
      $this->modified = new DateTime();
    }
  }

  /**
   * Enregistrer les hashtags modifiés dans la base de données
   */
  protected function saveHashtags() {
    $hasChanged = false;
    $HashtagWorkspaceRef = $this->__getNamespace() . '\\Workspaces\\HashtagWorkspaceRef';
    if (is_array($this->hashtags)) {
      // Enregistrer les hashtags dans la base de données
      foreach ($this->hashtags as $hashtag) {
        $ret = $hashtag->save();
        $hasChanged |= !is_null($ret);
        if ($hashtag->load()) {
          // Si c'est une insertion dans la bdd il faut créer le ref
          $ref = new $HashtagWorkspaceRef();
          $ref->hashtag = $hashtag->id;
          $ref->workspace = $this->id;
          $ref->load();
          $ref->save();
        }
      }
    }
    if (is_array($this->deletedHashtags)) {
      // Supprimer les hashtags à supprimer
      foreach ($this->deletedHashtags as $hashtag) {
        // On supprime la ref
        $ref = new $HashtagWorkspaceRef();
        $ref->hashtag = $hashtag->id;
        $ref->workspace = $this->id;
        // Doit on supprimer le hashtag ?
        if ($ref->delete()) {
          $hasChanged = true;
          $refList = new $HashtagWorkspaceRef();
          $refList->hashtag = $hashtag->id;
          $list = $refList->getList();
          if (count($list) === 0) {
            $hashtag->delete();
          }
        }
      }
    }
    // Si un hashtag a changé on change le timestamp modified
    if ($hasChanged) {
      $this->modified = new DateTime();
    }
  }

  /**
   * Suppression de l'objet
   * Nettoie le cache du user
   * 
   * @return boolean
   */
  public function delete() {
    $ret = $this->objectmelanie->delete();
    if ($ret && isset($this->user)) {
      $this->deletedHashtags;
      $this->user->cleanWorkspaces();
    }
    return $ret;
  }

  /**
   * Supprimer les hashtags et les refs
   */
  protected function deleteHashtags() {
    // Parcourir toutes les refs du workspace pour les supprimer
    $Hashtag = $this->__getNamespace() . '\\Workspaces\\Hashtag';
    $HashtagWorkspaceRef = $this->__getNamespace() . '\\Workspaces\\HashtagWorkspaceRef';
    $refList = new $HashtagWorkspaceRef();
    $refList->workspace = $this->id;
    $list = $refList->getList();
    if (is_array($list)) {
      foreach ($list as $ref) {
        $hashtag = $ref->hashtag;
        // Supprime les ref
        if ($ref->delete()) {
          // On va rechercher si le hashtag doit être supprimé
          $refListBis = new $HashtagWorkspaceRef();
          $refListBis->hashtag = $hashtag;
          $listBis = $refListBis->getList();
          if (count($listBis) === 0) {
            // La liste est vide on peut supprimer le hashtag
            $tag = new $Hashtag();
            $tag->id = $hashtag;
            $tag->delete();
          }
        }
      }
    }
  }

  /**
   * Lister les workspaces par hashtag
   * 
   * @param string $hashtag Hashtag recherché
   * @param string $orderby [Optionnel] nom du champ a trier
   * @param boolean $asc [Optionnel] tri ascendant ?
   * @param integer $limit [Optionnel] limite du nombre de résultats à retourner
   * @param integer $offset [Optionnel] offset pour la pagination
   * 
   * @return Workspace[]
   */
	public function listWorkspacesByHashtag($hashtag, $orderby = null, $asc = true, $limit = null, $offset = null) {
    $res = $this->objectmelanie->listWorkspacesByHashtag($hashtag, $orderby, $asc, $limit, $offset);
    $Workspace = $this->__getNamespace() . '\\Workspace';
    $workspaces = [];
    if (is_array($res)) {
      foreach ($res as $w) {
        $workspace = new $Workspace();
        $workspace->setObjectMelanie($w);
        $workspaces[] = $workspace;
      }
    }
    return $workspaces;
  }


  /**
   * Lister les workspaces par hashtag
   * 
   * @param string $orderby [Optionnel] nom du champ a trier
   * @param boolean $asc [Optionnel] tri ascendant ?
   * @param integer $limit [Optionnel] limite du nombre de résultats à retourner
   * @param integer $offset [Optionnel] offset pour la pagination
   * 
   * @return Workspace[]
   */
	public function listPublicsWorkspaces($orderby = null, $asc = true, $limit = null, $offset = null) {
    $res = $this->objectmelanie->listPublicsWorkspaces($orderby, $asc, $limit, $offset);
    $Workspace = $this->__getNamespace() . '\\Workspace';
    $workspaces = [];
    if (is_array($res)) {
      foreach ($res as $w) {
        $workspace = new $Workspace();
        $workspace->setObjectMelanie($w);
        $workspaces[] = $workspace;
      }
    }
    return $workspaces;
  }

  /**
   * Créer une liste de diffusion associée au workspace
   * 
   * @param string $name Nom de la liste
   * 
   * @return Group|null Retourne la liste créée ou null si erreur
   */
  public function createEmailList($members_email = [], $domain = null, $prefix = 'edt') {
    // Vérifier si la liste n'existe pas déjà ?
    $this->_emailList = $this->getEmailList();

    if (!isset($this->_emailList)) {
      // Lancer la création de la liste si elle n'existe pas
      $this->_emailList = new Group(null, 'WorkspaceGroup');
      $email = $this->getEmailList($prefix, $domain);
      $this->_emailList->email = $email;
      $this->_emailList->email_list = [$email];
      $this->_emailList->fullname = "Liste " . $this->title;
      $this->_emailList->members_email = $members_email;
      $ret = $this->_emailList->save();
      if (is_null($ret)) {
        $this->_emailList;
      }
    }
    // Retourne la liste
    return $this->_emailList;
  }

  /**
   * Retourne l'adresse email de la liste
   * 
   * @param string $domain Nom de domaine pour l'adresse email
   */
  protected function getListEmailAddress($prefix = '', $domain = null) {
    if (!isset($domain)) {
      // Si le domain n'est pas positionné, on prend celui du createur
      $user = new User();
      $user->uid = $this->creator;
      if ($user->load()) {
        $mail = explode('@', $user->email, 2);
        $domain = $mail[1];
      }
    }
    // Gestion du prefixe
    if (!empty($prefix)) {
      $prefix = $prefix.'.';
    }
    return $prefix.$this->uid.'@'.$domain;
  }

  /**
   * Récupérer la liste associée au workspace
   * 
   * @return Group|null Retourne la liste associée ou null si elle n'existe pas
   */
  public function getEmailList() {
    if (!isset($this->_emailList)) {

    }
    return $this->_emailList;
  }

  /**
   * ***************************************************
   * DATA MAPPING
   */
  /**
   * Mapping hashtags field
   * 
   * @param string[] $hashtags          
   */
  protected function setMapHashtags($hashtags) {
    $this->loadHastags();
    $hashtagsList = [];
    $Hashtag = $this->__getNamespace() . '\\Workspaces\\Hashtag';
    foreach ($hashtags as $label) {
      if (isset($this->hashtags[$label])) {
        // C'est un hashtag déjà existant, on l'enleve de la liste
        $hash = $this->hashtags[$label];
        unset($this->hashtags[$label]);
      }
      else {
        // C'est un nouvel hashtag on le crée
        $hash = new $Hashtag($this);
        $hash->label = $label;
      }
      $hashtagsList[$label] = $hash;
    }
    // La liste $this->hashtags restante contient les hashtags à supprimer
    $this->deletedHashtags = $this->hashtags;
    $this->hashtags = $hashtagsList;
  }

  /**
   * Mapping hashtags field
   * 
   * @return string[]
   */
  protected function getMapHashtags() {
    $hashtags = [];
    $this->loadHastags();
    foreach ($this->hashtags as $hashtag) {
      $hashtags[] = $hashtag->label;
    }
    return $hashtags;
  }

  /**
   * Chargement des hashtags du workspace
   */
  protected function loadHastags() {
    if (!isset($this->hashtags)) {
      $this->hashtags = [];
      $hashtags = $this->objectmelanie->getWorkspaceHashtags();
      if (is_array($hashtags)) {
        $Hashtag = $this->__getNamespace() . '\\Workspaces\\Hashtag';
        foreach ($hashtags as $hash) {
          $hashtag = new $Hashtag($this);
          $hashtag->setObjectMelanie($hash);
          $this->hashtags[$hashtag->label] = $hashtag;
        }
      }
    }
  }

  /**
   * Mapping shares field
   * 
   * @param Workspaces\Share[] $shares          
   */
  protected function setMapShares($shares) {
    $this->loadShares();
    $_shares = [];
    foreach ($shares as $share) {
      // On recherche si le partage existe déjà (pour les suppressions)
      if (isset($this->shares[$share->user])) {
        // Pour gérer le hashchanged correctement
        $_shares[$share->user] = $this->shares[$share->user];
        $_shares[$share->user]->rights = $share->rights;
        unset($this->shares[$share->user]);
      }
      else {
        $_shares[$share->user] = $share;
      }
    }
    // La liste restante est pour les suppressions
    $this->deletedShares = $this->shares;
    $this->shares = $_shares;
  }
  
  /**
   * Mapping shares field
   * 
   * @return Workspaces\Share[]
   */
  protected function getMapShares() {
    $this->loadShares();
    return $this->shares;
  }

  /**
   * Chargement des shares du workspace
   */
  protected function loadShares() {
    if (!isset($this->shares)) {
      $this->shares = [];
      $shares = $this->objectmelanie->getWorkspaceShares();
      if (is_array($shares)) {
        $Share = $this->__getNamespace() . '\\Workspaces\\Share';
        foreach ($shares as $s) {
          $share = new $Share($this);
          $share->setObjectMelanie($s);
          $this->shares[$share->user] = $share;
        }
      }
    }
  }
}