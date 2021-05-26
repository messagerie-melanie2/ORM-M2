<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 * ORM Mél Copyright © 2021 Groupe Messagerie/MTE
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
namespace LibMelanie\Api\Mel\Users;

use LibMelanie\Api\Defaut;

/**
 * Classe utilisateur pour Mel
 * pour la gestion du gestionnaire d'absence
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Mel
 * @api
 * 
 * @property \Datetime $start Date de début de l'absence
 * @property \Datetime $end Date de fin de l'absence
 * @property \boolean $enable Est-ce que l'absence est active
 * @property \string $message Message d'absence a afficher
 * @property \int $order Ordre de tri du message d'absence
 * @property Outofoffice::TYPE_* $type Type d'absence (Interne, Externe)
 */
class Outofoffice extends Defaut\Users\Outofoffice {
    /**
     * Mapping champ ldap => fonction set
     */
    private $mapping = [
        'DDEB'  => 'setMapStart',
        'DFIN'  => 'setMapEnd',
        'HDEB'  => 'setMapHour_start',
        'HFIN'  => 'setMapHour_end',
        'DGMT'  => 'setMapOffset',
        'JOURS' => 'setMapDays',
        'RAIN'  => 'setTypeInternal',
        'RAEX'  => 'setTypeExternal',
        'TOUT'  => 'setTypeAll',
    ];
    /**
     * Mapping des jours dans l'annuaire
     */
    private $mappingDays = [
        self::DAY_SUNDAY,
        self::DAY_MONDAY,
        self::DAY_TUESDAY,
        self::DAY_WEDNESDAY,
        self::DAY_THURSDAY,
        self::DAY_FRIDAY,
        self::DAY_SATURDAY,
    ];

    /**
     * Positionne le type Internal
     */
    protected function setTypeInternal() {
        $this->type = self::TYPE_INTERNAL;
    }
    /**
     * Positionne le type External
     */
    protected function setTypeExternal() {
        $this->type = self::TYPE_EXTERNAL;
    }
    /**
     * Positionne le type All
     */
    protected function setTypeAll() {
        $this->type = self::TYPE_ALL;
    }

    /**
     * Mapping start field
     *
     * @param \Datetime $start
     */
    protected function setMapStart($start) {
        if (is_string($start) && !empty($start)) {
            $this->start = new \DateTime($start);
        }
        else if ($start instanceof \DateTime) {
            $this->start = $start;
        }
        else {
            $this->start = null;
        }
    }

    /**
     * Mapping end field
     *
     * @param \Datetime $end
     */
    protected function setMapEnd($end) {
        if (is_string($end) && !empty($end)) {
            // Si la date de fin commence par 0, le message d'absence est désactivé
            $this->enable = strpos($end, '0') !== 0;
            // Date de fin
            if (strpos($end, '/') !== false) {
                $end = substr($end, 2);
            }
            $this->end = strlen($end) > 1 ? new \DateTime($end) : null;
        }
        else if ($end instanceof \DateTime) {
            $this->end = $end;
        }
        else {
            $this->end = null;
        }
    }

    /**
     * Mapping hour_start field
     *
     * @param \Datetime $hour_start
     */
    protected function setMapHour_start($hour_start) {
        if (is_string($hour_start)) {
            $timezone = new \DateTimeZone('GMT');
            $this->hour_start = \DateTime::createFromFormat('His', $hour_start, $timezone);
        }
        else if ($hour_start instanceof \DateTime) {
            $oldTimezone = $hour_start->getTimezone();
            if ($oldTimezone->getName() != 'Europe/Paris') {
                $offset = $oldTimezone->getOffset($hour_start);
                $this->offset = $offset/3600;
            }
            $this->hour_start = $hour_start;
        }
        else {
            $this->hour_start = null;
        }
        
    }
    /**
     * Mapping hour_start field
     * 
     * @return \Datetime $hour_start
     */
    protected function getMapHour_start() {
        $hour_start = $this->hour_start;
        if (isset($this->offset)) {
            $offset = sprintf("%+'03d00", $this->offset);
            $hour_start->setTimezone(new \DateTimeZone($offset));
        }
        return $hour_start;
    }

    /**
     * Mapping hour_end field
     *
     * @param \Datetime $hour_end
     */
    protected function setMapHour_end($hour_end) {
        if (is_string($hour_end)) {
            $timezone = new \DateTimeZone('GMT');
            $this->hour_end = \DateTime::createFromFormat('His', $hour_end, $timezone);
        }
        else if ($hour_end instanceof \DateTime) {
            $this->hour_end = $hour_end;
        }
        else {
            $this->hour_end = null;
        }
    }
    /**
     * Mapping hour_end field
     * 
     * @return \Datetime $hour_end
     */
    protected function getMapHour_end() {
        $hour_end = $this->hour_end;
        if (isset($this->offset)) {
            $offset = sprintf("%+'03d00", $this->offset);
            $hour_end->setTimezone(new \DateTimeZone($offset));
        }
        return $hour_end;
    }

