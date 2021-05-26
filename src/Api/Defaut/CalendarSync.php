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
namespace LibMelanie\Api\Defaut;

use LibMelanie\Lib\MceObject;
use LibMelanie\Objects\ObjectMelanie;
use LibMelanie\Log\M2Log;

/**
 * Classe pour la gestion des Sync pour les calendriers
 * Certains champs sont mappés directement ou passe par des classes externes
 *
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * 
 * @property integer $token Numéro de token associé à l'objet Sync
 * @property string $calendar Identifiant du calendrier associé à l'objet Sync
 * @property string $uid UID de l'événement concerné par le Sync
 * @property string $action Action effectuée sur l'uid (add, mod, del)
 * 
 * @method bool load() Chargement du CalendarSync, en fonction du calendrier et du token
 * @method bool exists() Test si le CalendarSync existe, en fonction du calendrier et du token
 * @method CalendarSync[] listCalendarSync($syncToken, $limit) Permet de lister les CalendarSync d'un calendrier
 */
class CalendarSync extends MceObject {

  // Constantes
  const RECURRENCE_ID = '@RECURRENCE-ID';
  
  /**
   * Mapping des actions entre la base et SabreDAV
   *
   * @var array
   */
  private static $actionMapper = [
      'add' => 'added',
      'mod' => 'modified',
      'del' => 'deleted'
  ];

  /**
   * Constructeur de l'objet
   *
   * @param Calendar $calendar
   */
  function __construct($calendar = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);

    // M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->__construct()");
    // Définition de la propriété de l'objet
    $this->objectmelanie = new ObjectMelanie('CalendarSync');

    // Définition des objets associés
    if (isset($calendar)) {
      $this->objectmelanie->calendar = $calendar->id;
    }
  }

  /**
   * ***************************************************
   * METHOD MAPPING
   */
  /**
   * Ne pas implémenter la sauvegarde pour l'instant
   * Le SyncToken est alimenté par le trigger
   *
   * @return boolean
   */
  function save() {
    return false;
  }
  /**
   * Ne pas implémenter la suppression pour l'instant
   * Le SyncToken est alimenté par le trigger
   *
   * @return boolean
   */
  function delete() {
    return false;
  }

  /**
   * Liste les actions par uid depuis le dernier token
   *
   * @param integer $limit
   *          [Optionnel]
   * @param string $startDate
   *          [Optionnel] Date de debut pour les événements retournés
   */
  public function listCalendarSync($limit = null, $startDate = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->listCalendarSync($limit)");
    $result = [
        'added' => [],
        'modified' => [],
        'deleted' => []
    ];
    if (isset($this->token)) {
      $operators = [
          'token' => \LibMelanie\Config\MappingMce::sup
      ];
      $_calendarSyncs = $this->objectmelanie->getList(null, null, $operators, 'token', false, $limit);
      foreach ($_calendarSyncs as $_calendarSync) {
        $mapAct = self::$actionMapper[$_calendarSync->action];
        // MANTIS 0004667: [CalendarSync] Nettoyer les uid en @RECURRENCE-ID
        $uid = $_calendarSync->uid;
        if (strpos($uid, self::RECURRENCE_ID) !== false) {
          $uid = substr($uid, 0, strlen($uid) - 23);
          $mapAct = self::$actionMapper['mod'];
        }
        // MANTIS 0004696: [SyncToken] Ne retourner qu'un seul uid
        $uid = $this->uidencode($uid) . '.ics';
        if (!in_array($uid, $result['added'])
            && !in_array($uid, $result['modified'])
            && !in_array($uid, $result['deleted'])) {
          $result[$mapAct][] = $uid;
        }        
      }
    } else {
      $Event = $this->__getNamespace() . '\\Event';
      $event = new $Event();
      $event->calendar = $this->objectmelanie->calendar;
      if (isset($startDate)) {
        $event->start = $startDate;
        $Recurrence = $this->__getNamespace() . '\\Recurrence';
        $event->recurrence->type = $Recurrence::RECURTYPE_NORECUR;
        $event->recurrence->enddate = $startDate;
        $filter = "#calendar# AND (#start# OR (#type# AND #enddate#))";
        $events = $event->getList([
            'uid'
        ], $filter, [
            'calendar'  => \LibMelanie\Config\MappingMce::eq,
            'start'     => \LibMelanie\Config\MappingMce::sup,
            'type'      => \LibMelanie\Config\MappingMce::sup,
            'enddate'   => \LibMelanie\Config\MappingMce::supeq,
        ]);
      } else {
        $events = $event->getList([
            'uid'
        ]);
      }

      foreach ($events as $_event) {
        $result['added'][] = $this->uidencode($_event->uid) . '.ics';
      }
    }

    return $result;
  }

  /**
   * ***************************************************
   * PRIVATE
   */
  /**
   * Encodage d'un uid pour les uri (pour les / notamment)
   * @param string $uid
   * @return string
   */
  private function uidencode($uid) {
    $search = ['/'];
    $replace = ['%2F'];
    return str_replace($search, $replace, $uid);
  }
}
