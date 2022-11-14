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
namespace LibMelanie\Api\Mi;

use LibMelanie\Api\Mce;
use LibMelanie\Api\Mi\Users\Outofoffice;
use LibMelanie\Api\Mi\Users\Share;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\MappingMce;

/**
 * Classe utilisateur pour MI
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/MI
 * @api
 * 
 * @property string $dn DN de l'utilisateur dans l'annuaire            
 * @property string $uid Identifiant unique de l'utilisateur
 * @property string $fullname Nom complet de l'utilisateur
 * @property string $name Nom de l'utilisateur
 * @property string $type Type de boite (voir Mce\Users\Type::*)
 * @property string $email Adresse email principale de l'utilisateur
 * @property array $email_list Liste de toutes les adresses email de l'utilisateur
 * @property string $email_send Adresse email d'émission principale de l'utilisateur
 * @property array $email_send_list Liste de toutes les adresses email d'émission de l'utilisateur
 * @property Share[] $shares Liste des partages de la boite
 * @property-read array $supported_shares Liste des droits supportés par cette boite
 * @property string $away_response Message d'absence de l'utilisateur (TODO: Objet pour traiter la syntaxe)
 * @property integer $internet_access_admin Accés Internet positionné par l'administrateur
 * @property integer $internet_access_user Accés Internet positionné par l'utilisateur
 * @property-read boolean $internet_access_enable Est-ce que l'accès Internet de l'utilisateur est activé
 * @property array $server_routage Champ de routage pour le serveur de message de l'utilisateur
 * @property-read string $server_host Host du serveur de messagerie de l'utilisateur
 * @property-read string $server_user User du serveur de messagerie de l'utilisateur
 * 
 * @property-read boolean $is_objectshare Est-ce que cet utilisateur est en fait un objet de partage
 * @property-read ObjectShare $objectshare Retourne l'objet de partage lié à cet utilisateur si s'en est un
 * 
 * @property-read boolean $is_synchronisation_enable Est-ce que la synchronisation est activée pour l'utilisateur ?
 * @property-read string $synchronisation_profile Profil de synchronisation positionné pour l'utilisateur (STANDARD ou SENSIBLE)
 * 
 * @method string getTimezone() [OSOLETE] Chargement du timezone de l'utilisateur
 * @method bool authentification($password, $master = false) Authentification de l'utilisateur sur l'annuaire Mélanie2
 * @method bool save() Enregistrement de l'utilisateur dans l'annuaire
 * @method bool load() Charge les données de l'utilisateur depuis l'annuaire Mélanie2 (en fonction de l'uid ou l'email)
 * @method bool exists() Est-ce que l'utilisateur existe dans l'annuaire Mélanie2 (en fonction de l'uid ou l'email)
 */
class User extends Mce\User {
  /**
   * Filtre pour la méthode getBalpEmission()
   * 
   * @ignore
   */
  const GET_BALP_EMISSION_FILTER = "(|(mcedelegation=%%uid%%:C)(mcedelegation=%%uid%%:G))";
  /**
   * Filtre pour la méthode getBalpGestionnaire()
   * 
   * @ignore
   */
  const GET_BALP_GESTIONNAIRE_FILTER = "(mcedelegation=%%uid%%:G)";
  
