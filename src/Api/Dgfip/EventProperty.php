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

use LibMelanie\Api\Defaut;

/**
 * Classe pour la gestion des propriétés des évènements
 * Permet d'ajouter de nouvelles options aux évènements
 * implémente les API de la librairie pour aller chercher les données dans la base de données
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/DGFIP
 * @api
 * 
 * @property string $event Identifiant de l'évènement associé
 * @property string $calendar Identifiant du calendrier associé à l'évènement
 * @property string $user Identifiant de l'utilisateur
 * @property string $key Clé pour l'accès à la propriété, elle doit être unique pour cet évènement
 * @property string $value Valeur associé à la clé
 * @method bool load() Chargement la priopriété, en fonction de l'évènement, du calendrier associé, de l'utilisateur et de la clé
 * @method bool exists() Test si la priopriété existe, en fonction de l'évènement, du calendrier associé, de l'utilisateur et de la clé
 * @method bool save() Sauvegarde la priopriété dans la base de données
 * @method bool delete() Supprime la priopriété, en fonction de l'évènement, du calendrier associé, de l'utilisateur et de la clé
 */
class EventProperty extends Defaut\EventProperty {}