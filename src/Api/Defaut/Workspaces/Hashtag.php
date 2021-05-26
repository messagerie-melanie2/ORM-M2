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
namespace LibMelanie\Api\Defaut\Workspaces;

use LibMelanie\Lib\MceObject;
use LibMelanie\Log\M2Log;
use LibMelanie\Objects\HashtagMelanie;

/**
 * Classe hashtag par defaut
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property integer $id Identifiant unique du hashtag
 * @property string $label Libellé du hashtag
 * 
 * @method bool load() Charge les données du hashtag depuis la base de données
 * @method bool exists() Est-ce que le hashtag existe dans la base de données ?
 * @method bool save() Enregistre le hashtag dans la base de données
 * @method bool delete() Supprime le hashtag de la base de données
 */
class Hashtag extends MceObject {
  /**
   * Accès aux objets associés
   * Workspace associé à l'objet
   * 
   * @var Workspace
   * @ignore
   *
   */
  protected $workspace;
  
  /**
   * Constructeur de l'objet
   * 
   * @param Workspace $workspace          
   */
  function __construct($workspace = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");
    // Définition du calendrier melanie2
    $this->objectmelanie = new HashtagMelanie;
    
    // Définition des objets associés
    if (isset($workspace)) {
      $this->workspace = $workspace;
    }
  }
  
  /**
   * Défini le workspace associé
   * 
   * @param Workspace $workspace          
   * @ignore
   *
   */
  public function setWorkspace($workspace) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setWorkspace()");
    $this->workspace = $workspace;
  }
}