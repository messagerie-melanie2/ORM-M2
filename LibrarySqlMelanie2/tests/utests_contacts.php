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

use LibMelanie\Api\Mce\User;
use LibMelanie\Api\Mce\Contact;
use LibMelanie\Api\Mce\Addressbook;
use LibMelanie\Config\MappingMce;
use LibMelanie\Log\M2Log;
use LibMelanie\Api\Mce\AddressbookSync;

$log = function ($message) {
	echo "[LibM2] $message \r\n";
};
M2Log::InitDebugLog($log);
M2Log::InitErrorLog($log);

echo "########################\r\n";

$user = new User();
$user->uid = 'julien.test2';

$addressbook = new Addressbook($user);
$addressbook->id = 'julien.test2';
$addressbook->load();

echo "SyncToken = " . $addressbook->synctoken . "\r\n\r\n";

$addressbooksync = new AddressbookSync($addressbook);
$addressbooksync->token = null;

echo var_export($addressbooksync->listAddressbookSync(), true);


// $contact = new Contact($user, $addressbook);
// //$contact->uid = '20150402100009.8164365ihcjmnzt5@roundcube';
// $contact->uid = "20150402095827.19575mo7ac0phpyb@roundcube";
// // $contact->alias = '%o%';

// // $contacts = $contact->getList(
// // 		['uid', 'name', 'addressbook', 'alias'],
// // 		'(#alias#) AND #addressbook#',
// // 		[
// // 				'uid' => MappingMce::like,
// // 				'alias' => MappingMce::like]
// // );

// // var_dump($contacts);

// if ($contact->load()) {
//   echo $contact->vcard;
// }

// $contact2 = new Contact($user, $addressbook);
// $contact2->vcard = $contact->vcard;
// var_dump($contact2);

// $addressbooks = $user->getSharedAddressbooks();
// var_dump($addressbooks);

// $defaultAddressbook = $user->getDefaultAddressbook();
// var_dump($defaultAddressbook);

// $addressbook = $addressbooks[0];
// $contacts = $addressbook->getAllContacts();
// var_dump(count($contacts));

// $contact = new Contact($user, $addressbooks[0]);
// $contact->lastname = 'Payen';
// $contact->firstname = 'Thomas';
// $contact->email = 'thomas.payen@i-carre.net';
// $contact->email1 = 'thomas.payen@apitech.fr';
// $contact->uid = '20130327134709.17573f3hb5qlnln1@zp.ac.melanie2.i2';
// $contact->homephone = '04 78 95 96 56';
// $contact->homecity = 'Lyon';
// $contact->homestreet = 'JF Raclet';
// $contact->homecountry = 'FR';
// $contact->homepostalcode = '69 000';
// $contact->workphone = '04 72 65 95 68';
// $contact->workstreet = 'Saint Theobald';
// $contact->workcity = 'L Isle D Abeau';
// $contact->workcountry = 'FR';
// $contact->workprovince = 'Isère';
// $contact->workpostalcode = '38 080';
// $contact->cellphone = '06 69 85 95 65';
// $contact->company = 'Apitech';
// $contact->role = 'Developper';
// $contact->id = md5($contact->uid.$addressbook->id);
// $contact->modified = time();

// $contact->save();
// var_dump($contact);

echo "#### 1: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "#### Cycles: ".gc_collect_cycles()." ######\r\n";
echo "#### 2: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "#### Peak: ".(memory_get_peak_usage(true)/1024/1024) . ' MiB'." ######\r\n";

$temps_fin = microtime_float();
echo "#####################################\r\n";
echo "DUREE EXECUTION: ".round($temps_fin - $temps_debut, 4)."\r\n";
echo "#####################################\r\n";

