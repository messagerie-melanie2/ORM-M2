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

use LibMelanie\Config\MappingMce;
use LibMelanie\Log\M2Log;
use LibMelanie\Objects\CalendarMelanie;
use LibMelanie\Sql\Sql;

/**
 * Classe calendrier pour GN
 *
 * @author Groupe Messagerie/MTE - Apitech
 * @author Groupe Messagerie/GN - OpenGroupe
 * @author patrick.bassoukissa_sounda@open-groupe.com
 * @package LibMCE
 * @subpackage API/GN
 * @api
 *
 */
class Calendar extends CalendarMelanie {


    public int $id;


    /**
     * Calendar constructor.
     * @param null $user
     */
    public function __construct($user = null)
    {
        $this->objectType = "CalendarMelanie";
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
        $maps = MappingMce::$Data_Mapping[$this->objectType];
        $n = MappingMce::name;
        $query = SqlGnRequests::listObjectsById;


        $query = str_replace('{user_uid}',  $maps['owner'][$n], $query);
        $query = str_replace('{datatree_name}', $maps['name'][$n], $query);
        $query = str_replace('{datatree_ctag}', $maps['ctag'][$n], $query);
        $query = str_replace('{datatree_synctoken}', $maps['synctoken'][$n], $query);
        $query = str_replace('{datatree_id}', $maps['id'][$n], $query);


        // Params
        $params = [
            "datatree_id" => $this->id
        ];

        $sql = Sql::GetInstance();
        // Liste les calendriers de l'utilisateur
        $r = $sql->executeQueryToObject($query, $params, $this);

        if ($this->isExist) {
            $this->initializeHasChanged();
        }
        $this->isExist = true;
        $this->isLoaded = true;
        return $this->isExist;
    }


}
