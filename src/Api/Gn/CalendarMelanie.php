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
namespace LibMelanie\Api\Gn;

use LibMelanie\Api\Defaut;
use LibMelanie\Config\MappingMce;
use LibMelanie\Log\M2Log;
use LibMelanie\Sql\Sql;
use LibMelanie\Sql\SqlMelanieRequests;

/**
 * Classe calendrier pour GN
 *
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/GN
 * @api
 *
 */
class CalendarMelanie extends \LibMelanie\Objects\CalendarMelanie {


    public int $id;
    public \LibMelanie\Api\Mce\User $owner;



    public function __construct()
    {
        parent::__construct();
        $this->sql = Sql::GetInstance();
    }


    /**
     * La classe Mel\Calendar ne permet pas la récupération par l'id seul
     * on le fait ici
     * @return bool|null
     */
    public function load() {

        M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->load()");
        if (!isset($this->id)) return false;

        // Test si l'objet existe, pas besoin de load
        if (is_bool($this->isExist) && $this->isLoaded) {
            return $this->isExist;
        }
        $query = SqlGnRequests::listObjectsById;

        $query = str_replace('{user_uid}', MappingMce::$Data_Mapping[$this->objectType]['owner'][MappingMce::name], $query);
        $query = str_replace('{datatree_name}', MappingMce::$Data_Mapping[$this->objectType]['id'][MappingMce::name], $query);
        $query = str_replace('{datatree_ctag}', MappingMce::$Data_Mapping[$this->objectType]['ctag'][MappingMce::name], $query);
        $query = str_replace('{datatree_synctoken}', MappingMce::$Data_Mapping[$this->objectType]['synctoken'][MappingMce::name], $query);
        $query = str_replace('{attribute_value}', MappingMce::$Data_Mapping[$this->objectType]['name'][MappingMce::name], $query);
        $query = str_replace('{datatree_id}', MappingMce::$Data_Mapping[$this->objectType]['object_id'][MappingMce::name], $query);


//die($query);
        // Params
        $params = [
            "datatree_id" => $this->id
        ];




        // Liste les calendriers de l'utilisateur
        $r = $this->sql->executeQuery($query, $params, self::class);

        print_r($this);
        print $this->getCalendarName();
        die('kkkkkkkkkkkkkkkkkkkkkkkkkk');
        if ($this->isExist) {
            $this->initializeHasChanged();
        }








        // Les données sont chargées
        $this->isLoaded = true;
        return $this->isExist;
    }


    public function getOwner() {
        die('ici');
    }

}
