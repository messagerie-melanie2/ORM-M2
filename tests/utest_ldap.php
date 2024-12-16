<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * Ce fichier est un exemple d'utilisation
 *
 * ORM M2 Copyright © 2017  PNE Annuaire et Messagerie/MEDDE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Librairie Mélanie2
 * @subpackage Tests
 * @author PNE Messagerie/Apitech
 *
 */
var_dump(gc_enabled());
ini_set('zend.enable_gc', 1);
var_dump(gc_enabled());

$temps_debut = microtime_float();
declare(ticks = 1);

function microtime_float() {
	return array_sum(explode(' ', microtime()));
}

// Configuration du nom de l'application pour l'ORM
if (!defined('CONFIGURATION_APP_LIBM2')) {
  define('CONFIGURATION_APP_LIBM2', 'roundcube');
}

// // Définition des inclusions
set_include_path(__DIR__.'/..');
include_once 'includes/libm2.php';

use LibMelanie\Ldap\Ldap;

$ldap = Ldap::GetInstance(LibMelanie\Config\Ldap::$SEARCH_LDAP);

$nomEnt = "SG/DNUM/UNI/DETN/GSIL";
$baseDn = "ou=PPSRL,ou=GSIL,ou=DETN,ou=UNI,ou=DNUM,ou=SG,ou=AC,ou=melanie,ou=organisation,dc=equipement,dc=gouv,dc=fr";
$resAttrib = array("mineqTypeEntree", "cn", "departmentNumber", "mineqOrdreAffichage", "postalCode", "dn");
$filterLdap = '(& (departmentNumber=' . $nomEnt . '*) (| (mineqTypeEntree=NUNI) (mineqTypeEntree=NSER) (mineqTypeEntree=BALI)) (mineqPortee=50))';

$sr = $ldap->search_alias($baseDn, $filterLdap, $resAttrib, 0, 0);
if ($sr && $ldap->count_entries($sr) >= 1) {
    $infos = $ldap->get_entries($sr);
    foreach ($infos as $info) {
        echo $info['dn'] . "\r\n";
    }
}
else {
    echo "Pas de résultat\r\n";
}