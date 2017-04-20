<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
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
 */
/* Chargement de la configuration */
include_once 'includes/includes_conf.php';

/* Chargement des logs */
include_once 'log/log.php';

/* Chargement des interfaces */
include_once 'interfaces/IObjectMelanie.php';

/* Chargement des libs */
include_once 'lib/icsdefs.php';
include_once 'lib/magicobject.php';
include_once 'lib/melanie2object.php';
include_once 'lib/icstoevent.php';
include_once 'lib/eventtoics.php';

/* Chargement des objets à sauvegarder en base */
include_once 'objects/addressbook.php';
include_once 'objects/history.php';
include_once 'objects/object.php';
include_once 'objects/taskslist.php';
include_once 'objects/user.php';

/* Chargement des exceptions */
include_once 'exceptions/melanie2exception.php';
include_once 'exceptions/objectmelanieundefinedexception.php';
include_once 'exceptions/propertydoesnotexist.php';
include_once 'exceptions/undefinedprimarykeyexception.php';
include_once 'exceptions/undefinedmappingexception.php';

/* Chargement du driver ldap */
include_once 'ldap/ldap.php';
include_once 'ldap/singleton.php';

/* Chargement du driver sql */
include_once 'sql/sql.php';
include_once 'sql/singleton.php';
include_once 'sql/sqlrequests.php';

/* Chargement de l'api melanie2 */
include_once 'api/melanie2/user.php';
include_once 'api/melanie2/taskslist.php';
include_once 'api/melanie2/task.php';
