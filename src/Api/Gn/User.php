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
namespace LibMelanie\Api\Gn;

use LibMelanie\Api\Mce;
use LibMelanie\Api\Gn\Users\Outofoffice;
use LibMelanie\Api\Gn\Users\Share;
use LibMelanie\Log\M2Log;
use LibMelanie\Config\MappingMce;

/**
 * Classe utilisateur pour GN
 * basé sur le User MCE
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/GN
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
   * Attributs par défauts pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_ATTRIBUTES = ['fullname', 'uid', 'name', 'email', 'email_list', 'email_send', 'email_send_list', 'server_routage', 'shares', 'type','mcemailroutingaddress','outofoffices'];

	/**
   * Configuration du mapping qui surcharge la conf
   */
  const MAPPING = [
    "dn"                      => 'dn',                            // DN de l'utilisateur
    "uid"                     => 'mail',                          // Identifiant de l'utilisateur
    "fullname"                => 'cn',                            // Nom complet de l'utilisateur
    "name"                    => 'displayname',                   // Display name de l'utilisateur
    "lastname"                => 'sn',                            // Last name de l'utilisateur
    "firstname"               => 'givenname',                     // First name de l'utilisateur
    "email"                   => 'mail',                          // Adresse e-mail principale de l'utilisateur en reception
    "email_list"              => [MappingMce::name => 'mailalternateaddress', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en reception pour l'utilisateur
    "email_send"              => 'mail',                          // Adresse e-mail principale de l'utilisateur en emission
    "email_send_list"         => [MappingMce::name => 'mailalternateaddress', MappingMce::type => MappingMce::arrayLdap], // Liste d'adresses e-mail en émission pour l'utilisateur
    "shares"                  => [MappingMce::name => 'mcedelegation', MappingMce::type => MappingMce::arrayLdap], // Liste des partages pour cette boite
    "server_routage"          => [MappingMce::name => 'mailhost', MappingMce::type => MappingMce::arrayLdap], // Champ utilisé pour le routage des messages
    "type"                    => 'mcetypecompte',                 // Type d'entrée (boite individuelle, partagée, ressource, ...)
    "street"                  => 'street',                        // Rue
    "postalcode"              => 'postalcode',                    // Code postal
    "locality"                => 'l',                             // Ville
    "title"                   => 'title',                         // Titre
    "memberof"                => [MappingMce::name => 'memberof', MappingMce::type => MappingMce::arrayLdap],
    "outofoffices"            => [MappingMce::name => 'mcevacation', MappingMce::type => MappingMce::arrayLdap], // Affichage du message d'absence de l'utilisateur
    "mcemailroutingaddress"   => [MappingMce::name => 'mcemailroutingaddress', MappingMce::type => MappingMce::arrayLdap], // routegemceadrressmail host
    "deliverymode"            => [MappingMce::name => 'deliverymode', MappingMce::type => MappingMce::stringLdap],
    "codeunite"               => 'codeunite',
    "displayname"             => 'displayname', 
    "employeenumber"          => 'employeenumber',
    "givenname"               => 'givenname',
  ];

  /**
   * ***************************************************
   * DATA MAPPING
   */
  /**
   * Mapping uid field
   *
   * @param string $uid
   */
  protected function setMapUid($uid) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapUid(" . (is_string($uid) ? $uid : "") . ")");
    if (!isset($this->objectmelanie)) {
      throw new \LibMelanie\Exceptions\ObjectMelanieUndefinedException();
    }
    if (strpos($uid, 'uid=') === 0) {
      // C'est un dn utilisateur
      $this->objectmelanie->dn = $uid;
    }
    else if (strpos($uid, '@') !== false) {
      // C'est une adresse e-mail et l'uid GN
      $this->objectmelanie->email = $uid;
      $this->objectmelanie->uid = $uid;
    }
    else {
      $this->objectmelanie->uid = $uid;
    }
  }

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
        $right = $share->type;
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
      foreach ($_shares as $_share) {
        $share = new Share();
        list($share->user, $right) = \explode(':', $_share, 2);
        $share->type = \strtoupper($right);
        $this->_shares[$share->user] = $share;
      }
    }
    return $this->_shares;
  }

  /**
   * Mapping shares field
   * 
   * @return array Liste des partages supportés par cette boite ([Share::TYPE_*])
   */
  protected function getMapSupported_shares() {
    return [Share::TYPE_ADMIN, Share::TYPE_SEND, Share::TYPE_WRITE, Share::TYPE_READ];
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
}
