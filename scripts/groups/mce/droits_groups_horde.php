<?php
/*********************************************************************
 * Script permettant de voir les groupes LDAP utilisés en 
 * permission par horde pour l'affichage kronolith et turba
 * Va aussi permettre d'avoir les stats sur le nombre d'utilisateurs concernés
 *********************************************************************/

#################DEFINITIONS INITIALES####################
// Recuperation du temps de depart
$temps_debut = microtime_float();

// l'usage des ticks est nécessaire depuis PHP 4.3.0
declare(ticks = 1);

// Installation des gestionnaires de signaux
pcntl_signal(SIGTERM, "sig_handler");
###########################################################


##################CONFIGURATION#########################

// Liste des Backend Horde a traiter
// Format : tableau nom backend => horde.shares.<backend>
$groups_uid = array ( 
	'Calendriers' => 'horde.shares.kronolith', 
	'Contacts' => 'horde.shares.turba' );

// Configuration des traces
$cfg_trace = false;

// Configuration du filtre des groupes non auto racine
$non_auto_racine = false;

// Configuration du mode bouchon : si true aucune donnees n'est inseree dans la base de donnees
$mode_bouchon = false;

// Configuration du mode test : affichage des creations/mises a jour/suppressions pour les testeurs
$mode_test = false;

// Configuration du mode debug pour l'ecriture dans un fichier
$mode_debug = true;
$debug_file = '/var/log/script_groups.log';

// Configuration de la connexion a la base de donnees
$_conf['sql'] = array(
	'hostspec' => '<hostname>',
	'password' => '<password>',
	'database' => '<database>',
	'port' => 5432,
	'username' => '<username>'
);

// Configuration de la connexion au serveur LDAP
$_conf['ldap'] = array(
    'host' => '<hostname>', //host name of your LDAP Server
    'port' => '<port>', //port
    'baseDNUsers' => '<base_dn>' //where to look at valid user
);

##############################################################

#########################FONCTIONS GENERIQUES##################
// Recuperation du temps
function microtime_float() {
	return array_sum(explode(' ', microtime()));
}

// Gestion du fichier de logs
function log_error($message) {
	global $debug_file, $mode_debug;
	$time = date('d-M-Y H:i:s');
	if ($mode_debug) error_log("[$time] $message\r\n", 3, $debug_file);
}

// gestionnaire de signaux système
function sig_handler($signo)
{
	global $temps_debut;
	switch ($signo) {
		case SIGTERM:
			// gestion de l'extinction
			$temps_fin = microtime_float();
			$temps_exec = round($temps_fin - $temps_debut, 2);
			trigger_error("Timeout: Fin de signal envoye au bout de $temps_exec sec");
			die();
			break;
		default:
			// gestion des autres signaux
	}
}
###############################################################


##################TRAITEMENT#########################
$message = "Demarrage du traitement";
log_error($message);

if ($cfg_trace) {
  echo "Lancement du script de statistique pour le nombre de droits sur les groupes<br />";
  echo "--------------------------------------------------<br /><br />";
  echo "Connexion a la base de donnees...<br />";
  echo "--------------------------------------------------<br /><br />";
}

// Conexion |  la base SQL
$connectString = "host=".$_conf['sql']['hostspec']." dbname=".$_conf['sql']['database']." user=".$_conf['sql']['username']." password=".$_conf['sql']['password']." port=".$_conf['sql']['port']." connect_timeout=1";
//$dbconn = pg_pconnect($connectString);
$dbconn = pg_connect($connectString);

if ($cfg_trace) { echo "----|||||------------------------------15%<br /><br />"; }

// Test si la connexion reussie
if (!$dbconn) {
  // Si la connexion echoue, erreur
  $message = "Erreur de connexion a la base de donnees";
  log_error($message);
  die();
}
if ($cfg_trace) { echo "----||||||||||||||||-----------------------40%<br /><br />"; }

// Test si la connexion est accessible
$stat = pg_connection_status($dbconn);
if ($stat !== PGSQL_CONNECTION_OK) {
  // Si la connexion est inaccessible on fait un reset
  $dbconn = pg_connection_reset($dbconn);
  if (!$dbconn) {
    // Le reset a echoue
    $message = "Erreur de connexion a la base de donnees, reset failed";
    log_error($message);
    die();
  }
}