  /**
   * Configuration du mapping qui surcharge la conf
   */
  const MAPPING = [
    "dn"                      => 'dn',                            // DN de l'utilisateur
    "uid"                     => 'uid',                           // Identifiant de l'utilisateur
    "fullname"                => 'cn',                            // Nom complet de l'utilisateur
    "name"                    => 'cn',                            // Nom court de l'utilisateur
    "email"                   => 'mail',                          // Adresse e-mail principale de l'utilisateur en reception
    "email_list"              => [MappingMce::name => 'mailalternateaddress', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en reception pour l'utilisateur
    "email_send"              => 'mail',                          // Adresse e-mail principale de l'utilisateur en emission
    "email_send_list"         => [MappingMce::name => 'mailalternateaddress', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en émission pour l'utilisateur
    "shares"                  => [MappingMce::name => 'mcedelegation', MappingMce::type => MappingMce::arrayLdap], // Liste des partages pour cette boite
    "server_routage"          => 'mailhost',                      // Champ utilisé pour le routage des messages
    "type"                    => 'mcetypecompte',                 // Type d'entrée (boite individuelle, partagée, ressource, ...)
    "street"                  => 'postaladdress',                        // Rue
    "postalcode"              => 'postalcode',                    // Code postal
    "locality"                => 'l',                             // Ville
    "title"                   => 'title',                         // Titre
    "outofoffices"            => [MappingMce::name => 'mcevacation', MappingMce::type => MappingMce::arrayLdap], // Affichage du message d'absence de l'utilisateur
    "service"                 => 'departmentnumber',              // Department Number

    // Nouveaux champs
    "lastname"                => 'sn',                            // Last name de l'utilisateur
    "firstname"               => 'givenname',                     // First name de l'utilisateur
    "phonenumber"             => 'telephonenumber',               // Numéro de téléphone
    "faxnumber"               => 'facsimiletelephonenumber',      // Numéro de fax
    "mobilephone"             => 'mobile',                        // Numéro de mobile
    "personaltitle"           => 'personaltitle',                 // Genre

    "mcevisibilite"           => [MappingMce::name => 'mcevisibilite', MappingMce::type => MappingMce::arrayLdap], // Gestion de l'affichage dans l'annuaire ministériel et interministériel

    "email_routage"           => 'mailroutingaddress',         // Email pour le routage interne
    "quota"                   => 'mailquotasize',                 // Taille de quota pour la boite
    "delegation"              => 'mceportaildelegation',                    // Delegation
    "delegationtarget"        => [MappingMce::name => 'mceportaildelegationtarget', MappingMce::type => MappingMce::arrayLdap],                    // Delegation
    "mceaccess"               => 'mceaccess',                     // Acces distant
    "mcedomain"               => 'mcedomain',                     // Domaine interne
    "nomadeaccess"            => 'nomadeaccess',                  // Acces VPN
    "password"                => 'userpassword',                  // Mot de passe
    "mceportailpassworddelay" => 'mceportailpassworddelay',       // Délai d'expiration du mot de passe
    "passwordexpirationtime"  => 'passwordexpirationtime',        // Date d'expiration du mot de passe
    "mceportailmethodauth"    => 'mceportailmethodauth',          // Method d'authentification

    "ou"                      => 'ou',                            // OU associé à l'entrée
    "gestionnaire"            => 'gestionnaire',                  // Gestionnaire de la boite
    "matricule"               => 'matricule',                     // Matricule de l'utilisateur
    "employeenumber"          => 'employeenumber',                // Employee number de l'utilisateur
    "profil"                  => 'mceportailprofil',
  ];

  /**
   * ***************************************************
   * DATA MAPPING
   */
  /**
   * Mapping shares field
   *
   * @param Share[] $shares
   */
  protected function setMapShares($shares) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapShares()");
    if (!isset($this->objectmelanie)) {
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    }
    $this->_shares = $shares;
    $_shares = [];
    foreach ($shares as $share) {
      $right = '';
      switch ($share->type) {
        case Share::TYPE_ADMIN:
          $right = 'G';
          break;
        case Share::TYPE_SEND:
          $right = 'C';
          break;
        case Share::TYPE_WRITE:
          $right = 'E';
          break;
        case Share::TYPE_READ:
          $right = 'L';
          break;
      }
      $_shares[] = $share->user . ':' . $right;
    }
    $this->objectmelanie->shares = $_shares;
  }

  /**
   * Mapping shares field
   * 
   * @return Share[] Liste des partages positionnés sur cette boite
   */
  protected function getMapShares() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapShares()");
    if (!isset($this->_shares)) {
      $_shares = $this->objectmelanie->shares;
      $this->_shares = [];
      if (is_array($_shares)) {
        foreach ($_shares as $_share) {
          $share = new Share();
          list($share->user, $right) = \explode(':', $_share, 2);
          switch (\strtoupper($right)) {
            case 'G':
              $share->type = Share::TYPE_ADMIN;
              break;
            case 'C':
              $share->type = Share::TYPE_SEND;
              break;
            case 'E':
              $share->type = Share::TYPE_WRITE;
              break;
            case 'L':
              $share->type = Share::TYPE_READ;
              break;
          }
          $this->_shares[$share->user] = $share;
        }
      }
    }
    return $this->_shares;
  }

  /**
   * Récupération du champ out of offices
   * 
   * @return Outofoffice[] Tableau de d'objets Outofoffice
   */
  protected function getMapOutofoffices() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapOutofoffices()");
    $objects = [];
    if (is_array($this->objectmelanie->outofoffices)) {
      $i = 0;
      foreach ($this->objectmelanie->outofoffices as $oof) {
        $object = new Outofoffice($oof);
        if (isset($object->days)) {
          $key = Outofoffice::HEBDO.$i++;
        }
        else {
          $key = $object->type;
        }
        $objects[$key] = $object;
      }
    }
    return $objects;
	}

  /**
   * Positionnement du champ out of offices
   * 
   * @param Outofoffice[] $OofObjects
   */
  protected function setMapOutofoffices($OofObjects) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapOutofoffices()");
    $reponses = [];
    if (is_array($OofObjects)) {
      foreach ($OofObjects as $OofObject) {
        $reponses[] = $OofObject->render();
      }
    }
    $this->objectmelanie->outofoffices = array_unique($reponses);
	}

  /**
   * Mapping is_synchronisation_enable field
   * 
   * @return boolean true si la synchronisation est activée pour l'utilisateur
   */
  protected function getMapIs_synchronisation_enable() {
    return true;
  }

  /**
   * Mapping synchronisation_profile field
   * 
   * @return string Profil de synchronisation de l'utilisateur
   */
  protected function getMapSynchronisation_profile() {
    return 'STANDARD';
  }

  /**
   * Mapping shares field
   * 
   * @return array Liste des partages supportés par cette boite ([Share::TYPE_*])
   */
  protected function getMapSupported_shares() {
    return [Share::TYPE_ADMIN, Share::TYPE_SEND, Share::TYPE_WRITE, Share::TYPE_READ];
  }
}
