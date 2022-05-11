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
use LibMelanie\Log\M2Log;

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
 */
class ContactToVCard {
  /**
   * Identifiant de l'outil utilisant le VCard (pour la génération)
   *
   * @var string
   */
  const PRODID = '-//Groupe Messagerie MTES/ORM LibMCE';
  /**
   * Version ICalendar utilisé pour la génération du VCard
   *
   * @var string
   */
  const VERSION = '4.0';

  /**
   * Constructeur privé pour ne pas instancier la classe
   */
  private function __construct() {
  }

  /**
   * Génére un VCard en fonction du contact passé en paramètre
   * Le contact doit être de type Contact de la librairie LibM2
   *
   * @param Contact $contact
   * @param Addressbook $addressbook
   * @param User $user
   * @param VObject\Component\VCard $vcard
   * @return string $vcard
   */
  public static function Convert($contact, $addressbook = null, $user = null, VObject\Component\VCard $vcard = null) {
    if (! isset($vcard)) {
      $vcard = self::getVCard($contact, $addressbook, $user);
    }
    return $vcard->serialize();
  }

  /**
   * Génére un VObject\Component\VCard en fonction du contact passé en paramètre
   * Le contact doit être de type Contact de la librairie LibM2
   *
   * @param Contact $contact
   * @param Addressbook $addressbook
   * @param User $user
   * @return VObject\Component\VCard $vcard
   */
  public static function getVCard($contact, $addressbook = null, $user = null) {
    M2Log::Log(M2Log::LEVEL_DEBUG, "VCardToContact->getVCard()");
    $vcontact = new VObject\Component\VCard();
    // PRODID et Version
    $vcontact->PRODID = self::PRODID;
    $vcontact->VERSION = self::VERSION;

    // Properties
    $vcontact->UID = $contact->uid;

    // Is a group or a contact ?
    if ($contact->type == Contact::TYPE_LIST) {
      // Kind group
      $vcontact->KIND = 'group';
      // Group name
      $vcontact->FN = $contact->lastname;
      // Members list
      $members = unserialize($contact->members);
      if (is_array($members) && count($members)) {
        // Récupère les contacts membres du groupe
        $class = get_class($contact);
        $_contact = new $class([$contact->getUserMelanie(), $contact->getAddressbookMelanie()]);
        $_contact->type = \LibMelanie\Api\Defaut\Contact::TYPE_CONTACT;
        $_contact->id = $members;
        $_contacts = $_contact->getList('uid');
        if (is_array($_contacts) && count($_contacts)) {
          foreach ($_contacts as $_c) {
            $vcontact->add(VCard::MEMBER, 'urn:uuid:' . $_c->uid);
          }
          
        }
      }
    }
    else {
      // User name
      $comp_name = [];
      if (!empty($contact->lastname)) {
        $comp_name[] = $contact->lastname;

        if (!empty($contact->firstname)) {
          $vcontact->FN = $contact->firstname . ' ' . $contact->lastname;
          if (!empty($contact->middlenames)) {
            $comp_name[] = $contact->firstname . ',' . str_replace(' ', ',', $contact->middlenames);
          }
          else {
            $comp_name[] = $contact->firstname;
          }
        }
        else {
          $vcontact->FN = $contact->lastname;
        }

      }
      else if (!empty($contact->firstname)) {
        $vcontact->FN = $contact->firstname;
        // No last name
        $comp_name[] = "";
        if (isset($contact->middlenames)) {
          $comp_name[] = $contact->firstname . ',' . str_replace(' ', ',', $contact->middlenames);
        }
        else {
          $comp_name[] = $contact->firstname;
        }
      }
      else {
        $vcontact->FN = '';
      }
      // Title
      if (isset($contact->title)) {
        $comp_name[] = $contact->title;
      }
      else {
        $comp_name[] = "";
      }
      // Prefix & Suffix
      if (!empty($contact->nameprefix)) {
        $vcontact->FN = $contact->nameprefix . ' ' . $vcontact->FN;
        $comp_name[] = $contact->nameprefix;
      }
      else {
        $comp_name[] = "";
      }
      if (!empty($contact->namesuffix)) {
        $vcontact->FN = $vcontact->FN . ' ' . $contact->namesuffix;
        $comp_name[] = $contact->namesuffix;
      }
      else {
        $comp_name[] = "";
      }
      // Show name
      if (!empty($contact->name)) {
        $vcontact->FN = $contact->name;
      }
      // Components name
      if (count($comp_name) > 0) {
        $vcontact->N = $comp_name;
      }

      // Tel
      if (!empty($contact->cellphone)) {
        $vcontact->add(VCard::TEL, $contact->cellphone, ['type' => 'cell']);
      }
      if (!empty($contact->fax)) {
        $vcontact->add(VCard::TEL, $contact->fax, ['type' => 'fax']);
      }
      if (!empty($contact->pager)) {
        $vcontact->add(VCard::TEL, $contact->pager, ['type' => 'pager']);
      }
      if (!empty($contact->workphone)) {
        $vcontact->add(VCard::TEL, $contact->workphone, ['type' => 'work,voice']);
      }
      if (!empty($contact->homephone)) {
        $vcontact->add(VCard::TEL, $contact->homephone, ['type' => 'home,voice']);
      }

      // Email
      if (!empty($contact->email)) {
        $vcontact->add(VCard::EMAIL, $contact->email, ['type' => 'home']);
      }
      if (!empty($contact->email1)) {
        $vcontact->add(VCard::EMAIL, $contact->email1, ['type' => 'work']);
      }
      if (!empty($contact->email2)) {
        $vcontact->add(VCard::EMAIL, $contact->email2, ['type' => 'other']);
      }

      // Home address
      if (!empty($contact->homeaddress) || !empty($contact->homestreet) || !empty($contact->homecity) || !empty($contact->homecountry)) {
        $adr = [];
        // Pobox
        $adr[] = isset($contact->homepob) ? $contact->homepob : "";
        // Extended - not supported now
        $adr[] = "";
        // Street
        $adr[] = isset($contact->homestreet) ? $contact->homestreet : "";
        // Locality
        $adr[] = isset($contact->homecity) ? $contact->homecity : "";
        // Region
        $adr[] = isset($contact->homeprovince) ? $contact->homeprovince : "";
        // Code
        $adr[] = isset($contact->homepostalcode) ? $contact->homepostalcode : "";
        // Country
        $adr[] = isset($contact->homecountry) ? $contact->homecountry : "";
        // Params
        $params = ['type' => 'home'];
        if (!empty($contact->homeaddress)) {
          $params['label'] = $contact->homeaddress;
        }
        // Add ADR
        $vcontact->add(VCard::ADR, $adr, $params);
      }

      // Work address
      if (!empty($contact->workaddress) || !empty($contact->workstreet) || !empty($contact->workcity) || !empty($contact->workcountry)) {
        $adr = [];
        // Pobox
        $adr[] = isset($contact->workpob) ? $contact->workpob : "";
        // Extended - not supported now
        $adr[] = "";
        // Street
        $adr[] = isset($contact->workstreet) ? $contact->workstreet : "";
        // Locality
        $adr[] = isset($contact->workcity) ? $contact->workcity : "";
        // Region
        $adr[] = isset($contact->workprovince) ? $contact->workprovince : "";
        // Code
        $adr[] = isset($contact->workpostalcode) ? $contact->workpostalcode : "";
        // Country
        $adr[] = isset($contact->workcountry) ? $contact->workcountry : "";
        // Params
        $params = ['type' => 'work'];
        if (!empty($contact->workaddress)) {
          $params['label'] = $contact->workaddress;
        }
        // Add ADR
        $vcontact->add(VCard::ADR, $adr, $params);
      }

      // Others properties
      // Nickname
      if (!empty($contact->alias)) {
        $vcontact->NICKNAME = $contact->alias;
      }
      // Photo
      if (!empty($contact->photo)) {
        $vcontact->add(VCard::PHOTO, 'base64,' . base64_encode(pack('H' . strlen($contact->photo), $contact->photo)), ['data' => $contact->phototype]);
      }
      // Logo
      if (!empty($contact->logo)) {
        $vcontact->add(VCard::LOGO, 'base64,' . base64_encode(pack('H' . strlen($contact->logo), $contact->logo)), ['data' => $contact->logotype]);
      }
      // Birthday
      if (!empty($contact->birthday)) {
        $vcontact->BIRTHDAY = date('Ymd', strtotime($contact->birthday));
      }
      // Role
      if (!empty($contact->role)) {
        $vcontact->TITLE = $contact->role;
      }
      // Org
      if (!empty($contact->company)) {
        $vcontact->ORG = $contact->company;
      }
      // Categories
      if (!empty($contact->category)) {
        $vcontact->CATEGORIES = $contact->category;
      }
      // URL
      if (!empty($contact->url)) {
        $vcontact->URL = $contact->url;
      }
      // Notes
      if (!empty($contact->notes)) {
        $vcontact->NOTES = $contact->notes;
      }
      // Geo
      if (!empty($contact->geo)) {
        $vcontact->GEO = $contact->geo;
      }
      // Timezone
      if (!empty($contact->timezone)) {
        $vcontact->TZ = $contact->timezone;
      }
      // Freebusy URL
      if (!empty($contact->freebusyurl)) {
        $vcontact->FBURL = $contact->freebusyurl;
      }
    }

    return $vcontact;
  }
}