if ($cfg_trace) { echo "----|||||||||||||||||||||||||||||||||||-------70%<br /><br />"; }

// Test si la connexion est disponible
$bs = pg_connection_busy($dbconn);
if ($bs) {
  $message = "Erreur de connexion a la base de donnees, reset failed";
  pg_close($dbconn);
  log_error($message);
  die();
}

if ($cfg_trace) {
  echo "----|||||||||||||||||||||||||||||||||||||||||||---100%<br /><br />";
  echo "Connecte<br />";
  echo "--------------------------------------------------<br /><br />";
  echo "Connexion au serveur LDAP...<br />";
  echo "--------------------------------------------------<br /><br />";
}

// Connexion au serveur LDAP
$ds = ldap_connect ($_conf['ldap']['host'], $_conf['ldap']['port']);
if (!$ds) {
  // erreur de connexion au ldap
  $message = "Erreur de connexion au LDAP";
  pg_close($dbconn);
  log_error($message);
  die();
}

if ($cfg_trace) {
  echo "Connecte<br />";
  echo "--------------------------------------------------<br /><br />";
  echo "Binding de la connexion au serveur LDAP...<br />";
  echo "--------------------------------------------------<br /><br />";
}

// Binding
$bs = ldap_bind ($ds);

if ($cfg_trace) {
  echo "Bind<br />";
  echo "--------------------------------------------------<br /><br />";
}

