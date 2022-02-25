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
use LibMelanie\Log\M2Log;
use LibMelanie\Objects\ObjectMelanie;

/**
 * Classe de gestion des notifications pour le Bnum
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $uid Identifiant unique de la notification
 * @property string $owner Propriétaire de la notification (uid utilisateur ou service)
 * @property string $from Origine de la notification
 * @property string $title Titre de la notification
 * @property string $content Contenu de la notification
 * @property integer $modified timestamp de création/modification de la notification
 * @property string $category Catégorie de la notification
 * @property string $action Bouton d'action pour la notification
 * @property boolean $isread Est-ce que la notification a été lue ?
 * @property boolean $isdeleted Est-ce que la notification est supprimée ?
 * 
 * @method bool load() Charge les données de la notification depuis la base de données
 * @method bool exists() Est-ce que la notification existe dans la base de données ?
 * @method bool save() Enregistre la notification dans la base de données
 * @method bool delete() Supprime la notification de la base de données
 */
class Notification extends MceObject {
  /**
   * Constructeur de l'objet
   * 
   * @param \LibMelanie\Api\Defaut\User $user Utilisateur
   */
  public function __construct($user = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");
    // Définition de l'objet
    $this->objectmelanie = new ObjectMelanie('Notification');

    if (isset($user)) {
      $this->owner = $user->uid;
    }
  }  
}