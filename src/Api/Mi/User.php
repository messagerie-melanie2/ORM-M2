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
   * ***************************************************
   * DATA MAPPING
   */
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
        if ($object->type == Outofoffice::TYPE_ALL) {
          $key = $object->type.$i++;
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
