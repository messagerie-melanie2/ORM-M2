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
namespace LibMelanie\Api\Dgfip;

use LibMelanie\Api\Mce;
use LibMelanie\Api\Dgfip\Users\Share;
use LibMelanie\Log\M2Log;

/**
 * Classe utilisateur pour DGFIP
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/DGFIP
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
   * Récupère la liste des objets de partage accessibles à l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return ObjectShare[] Liste d'objets
   */
  public function getObjectsShared($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getObjectsShared() [" . $this->_server . "]");
    if (!isset($this->_objectsShared)) {
      if (isset($attributes) && is_string($attributes)) {
        $attributes = [$attributes];
      }
      if (!isset($attributes)) {
        $attributes = static::GET_BALP_ATTRIBUTES;
      }
      // Récupérer les shares
      $this->_objectsShared = [];
      $this->load(['shares']);
      $shares = $this->getShares();

      if (is_array($shares)) {
        foreach ($shares as $share) {
          // Genere la mailbox
          $mailbox = new User();
          $mailbox->uid = $share->user;
          if ($mailbox->load($attributes)) {
            // Genere l'object share à partir de la mailbox
            $objectShare = new ObjectShare();
            $objectShare->setMailbox($mailbox, $this);
            $this->_objectsShared[$objectShare->uid] = $objectShare;
          }        
        }
      }
      $this->executeCache();
    }
    return $this->_objectsShared;
  }

  /**
   * Récupère la liste des boites partagées à l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return User[] Liste de boites
   */
  public function getShared($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getShared() [" . $this->_server . "]");
    if (!isset($this->_shared)) {
      if (isset($attributes) && is_string($attributes)) {
        $attributes = [$attributes];
      }
      if (!isset($attributes)) {
        $attributes = static::GET_BALP_ATTRIBUTES;
      }
      // Récupérer les shares
      $this->_objectsShared = [];
      $this->load(['shares']);
      $shares = $this->getShares();

      if (is_array($shares)) {
        foreach ($shares as $share) {
          // Genere la mailbox
          $mailbox = new User();
          $mailbox->uid = $share->user;
          $this->_shared[$mailbox->uid] = $mailbox;
        }
      }
      $this->executeCache();
    }
    return $this->_shared;
  }

  /**
   * Récupère la liste des objets de partage accessibles au moins en émission à l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return ObjectShare[] Liste d'objets
   */
  public function getObjectsSharedEmission($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getObjectsSharedEmission() [" . $this->_server . "]");
    if (!isset($this->_objectsSharedEmission)) {
      if (isset($attributes) && is_string($attributes)) {
        $attributes = [$attributes];
      }
      if (!isset($attributes)) {
        $attributes = static::GET_BALP_EMISSION_ATTRIBUTES;
      }
      // Récupérer les shares
      $this->_objectsShared = [];
      $this->load(['shares']);
      $shares = $this->getShares();

      if (is_array($shares)) {
        foreach ($shares as $share) {
          if ($share->type == Share::TYPE_READ || $share->type == Share::TYPE_WRITE) {
            continue;
          }
          // Genere la mailbox
          $mailbox = new User();
          $mailbox->uid = $share->user;
          if ($mailbox->load($attributes)) {
            // Genere l'object share à partir de la mailbox
            $objectShare = new ObjectShare();
            $objectShare->setMailbox($mailbox, $this);
            $this->_objectsShared[$objectShare->uid] = $objectShare;
          }        
        }
      }
      $this->executeCache();
    }
    return $this->_objectsSharedEmission;
  }

  /**
   * Récupère la liste des boites accessibles au moins en émission à l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return User[] Liste d'objets
   */
  public function getSharedEmission($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedEmission() [" . $this->_server . "]");
    if (!isset($this->_objectsSharedEmission)) {
      if (isset($attributes) && is_string($attributes)) {
        $attributes = [$attributes];
      }
      if (!isset($attributes)) {
        $attributes = static::GET_BALP_EMISSION_ATTRIBUTES;
      }
      // Récupérer les shares
      $this->_objectsShared = [];
      $this->load(['shares']);
      $shares = $this->getShares();

      if (is_array($shares)) {
        foreach ($shares as $share) {
          if ($share->type == Share::TYPE_READ || $share->type == Share::TYPE_WRITE) {
            continue;
          }
          // Genere la mailbox
          $mailbox = new User();
          $mailbox->uid = $share->user;
          $this->_shared[$mailbox->uid] = $mailbox;
        }
      }
      $this->executeCache();
    }
    return $this->_objectsSharedEmission;
  }

  /**
   * Récupère la liste des objets de partage accessibles en tant que gestionnaire pour l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return ObjectShare[] Liste d'objets
   */
  public function getObjectsSharedGestionnaire($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getObjectsSharedGestionnaire() [" . $this->_server . "]");
    if (!isset($this->_objectsSharedGestionnaire)) {
      if (isset($attributes) && is_string($attributes)) {
        $attributes = [$attributes];
      }
      if (!isset($attributes)) {
        $attributes = static::GET_BALP_GESTIONNAIRE_ATTRIBUTES;
      }
      // Récupérer les shares
      $this->_objectsShared = [];
      $this->load(['shares']);
      $shares = $this->getShares();

      if (is_array($shares)) {
        foreach ($shares as $share) {
          if ($share->type != Share::TYPE_ADMIN) {
            continue;
          }
          // Genere la mailbox
          $mailbox = new User();
          $mailbox->uid = $share->user;
          if ($mailbox->load($attributes)) {
            // Genere l'object share à partir de la mailbox
            $objectShare = new ObjectShare();
            $objectShare->setMailbox($mailbox, $this);
            $this->_objectsShared[$objectShare->uid] = $objectShare;
          }        
        }
      }
      $this->executeCache();
    }
    return $this->_objectsSharedGestionnaire;
  }

  /**
   * Récupère la liste des boites accessibles en tant que gestionnaire pour l'utilisateur
   * 
   * @param array $attributes [Optionnal] List of attributes to load
   *
   * @return User[] Liste d'objets
   */
  public function getSharedGestionnaire($attributes = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getSharedGestionnaire() [" . $this->_server . "]");
    if (!isset($this->_objectsSharedGestionnaire)) {
      if (isset($attributes) && is_string($attributes)) {
        $attributes = [$attributes];
      }
      if (!isset($attributes)) {
        $attributes = static::GET_BALP_GESTIONNAIRE_ATTRIBUTES;
      }
      // Récupérer les shares
      $this->_objectsShared = [];
      $this->load(['shares']);
      $shares = $this->getShares();

      if (is_array($shares)) {
        foreach ($shares as $share) {
          if ($share->type != Share::TYPE_ADMIN) {
            continue;
          }
          // Genere la mailbox
          $mailbox = new User();
          $mailbox->uid = $share->user;
          $this->_shared[$mailbox->uid] = $mailbox;
        }
      }
      $this->executeCache();
    }
    return $this->_objectsSharedGestionnaire;
  }

  /**
   * Récupération de la liste des partages de l'objet
   * 
   * @return Share[] Liste des partages de l'objet
   */
  protected function getShares() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapShares()");
    if (!isset($this->_shares)) {
      $_shares = $this->objectmelanie->shares;
      $this->_shares = [];
      foreach ($_shares as $_share) {
        $share = $this->_share_process($_share);
        $this->_shares[$share->user] = $share;
      }
    }
    return $this->_shares;
  }

  /**
   * Traitement du droit dans l'entrée
   * TODO: A affiner une fois la syntaxe comprise
   * 
   * @param string $shareEntry
   * @return Share
   */
  private function _share_process($shareEntry) {
    $share = new Share();
    list($code, $type, $bal, $list) = \explode(';', $shareEntry, 4);
    list($right, $share->user) = \explode(':', $bal, 2);
    switch (\strtoupper($right)) {
      case 'GBAL':
        $share->type = Share::TYPE_ADMIN;
        break;
    }
    return $share;
  }
}
