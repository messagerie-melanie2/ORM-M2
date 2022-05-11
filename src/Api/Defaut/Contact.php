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
use LibMelanie\Objects\ObjectMelanie;
use LibMelanie\Objects\HistoryMelanie;
use LibMelanie\Exceptions;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\Config;

/**
 * Classe contact par defaut,
 * implémente les API de la librairie pour aller chercher les données dans la base de données
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $id Identifiant unique du contact
 * @property string $addressbook Identifiant de la liste de contacts associée
 * @property string $uid UID du contact
 * @property string $type Type de l'objet
 * @property int $modified Timestamp de dernière modification du contact
 * @property string $members Membres de la liste ? (TODO: Peut être faire un tableau de Contact ?)
 * @property string $name Nom du contact
 * @property string $alias Surnom du contact
 * @property string $freebusyurl URL de Freebusy pour ce contact
 * @property string $firstname Prénom du contact
 * @property string $lastname Nom de famille du contact
 * @property string $middlenames Autres noms pour le contact
 * @property string $nameprefix Prefix du contact
 * @property string $namesuffix Suffix du contact
 * @property string $birthday Date d'anniversaire
 * @property string $title Titre du contact
 * @property string $company Entreprise du contact
 * @property string $notes Notes associées au contact
 * @property string $email Adresse e-mail
 * @property string $email1 Deuxième adresse e-mail
 * @property string $email2 Troisième adresse e-mail
 * @property string $cellphone Numéro de mobile
 * @property string $fax Numéro de fax
 * @property string $category Categorie du contact
 * @property string $url URL associée au contact
 * @property string $homeaddress Adresse du domicile
 * @property string $homephone Numéro de téléphone du domicile
 * @property string $homestreet Rue du domicile
 * @property string $homepob Boite aux lettres du domicile
 * @property string $homecity Ville du domicile
 * @property string $homeprovince Département du domicile
 * @property string $homepostalcode Code postal du domicile
 * @property string $homecountry Pays du domicile
 * @property string $workaddress Adresse du bureau
 * @property string $workphone Numéro de téléphone du bureau
 * @property string $workstreet Rue du bureau
 * @property string $workpob Boite aux lettres du bureau
 * @property string $workcity Ville du bureau
 * @property string $workprovince Département du bureau
 * @property string $workpostalcode Code postal du bureau
 * @property string $workcountry Pays du bureau
 * @property string $pgppublickey Clé publique du contact
 * @property string $smimepublickey SMIME pour la clé publique
 * @property string $photo Photo du contact
 * @property string $phototype Type du fichier photo
 * @property string $logo Logo du contact
 * @property string $logotype Type du fichier logo
 * @property string $timezone Timezone du contact
 * @property string $geo Geo
 * @property string $pager Pager
 * @property string $role Role du contact
 * @property string $vcard VCard associé au contact courant, calculé à la volée en attendant la mise en base de données
 * @method bool load() Chargement le contact, en fonction de l'addressbook et de l'uid
 * @method bool exists() Test si le contact existe, en fonction de l'addressbook et de l'uid
 * @method bool save() Sauvegarde le contact et l'historique dans la base de données
 * @method bool delete() Supprime le contact et met à jour l'historique dans la base de données
 */
class Contact extends MceObject {
  // Accès aux objets associés
  /**
   * Utilisateur associé à l'objet
   * 
   * @var User
   */
  protected $user;
  /**
   * Liste de contacts associée à l'objet
   * 
   * @var Addressbook
   */
  protected $addressbookmce;
  
  /**
   * **
   * CONSTANTES
   */
  // Type Fields
  const TYPE_CONTACT = 'Object';
  const TYPE_LIST = 'Group';
  
  /**
   * Constructeur de l'objet
   * 
   * @param User $user          
   * @param Addressbook $addressbook          
   */
  public function __construct($user = null, $addressbook = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    // M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->__construct()");
    // Définition du contact melanie2
    $this->objectmelanie = new ObjectMelanie('ContactMelanie');
    
    // Définition des objets associés
    if (isset($user))
      $this->user = $user;
    if (isset($addressbook)) {
      $this->addressbookmce = $addressbook;
      $this->objectmelanie->addressbook = $this->addressbookmce->id;
    }
  }
  
