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

use LibMelanie\Lib\MceObject;
use LibMelanie\Objects\AddressbookMelanie;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\Config;

/**
 * Classe de carnet d'adresses par defaut
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $id Identifiant unique du carnet d'adresses
 * @property string $owner Identifiant du propriétaire du carnet d'adresses
 * @property string $name Nom complet du carnet d'adresses
 * @property int $perm Permission associée, utiliser asRight()
 * @property string $ctag CTag du carnet d'adresses
 * @property int $synctoken SyncToken du carnet d'adresses
 * @property-read string $carddavurl URL CardDAV pour le carnet d'adresses
 * @method bool load() Charge les données du carnet d'adresses depuis la base de données
 * @method bool exists() Test dans la base de données si le carnet d'adresses existe déjà
 * @method bool save() Création ou modification du carnet d'adresses
 * @method bool delete() Supprimer le carnet d'adresses et toutes ses données de la base de données
 * @method void getCTag() Charge la propriété ctag avec l'identifiant de modification du carnet d'adresses
 * @method bool asRight($action) Retourne un boolean pour savoir si les droits sont présents
 */
class Addressbook extends MceObject {
  /**
   * Utilisateur associé au carnet d'adresses
   * 
   * @var User $user
   * @ignore
   *
   */
  protected $user;
  
  /**
   * Constructeur de l'objet
   * 
   * @param User|string $user ou $id
   */
  function __construct($user = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");
    // Définition du carnet d'adresse Mel
    $this->objectmelanie = new AddressbookMelanie();
    // Définition des objets associés
    if (isset($user)) {
      if (is_object($user)) {
        $this->user = $user;
        $this->objectmelanie->user_uid = $this->user->uid;
      }
      else {
        $this->objectmelanie->id = $user;
      }
    }
  }
  
  /**
   * Défini l'utilisateur Melanie
   * 
   * @param User $user          
   * @ignore
   *
   */
  public function setUserMelanie($user) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setUserMelanie()");
    $this->user = $user;
    $this->objectmelanie->user_uid = $this->user->uid;
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
    $ret = $this->objectmelanie->save();
    if (!is_null($ret) && isset($this->user)) {
      $this->user->cleanAddressbooks();
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
    if ($ret && isset($this->user)) {
      $this->user->cleanAddressbooks();
    }
    return $ret;
  }

  /**
   * Récupère la liste de tous les contacts
   * need: $this->id
   * 
   * @return Contact[]
   */
  public function getAllContacts() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getAllContacts()");
    $_contacts = $this->objectmelanie->getAllContacts();
    if (!isset($_contacts))
      return null;
    $contacts = [];
    $Contact = $this->__getNamespace() . '\\Contact';
    foreach ($_contacts as $_contact) {
      $contact = new $Contact($this->user, $this);
      $contact->setObjectMelanie($_contact);
      $contacts[$_contact->id] = $contact;
    }
    // Détruit les variables pour libérer le plus rapidement de la mémoire
    unset($_contacts);
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    return $contacts;
  }

  /**
   * Récupère la liste de tous les groupes
   * need: $this->id
   * 
   * @return Contact[]
   */
  public function getAllGroups() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getAllGroups()");
    $_contacts = $this->objectmelanie->getAllGroups();
    if (!isset($_contacts))
      return null;
    $contacts = [];
    $Contact = $this->__getNamespace() . '\\Contact';
    foreach ($_contacts as $_contact) {
      $contact = new $Contact($this->user, $this);
      $contact->setObjectMelanie($_contact);
      $contacts[$_contact->id] = $contact;
    }
    // Détruit les variables pour libérer le plus rapidement de la mémoire
    unset($_contacts);
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    return $contacts;
  }

  /**
   * Récupère la liste de tous les groupes et contacts
   * need: $this->id
   * 
   * @return Contact[]
   */
  public function getAllGroupsAndContacts() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getAllGroupsAndContacts()");
    $_contacts = $this->objectmelanie->getAllGroupsAndContacts();
    if (!isset($_contacts))
      return null;
    $contacts = [];
    $Contact = $this->__getNamespace() . '\\Contact';
    foreach ($_contacts as $_contact) {
      $contact = new $Contact($this->user, $this);
      $contact->setObjectMelanie($_contact);
      $contacts[$_contact->id] = $contact;
    }
    // Détruit les variables pour libérer le plus rapidement de la mémoire
    unset($_contacts);
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    return $contacts;
  }
  
  /**
   * ***************************************************
   * DATA MAPPING
   */
  /**
   * Mapping carddavurl field
   */
  protected function getMapCarddavurl() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapCarddavurl()");
    if (!isset($this->objectmelanie)) throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    $url = null;
    if (Config::is_set(Config::ADDRESSBOOK_CARDDAV_URL)) {
      $url = str_replace(['%u', '%o', '%i'], [$this->user->uid, $this->objectmelanie->owner, $this->objectmelanie->id], Config::get(Config::ADDRESSBOOK_CARDDAV_URL));
    }
    return $url;
  }
}