    /**
     * Mapping offset field
     *
     * @param string $offset
     */
    protected function setMapOffset($offset) {
        $this->offset = $offset;
    }

    /**
     * Mapping days field
     *
     * @param array $days
     */
    protected function setMapDays($days) {
        if (is_array($days) && count($days)) {
            $res = '';
            foreach ($days as $day) {
                if (($key = array_search($day, $this->mappingDays)) !== false) {
                    $res .= $key;
                }
                $this->days = $res;
            }
        }
        else if (is_string($days)) {
            $this->days = $days;
        }
        else {
            $this->days = null;
        }
    }

    /**
     * Mapping days field
     * 
     * @return array $days
     */
    protected function getMapDays() {
        if (isset($this->days)) {
            $days = [];
            $tab = str_split($this->days);
            foreach ($tab as $day) {
                if (isset($this->mappingDays[$day])) {
                    $days[] = $this->mappingDays[$day];
                }
            }
            return $days;
        }
        return null;
    }

    /**
     * Define depuis un format annuaire l'entrée du message d'absence
     * 
     * @param string Ligne de l'entrée d'annuaire
     */
    public function define($data) {
        // Positionnement de TEXTE qui doit être en dernier
        $pos = strpos($data, "TEXTE:") + 6;
        // On explode toutes les propriétés avant TEXTE
        $tab = explode(" ", substr($data, 0, $pos));
        
        if (is_array($tab)) {
            foreach ($tab as $entry) {
                if (strpos($entry, ':') !== false) {
                    list($key, $val) = explode(":", $entry, 2);
                    // Mapping
                    if (isset($this->mapping[$key])) {
                        // Appel la méthode set* en fonction du mapping
                        call_user_func([$this, $this->mapping[$key]], $val);
                    }
                }
                else if (strpos($entry, '~') !== false) {
                    // Gestion du tri
                    $this->setMapOrder(intval(str_replace('~', '', $entry)));
                }
            }
        }
        $this->setMapMessage(substr($data, $pos));
    }

    /**
     * Retourne au format annuaire l'entrée du message d'absence
     */
    public function render() {
        $data = [];
        // Gestion du classement
        if (isset($this->order)) {
            $data[] = $this->order . '~';
        }
        else {
            $data[] = '50~';
        }
        // Type de message d'absence
        switch ($this->type) {
            case Outofoffice::TYPE_INTERNAL:
                $data[] = 'RAIN:';
                break;
            case Outofoffice::TYPE_EXTERNAL:
                $data[] = 'RAEX:';
                break;
            default:
            case Outofoffice::TYPE_ALL:
                $data[] = 'TOUT:';
                break;
        }
        // Absence récurrente ?
        if (isset($this->days)) {
            // Jours
            $data[] = 'JOURS:' . $this->days;
            // Offset du timezone
            if (isset($this->offset)) {
                $data[] = 'DGMT:' . $this->offset;
            }
            // 0006078: [Outofoffice] ne pas mettre les horaires si on est en journée entière (donc heure début = heure de fin)
            if ($this->hour_start->format('His') != $this->hour_end->format('His')) {
                // Heure de debut
                if (isset($this->hour_start)) {
                    if ($this->hour_start->getTimezone()->getName() != 'Europe/Paris') {
                        $this->hour_start->setTimezone(new \DateTimeZone('GMT'));
                    }
                    $data[] = 'HDEB:' . $this->hour_start->format('His');
                }
                // Heure de fin
                if (isset($this->hour_end)) {
                    if ($this->hour_end->getTimezone()->getName() != 'Europe/Paris') {
                        $this->hour_end->setTimezone(new \DateTimeZone('GMT'));
                    }
                    $data[] = 'HFIN:' . $this->hour_end->format('His');
                }
            }
        }
        else {
            // Date de debut
            $data[] = 'DDEB:' . (isset($this->start) ? $this->start->format('Ymd') : '');
            // Date de fin
            $data[] = 'DFIN:' . ($this->enable ? '' : (isset($this->end) ? '0/' : '0')) . (isset($this->end) ? $this->end->format('Ymd') : '');
        }
        // Forcer le fait de continuer les règles pour les récurrences
        $data[] = 'DSVT:1';
        // Texte
        $data[] = 'TEXTE:' . $this->message;

        return implode(' ', $data);
    }
}