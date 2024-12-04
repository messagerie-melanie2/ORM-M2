<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
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
namespace LibMelanie\Config;

/**
 * Configuration des envois de mails pour l'ORM
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Config
 */
class ConfigMail {
    /**
     * Configuration des expéditeurs de mails pour l'ORM
     * 
     * Toujours prévoir une configuration 'default' pour tous les envois
     * Il y a également la possibilité d'ajouter d'autres expéditeurs pour les applications
     * 
     * Format d'un expéditeur :
     *  'nom' => [
     *      'from'          => 'from@example.com',
     *      'host'          => 'smtp.example.com',
     *      'port'          => 25,
     *      'smtpAuth'      => true|false,
     *      'username'      => 'username',
     *      'password'      => 'password',
     *      'smtpSecure'    => 'tls|ssl'|null,
     *  ]
     * 
     * @var array
     */
    const SENDERS = [
        // Configuration par défaut, utilisée si les autres configurations ne sont pas trouvées
        // 'default' => [
        //     'from'          => 'email@example.com',
        //     'host'          => 'smtp.example.com',
        //     'port'          => 25,
        //     'smtpAuth'      => true,
        //     'username'      => 'login',
        //     'password'      => 'password',
        //     'smtpSecure'    => 'tls',
        // ],
    ];

    /**
     * Activer le mode debug pour les envois de mails
     * 
     * @var boolean
     */
    const DEBUG = false;
}