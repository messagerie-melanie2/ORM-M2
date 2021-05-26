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

use LibMelanie\Api\Defaut;

/**
 * Classe pour la gestion des droits
 * Permet d'ajouter de nouveaux partages sur la lib MCE
 * implémente les API de la librairie pour aller chercher les données dans la base de données
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/MI
 * @api
 * 
 * @property string $object_id Identifiant de l'objet utilisé pour le partage
 * @property string $name Utilisateur ou groupe auquel est associé le partage
 * @property Share::TYPE_* $type Type de partage
 * @property Share::ACL_* $acl Niveau d'acl, utilisé sous forme ACL_WRITE | ACL_FREEBUSY
 * @method bool load() Chargement du partage, en fonction de l'object_id et du nom
 * @method bool exists() Test si le partage existe, en fonction de l'object_id et du nom
 * @method bool save() Sauvegarde la priopriété dans la base de données
 * @method bool delete() Supprime le partage, en fonction de l'object_id et du nom
 */
class Share extends Defaut\Share {}