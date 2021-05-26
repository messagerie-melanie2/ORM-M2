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
 * Classe evenement pour Mel,
 * implémente les API de la librairie pour aller chercher les données dans la base de données
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Mel
 * @api
 * 
 * @property string $name Nom de l'organisateur
 * @property string $calendar Calendrier de l'organisateur
 * @property-read Attendee[] $attendees Tableau d'objets Attendee pour l'organisateur (Lecture seule)
 * @property string $email Email de l'organisateur
 * @property string $uid Uid de l'organisateur
 * @property string $role Role de l'organisateur
 * @property string $partstat Statut de participation de l'organisateur
 * @property string $sent_by Sent-By pour l'organisateur
 * @property string $owner_email Email du owner du calendrier s'il est partagé
 * @property string $rsvp Repondez svp pour l'organisateur
 * @property bool $extern Boolean pour savoir si l'organisateur est externe au ministère
 */
class Organizer extends Defaut\Organizer {}