foreach ($groups_uid as $group_name => $group_uid) {

  // Requete sql permettant de recuperer toutes les donnees liees aux droits sur les groupes
  $query = "SELECT hda.datatree_id, user_uid, datatree_name, attribute_name, attribute_key, attribute_value FROM horde_datatree hd ";
  $query .= "INNER JOIN horde_datatree_attributes hda ";
  $query .= "ON hd.datatree_id = hda.datatree_id ";
  $query .= "WHERE hd.group_uid = '" . $group_uid . "' ";
  $query .= "AND (hda.attribute_name = 'perm_groups' OR hda.attribute_name = 'perm_usersfg')  ";
  $query .= "ORDER BY datatree_name, attribute_name; ";

  if ($cfg_trace) {
    echo "Execution de la requete...<br />";
    echo $query."<br />";
    echo "--------------------------------------------------<br /><br />";
  }

  $result = pg_query($dbconn, $query);
  if (!$result) {
    $message = "Erreur dans la requete : $query";
    log_error($message);
    die();
  }
  if ($cfg_trace) {
    echo "Traitement des resultats<br /><br />";
    echo "--------------------------------------------------<br /><br />";
  }

  // Filtres LDAP
  $findString = "mceRDN=_agents_majauto_";
  $restriction_search = array( "memberuid" );
  $restriction_read = array( "mcetypeentree" );
  $filtre_read = "objectClass=*";

  // Compteurs
  $countDroits = 0;
  $countGroup = 0;
  $countLigne = 0;
  $countGroupNoRoot = 0;
  $countInsert = 0;
  $countUpdate = 0;
  $countDelete = 0;

  // Tableau de stockage des elements
  $membersArray = array();

  while ($row = pg_fetch_object($result)) {
    $countLigne++;
  
    // Gestion des groupes
    if ($row->attribute_name == 'perm_groups') {
      // Split le résultat pour récupérer le DN et le filtre
      $arrayValues = explode(',', $row->attribute_key, 2);

      if (count($arrayValues) == 2) {
        $countGroup++;    

        // DN
        $baseDnUsers = $arrayValues[1];
        // Filtre
        $filtre = $arrayValues[0];

        if ($cfg_trace) {
          echo "<br />Filtre: $filtre <br />";
          echo "DN: $baseDnUsers <br />";
          echo "Group: " . $row->attribute_key . "<br />";
          echo "Droits: " . $row->attribute_value . "<br />";
          echo "-------------<br />";
        }

        if ($non_auto_racine) {
          // Filtre des groupes ayant des autos racine en majauto
          if (strpos($row->attribute_key,$findString) !== FALSE) {
            $lr = @ldap_read ($ds, $baseDnUsers, $filtre_read, $restriction_read);
            $info = @ldap_get_entries($ds, $lr);
            if ($info['count'] > 0 && $info[0]['mcetypeentree'] && is_array($info[0]['mcetypeentree'])) {
              if ($cfg_trace) { echo "mcetypeentree: " . $info[0]['mcetypeentree'][0] . "<br />"; }
              if ($info[0]['mcetypeentree'][0] != 'NUNI') continue;
            }
          }
        }

        // Lancement de la recherche des membres
        //$ls = @ldap_search ($ds, $baseDnUsers, $filtre, $restriction_search);
        // Problème de scope : utilisation du one level
        $ls = @ldap_list($ds, $baseDnUsers, $filtre, $restriction_search);
        $info = @ldap_get_entries($ds, $ls);

        if ($info['count'] > 0 && isset($info[0]['memberuid']) && is_array($info[0]['memberuid'])) {
          $countGroupNoRoot++;
          if ($cfg_trace) { echo "UID: " . $row->user_uid . "<br />Calendar: " . $row->datatree_name . "<br />Membres: " . var_export($info[0]['memberuid'], true). "<br /><br />"; }
          $countDroits += $info[0]['memberuid']['count'];

          // Boucle sur l'insertion des droits sur les groupes
          for ($i = 0; $i < $info[0]['memberuid']['count']; $i++) {
            $member = $info[0]['memberuid'][$i];
            if (!isset($membersArray[$row->datatree_name])) $membersArray[$row->datatree_name] = array();
            if (!isset($membersArray[$row->datatree_name]['perm_groups'])) $membersArray[$row->datatree_name]['perm_groups'] = array();

            // Traitement des droits les plus eleves si un membre est dans plusieurs groupes avec des droits differents sur un meme calendrier
            if (!isset($membersArray[$row->datatree_name]['perm_groups'][$member]) || $membersArray[$row->datatree_name]['perm_groups'][$member] < $row->attribute_value) $membersArray[$row->datatree_name]['perm_groups'][$member] = $row->attribute_value;
          }
 
          if ($cfg_trace) { echo "-------------<br /><br />"; }
        }
      }
    }
    // Gestion des users from group
    else if ($row->attribute_name == 'perm_usersfg') {
      $usersfbMembers[$row->attribute_key] = $row->attribute_value;
      if (!isset($membersArray[$row->datatree_name])) $membersArray[$row->datatree_name] = array();
      if (!isset($membersArray[$row->datatree_name]['perm_usersfg'])) $membersArray[$row->datatree_name]['perm_usersfg'] = array();
      $membersArray[$row->datatree_name]['perm_usersfg'][$row->attribute_key] = $row->attribute_value;
    }

    if (isset($membersArray[$row->datatree_name]) && !isset($membersArray[$row->datatree_name]['datatree_id']))
      $membersArray[$row->datatree_name]['datatree_id'] = $row->datatree_id;

  }

  // Lancement du traitement des droits sur les donnees collectees
  traitementDroits ($membersArray, $group_name, $group_uid);
  
  $message = "[$group_name] Traitement / insert : $countInsert / update : $countUpdate / delete : $countDelete";
  log_error($message);
}

