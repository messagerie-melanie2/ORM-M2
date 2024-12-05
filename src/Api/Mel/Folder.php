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
use LibMelanie\Log\M2Log;
use LibMelanie\Config\MappingMce;

/**
 * Classe groupe LDAP pour Mel
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Mel
 * @api
 * 
 * @property string $dn DN du groupe l'annuaire 
 * @property string $fullname Nom complet du groupe LDAP
 * @property string $type Type de groupe (voir Mce\Users\Type::*)
 * @property string $email Adresse email principale de l'utilisateur
 * @property array $email_list Liste de toutes les adresses email de l'utilisateur
 * @property User[] $members Liste des membres appartenant au groupe
 * @property array $members_email Liste des adresses e-mail de la liste
 * @property array $owners Liste des propriétaires du groupe LDAP
 * @property string $service Service du groupe dans l'annuaire
 * @property-read boolean $is_dynamic Est-ce qu'il s'agit d'une liste dynamique ?
 */
class Folder extends Defaut\Folder
{
    /**
     * Configuration du délimiteur pour le server host
     * 
     * @var string
     */
    const SERVER_HOST_DELIMITER = '%';

    /**
     * Filtre pour la méthode load()
     * 
     * @ignore
     */
    const LOAD_FILTER = null;
    /**
     * Attributs par défauts pour la méthode load()
     * 
     * @ignore
     */
    const LOAD_ATTRIBUTES = ['dn', 'fullname', 'email', 'description'];

    /**
     * Configuration du mapping qui surcharge la conf
     */
    const MAPPING = User::MAPPING;

    /**
     * Configuration du mapping pour récupérer bon type de boite
     */
    const FOLDER_TYPE_MAPPING = ['NUNI' => 'BALU', 'NSER' => 'BALS'];

    /**
     * Configuration du mapping pour determiner le type d'enfant
     */
    const CHILDREN_TYPE_MAPPING = ['folder' => 'organizationalUnit', 'user' => 'mineqMelBoite', 'group' => 'mineqMelListe'];

    /**
     * Configuration du mapping pour l'ordre de l'annuaire
     */
    const CHILDREN_ORDER_TYPE_MAPPING = ['folder' => '0', 'user' => '1', 'fonction' => '2', 'group' => '3', 'other' => '4'];
}
