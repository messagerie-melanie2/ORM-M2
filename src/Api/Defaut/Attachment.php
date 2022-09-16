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

use LibMelanie\Objects\AttachmentMelanie;
use LibMelanie\Lib\MceObject;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\Config;

/**
 * Classe pièces jointes par defaut,
 * implémente les API de la librairie pour aller chercher les données dans la base de données
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $id [TYPE_BINARY] Identifiant unique de la pièce jointe
 * @property int $modified [TYPE_BINARY] Timestamp de la modification de la pièce jointe
 * @property boolean $isfolder [TYPE_BINARY] Si l'objet est un dossier et non pas un fichier
 * @property string $name [TYPE_BINARY] Nom de la pièce jointe
 * @property string $path [TYPE_BINARY] Chemin vers la pièce jointe
 * @property string $owner [TYPE_BINARY] Propriétaire de la pièce jointe
 * @property string $data Données encodées de la pièce jointe
 * @property string $url URL vers la pièce jointe
 * @property Attachment::TYPE_* $type Type de la pièce jointe / Binaire ou URL (Binaire par défaut)
 * @property-read string $hash Lecture du HASH lié aux données de la pièce jointe (lecture seule)
 * @property-read int $size Taille en octet de la pièce jointe binaire (lecture seule)
 * @property-read string $contenttype Content type de la pièce jointe (lecture seule)
 * 
 * @method bool load() Chargement la pièce jointe, données comprises
 * @method bool exists() Test si la pièce jointe existe
 * @method bool save() Sauvegarde la pièce jointe si elle est de type binaire
 * @method bool delete() Supprime la pièce jointe binaire de la base
 */
class Attachment extends MceObject {
  const TYPE_BINARY = 'BINARY';
  const TYPE_URL = 'URL';
  const PATH_ROOT = '.horde/kronolith/documents';
  
  // object privé
  /**
   * Type de la pièce jointe
   * Binaire ou URL (Binaire par défaut)
   * 
   * @var Attachment::TYPE_*
   */
  protected $type = self::TYPE_BINARY;

  /**
   * Taille de la pièce jointe
   * 
   * @var integer
   */
  protected $_size;

  /**
   * Hash des données
   * 
   * @var string
   */
  protected $_hash;

  /**
   * ContentType de la pièce jointe
   * 
   * @var string
   */
  protected $_contenttype;
  
  /**
   * ***************************************************
   * PUBLIC METHODS
   */
  /**
   * Constructeur de l'objet
   */
  public function __construct() {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    // M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->__construct()");
    // Définition de la pièce jointe melanie2
    $this->objectmelanie = new AttachmentMelanie();
  }
  
