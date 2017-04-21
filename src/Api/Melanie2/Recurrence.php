<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * ORM M2 Copyright © 2017 PNE Annuaire et Messagerie/MEDDE
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
namespace LibMelanie\Api\Melanie2;

use LibMelanie\Objects\EventMelanie;
use LibMelanie\Lib\Melanie2Object;
use LibMelanie\Config\ConfigMelanie;
use LibMelanie\Config\MappingMelanie;
use LibMelanie\Exceptions;
use LibMelanie\Log\M2Log;
use LibMelanie\Lib\ICS;

/**
 * Classe recurrence pour Melanie2
 * Doit être lié à un objet Event pour écrire directement dans les API
 * Certains champs sont mappés directement
 * 
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage API Mélanie2
 *             @api
 * @property string $enddate Date de fin de récurrence au format compatible DateTime
 * @property int $count Nombre d'occurrences
 * @property int $interval Interval de répétition de la récurrence
 * @property Recurrence::RECURTYPE_* $type Type de récurrence
 * @property Recurrence::RECURDAYS_* $days Jours de récurrence
 * @property array $rrule Parses an iCalendar 2.0 recurrence rule
 */
class Recurrence extends Melanie2Object {
  // Accès aux objets associés
  /**
   * Evenement associé à l'objet
   * 
   * @var EventMelanie
   */
  private $event;
  
  // RECURDAYS Fields
  const RECURDAYS_NODAY = ConfigMelanie::NODAY;
  const RECURDAYS_SUNDAY = ConfigMelanie::SUNDAY;
  const RECURDAYS_MONDAY = ConfigMelanie::MONDAY;
  const RECURDAYS_TUESDAY = ConfigMelanie::TUESDAY;
  const RECURDAYS_WEDNESDAY = ConfigMelanie::WEDNESDAY;
  const RECURDAYS_THURSDAY = ConfigMelanie::THURSDAY;
  const RECURDAYS_FRIDAY = ConfigMelanie::FRIDAY;
  const RECURDAYS_SATURDAY = ConfigMelanie::SATURDAY;
  
  // RECURTYPE Fields
  const RECURTYPE_NORECUR = ConfigMelanie::NORECUR;
  const RECURTYPE_DAILY = ConfigMelanie::DAILY;
  const RECURTYPE_WEEKLY = ConfigMelanie::WEEKLY;
  const RECURTYPE_MONTHLY = ConfigMelanie::MONTHLY;
  const RECURTYPE_MONTHLY_BYDAY = ConfigMelanie::MONTHLY_BYDAY;
  const RECURTYPE_YEARLY = ConfigMelanie::YEARLY;
  const RECURTYPE_YEARLY_BYDAY = ConfigMelanie::YEARLY_BYDAY;
  
  /**
   * Constructeur de l'objet
   * 
   * @param Event $event          
   */
  function __construct($event = null) {
    // Défini la classe courante
    $this->get_class = get_class($this);
    
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->__construct()");
    // Définition de l'évènement melanie2
    if (isset($event)) {
      $this->event = $event;
      $this->objectmelanie = $this->event;
    }
  }
  
  /**
   * ***************************************************
   * METHOD MAPPING
   */
  