/*****************************************************************************************************************
 * Fonction de traitement des droits recuperes depuis le LDAP
 *
 * Va comparer les droits sur les groupes et les droit individuels pour lancer les requetes insert/update/delete
 *
*******************************************************************************************************************/
function traitementDroits ($membersArray, $group_name, $group_uid) {
  global $countInsert, $countUpdate, $countDelete, $dbconn, $cfg_trace;

  // Stockage des donnees pour les requetes sql
  $aInsert = array ();
  $aUpdate = array ();
  $aDelete = array ();

  if ($cfg_trace) { echo "Demarrage du traitement ... <br /><br />"; }
  if ($cfg_trace) { echo "---------------------------------- <br /><br />"; }

  // Generation des donnees pour le traitement SQL
  foreach ($membersArray as $datatree_name => $array_values) {
    if ($cfg_trace) { echo "datatree_id : ".$array_values['datatree_id']." <br />"; }
    if ($cfg_trace) { echo "datatree_name : ".$datatree_name." <br /><br />"; }

    // Si au moins un droit sur un groupe est postionne
    if (isset($array_values['perm_groups'])) {
      // Recuperation des droits positionnes individuellement
      $query_members = "SELECT attribute_key, attribute_value FROM horde_datatree INNER JOIN horde_datatree_attributes USING (datatree_id) WHERE group_uid = '" . $group_uid . "' AND datatree_name = '" . $datatree_name . "' AND attribute_name = 'perm_users';";

      $result_members = pg_query($dbconn, $query_members);
      if (!$result_members) {
        $message = "Erreur dans la requête : \"" . $query_members . "\"";
        log_error($message);
        if ($cfg_trace) { echo "$message <br />"; }
        return -1;
      }
      $array_values['perm_users'] = array();

      // Recuperation des droits individuels dans notre tableau
      while ($row_members = pg_fetch_object($result_members)) {
        $array_values['perm_users'][$row_members->attribute_key] = $row_members->attribute_value;
      }

      // Parcour les droits sur les groupes pour la mise en place des donnees
      foreach ($array_values['perm_groups'] as $member_group => $droit_member_group) {
        // Deux cas, pour chaque droit sur les groupes, un droit individuel est postionne ou non
        if (isset($array_values['perm_users'][$member_group])) {
          // Si un droit individuel est postionne on ne doit pas avoir de droit fg. Donc s'il existe on le supprime
          if (isset($array_values['perm_usersfg']) && isset($array_values['perm_usersfg'][$member_group])) {
            $aDelete[$member_group] = $array_values['perm_usersfg'][$member_group];
            if ($cfg_trace) { echo "aDelete[member_group] = array_values['perm_usersfg'][member_group]; <br />"; }
          }
        }
        else {
          // Si un droit individuel n'est pas postionne on doit avoir un droit fg
          if (isset($array_values['perm_usersfg']) && isset($array_values['perm_usersfg'][$member_group])) {
            // Si le droit positonne est different du droit sur le groupe, c'est un update
            if ($array_values['perm_usersfg'][$member_group] != $droit_member_group) {
              $aUpdate[$member_group] = $droit_member_group;
              if ($cfg_trace) { echo "aUpdate[member_group] = droit_member_group; <br />"; }
            }
          }
          else {
            // si le droit n'est pas postionne c'est un insert
            $aInsert[$member_group] = $droit_member_group;
            if ($cfg_trace) { echo "aInsert[member_group] = droit_member_group; <br />"; }
          }
        }
        if (isset($array_values['perm_usersfg']) && isset($array_values['perm_usersfg'][$member_group]))
          unset($array_values['perm_usersfg'][$member_group]);
      }

      // Parcour les droits fg restant pour la suppression
      if (isset($array_values['perm_usersfg'])) {
        foreach ($array_values['perm_usersfg'] as $member_fg => $droit_member_fg) {
          $aDelete[$member_fg] = $droit_member_fg;
        }
      }

    }
    else {
      // Si aucun droit sur un groupe n'est positionne, on supprime tous les droits fg (s'ils existent)
      $aDelete = isset($array_values['perm_usersfg']) ? $array_values['perm_usersfg'] : array(); 
      if ($cfg_trace) { echo "array_values['perm_usersfg'] <br />"; }
    }
    
    // Lancement des requetes sql de traitement
    $countInsert += insertAcls ($array_values['datatree_id'], $aInsert, $group_name);
    $countUpdate += updateAcls ($array_values['datatree_id'], $aUpdate, $group_name);
    $countDelete += deleteAcls ($array_values['datatree_id'], $aDelete, $group_name);
    $aInsert = array ();
    $aUpdate = array ();
    $aDelete = array ();
 
    if ($cfg_trace) { echo "---------------------------------- <br /><br />"; }
  }
}

