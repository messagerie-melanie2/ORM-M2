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
namespace LibMelanie\Api\Mce;

use LibMelanie\Api\Defaut;

/**
 * Classe contact pour MCE,
 * implémente les API de la librairie pour aller chercher les données dans la base de données
 * Certains champs sont mappés directement ou passe par des classes externes
 * 
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/MCE
 * @api
 * 
 * @property string $id Identifiant unique du contact
 * @property string $addressbook Identifiant de la liste de contacts associée
 * @property string $uid UID du contact
 * @property string $type Type de l'objet
 * @property int $modified Timestamp de dernière modification du contact
 * @property string $members Membres de la liste ? (TODO: Peut être faire un tableau de Contact ?)
 * @property string $name Nom du contact
 * @property string $alias Surnom du contact
 * @property string $freebusyurl URL de Freebusy pour ce contact
 * @property string $firstname Prénom du contact
 * @property string $lastname Nom de famille du contact
 * @property string $middlenames Autres noms pour le contact
 * @property string $nameprefix Prefix du contact
 * @property string $namesuffix Suffix du contact
 * @property string $birthday Date d'anniversaire
 * @property string $title Titre du contact
 * @property string $company Entreprise du contact
 * @property string $notes Notes associées au contact
 * @property string $email Adresse e-mail
 * @property string $email1 Deuxième adresse e-mail
 * @property string $email2 Troisième adresse e-mail
 * @property string $cellphone Numéro de mobile
 * @property string $fax Numéro de fax
 * @property string $category Categorie du contact
 * @property string $url URL associée au contact
 * @property string $homeaddress Adresse du domicile
 * @property string $homephone Numéro de téléphone du domicile
 * @property string $homestreet Rue du domicile
 * @property string $homepob Boite aux lettres du domicile
 * @property string $homecity Ville du domicile
 * @property string $homeprovince Département du domicile
 * @property string $homepostalcode Code postal du domicile
 * @property string $homecountry Pays du domicile
 * @property string $workaddress Adresse du bureau
 * @property string $workphone Numéro de téléphone du bureau
 * @property string $workstreet Rue du bureau
 * @property string $workpob Boite aux lettres du bureau
 * @property string $workcity Ville du bureau
 * @property string $workprovince Département du bureau
 * @property string $workpostalcode Code postal du bureau
 * @property string $workcountry Pays du bureau
 * @property string $pgppublickey Clé publique du contact
 * @property string $smimepublickey SMIME pour la clé publique
 * @property string $photo Photo du contact
 * @property string $phototype Type du fichier photo
 * @property string $logo Logo du contact
 * @property string $logotype Type du fichier logo
 * @property string $timezone Timezone du contact
 * @property string $geo Geo
 * @property string $pager Pager
 * @property string $role Role du contact
 * @property string $vcard VCard associé au contact courant, calculé à la volée en attendant la mise en base de données
 * @method bool load() Chargement le contact, en fonction de l'addressbook et de l'uid
 * @method bool exists() Test si le contact existe, en fonction de l'addressbook et de l'uid
 * @method bool save() Sauvegarde le contact et l'historique dans la base de données
 * @method bool delete() Supprime le contact et met à jour l'historique dans la base de données
 */
class Contact extends Defaut\Contact {}