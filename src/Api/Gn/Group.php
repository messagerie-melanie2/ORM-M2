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
use LibMelanie\Ldap\Ldap;
use LibMelanie\Config\Ldap as LdapConfig;

/**
 * Classe groupe LDAP pour GN
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/GN
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
 * @property string $cn CN
 * @property string $description Description
 * @property string $gidnumber GidNumber
 * @property string $codeunite CodeUnite
 */
class Group extends Defaut\Group {
    /**
     * Attributs par défauts pour la méthode load()
     * 
     * @ignore
     */
    const LOAD_ATTRIBUTES = ['dn', 'fullname', 'email', 'owners'];

    /**
     * TODO le cn devrait être récupérable directement
     * renvoi le cn
     * @return mixed|null
     * @throws \Safe\Exceptions\PcreException
     */
    public function getMapCn() {
        if (\Safe\preg_match("#(cn=)(.*)(,dmd*)#",$this->dn, $matches)) {
            return $matches[2];
        } else {
            return null;
        }
    }

    /**
     * Récupère la liste des membres d'un groupe
     * On ne "load" pas les membres, car certains groupes sont trop grand => trop de requêtes
     * 
     * @return array|Defaut\User[]
     */
    public function getMapMembers() {
        if (!isset($this->_members)) {
            $this->_members = [];

            $ldap = Ldap::GetInstance(LdapConfig::$SEARCH_LDAP);
            $filter = $ldap->getConfig("get_users_by_group");
            $filter = str_replace("%%memberOf%%", $this->dn, $filter);
            $search = $ldap->search($ldap->getConfig("base_dn"), $filter);
            $entries = $ldap->get_entries($search);

            $attributesMember = $ldap->getConfig('get_user_infos_attributes');

            if ($entries
                    && is_array($entries)
                    && $entries['count'] > 0) {
                array_shift($entries);
                foreach ($entries as $i => $entry) {
                    $member = new Member();
                    foreach ($attributesMember as $k) {
                        if (isset($entry[$k])) {
                            $member->$k = $entry[$k][0];
                        } else {
                            $member->$k = null;
                        }
                    }
                    $this->_members[] = $member;
                }
            }
        }
        return $this->_members;
    }
}