  /**
   * Méthode pour récupérer l'URL vers la pièce jointe
   * Dans le cas d'une pièce jointe URL on récupère simplement l'URL
   * Dans le cas d'une pièce jointe binaire, utilise l'url de téléchargement configuré
   * 
   * @return string
   */
  public function getDownloadURL() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getDownloadURL()");
    if ($this->type == self::TYPE_BINARY) {
      $url = null;
      if (Config::is_set(Config::ATTACHMENT_DOWNLOAD_URL)) {
        $url = Config::get(Config::ATTACHMENT_DOWNLOAD_URL);
        $url = str_replace('%f', urlencode($this->name), $url);
        $url = str_replace('%p', urlencode(substr($this->path, strlen(Config::get(Config::DEFAULT_ATTACHMENTS_FOLDER)) + 1)), $url);
      }
      return $url;
    } else {
      return $this->data;
    }
  }
  
  /**
   * ***************************************************
   * METHOD MAPPING
   */
  /**
   * Mapping de la sauvegarde de l'objet
   * 
   * @ignore
   *
   */
  function save() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->save()");
    if (!isset($this->objectmelanie))
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    
    // Si c'est une pièce jointe de type URL l'enregistrement est différent
    if ($this->type === self::TYPE_URL)
      return null;
    
    // Création du chemin s'il n'existe pas déjà
    $path = trim(str_replace(self::PATH_ROOT, '', $this->objectmelanie->path), '/');
    if (!empty($path)) {
      $paths = explode('/', $path);
      $current_path = "";
      foreach ($paths as $path) {
        // Creation du dossier
        $_folder = new static();
        $_folder->name = $path;
        $_folder->path = $current_path;
        if (!$_folder->load()) {
          $_folder->isfolder = true;
          $_folder->modified = time();
          $_folder->owner = $this->objectmelanie->owner;
          $_folder->save();
        } else {
          break;
        }
        if ($current_path != "")
          $current_path .= "/";
        $current_path .= $path;
      }
    }
    
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    // Sauvegarde l'objet
    return $this->objectmelanie->save();
  }
  
  /**
   * Mapping de la suppression de l'objet
   * 
   * @ignore
   *
   */
  function delete() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->delete()");
    if (!isset($this->objectmelanie))
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    // Si c'est une pièce jointe de type URL la suppression est différente
    if ($this->type === self::TYPE_URL)
      return null;
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    // Suppression de l'objet
    return $this->objectmelanie->delete();
  }
  
  /**
   * Mapping du chargement de l'objet
   * 
   * @ignore
   *
   */
  function load() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->load()");
    if (!isset($this->objectmelanie))
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    // Si c'est une pièce jointe de type URL l'enregistrement est différent
    if ($this->type === self::TYPE_URL)
      return null;
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    // Charge l'objet
    return $this->objectmelanie->load();
  }
  
  /**
   * Permet de récupérer la liste d'objet en utilisant les données passées
   * (la clause where s'adapte aux données)
   * Il faut donc peut être sauvegarder l'objet avant d'appeler cette méthode
   * pour réinitialiser les données modifiées (propriété haschanged)
   * La particularité de cette méthode est qu'elle ne charge pas les données de la pièces jointes automatiquement
   * pour chaque pièce jointe il faut ensuite charger les données en faisant un load().
   * 
   * @param String[] $fields
   *          Liste les champs à récupérer depuis les données
   * @param String $filter
   *          Filtre pour la lecture des données en fonction des valeurs déjà passé, exemple de filtre : "((#description# OR #title#) AND #start#)"
   * @param String[] $operators
   *          Liste les propriétés par operateur (MappingMce::like, MappingMce::supp, MappingMce::inf, MappingMce::diff)
   * @param String $orderby
   *          Tri par le champ
   * @param bool $asc
   *          Tri ascendant ou non
   * @param int $limit
   *          Limite le nombre de résultat (utile pour la pagination)
   * @param int $offset
   *          Offset de début pour les résultats (utile pour la pagination)
   * @param String[] $case_unsensitive_fields
   *          Liste des champs pour lesquels on ne sera pas sensible à la casse
   * @return Attachment[] Array
   */
  function getList($fields = [], $filter = "", $operators = [], $orderby = "", $asc = true, $limit = null, $offset = null, $case_unsensitive_fields = []) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getList()");
    $_attachments = $this->objectmelanie->getList($fields, $filter, $operators, $orderby, $asc, $limit, $offset, $case_unsensitive_fields);
    if (!isset($_attachments))
      return null;
    $attachments = [];
    foreach ($_attachments as $_attachment) {
      // Ne considérer l'attachment loadé que si les data sont chargées
      if (empty($fields) || in_array('data', $fields)) {
        $_attachment->setIsExist();
        $_attachment->setIsLoaded();
      }
      $attachment = new static();
      $attachment->setObjectMelanie($_attachment);
      $attachments[$_attachment->id] = $attachment;
    }
    // Détruit les variables pour libérer le plus rapidement de la mémoire
    unset($_attachments);
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    return $attachments;
  }
  
  /**
   * ***************************************************
   * DATA MAPPING
   */
  /**
   * Mapping path field
   * 
   * @param string $path          
   */
  protected function setMapPath($path) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapPath()");
    if (!isset($this->objectmelanie))
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    if (is_string($path)) {
      if ($path == "") {
        $path = self::PATH_ROOT;
      } elseif (strpos($path, self::PATH_ROOT) === false) {
        if (strpos($path, '/') !== 0) {
          $path = '/' . $path;
        }
        $path = self::PATH_ROOT . $path;
      }
    }
    $this->objectmelanie->path = $path;
  }
  
  /**
   * Mapping isfolder field
   * 
   * @param boolean $isfolder          
   */
  protected function setMapIsfolder($isfolder) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapIsfolder($isfolder)");
    if (!isset($this->objectmelanie))
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    if ($isfolder)
      $this->objectmelanie->type = Config::get(Config::TYPE_FOLDER);
    else
      $this->objectmelanie->type = Config::get(Config::TYPE_FILE);
  }
  /**
   * Mapping isfolder field
   */
  protected function getMapIsfolder() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapIsfolder()");
    if (!isset($this->objectmelanie))
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    if ($this->objectmelanie->type === Config::get(Config::TYPE_FOLDER))
      return true;
    else
      return false;
  }
  
  /**
   * Mapping data field
   */
  protected function getMapData() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapData()");
    if (!isset($this->objectmelanie))
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    if ($this->type == self::TYPE_URL)
      return $this->objectmelanie->data;
    else {
      if (!isset($this->objectmelanie->data))
        $this->load();
      return pack('H' . strlen($this->objectmelanie->data), $this->objectmelanie->data);
    }
  }
  /**
   * Mapping data field
   * 
   * @param string $data          
   * @throws Exceptions\ObjectMelanieUndefinedException
   * @return boolean
   */
  protected function setMapData($data) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapData()");
    if (!isset($this->objectmelanie))
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    if ($this->type == self::TYPE_URL || $this->objectmelanie->type === Config::get(Config::TYPE_FOLDER))
      return false;
    else {
      $this->objectmelanie->data = bin2hex($data);
    }
    return true;
  }
  
  /**
   * Mapping url field
   */
  protected function getMapUrl() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapUrl()");
    if (!isset($this->objectmelanie))
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    if ($this->type == self::TYPE_BINARY) {
      $url = Config::get(Config::ATTACHMENT_DOWNLOAD_URL);
      $url = str_replace('%f', urlencode($this->name), $url);
      $url = str_replace('%p', urlencode(substr($this->path, strlen(Config::get(Config::DEFAULT_ATTACHMENTS_FOLDER)) + 1)), $url);
      return $url;
    } else {
      return $this->objectmelanie->data;
    }
  }
  /**
   * Mapping url field
   * 
   * @param string $url          
   * @throws Exceptions\ObjectMelanieUndefinedException
   * @return boolean
   */
  protected function setMapUrl($url) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapUrl($url)");
    if (!isset($this->objectmelanie))
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    
    $this->objectmelanie->data = $url;
    $this->type = self::TYPE_URL;
    return true;
  }
  
  /**
   * Mapping type field
   * 
   * @throws Exceptions\ObjectMelanieUndefinedException
   * @return Attachment::TYPE_*
   */
  protected function getMapType() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapType()");
    if (!isset($this->objectmelanie))
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    return $this->type;
  }
  /**
   * Mapping type field
   * 
   * @param Attachment::TYPE_* $type          
   * @throws Exceptions\ObjectMelanieUndefinedException
   * @return boolean
   */
  protected function setMapType($type) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapType($type)");
    if (!isset($this->objectmelanie))
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    $this->type = $type;
    return true;
  }
  
  /**
   * Mapping size field
   */
  protected function getMapSize() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapSize()");
    if ($this->type == self::TYPE_URL) {
      return 0;
    }
    else if (!isset($this->_size)) {
      $this->_size = mb_strlen($this->getMapData());
    }
    return $this->_size;
  }

  /**
   * Mapping size field
   */
  protected function setMapSize($size) {
    $this->_size = $size;
  }
  
  /**
   * Mapping hash field
   */
  protected function getMapHash() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapHash()");
    if (!isset($this->_hash)) {
      $this->_hash = hash('md5', $this->getMapData());
    }
    return $this->_hash;
  }

  /**
   * Mapping hash field
   */
  protected function setMapHash($hash) {
    $this->_hash = $hash;
  }
  
  /**
   * Mapping content type field
   */
  protected function getMapContenttype() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapContenttype()");
    if ($this->type == self::TYPE_URL) {
      return null;
    }
    else if (!isset($this->_contenttype)) {
      // ContentType par défaut au cas ou on ne le trouve pas
      $this->_contenttype = Config::get(Config::DEFAULT_ATTACHMENT_CONTENTTYPE);
      if (class_exists("finfo")) {
        // Utilisation de la classe finfo pour récupérer le contenttype
        $finfo = new \finfo(FILEINFO_MIME);
        $infos = $finfo->buffer($this->getMapData());
        if ($infos !== FALSE) {
          $infos = explode(';', $infos);
          $this->_contenttype = $infos[0];
        }
      }
    }
    return $this->_contenttype;
  }

  /**
   * Mapping content type field
   */
  protected function setMapContenttype($contenttype) {
    $this->_contenttype = $contenttype;
  }
}