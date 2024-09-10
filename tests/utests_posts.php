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

// Enregistrer un nouveau post

// $post = new LibMelanie\Api\Defaut\Posts\Post();
// $post->uid = generateRandomString(24);
// $post->creator = 'thomas.test2';
// $post->title = 'Post de test 2';
// $post->summary = 'Ceci est un post de test 2';
// $post->content = 'Ceci est un post de test 2';
// $post->workspace = 'un-espace-2';

// $post->save();


// Charger tous les posts

$post = new \LibMelanie\Api\Defaut\Posts\Post();
$post->workspace = 'un-espace-2';

$posts = $post->getList();

if (!empty($posts)) {
	header('Content-Type: application/json');
	// Préparer les données des posts pour la réponse JSON
	$posts_array = [];
	foreach ($posts as $post) {
		$posts_array[] = [
			'uid' => $post->uid,
			'title' => $post->title,
			'summary' => $post->summary,
			'content' => $post->content,
			'creator' => $post->creator,
			'workspace' => $post->workspace
		];

		$uids[] = $post->uid;
	}
	echo "Posts trouvés: " . implode(",", $uids) . "\r\n";
} else {
	echo 'Aucun post trouvé.';
}

// Charger un post

$post = new LibMelanie\Api\Defaut\Posts\Post();
$post->uid = "ndWtChyQ4IwabbWjWwlM7Qo9";
if ($post->load()) {
  	echo "Post trouvé: " . $post->title . "\r\n";


	// Enregistrer un commentaire

	// $comment = new LibMelanie\Api\Defaut\Posts\Comment($post);
	// $comment->uid = generateRandomString(24);
	// $comment->creator = 'thomas.test3';
	// $comment->content = 'C\'est ma réponse au commentaire';
	// $comment->parent = 1;
	// $comment->save();

	echo "Nombre de commentaires : " . $post->countComments() . "\r\n";
	echo "Nombre de réactions : " . $post->countReactions() . "\r\n";

	foreach ($post->listComments() as $comment) {
		echo "Commentaire trouvé: " . $comment->content . "\r\n";
		echo "Nombre de like sur le commentaire : " . $comment->countLikes() . "\r\n";

		// Ajouter un like

		// $like = new LibMelanie\Api\Defaut\Posts\Comments\Like($comment);
		// $like->type = 'like';
		// $like->creator = 'thomas.test1';

		// $like->save();
	}
} else {
  echo "Post non trouvé\r\n";
}

echo "#### 1: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "#### Cycles: ".gc_collect_cycles()." ######\r\n";
echo "#### 2: ".(memory_get_usage()/1024/1024) . ' MiB'." ######\r\n";
echo "#### Peak: ".(memory_get_peak_usage(true)/1024/1024) . ' MiB'." ######\r\n";

$temps_fin = microtime_float();
echo "#####################################\r\n";
echo "DUREE EXECUTION: ".round($temps_fin - $temps_debut, 4)."\r\n";
echo "#####################################\r\n";