  /**
   * ***************************************************
   * MAPPING
   */
  /**
   * Mapping type field
   * 
   * @param Recurrence::RECURTYPE $type          
   * @ignore
   *
   */
  protected function setMapType($type) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapType($type)");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    $this->objectmelanie->type = MappingMelanie::$MapRecurtypeObjectMelanie[$type];
  }
  /**
   * Mapping type field
   * 
   * @ignore
   *
   */
  protected function getMapType() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapRecurtype()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    return MappingMelanie::$MapRecurtypeObjectMelanie[$this->objectmelanie->type];
  }
  
  /**
   * Mapping days field
   * 
   * @param
   *          array of Recurrence::RECURDAYS $days
   * @ignore
   *
   */
  protected function setMapDays($days) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapDays()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    $this->objectmelanie->days = MappingMelanie::NODAY;
    if (is_array($days)) {
      foreach ($days as $day) {
        $this->objectmelanie->days += intval(MappingMelanie::$MapRecurdaysObjectMelanie[$day]);
      }
    } else {
      $this->objectmelanie->days += intval(MappingMelanie::$MapRecurdaysObjectMelanie[$days]);
    }
  }
  /**
   * Mapping days field
   * 
   * @ignore
   *
   */
  protected function getMapDays() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapDays()");
    if (!isset($this->objectmelanie))
      throw new Exceptions\ObjectMelanieUndefinedException();
    $days = [];
    foreach (MappingMelanie::$MapRecurdaysObjectMelanie as $day) {
      if (is_integer(MappingMelanie::$MapRecurdaysObjectMelanie[$day]) && MappingMelanie::$MapRecurdaysObjectMelanie[$day] & $this->objectmelanie->days)
        $days[] = $day;
    }
    return $days;
  }
  
  /**
   * Parses an iCalendar 2.0 recurrence rule.
   * based on Horde_Date_Recurrence class
   * 
   * @link http://rfc.net/rfc2445.html#s4.3.10
   * @link http://rfc.net/rfc2445.html#s4.8.5
   * @link http://www.shuchow.com/vCalAddendum.html
   * @param array $rdata
   *          An iCalendar 2.0 conform RRULE value.
   */
  protected function setMapRrule($rdata) {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->setMapRrule()");
    $recurrence = $this;
    
    if (isset($rdata[ICS::FREQ])) {
      // Always default the recurInterval to 1.
      $recurrence->interval = isset($rdata[ICS::INTERVAL]) ? $rdata[ICS::INTERVAL] : 1;
      $recurrence->days = array();
      // MANTIS 4103: Calculer une date de fin approximative pour un count
      $nbdays = $recurrence->interval;
      
      switch (strtoupper($rdata[ICS::FREQ])) {
        case ICS::FREQ_DAILY :
          $recurrence->type = self::RECURTYPE_DAILY;
          $nbdays = $nbdays + 7;
          break;
        
        case ICS::FREQ_WEEKLY :
          $recurrence->type = self::RECURTYPE_WEEKLY;
          if (isset($rdata[ICS::BYDAY])) {
            if (is_array($rdata[ICS::BYDAY])) {
              $recurrence->days = $rdata[ICS::BYDAY];
            } else {
              $recurrence->days = explode(',', $rdata[ICS::BYDAY]);
            }
          }
          $nbdays = $nbdays * 7 + 14;
          break;
        
        case ICS::FREQ_MONTHLY :
          if (isset($rdata[ICS::BYDAY])) {
            $recurrence->type = self::RECURTYPE_MONTHLY_BYDAY;
            if (is_array($rdata[ICS::BYDAY])) {
              $recurrence->days = $rdata[ICS::BYDAY];
            } else {
              $recurrence->days = explode(',', $rdata[ICS::BYDAY]);
            }
          } else {
            $recurrence->type = self::RECURTYPE_MONTHLY;
          }
          $nbdays = $nbdays * 31 + 31;
          break;
        
        case ICS::FREQ_YEARLY :
          if (isset($rdata[ICS::BYYEARDAY])) {
            $recurrence->type = self::RECURTYPE_YEARLY;
          } elseif (isset($rdata[ICS::BYDAY])) {
            $recurrence->type = self::RECURTYPE_YEARLY_BYDAY;
            if (is_array($rdata[ICS::BYDAY])) {
              $recurrence->days = $rdata[ICS::BYDAY];
            } else {
              $recurrence->days = explode(',', $rdata[ICS::BYDAY]);
            }
          } else {
            $recurrence->type = self::RECURTYPE_YEARLY;
          }
          $nbdays = $nbdays * 366 + 300;
          break;
      }
      if (isset($rdata[ICS::UNTIL])) {
        // Récupération du timezone
        $calendar = $this->event->getCalendarMelanie();
        if (isset($calendar)) {
          $timezone = $calendar->getTimezone();
        }
        if (!isset($timezone)) {
          $timezone = ConfigMelanie::CALENDAR_DEFAULT_TIMEZONE;
        }
        // Génération de la date de fin de récurrence
        $recurrence->enddate = $rdata[ICS::UNTIL];
        $recurenddate = new \DateTime($recurrence->enddate, new \DateTimeZone('UTC'));
        $startdate = new \DateTime($this->event->start, new \DateTimeZone($timezone));
        $enddate = new \DateTime($this->event->end, new \DateTimeZone($timezone));
        // Est-ce que l'on est en journée entière ?
        if ($startdate->format('H:i:s') == '00:00:00' && $enddate->format('H:i:s') == '00:00:00') {
          // On position la date de fin de récurrence de la même façon
          $recurrence->enddate = $recurenddate->format('Y-m-d') . ' 00:00:00';
        } else {
          // On position la date de fin basé sur la date de début en UTC
          // Voir MANTIS 3584: Les récurrences avec une date de fin se terminent à J+1 sur mobile
          $startdate->setTimezone(new \DateTimeZone('UTC'));
          $recurrence->enddate = $recurenddate->format('Y-m-d') . ' ' . $startdate->format('H:i:s');
        }
        // MANTIS 3610: Impossible de modifier la date de fin d'un evt récurrent si celui-ci était paramétré avec un nombre d'occurrences
        // Forcer le count a 0
        $recurrence->count = '';
      } elseif (isset($rdata[ICS::COUNT])) {
        $recurrence->count = intval($rdata[ICS::COUNT]);
        // MANTIS 4103: Calculer une date de fin approximative pour un count
        $enddate = new \DateTime($event->end);
        $enddate->add(new \DateInterval("P" . $nbdays . "D"));
        $recurrence->enddate = $enddate->format('Y-m-d H:i:s');
      } else {
        $recurrence->enddate = "9999-12-31 00:00:00";
        $recurrence->count = '';
      }
    } else {
      // No recurrence data - event does not recur.
      $recurrence->type = self::RECURTYPE_NORECUR;
      $recurrence->count = '';
      $recurrence->enddate = '';
      $recurrence->days = '';
      $recurrence->interval = '';
    }
  }
  
  /**
   * Creates an iCalendar 2.0 recurrence rule.
   * based on Horde_Date_Recurrence class
   * 
   * @link http://rfc.net/rfc2445.html#s4.3.10
   * @link http://rfc.net/rfc2445.html#s4.8.5
   * @link http://www.shuchow.com/vCalAddendum.html
   * @return array An iCalendar 2.0 conform RRULE value for roundcube.
   */
  protected function getMapRrule() {
    M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class . "->getMapRrule()");
    // Tableau permettant de recuperer toutes les valeurs de la recurrence
    $recurrence = [];
    // Récupération des informations de récurrence de l'évènement
    $_recurrence = $this;
    // Si une recurrence est bien definie dans l'evenement
    if ($_recurrence->type !== self::RECURTYPE_NORECUR) {
      switch ($_recurrence->type) {
        case self::RECURTYPE_DAILY :
          $recurrence[ICS::FREQ] = ICS::FREQ_DAILY;
          if (isset($_recurrence->interval)) {
            // Recupere l'interval de recurrence
            $recurrence[ICS::INTERVAL] = $_recurrence->interval;
          }
          break;
        
        case self::RECURTYPE_WEEKLY :
          $recurrence[ICS::FREQ] = ICS::FREQ_WEEKLY;
          if (isset($_recurrence->interval)) {
            // Recupere l'interval de recurrence
            $recurrence[ICS::INTERVAL] = $_recurrence->interval;
          }
          if (is_array($_recurrence->days) && count($_recurrence->days) > 0) {
            // Jour de récurrence
            $recurrence[ICS::BYDAY] = implode(',', $_recurrence->days);
          }
          break;
        
        case self::RECURTYPE_MONTHLY :
          $recurrence[ICS::FREQ] = ICS::FREQ_MONTHLY;
          if (isset($_recurrence->interval)) {
            // Recupere l'interval de recurrence
            $recurrence[ICS::INTERVAL] = $_recurrence->interval;
          }
          $start = new \DateTime($this->event->start);
          $recurrence[ICS::BYMONTHDAY] = $start->format('d');
          break;
        
        case self::RECURTYPE_MONTHLY_BYDAY :
          $start = new \DateTime($this->event->start);
          $day_of_week = $start->format('w');
          $nth_weekday = ceil($start->format('d') / 7);
          
          $vcaldays = [
              'SU',
              'MO',
              'TU',
              'WE',
              'TH',
              'FR',
              'SA'
          ];
          
          $recurrence[ICS::FREQ] = ICS::FREQ_MONTHLY;
          if (isset($_recurrence->interval)) {
            // Recupere l'interval de recurrence
            $recurrence[ICS::INTERVAL] = $_recurrence->interval;
          }
          $recurrence[ICS::BYDAY] = $nth_weekday . $vcaldays[$day_of_week];
          break;
        
        case self::RECURTYPE_YEARLY :
          $recurrence[ICS::FREQ] = ICS::FREQ_YEARLY;
          if (isset($_recurrence->interval)) {
            // Recupere l'interval de recurrence
            $recurrence[ICS::INTERVAL] = $_recurrence->interval;
          }
          break;
        
        case self::RECURTYPE_YEARLY_BYDAY :
          $start = new \DateTime($this->event->start);
          $monthofyear = $start->format('m'); // 01 à 12
          $nth_weekday = ceil($start->format('d') / 7);
          $day_of_week = $start->format('w');
          $vcaldays = [
              'SU',
              'MO',
              'TU',
              'WE',
              'TH',
              'FR',
              'SA'
          ];
          
          $recurrence[ICS::FREQ] = ICS::FREQ_YEARLY;
          if (isset($_recurrence->interval)) {
            // Recupere l'interval de recurrence
            $recurrence[ICS::INTERVAL] = $_recurrence->interval;
          }
          $recurrence[ICS::BYDAY] = $nth_weekday . $vcaldays[$day_of_week];
          $recurrence[ICS::BYMONTH] = $monthofyear;
          break;
      }
      if (isset($_recurrence->count) && intval($_recurrence->count) > 0) {
        // Gestion du nombre d'occurences
        $recurrence['COUNT'] = intval($_recurrence->count);
      } elseif (isset($_recurrence->enddate)) {
        // Gestion d'une date de fin
        $recurrence['UNTIL'] = new \DateTime($_recurrence->enddate, new \DateTimeZone('UTC'));
        if ($recurrence['UNTIL']->format('Y') == '9999') {
          // Si l'année est en 9999 on considère qu'il n'y a de date de fin
          unset($recurrence['UNTIL']);
        }
      }
    }
    return $recurrence;
  }
}