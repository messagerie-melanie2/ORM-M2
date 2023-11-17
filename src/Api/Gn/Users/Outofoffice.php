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

namespace LibMelanie\Api\Gn\Users;

use LibMelanie\Api\Mce;

/**
 * Classe utilisateur pour GN
 * utilisation de la syntaxe Mél
 * pour la gestion du gestionnaire d'absence
 *
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/MCE
 * @api
 *
 * @property \Datetime $start Date de début de l'absence
 * @property \Datetime $end Date de fin de l'absence
 * @property boolean $enable Est-ce que l'absence est active
 * @property string $message Message d'absence a afficher
 * @property int $order Ordre de tri du message d'absence
 * @property Outofoffice::TYPE_* $type Type d'absence (Interne, Externe)
 */
class Outofoffice extends Mce\Users\Outofoffice
{
    /**
     * Version supporté pour le format MCE
     */
    const VERSION = '1';

    /**
     * Sujet par défaut (non supporté dans le webmail)
     * Ne pas mettre de ';' non compatible avec la syntaxe
     */
    const SUBJECT = 'Auto: %s';

    /**
     * Délai d'émission des messages
     */
    const DELAY = 'd1';

    /**
     * Préfixe pour désactiver une règle
     */
    const DISABLED = 'DIS-';

    /**
     * Mapping des types dans l'annuaire
     */
    protected $mappingTypes = [
        'ALL' => self::TYPE_ALL,
        'IN' => self::TYPE_INTERNAL,
        'OUT' => self::TYPE_EXTERNAL,
    ];

    /**
     * Mapping des jours dans l'annuaire
     */
    protected $mappingDays = [
        self::DAY_SUNDAY,
        self::DAY_MONDAY,
        self::DAY_TUESDAY,
        self::DAY_WEDNESDAY,
        self::DAY_THURSDAY,
        self::DAY_FRIDAY,
        self::DAY_SATURDAY,
    ];

    /**
     * Retourne au format annuaire l'entrée du message d'absence
     */
    public function render()
    {
        // ^[0-9]{2};{Version};(ALL|IN|OUT);[0-9]{14}Z;[0-9]{14}Z;[dhms][1-9][0-9]*;{repetitivite};[^;]*;[^;]*$
        $data = [];
        // Gestion du classement
        if (isset($this->order)) {
            $data[] = sprintf("%02d", $this->order);
        } else {
            $data[] = '01';
        }
        // Version
        $data[] = static::VERSION;
        // Type de message d'absence
        $types = array_flip($this->mappingTypes);
        $type = '';
        // Gestion de la désactivation de la règle dans le type (ALL = Hebdo donc pas de désactivation pour l'instant)
        if (!$this->enable && $this->type != self::TYPE_ALL) {
            $type = static::DISABLED;
        }
        // Mapping du type
        if (isset($types[$this->type])) {
            $type .= $types[$this->type];
        } else {
            $type .= $types[self::TYPE_ALL];
        }
        $data[] = $type;
        if (isset($this->start)) {
            $this->start->setTimezone(new \DateTimeZone('UTC'));
            $data[] = $this->start->format('YmdHis') . 'Z';
        } else {
            $data[] = '*';
        }
        // Date de fin
        if (isset($this->end)) {
            $this->end->setTimezone(new \DateTimeZone('UTC'));
            $data[] = $this->end->format('YmdHis') . 'Z';
        } else {
            $data[] = '*';
        }
        // Délai
        $data[] = static::DELAY;
        // Récurrence
        $data[] = $this->renderRecurrence();
        // Sujet
        $data[] = static::SUBJECT;
        // Message
        $data[] = str_replace(';', ',', $this->message);

        return implode(';', $data);
    }

    /**
     * Retourne la récurrence au format MCE
     *
     * @return $recurrence
     */
    protected function renderRecurrence()
    {
        $minute = '*';
        $hour = '*';
        $day = '*';
        $month = '*';
        $dayofweek = '*';
        $weekofmonth = '*';

        // Gestion des jours
        if (isset($this->days)) {
            $days = [];
            $mappingDays = array_flip($this->mappingDays);
            foreach ($this->days as $d) {
                if (isset($mappingDays[$d])) {
                    $days[] = $mappingDays[$d];
                }
            }
            $dayofweek = implode(',', $days);
        }

        // Gestion des heures
        if (isset($this->hour_start)
            && isset($this->hour_end)) {
            $hour = $this->hour_start->format('G') . '-' . $this->hour_end->format('G');
        }

        return "$minute $hour $day $month $dayofweek $weekofmonth";
    }
}
