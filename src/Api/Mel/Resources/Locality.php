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
namespace LibMelanie\Api\Mel\Resources;

use LibMelanie\Api\Defaut;

/**
 * Classe locality pour les ressources MTE
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property string $dn     DN de la localite dans l'annuaire            
 * @property string $uid     Identifiant de la localite
 * @property string $name   Nom de la localite
 * 
 * @method bool save()      Enregistrement de la localite dans l'annuaire
 * @method bool load()      Chargement de la localite dans l'annuaire
 * @method bool delete()    Suppression de la localite dans l'annuaire
 */
class Locality extends Defaut\Resources\Locality {

  // **** Configuration des filtres et des attributs par défaut
  /**
   * Filtre pour la méthode load()
   * 
   * @ignore
   */
  const LOAD_FILTER = "(ou=%%uid%%)";

  /**
   * Filtre pour la méthode listAllLocalities()
   * 
   * @ignore
   */
  const LIST_LOCALITIES_FILTER = "(objectClass=organizationalUnit)";
  /**
   * Filtre pour la méthode listResources()
   * 
   * @ignore
   */
  const LIST_RESOURCES_FILTER = "(objectClass=mineqMelSA)";
  /**
   * Filtre pour la méthode listResources() par type
   * 
   * @ignore
   */
  const LIST_RESOURCES_BY_TYPE_FILTER = "(&(objectClass=mineqMelSA)(sn=%%type%%))";

  /**
   * DN a utiliser comme base pour les requetes
   */
  const DN = 'ou=Ressources,ou=BNUM,ou=applications,ou=ressources,dc=equipement,dc=gouv,dc=fr';

  /**
   * Configuration du mapping qui surcharge la conf
   */
  const MAPPING = [
    "uid"      => 'ou',             // Identifiant de la localite
    "name"     => 'description',    // Name de la ressource
  ];
}
