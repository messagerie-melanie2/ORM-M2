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
namespace LibMelanie\Api\Defaut\Users;

use LibMelanie\Lib\MceObject;

/**
 * Classe utilisateur par defaut
 * pour la gestion du gestionnaire d'absence
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut/Users
 * @api
 * 
 * @property \Datetime $start Date de début de l'absence
 * @property \Datetime $end Date de fin de l'absence
 * @property boolean $enable Est-ce que l'absence est active
 * @property string $message Message d'absence a afficher
 * @property int $order Ordre de tri du message d'absence
 * @property Outofoffice::TYPE_* $type Type d'absence (Interne, Externe)
 * @property Outofoffice::DAY_*[] $days Liste des jours pour une absence hebdomadaire
 * @property \Datetime $hour_start Heure de début de l'absence
 * @property \Datetime $hour_end Heure de fin de l'absence
 * @property int $offset Offset du timezone en fonction de GMT
 */
class Outofoffice extends MceObject {
  // TYPE d'absence Internal ou External
  const TYPE_EXTERNAL = 'ext';
  const TYPE_INTERNAL = 'int';
  const TYPE_ALL = 'all';

  // Identifiant des absences récurrentes
  const HEBDO = 'hebdo';

  // DAY utilisable
  const DAY_SUNDAY = 'sun';
  const DAY_MONDAY = 'mon';
  const DAY_TUESDAY = 'tue';
  const DAY_WEDNESDAY = 'wed';
  const DAY_THURSDAY = 'thu';
  const DAY_FRIDAY = 'fri';
  const DAY_SATURDAY = 'sat';

  /**
   * Liste des propriétés à sérialiser pour le cache
   */
  protected $serializedProperties = [
    'start',
    'end',
    'days',
    'hour_start',
    'hour_end',
    'offset',
    'enable',
    'message',
    'order',
    'type',
  ];

  /**
   * Constructeur permettant de parser les données
   * 
   * @param string Ligne de l'entrée d'annuaire à parser
   */
  public function __construct($data = null) {
    if (isset($data)) {
      $this->define($data);
    }
  }

  /**
   * Define depuis un format annuaire l'entrée du message d'absence
   * 
   * @param string Ligne de l'entrée d'annuaire
   */
  public function define($data) {}

  /**
   * Retourne au format annuaire l'entrée du message d'absence
   */
  public function render() {
    return null;
  }

  /**
   * Date de début de l'absence
   * @var \Datetime $start
   */
  protected $start;
  /**
   * Mapping start field
   *
   * @param \Datetime $start
   */
  protected function setMapStart($start) {
    $this->start = $start;
  }
  /**
   * Mapping start field
   * 
   * @return \Datetime $start
   */
  protected function getMapStart() {
    return $this->start;
  }
  /**
   * Mapping start field
   *
   * @return boolean
   */
  protected function issetMapStart() {
    return isset($this->start);
  }

  /**
   * Date de fin de l'absence
   * @var \Datetime $end
   */
  protected $end;
  /**
   * Mapping end field
   *
   * @param \Datetime $end
   */
  protected function setMapEnd($end) {
    $this->end = $end;
  }
  /**
   * Mapping end field
   * 
   * @return \Datetime $end
   */
  protected function getMapEnd() {
    return $this->end;
  }
  /**
   * Mapping end field
   *
   * @return boolean
   */
  protected function issetMapEnd() {
    return isset($this->end);
  }

  /**
   * Liste des jours pour une récurrence hebdomadaire
   * @var array $days
   */
  protected $days;
  /**
   * Mapping days field
   *
   * @param array $days
   */
  protected function setMapDays($days) {
    $this->days = $days;
  }
  /**
   * Mapping days field
   * 
   * @return array $days
   */
  protected function getMapDays() {
    return $this->days;
  }
  /**
   * Mapping days field
   *
   * @return boolean
   */
  protected function issetMapDays() {
    return isset($this->days);
  }

