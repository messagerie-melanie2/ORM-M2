<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * ORM Mél Copyright © 2020 Groupe Messagerie/MTES
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
namespace LibMelanie\Api\Melanie2;

use LibMelanie\Lib\Melanie2Object;
use LibMelanie\Objects\AddressbookMelanie;
use LibMelanie\Objects\UserMelanie;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\Config;

/**
 * Classe de carnet d'adresses pour Melanie2
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage API Mélanie2
 *             @api
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
class Addressbook extends Melanie2Object {
  /**
   * Accès aux objets associés
   * UID de l'utilisateur du calendrier
   * 
   * @var string $usermelanie
   * @ignore
   *
   */
  public $usermelanie;
  
  /**
   * Constructeur de l'objet
   * 
   * @param UserMelanie $usermelanie          
   */
  function __construct($usermelanie = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");
    // Définition du carnet d'adresse melanie2
    $this->objectmelanie = new AddressbookMelanie();
    // Définition des objets associés
    if (isset($usermelanie)) {
      $this->usermelanie = $usermelanie;
      $this->objectmelanie->user_uid = $this->usermelanie->uid;
    }
  }
  
  /**
   * Défini l'utilisateur Melanie
   * 
   * @param UserMelanie $usermelanie          
   * @ignore
   *
   */
  public function setUserMelanie($usermelanie) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setUserMelanie()");
    $this->usermelanie = $usermelanie;
    $this->objectmelanie->user_uid = $this->usermelanie->uid;
  }
  
  /**
   * ***************************************************
   * METHOD MAPPING
   */
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
    foreach ($_contacts as $_contact) {
      $contact = new Contact($this->usermelanie, $this);
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
      $url = str_replace(['%u', '%o', '%i'], [$this->usermelanie->uid, $this->objectmelanie->owner, $this->objectmelanie->id], Config::get(Config::ADDRESSBOOK_CARDDAV_URL));
    }
    return $url;
  }
}