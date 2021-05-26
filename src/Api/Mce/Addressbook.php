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
namespace LibMelanie\Api\Mce;

use LibMelanie\Api\Defaut;

/**
 * Classe de carnet d'adresses pour MCE
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/MCE
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
class Addressbook extends Defaut\Addressbook {}