  /**
   * Heure de début pour une absence récurrente
   * @var \Datetime $hour_start
   */
  protected $hour_start;
  /**
   * Mapping hour_start field
   *
   * @param \Datetime $hour_start
   */
  protected function setMapHour_start($hour_start) {
    $this->hour_start = $hour_start;
  }
  /**
   * Mapping hour_start field
   * 
   * @return \Datetime $hour_start
   */
  protected function getMapHour_start() {
    return $this->hour_start;
  }
  /**
   * Mapping hour_start field
   *
   * @return boolean
   */
  protected function issetMapHour_start() {
    return isset($this->hour_start);
  }

  /**
   * Heure de fin pour une absence récurrente
   * @var \Datetime $hour_end
   */
  protected $hour_end;
  /**
   * Mapping hour_end field
   *
   * @param \Datetime $hour_end
   */
  protected function setMapHour_end($hour_end) {
    $this->hour_end = $hour_end;
  }
  /**
   * Mapping hour_end field
   * 
   * @return \Datetime $hour_end
   */
  protected function getMapHour_end() {
    return $this->hour_end;
  }
  /**
   * Mapping hour_end field
   *
   * @return boolean
   */
  protected function issetMapHour_end() {
    return isset($this->hour_end);
  }

  /**
   * Offset du timezone en fonction de GMT
   * @var int $offset
   */
  protected $offset;
  /**
   * Mapping offset field
   *
   * @param int $offset
   */
  protected function setMapOffset($offset) {
    $this->offset = $offset;
  }
  /**
   * Mapping offset field
   * 
   * @return int $offset
   */
  protected function getMapOffset() {
    return $this->offset;
  }
  /**
   * Mapping offset field
   *
   * @return boolean
   */
  protected function issetMapOffset() {
    return isset($this->offset);
  }

  /**
   * Est-ce que l'absence est active
   * @var boolean $enable
   */
  protected $enable;
  /**
   * Mapping enable field
   *
   * @param boolean $enable
   */
  protected function setMapEnable($enable) {
    $this->enable = $enable;
  }
  /**
   * Mapping enable field
   * 
   * @return boolean $enable
   */
  protected function getMapEnable() {
    return $this->enable;
  }
  /**
   * Mapping enable field
   *
   * @return boolean
   */
  protected function issetMapEnable() {
    return isset($this->enable);
  }

  /**
   * Message d'absence a afficher
   * @var string $message
   */
  protected $message;
  /**
   * Mapping message field
   *
   * @param string $message
   */
  protected function setMapMessage($message) {
    $this->message = $message;
  }
  /**
   * Mapping message field
   * 
   * @return string $message
   */
  protected function getMapMessage() {
    return $this->message;
  }
  /**
   * Mapping message field
   *
   * @return boolean
   */
  protected function issetMapMessage() {
    return isset($this->message);
  }

  /**
   * Ordre de tri du message d'absence
   * @var int $order
   */
  protected $order;
  /**
   * Mapping order field
   *
   * @param int $order
   */
  protected function setMapOrder($order) {
    $this->order = $order;
  }
  /**
   * Mapping order field
   * 
   * @return int $order
   */
  protected function getMapOrder() {
    return $this->order;
  }
  /**
   * Mapping order field
   *
   * @return boolean
   */
  protected function issetMapOrder() {
    return isset($this->order);
  }

  /**
   * Type d'absence (Interne, Externe)
   * @var Outofoffice::TYPE_* $type
   */
  protected $type;
  /**
   * Mapping type field
   *
   * @param Outofoffice::TYPE_* $type
   */
  protected function setMapType($type) {
    $this->type = $type;
  }
  /**
   * Mapping type field
   * 
   * @return Outofoffice::TYPE_* $type
   */
  protected function getMapType() {
    return $this->type;
  }
  /**
   * Mapping type field
   *
   * @return boolean
   */
  protected function issetMapType() {
    return isset($this->type);
  }
}