  /**
   * Défini l'utilisateur MCE
   * 
   * @param User $user          
   * @ignore
   *
   */
  public function setUserMelanie($user) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setUserMelanie()");
    $this->user = $user;
  }

  /**
   * Retourne l'utilisateur MCE
   * 
   * @return User
   * @ignore
   */
  public function getUserMelanie() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getUserMelanie()");
    return $this->user;
  }
  
  /**
   * Défini la liste de contacts Melanie
   * 
   * @param Addressbook $addressbook          
   * @ignore
   *
   */
  public function setAddressbookMelanie($addressbook) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setAddressbookMelanie()");
    $this->addressbookmce = $addressbook;
    $this->objectmelanie->addressbook = $this->addressbookmce->id;
  }

  /**
   * Récupère la liste de contacts Melanie
   * 
   * @return Addressbook         
   * @ignore
   *
   */
  public function getAddressbookMelanie() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getAddressbookMelanie()");
    return $this->addressbookmce;
  }
  
  /**
   * ***************************************************
   * METHOD MAPPING
   */
  /**
   * Mapping de la sauvegarde de l'objet
   * Appel la sauvegarde de l'historique en même temps
   * 
   * @ignore
   *
   */
  function save() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->save()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    // Sauvegarde l'objet
    $insert = $this->objectmelanie->save();
    if (!is_null($insert)) {
      // Gestion de l'historique
      $history = new HistoryMelanie();
      $history->uid = Config::get(Config::ADDRESSBOOK_PREF_SCOPE) . ":" . $this->objectmelanie->addressbook . ":" . $this->objectmelanie->uid;
      $history->action = $insert ? Config::get(Config::HISTORY_ADD) : Config::get(Config::HISTORY_MODIFY);
      $history->timestamp = time();
      $history->description = "LibM2/" . Config::get(Config::APP_NAME);
      $history->who = isset($this->user) ? $this->user->uid : $this->objectmelanie->addressbook;
      // Enregistrement dans la base
      return $history->save();
    }
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->save() Error: return false");
    return false;
  }
  
  /**
   * Mapping de la suppression de l'objet
   * Appel la sauvegarde de l'historique en même temps
   * 
   * @ignore
   *
   */
  function delete() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->delete()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    // Suppression de l'objet
    if ($this->objectmelanie->delete()) {
      // Gestion de l'historique
      $history = new HistoryMelanie();
      $history->uid = Config::get(Config::ADDRESSBOOK_PREF_SCOPE) . ":" . $this->objectmelanie->addressbook . ":" . $this->objectmelanie->uid;
      $history->action = Config::get(Config::HISTORY_DELETE);
      $history->timestamp = time();
      $history->description = "LibM2/" . Config::get(Config::APP_NAME);
      $history->who = isset($this->user) ? $this->user->uid : $this->objectmelanie->addressbook;
      // Enregistrement dans la base
      return $history->save();
    }
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    M2Log::Log(M2Log::LEVEL_ERROR, $this->get_class . "->delete() Error: return false");
    return false;
  }
  
  /**
   * Appel le load maitre
   * 
   * @ignore
   *
   */
  function load() {
    $ret = $this->objectmelanie->load();
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    return $ret;
  }
  
  /**
   * Permet de récupérer la liste d'objet en utilisant les données passées
   * (la clause where s'adapte aux données)
   * Il faut donc peut être sauvegarder l'objet avant d'appeler cette méthode
   * pour réinitialiser les données modifiées (propriété haschanged)
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
   * @return Contact[] Array
   */
  function getList($fields = [], $filter = "", $operators = [], $orderby = "", $asc = true, $limit = null, $offset = null, $case_unsensitive_fields = []) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getList()");
    $_contacts = $this->objectmelanie->getList($fields, $filter, $operators, $orderby, $asc, $limit, $offset, $case_unsensitive_fields);
    if (!isset($_contacts))
      return null;
    $contacts = [];
    foreach ($_contacts as $_contact) {
      $_contact->setIsExist();
      $_contact->setIsLoaded();
      $contact = new static($this->user, $this->addressbookmce);
      $contact->setObjectMelanie($_contact);
      $contacts[$_contact->id] = $contact;
    }
    // TODO: Test - Nettoyage mémoire
    //gc_collect_cycles();
    return $contacts;
  }
  
  /**
   * ***************************************************
   * DATA MAPPING
   */
  /**
   * Map vcard to current contact
   * 
   * @ignore
   *
   */
  protected function setMapVcard($vcard) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapVcard()");
    \LibMelanie\Lib\VCardToContact::Convert($vcard, $this, $this->addressbookmce, $this->user);
  }
  /**
   * Map current contact to vcard
   * 
   * @return string $vcard
   * @ignore
   *
   */
  protected function getMapVcard() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapVcard()");
    return \LibMelanie\Lib\ContactToVCard::Convert($this, $this->addressbookmce, $this->user);
  }
}