/******************
 * Execute les requetes d'insertion
******************/
function insertAcls ($datatree_id, $aInsert, $group_name) {
  global $cfg_trace, $dbconn, $mode_bouchon, $mode_test;

  $count = 0;
  foreach ($aInsert as $member => $value) {
    $query = "INSERT INTO horde_datatree_attributes VALUES ($datatree_id, 'perm_usersfg', '$member', '$value');";

    log_error("[$group_name][$datatree_id] Creation du droit pour $member a $value");
    if ($cfg_trace) { echo "$query <br />"; }
    else if ($mode_test) { echo "[$group_name][$datatree_id] Creation du droit pour $member a $value <br />"; }
    if (!$mode_bouchon) {
      $result = pg_query($dbconn, $query);
      if (!$result) { 
        if ($cfg_trace) { echo "Erreur dans la requête : \"" . $query . "\" <br />"; } 
        continue;
      }
    }
    $count++;
  } 
  return $count;
}

/******************
 * Execute les requetes d'update
******************/
function updateAcls ($datatree_id, $aUpdate, $group_name) {
  global $cfg_trace, $dbconn, $mode_bouchon, $mode_test;

  $count = 0;
  foreach ($aUpdate as $member => $value) {
    $query = "UPDATE horde_datatree_attributes SET attribute_value = '$value' WHERE datatree_id = $datatree_id AND attribute_name = 'perm_usersfg' AND attribute_key = '$member';";

    log_error("[$group_name][$datatree_id] Mise a jour du droit pour $member a $value");
    if ($cfg_trace) { echo "$query <br />"; }
    else if ($mode_test) { echo "[$group_name][$datatree_id] Mise a jour du droit pour $member a $value <br />"; }
    if (!$mode_bouchon) {
      $result = pg_query($dbconn, $query);
      if (!$result) { 
        if ($cfg_trace) { echo "Erreur dans la requête : \"" . $query . "\" <br />"; } 
        continue;
      }
    }
    $count++;
  }
  return $count;
}

/******************
 * Execute les requetes de delete
******************/
function deleteAcls ($datatree_id, $aDelete, $group_name) {
  global $cfg_trace, $dbconn, $mode_bouchon, $mode_test;

  $count = 0;
  foreach ($aDelete as $member => $value) {
    $query = "DELETE FROM horde_datatree_attributes WHERE datatree_id = $datatree_id AND attribute_name = 'perm_usersfg' AND attribute_key = '$member' AND attribute_value = '$value';";

    log_error("[$group_name][$datatree_id] Suppression du droit pour $member a $value");
    if ($cfg_trace) { echo "$query <br />"; }
    else if ($mode_test) { echo "[$group_name][$datatree_id] Suppression du droit pour $member a $value <br />"; }
    if (!$mode_bouchon) {
      $result = pg_query($dbconn, $query);
      if (!$result) { 
         if ($cfg_trace) { echo "Erreur dans la requête : \"" . $query . "\" <br />"; } 
         continue;
      }
    }
    $count++;
  }
  return $count;
}

$temps_fin = microtime_float();

$temps_exec = round($temps_fin - $temps_debut, 2);

if ($cfg_trace) {
  echo "--------------------------------------------------<br />\n";
  echo "Total de lignes retournees par la requete SQL : ".$countLigne."  <br />\n";
  echo "Total des droits sur groupes existants et non auto racine : $countGroupNoRoot  <br />\n";
  echo "Total des droits a ajouter : $countDroits  <br />\n";
  echo "Total d'insert dans la base de donnees : $countInsert  <br />\n";
  echo "Total d'update dans la base de donnees : $countUpdate  <br />\n";
  echo "Total de delete dans la base de donnees : $countDelete  <br />\n";
  echo "Duree execution du script : $temps_exec  <br />\n";
  echo "--------------------------------------------------<br />\n";
}

$message = "Fin du traitement en $temps_exec secondes";
log_error($message);

if ($cfg_trace) {
  echo "Fermeture connexions<br /><br />";
  echo "--------------------------------------------------<br /><br />";
}
$cs = ldap_close ($ds);
pg_close($dbconn);

if ($cfg_trace) { echo "--------------------------------------------------<br /><br />"; }



