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

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

$post = new \LibMelanie\Api\Defaut\Posts\Post();
$post->workspace = 'un-espace-2';
// $post->id = 2;
// $image = $post->firstImage();

// echo "Image: ".$image->uid."\r\n";

$posts = $post->listPosts('#livre #manga creator:damien.cotton.i', [], 'comments', false, 10, 0, ['la4YyVqydAX83U6YOVIQ0kGs', 'VLoLXqikjC3I0RqFlhNtbgu9']);

foreach ($posts as $post) {
  echo "Post: ".$post->id."\r\n";
  echo "Uid: ".$post->uid."\r\n";
  echo "Title: ".$post->title."\r\n";
  echo "Summary: ".$post->summary."\r\n";
  echo "Created: ".$post->created."\r\n";
  echo "Modified: ".$post->modified."\r\n";
  echo "Creator: ".$post->creator."\r\n";
  echo "Workspace: ".$post->workspace."\r\n";
  echo "Reactions: ". $post->reactions."\r\n";
  echo "Commentaires: ". $post->comments."\r\n";
  echo "Likes: ". $post->likes."\r\n";
  echo "Dislikes: ".$post->dislikes."\r\n";
  echo "########################\r\n";
}


echo "#### 1: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "#### Cycles: ".gc_collect_cycles()." ######\r\n";
echo "#### 2: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "#### Peak: ".(memory_get_peak_usage(true)/1024/1024) . ' MiB'." ######\r\n";

$temps_fin = microtime_float();
echo "#####################################\r\n";
echo "DUREE EXECUTION: ".round($temps_fin - $temps_debut, 4)."\r\n";
echo "#####################################\r\n";

