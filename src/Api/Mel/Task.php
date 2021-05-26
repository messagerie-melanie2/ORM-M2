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
namespace LibMelanie\Api\Mel;

use LibMelanie\Api\Defaut;

/**
 * Classe tâche pour Mel,
 * implémente les API de la librairie pour aller chercher les données dans la base de données
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Mel
 * @api
 * 
 * @property string $id Identifiant unique de la tâche
 * @property string $taskslist Identifiant de la liste de tâches associée
 * @property string $uid UID de la tâche
 * @property string $owner Créateur de la tâche
 * @property string $name Nom de la tâche
 * @property string $description Description de la tâche
 * @property Task::PRIORITY_* $priority Priorité de la tâche
 * @property string $category Catégorie de la tâche
 * @property int $alarm Alarme en minute (TODO: class Alarm)
 * @property Task::COMPLETED_* $completed Tâche terminée
 * @property Task::CLASS_* $class Class de la tâche (privé/public)
 * @property string $assignee Utilisateur à qui est assigné la tâche
 * @property int $estimate Estimation de la tâche ?
 * @property string $parent ID de la tâche parente
 * @property int $due Timestamp correspondant à la date de fin prévue
 * @property int $completed_date Timestamp correspondant à la date de fin réelle
 * @property int $start Timestamp correspondant à la date de début
 * @property int $modified Timestamp de la modification de la tâche
 *           Liste des attributs :
 * @property int $percent_complete Pourcentage de réalisation pour la tâche
 * @property string $status Status de la tâche
 * @property string $ics ICS associé à l'évènement courant, calculé à la volée en attendant la mise en base de données
 * @method bool load() Chargement l'évènement, en fonction du taskslist et de l'uid
 * @method bool exists() Test si l'évènement existe, en fonction du taskslist et de l'uid
 * @method bool save() Sauvegarde l'évènement et l'historique dans la base de données
 * @method bool delete() Supprime l'évènement et met à jour l'historique dans la base de données
 */
class Task extends Defaut\Task {}