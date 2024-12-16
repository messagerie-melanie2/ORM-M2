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
 * Classe pièces jointes pour DGFIP
 * implémente les API de la librairie pour aller chercher les données dans la base de données
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/DGFIP
 * @api
 * 
 * @property string $id [TYPE_BINARY] Identifiant unique de la pièce jointe
 * @property int $modified [TYPE_BINARY] Timestamp de la modification de la pièce jointe
 * @property boolean $isfolder [TYPE_BINARY] Si l'objet est un dossier et non pas un fichier
 * @property string $name [TYPE_BINARY] Nom de la pièce jointe
 * @property string $path [TYPE_BINARY] Chemin vers la pièce jointe
 * @property string $owner [TYPE_BINARY] Propriétaire de la pièce jointe
 * @property string $data Données encodées de la pièce jointe
 * @property string $url URL vers la pièce jointe
 * @property Attachment::TYPE_* $type Type de la pièce jointe / Binaire ou URL (Binaire par défaut)
 * @property-read string $hash Lecture du HASH lié aux données de la pièce jointe (lecture seule)
 * @property-read int $size Taille en octet de la pièce jointe binaire (lecture seule)
 * @property-read string $contenttype Content type de la pièce jointe (lecture seule)
 * @method bool load() Chargement la pièce jointe, données comprises
 * @method bool exists() Test si la pièce jointe existe
 * @method bool save() Sauvegarde la pièce jointe si elle est de type binaire
 * @method bool delete() Supprime la pièce jointe binaire de la base
 */
class Attachment extends Defaut\Attachment {}