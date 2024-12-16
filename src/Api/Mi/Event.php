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
 * Classe evenement pour MI
 * implémente les API de la librairie pour aller chercher les données dans la base de données
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/MI
 * @api
 * 
 * @property string $id Identifiant unique de l'évènement
 * @property string $calendar Identifiant du calendrier de l'évènement
 * @property string $uid UID de l'évènement
 * @property string $owner Créateur de l'évènement
 * @property string $keywords Keywords
 * @property string $title Titre de l'évènement
 * @property string $description Description de l'évènement
 * @property string $category Catégorie de l'évènment
 * @property string $location Lieu de l'évènement
 * @property Event::STATUS_* $status Statut de l'évènement
 * @property Event::CLASS_* $class Class de l'évènement (privé/public)
 * @property Event::TRANSP_* $transparency Etat de transparence de l'événement
 * @property Event::PRIORITY_* $priority Priorité de l'événement
 * @property int $sequence Séquence de l'événement
 * @property int $alarm Alarme en minute (TODO: class Alarm)
 * @property Attendee[] $attendees Tableau d'objets Attendee
 * @property boolean $hasattendees Est-ce que cette instance de l'événement a des participants
 * @property string $start String au format compatible DateTime, date de début
 * @property string $end String au format compatible DateTime, date de fin
 * @property \DateTime $dtstart DateTime basée sur le champ $start
 * @property \DateTime $dtend DateTime basée sur le champ $end
 * @property-read \DateTime $dtstart_utc DateTime basée sur le champ $start au timezone UTC
 * @property-read \DateTime $dtend_utc DateTime basée sur le champ $end au timezone UTC
 * @property string $timezone Timezone de l'événement
 * @property boolean $all_day Est-ce que c'est un événement journée entière
 * @property int $created Timestamp de création de l'évènement
 * @property int $modified Timestamp de la modification de l'évènement
 * @property Recurrence $recurrence objet Recurrence
 * @property Organizer $organizer objet Organizer
 * @property Exception[] $exceptions Liste d'exception
 * @property Attachment[] $attachments Liste des pièces jointes associées à l'évènement (URL ou Binaire)
 * @property bool $deleted Défini si l'exception est un évènement ou juste une suppression
 * @property-read string $realuid UID réellement stocké dans la base de données (utilisé pour les exceptions) (Lecture seule)
 * @property string $ics ICS associé à l'évènement courant, calculé à la volée en attendant la mise en base de données
 * @property-read VObject\Component\VCalendar $vcalendar Object VCalendar associé à l'évènement, peut permettre des manipulations sur les récurrences
 * @property $move Il s'ajout d'un MOVE, les participants sont conservés
 * @method bool load() Chargement l'évènement, en fonction du calendar et de l'uid
 * @method bool exists() Test si l'évènement existe, en fonction du calendar et de l'uid
 * @method bool save() Sauvegarde l'évènement et l'historique dans la base de données
 * @method bool delete() Supprime l'évènement et met à jour l'historique dans la base de données
 */
class Event extends Defaut\Event {}