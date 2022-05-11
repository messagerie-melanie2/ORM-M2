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
namespace LibMelanie\Lib;

use LibMelanie\Api\Defaut\User;
use LibMelanie\Api\Defaut\Contact;
use LibMelanie\Api\Defaut\Addressbook;

// Utilisation de la librairie Sabre VObject pour la conversion ICS
@include_once 'vendor/autoload.php';
use Sabre\VObject;

/**
 * Class de génération de VCard en fonction de l'objet contact
 * Méthodes Statiques
 *
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage Lib
 *
 */
class VCardToContact {
	/**
	 *  Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() { }

	/**
	 * Génére un contact mélanie2 en fonction du VCard passé en paramètre
	 * Le contact doit être de type Contact de la librairie LibM2
	 *
	 * @param string $vcard
	 * @param Contact $contact
   * @param Addressbook $addressbook
   * @param User $user
   * 
	 * @return Contact
	 */
	public static function Convert($vcard, $contact, $addressbook = null, $user = null) {
	  $vcontact = VObject\Reader::read($vcard);
	  $contact->uid = (string)$vcontact->UID;

	  if (isset($vcontact->KIND)
	      && strtolower($vcontact->KIND) == 'group') {
	    // Type list
      $contact->type = Contact::TYPE_LIST;
      // Members list
      $members = [];
      if (isset($vcontact->MEMBER)) {
        foreach($vcontact->MEMBER as $vcontact_member) {
          $members[] = $vcontact_member->getValue();
        }
      }
      $contact->members = serialize($members);
      // Group name
      $contact->lastname = (string)$vcontact->FN;
    }
    else {
      // Fullname
      $contact->name = (string)$vcontact->FN;

      // Complete name
      $contact->lastname = "";
      $contact->firstname = "";
      $contact->middlenames = "";
      $contact->title = "";
      $contact->nameprefix = "";
      $contact->namesuffix = "";
      if (isset($vcontact->N)) {
        $names = explode(';', str_replace("\\", "", (string)$vcontact->N));
        $contact->lastname = isset($names[0]) ? $names[0] : '';
        $firstname = explode(',', isset($names[1]) ? $names[1] : '');
        $contact->firstname = $firstname[0];
        if (isset($firstname[1])) {
          unset($firstname[0]);
          $contact->middlenames = implode(' ', $firstname);
        }
        $contact->title = isset($names[2]) ? $names[2] : '';
        $contact->nameprefix = isset($names[3]) ? $names[3] : '';
        $contact->namesuffix = isset($names[4]) ? $names[4] : '';
      }

      // TEL
      $contact->cellphone = "";
      $contact->fax = "";
      $contact->pager = "";
      $contact->workphone = "";
      $contact->homephone = "";
      if (isset($vcontact->TEL)) {
        foreach($vcontact->TEL as $vcontact_tel) {
          $parameters = $vcontact_tel->parameters;
          $type = isset($parameters['TYPE']) ? strtolower($parameters['TYPE']) : "";
          if (strpos($type, 'cell') !== false) {
            $contact->cellphone = $vcontact_tel->getValue();
          }
          elseif (strpos($type, 'fax') !== false) {
            $contact->fax = $vcontact_tel->getValue();
          }
          elseif (strpos($type, 'pager') !== false) {
            $contact->pager = $vcontact_tel->getValue();
          }
          elseif (strpos($type, 'work') !== false) {
            $contact->workphone = $vcontact_tel->getValue();
          }
          else {
            $contact->homephone = $vcontact_tel->getValue();
          }
        }
      }

      // Email
      $contact->email = "";
      $contact->email1 = "";
      $contact->email2 = "";
      if (isset($vcontact->EMAIL)) {
        foreach($vcontact->EMAIL as $vcontact_email) {
          $parameters = $vcontact_email->parameters;
          $type = isset($parameters['TYPE']) ? strtolower($parameters['TYPE']) : "";
          if (strpos($type, 'other') !== false) {
            $contact->email2 = $vcontact_email->getValue();
          }
          elseif (strpos($type, 'work') !== false) {
            $contact->email1 = $vcontact_email->getValue();
          }
          else {
            $contact->email = $vcontact_email->getValue();
          }
        }
      }

      // Address
      if (isset($vcontact->ADR)) {
        foreach($vcontact->ADR as $vcontact_adr) {
          $parameters = $vcontact_adr->parameters;
          $type = isset($parameters['TYPE']) ? strtolower($parameters['TYPE']) : "";
          $values = explode(';', $vcontact_adr->getValue());
          if (strpos($type, 'work') !== false) {
            $contact->workaddress = isset($parameters['LABEL']) ? $parameters['LABEL'] : "";
            $contact->workpob = isset($values[0]) ? $values[0] : "";
            $contact->workstreet = isset($values[2]) ? $values[2] : "";
            $contact->workcity = isset($values[3]) ? $values[3] : "";
            $contact->workprovince = isset($values[4]) ? $values[4] : "";
            $contact->workpostalcode = isset($values[5]) ? $values[5] : "";
            $contact->workcountry = isset($values[6]) ? $values[6] : "";
          }
          else {
            $contact->homeaddress = isset($parameters['LABEL']) ? $parameters['LABEL'] : "";
            $contact->homepob = isset($values[0]) ? $values[0] : "";
            $contact->homestreet = isset($values[2]) ? $values[2] : "";
            $contact->homecity = isset($values[3]) ? $values[3] : "";
            $contact->homeprovince = isset($values[4]) ? $values[4] : "";
            $contact->homepostalcode = isset($values[5]) ? $values[5] : "";
            $contact->homecountry = isset($values[6]) ? $values[6] : "";
          }
        }
      }

      // Others properties
      // Nickname
      if (isset($vcontact->NICKNAME))
        $contact->alias = $vcontact->NICKNAME;
      else
        $contact->alias = "";
      // Photo
      if (isset($vcontact->PHOTO)) {
        $contact->phototype = $vcontact->PHOTO['data'];
        $contact->photo = bin2hex(base64_decode(str_replace('base64,', '', $vcontact->PHOTO)));
      }
      else {
        $contact->phototype = "";
        $contact->photo = "";
      }
      // Logo
      if (isset($vcontact->LOGO)) {
        $contact->logotype = $vcontact->LOGO['data'];
        $contact->logo = bin2hex(base64_decode(str_replace('base64,', '', $vcontact->LOGO)));
      }
      else {
        $contact->logotype = "";
        $contact->logo = "";
      }
      // Birthday
      if (isset($vcontact->BIRTHDAY))
        $contact->birthday = date('Y-m-d H:i:s', strtotime((string)$vcontact->BIRTHDAY));
      else
        $contact->birthday = "";
      // Role
      if (isset($vcontact->TITLE))
        $contact->role = $vcontact->TITLE;
      else
        $contact->role = "";
      // Org
      if (isset($vcontact->ORG))
        $contact->company = $vcontact->ORG;
      else
        $contact->company = "";
      // Categories
      if (isset($vcontact->CATEGORIES))
        $contact->category = $vcontact->CATEGORIES;
      else
        $contact->category = "";
      // URL
      if (isset($vcontact->URL))
        $contact->url = $vcontact->URL;
      else
        $contact->url = "";
      // Notes
      if (isset($vcontact->NOTES))
        $contact->notes = $vcontact->NOTES;
      else
        $contact->notes = "";
      // Geo
      if (isset($vcontact->GEO))
        $contact->geo = $vcontact->GEO;
      else
        $contact->geo = "";
      // Timezone
      if (isset($vcontact->TZ))
        $contact->timezone = $vcontact->TZ;
      else
        $contact->timezone = "";
      // Freebusy URL
      if (isset($vcontact->FBURL))
        $contact->freebusyurl = $vcontact->FBURL;
      else
        $contact->freebusyurl = "";
    }

	  return $contact;
	}
}