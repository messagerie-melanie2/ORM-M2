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

/**
 * Classe listant les objets ICS pour les convertions
 * Elle se base sur la RFC 2445 iCalendar http://tools.ietf.org/html/rfc2445
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Lib Mélanie2
 *
 */
class ICS {
	/****
	 * CONSTANTES
	*/
	// Components
	/**
	 * Calendar Components (http://tools.ietf.org/html/rfc2445#section-4.6)
	 */
	const VCALENDAR = 'VCALENDAR';
	/**
	 * Event Component (http://tools.ietf.org/html/rfc2445#section-4.6.1)
	 */
	const VEVENT = 'VEVENT';
	/**
	 * To-do Component (http://tools.ietf.org/html/rfc2445#section-4.6.2)
	 */
	const VTODO = 'VTODO';
	/**
	 * Journal Component (http://tools.ietf.org/html/rfc2445#section-4.6.3)
	 */
	const VJOURNAL = 'VJOURNAL';
	/**
	 * Free/Busy Component (http://tools.ietf.org/html/rfc2445#section-4.6.4)
	 */
	const VFREEBUSY = 'VFREEBUSY';
	/**
	 * Time Zone Component (http://tools.ietf.org/html/rfc2445#section-4.6.5)
	 */
	const VTIMEZONE = 'VTIMEZONE';
	/**
	 * Alarm Component (http://tools.ietf.org/html/rfc2445#section-4.6.6)
	 */
	const VALARM = 'VALARM';

	const DAYLIGHT = 'DAYLIGHT';
	const STANDARD = 'STANDARD';

	// Calendar properties
	/**
	 * Calendar Scale (http://tools.ietf.org/html/rfc2445#section-4.7.1)
	 */
	const CALSCALE = 'CALSCALE';
	/**
	 * Method (http://tools.ietf.org/html/rfc2445#section-4.7.2)
	 */
	const METHOD = 'METHOD';
	/**
	 * Product Identifier (http://tools.ietf.org/html/rfc2445#section-4.7.3)
	 */
	const PRODID = 'PRODID';
	/**
	 * Version (http://tools.ietf.org/html/rfc2445#section-4.7.4)
	 */
	const VERSION = 'VERSION';

	// Properties
	/**
	 * Categories (http://tools.ietf.org/html/rfc2445#section-4.8.1.2)
	 */
	const CATEGORIES = 'CATEGORIES';
	/**
	 * Classification (http://tools.ietf.org/html/rfc2445#section-4.8.1.3)
	 */
	const CLASS_ = 'CLASS';
	const CLASS_PUBLIC = 'PUBLIC';
	const CLASS_PRIVATE = 'PRIVATE';
	const CLASS_CONFIDENTIAL = 'CONFIDENTIAL';
	/**
	 * Comment (http://tools.ietf.org/html/rfc2445#section-4.8.1.4)
	 */
	const COMMENT = 'COMMENT';
	/**
	 * Description (http://tools.ietf.org/html/rfc2445#section-4.8.1.5)
	 */
	const DESCRIPTION = 'DESCRIPTION';
	/**
	 * Geographic Position (http://tools.ietf.org/html/rfc2445#section-4.8.1.6)
	 */
	const GEO = 'GEO';
	/**
	 * Location (http://tools.ietf.org/html/rfc2445#section-4.8.1.7)
	 */
	const LOCATION = 'LOCATION';
	/**
	 * Percent Complete (http://tools.ietf.org/html/rfc2445#section-4.8.1.8)
	 */
	const PERCENT_COMPLETE = 'PERCENT-COMPLETE';
	/**
	 * Priority (http://tools.ietf.org/html/rfc2445#section-4.8.1.9)
	 */
	const PRIORITY = 'PRIORITY';
	/**
	 * Resources (http://tools.ietf.org/html/rfc2445#section-4.8.1.10)
	 */
	const RESOURCES = 'RESOURCES';
	/**
	 * Status (http://tools.ietf.org/html/rfc2445#section-4.8.1.11)
	 */
	const STATUS = 'STATUS';
	const STATUS_TENTATIVE = 'TENTATIVE';
	const STATUS_CONFIRMED = 'CONFIRMED';
	const STATUS_CANCELLED = 'CANCELLED';
	const STATUS_COMPLETED = 'COMPLETED';
	const STATUS_NEEDS_ACTION = 'NEEDS-ACTION';
	const STATUS_IN_PROCESS = 'IN-PROCESS';
	/**
	 * Summary (http://tools.ietf.org/html/rfc2445#section-4.8.1.12)
	 */
	const SUMMARY = 'SUMMARY';

	// Properties attributes
	/**
	 * Alternate Text Representation (http://tools.ietf.org/html/rfc2445#section-4.2.1)
	 */
	const ALTREP = 'ALTREP';
	/**
	 * Language (http://tools.ietf.org/html/rfc2445#section-4.2.10)
	 */
	const LANGUAGE = 'LANGUAGE';
	/**
	 * Value Data Types (http://tools.ietf.org/html/rfc2445#section-4.2.20)
	 * "BINARY" / "BOOLEAN" / "CAL-ADDRESS" / "DATE" / "DATE-TIME" / "DURATION" / "FLOAT" / "INTEGER" / "PERIOD" / "RECUR" / "TEXT" / "TIME" / "URI" / "UTC-OFFSET"
	 */
	const VALUE = 'VALUE';
	/**
	 * Binary (http://tools.ietf.org/html/rfc2445#section-4.3.1)
	 */
	const VALUE_BINARY = 'BINARY';
	/**
	 * Boolean (http://tools.ietf.org/html/rfc2445#section-4.3.2)
	 */
	const VALUE_BOOLEAN = 'BOOLEAN';
	/**
	 * Calendar User Address (http://tools.ietf.org/html/rfc2445#section-4.3.3)
	 */
	const VALUE_CAL_ADDRESS = 'CAL-ADDRESS';
	/**
	 * Date (http://tools.ietf.org/html/rfc2445#section-4.3.4)
	 */
	const VALUE_DATE = 'DATE';
	/**
	 * Date-Time (http://tools.ietf.org/html/rfc2445#section-4.3.5)
	 */
	const VALUE_DATE_TIME = 'DATE-TIME';
	/**
	 * Duration (http://tools.ietf.org/html/rfc2445#section-4.3.6)
	 */
	const VALUE_DURATION = 'DURATION';
	/**
	 * Float (http://tools.ietf.org/html/rfc2445#section-4.3.7)
	 */
	const VALUE_FLOAT = 'FLOAT';
	/**
	 * Integer (http://tools.ietf.org/html/rfc2445#section-4.3.8)
	 */
	const VALUE_INTEGER = 'INTEGER';
	/**
	 * Period of Time (http://tools.ietf.org/html/rfc2445#section-4.3.9)
	 */
	const VALUE_PERIOD = 'PERIOD';
	/**
	 * Recurrence Rule (http://tools.ietf.org/html/rfc2445#section-4.3.10)
	 */
	const VALUE_RECUR = 'RECUR';
	/**
	 * Text (http://tools.ietf.org/html/rfc2445#section-4.3.11)
	 */
	const VALUE_TEXT = 'TEXT';
	/**
	 * Time (http://tools.ietf.org/html/rfc2445#section-4.3.12)
	 */
	const VALUE_TIME = 'TIME';
	/**
	 * URI (http://tools.ietf.org/html/rfc2445#section-4.3.13)
	 */
	const VALUE_URI = 'URI';
	/**
	 * UTC Offset (http://tools.ietf.org/html/rfc2445#section-4.3.14)
	 */
	const VALUE_UTC_OFFSET = 'UTC-OFFSET';

	// Date and Time
	/**
	 * Date/Time Completed (http://tools.ietf.org/html/rfc2445#section-4.8.2.1)
	 */
	const COMPLETED = 'COMPLETED';
	/**
	 * Date/Time End (http://tools.ietf.org/html/rfc2445#section-4.8.2.2)
	 */
	const DTEND = 'DTEND';
	/**
	 * Date/Time Due (http://tools.ietf.org/html/rfc2445#section-4.8.2.3)
	 */
	const DUE = 'DUE';
	/**
	 * Date/Time Start (http://tools.ietf.org/html/rfc2445#section-4.8.2.4)
	 */
	const DTSTART = 'DTSTART';
	/**
	 * Duration (http://tools.ietf.org/html/rfc2445#section-4.8.2.5)
	 */
	const DURATION = 'DURATION';
	/**
	 * Free/Busy Time (http://tools.ietf.org/html/rfc2445#section-4.8.2.6)
	 */
	const FREEBUSY = 'FREEBUSY';
	/**
	 * Time Transparency (http://tools.ietf.org/html/rfc2445#section-4.8.2.6)
	 * "OPAQUE"      ;Blocks or opaque on busy time searches.
                / "TRANSPARENT" ;Transparent on busy time searches.
	 */
	const TRANSP = 'TRANSP';
	const TRANSP_OPAQUE = 'OPAQUE';
	const TRANSP_TRANSPARENT = 'TRANSPARENT';

	/**
	 * Date/Time Created (http://tools.ietf.org/html/rfc2445#section-4.8.7.1)
	 */
	const CREATED = 'CREATED';
	/**
	 * Date/Time Stamp (http://tools.ietf.org/html/rfc2445#section-4.8.7.2)
	 */
	const DTSTAMP = 'DTSTAMP';
	/**
	 * Last Modified (http://tools.ietf.org/html/rfc2445#section-4.8.7.3)
	 */
	const LAST_MODIFIED = 'LAST-MODIFIED';
	/**
	 * Sequence Number (http://tools.ietf.org/html/rfc2445#section-4.8.7.4)
	 */
	const SEQUENCE = 'SEQUENCE';

	// Date and Time attributes
	/**
	 * Time Zone Identifier (http://tools.ietf.org/html/rfc2445#section-4.2.19)
	 *                      (http://tools.ietf.org/html/rfc2445#section-4.8.3.1)
	 */
	const TZID = 'TZID';
	/**
	 * Time Zone Name (http://tools.ietf.org/html/rfc2445#section-4.8.3.2)
	 */
	const TZNAME = 'TZNAME';
	/**
	 * Time Zone Offset From (http://tools.ietf.org/html/rfc2445#section-4.8.3.3)
	 */
	const TZOFFSETFROM = 'TZOFFSETFROM';
	/**
	 * Time Zone Offset To (http://tools.ietf.org/html/rfc2445#section-4.8.3.4)
	 */
	const TZOFFSETTO = 'TZOFFSETTO';
	/**
	 * Time Zone URL (http://tools.ietf.org/html/rfc2445#section-4.8.3.5)
	 */
	const TZURL = 'TZURL';

	// Recurrence
	/**
	 * Exception Date/Times (http://tools.ietf.org/html/rfc2445#section-4.8.5.1)
	 */
	const EXDATE = 'EXDATE';
	/**
	 * Exception Rule (http://tools.ietf.org/html/rfc2445#section-4.8.5.2)
	 */
	const EXRULE = 'EXRULE';
	/**
	 * Recurrence Date/Times (http://tools.ietf.org/html/rfc2445#section-4.8.5.3)
	 */
	const RDATE = 'RDATE';
	/**
	 * Recurrence Rule (http://tools.ietf.org/html/rfc2445#section-4.8.5.4)
	 */
	const RRULE = 'RRULE';

	// Recurrence attributes
	/**
	 * "SECONDLY" / "MINUTELY" / "HOURLY" / "DAILY"
                / "WEEKLY" / "MONTHLY" / "YEARLY"
	 */
	const FREQ = 'FREQ';
	const FREQ_SECONDLY = 'SECONDLY';
	const FREQ_MINUTELY = 'MINUTELY';
	const FREQ_HOURLY = 'HOURLY';
	const FREQ_DAILY = 'DAILY';
	const FREQ_WEEKLY = 'WEEKLY';
	const FREQ_MONTHLY = 'MONTHLY';
	const FREQ_YEARLY = 'YEARLY';

	const INTERVAL = 'INTERVAL';
	const COUNT = 'COUNT';
	const UNTIL = 'UNTIL';
	/**
	 * "SU" / "MO" / "TU" / "WE" / "TH" / "FR" / "SA"
     ;Corresponding to SUNDAY, MONDAY, TUESDAY, WEDNESDAY, THURSDAY,
     ;FRIDAY, SATURDAY and SUNDAY days of the week.
	 */
	const BYDAY = 'BYDAY';
	const BYMONTH = 'BYMONTH';
	/**
	 * "SU" / "MO" / "TU" / "WE" / "TH" / "FR" / "SA"
	 ;Corresponding to SUNDAY, MONDAY, TUESDAY, WEDNESDAY, THURSDAY,
	 ;FRIDAY, SATURDAY and SUNDAY days of the week.
	 */
	const WKST = 'WKST';
	const BYMONTHDAY = 'BYMONTHDAY';
	const BYWEEKNO = 'BYWEEKNO';
	const BYYEARDAY = 'BYYEARDAY';
	const BYSETPOS = 'BYSETPOS';

	const SU = 'SU';
	const MO = 'MO';
	const TU = 'TU';
	const WE = 'WE';
	const TH = 'TH';
	const FR = 'FR';
	const SA = 'SA';

	// Alarm attributes
	/**
	 * Action (http://tools.ietf.org/html/rfc2445#section-4.8.6.1)
	 */
	const ACTION = 'ACTION';
	const ACTION_AUDIO = 'AUDIO';
	const ACTION_DISPLAY = 'DISPLAY';
	const ACTION_EMAIL = 'EMAIL';
	const ACTION_PROCEDURE = 'PROCEDURE';

	/**
	 * Repeat Count (http://tools.ietf.org/html/rfc2445#section-4.8.6.2)
	 */
	const REPEAT = 'REPEAT';
	/**
	 * Trigger (http://tools.ietf.org/html/rfc2445#section-4.8.6.3)
	 */
	const TRIGGER = 'TRIGGER';

	/**
	 * Alarm Trigger Relationship (http://tools.ietf.org/html/rfc2445#section-4.2.14)
	 * "START"       ; Trigger off of start
                        / "END")        ; Trigger off of end
	 */
	const RELATED = 'RELATED';

	// Attendees
	/**
	 * Attendee (http://tools.ietf.org/html/rfc2445#section-4.8.4.1)
	 */
	const ATTENDEE = 'ATTENDEE';
	/**
	 * Contact (http://tools.ietf.org/html/rfc2445#section-4.8.4.2)
	 */
	const CONTACT = 'CONTACT';
	/**
	 * Organizer (http://tools.ietf.org/html/rfc2445#section-4.8.4.3)
	 */
	const ORGANIZER = 'ORGANIZER';
	/**
	 * Recurrence ID (http://tools.ietf.org/html/rfc2445#section-4.8.4.4)
	 */
	const RECURRENCE_ID = 'RECURRENCE-ID';
	/**
	 * Related To (http://tools.ietf.org/html/rfc2445#section-4.8.4.5)
	 */
	const RELATED_TO = 'RELATED-TO';
	/**
	 * Uniform Resource Locator (http://tools.ietf.org/html/rfc2445#section-4.8.4.6)
	 */
	const URL = 'URL';
	/**
	 * Unique Identifier (http://tools.ietf.org/html/rfc2445#section-4.8.4.7)
	 */
	const UID = 'UID';
	/**
	 * Relationship Type (http://tools.ietf.org/html/rfc2445#section-4.2.15)
	 */
	const RELTYPE = 'RELTYPE';
	/**
	 * Parent relationship. Default.
	 */
	const RELTYPE_PARENT = 'PARENT';
	/**
	 * Child relationship
	 */
	const RELTYPE_CHILD = 'CHILD';
	/**
	 * Sibling relationship
	 */
	const RELTYPE_SIBLING = 'SIBLING';
	/**
	 * Recurrence Identifier Range (http://tools.ietf.org/html/rfc2445#section-4.2.13)
	 */
	const RANGE = 'RANGE';

	// Attendees attributes
	/**
	 * Common Name (http://tools.ietf.org/html/rfc2445#section-4.2.2)
	 */
	const CN = 'CN';
	/**
	 * Calendar User Type (http://tools.ietf.org/html/rfc2445#section-4.2.3)
	 * 
	                     / "INDIVIDUAL"   ; An individual
                         / "GROUP"        ; A group of individuals
                         / "RESOURCE"     ; A physical resource
                         / "ROOM"         ; A room resource
                         / "UNKNOWN"      ; Otherwise not known
	 */
	const CUTYPE = 'CUTYPE';
	const CUTYPE_INDIVIDUAL = 'INDIVIDUAL';
	const CUTYPE_GROUP = 'GROUP';
	const CUTYPE_RESOURCE = 'RESOURCE';
	const CUTYPE_ROOM = 'ROOM';
	const CUTYPE_UNKNOWN = 'UNKNOWN';
	/**
	 * Delegators (http://tools.ietf.org/html/rfc2445#section-4.2.3)
	 */
	const DELEGATED_FROM = 'DELEGATED-FROM';
	/**
	 * Delegatees (http://tools.ietf.org/html/rfc2445#section-4.2.5)
	 */
	const DELEGATED_TO = 'DELEGATED-TO';
	/**
	 * Directory Entry Reference (http://tools.ietf.org/html/rfc2445#section-4.2.6)
	 */
	const DIR = 'DIR';
	/**
	 * Group or List Membership (http://tools.ietf.org/html/rfc2445#section-4.2.11)
	 */
	const MEMBER = 'MEMBER';
	/**
	 * Participation Status (http://tools.ietf.org/html/rfc2445#section-4.2.12)
	 * "NEEDS-ACTION"        ; To-do needs action
                        / "ACCEPTED"            ; To-do accepted
                        / "DECLINED"            ; To-do declined
                        / "TENTATIVE"           ; To-do tentatively
                                                ; accepted
                        / "DELEGATED"           ; To-do delegated
                        / "COMPLETED"           ; To-do completed.
                                                ; COMPLETED property has
                                                ;date/time completed.
                        / "IN-PROCESS"          ; To-do in process of
                                                ; being completed
	 */
	const PARTSTAT = 'PARTSTAT';
	const PARTSTAT_NEEDS_ACTION = 'NEEDS-ACTION';
	const PARTSTAT_ACCEPTED = 'ACCEPTED';
	const PARTSTAT_DECLINED = 'DECLINED';
	const PARTSTAT_TENTATIVE = 'TENTATIVE';
	const PARTSTAT_DELEGATED = 'DELEGATED';
	const PARTSTAT_COMPLETED = 'COMPLETED';
	const PARTSTAT_IN_PROCESS = 'IN-PROCESS';
	/**
	 * Participation Role (http://tools.ietf.org/html/rfc2445#section-4.2.16)
	 * "CHAIR"               ; Indicates chair of the
                                        ; calendar entity
                / "REQ-PARTICIPANT"     ; Indicates a participant whose
                                        ; participation is required
                / "OPT-PARTICIPANT"     ; Indicates a participant whose
                                        ; participation is optional
                / "NON-PARTICIPANT"     ; Indicates a participant who is
                                        ; copied for information
                                        ; purposes only
	 */
	const ROLE = 'ROLE';
	const ROLE_CHAIR = 'CHAIR';
	const ROLE_REQ_PARTICIPANT = 'REQ-PARTICIPANT';
	const ROLE_OPT_PARTICIPANT = 'OPT-PARTICIPANT';
	const ROLE_NON_PARTICIPANT = 'NON-PARTICIPANT';
	/**
	 * RSVP Expectation (http://tools.ietf.org/html/rfc2445#section-4.2.17)
	 */
	const RSVP = 'RSVP';
	const RSVP_TRUE = 'TRUE';
	const RSVP_FALSE = 'FALSE';
	/**
	 * Sent By (http://tools.ietf.org/html/rfc2445#section-4.2.18)
	 * "TRUE" / "FALSE"
	 */
	const SENT_BY = 'SENT-BY';

	// Attachment
	/**
	 * Attachment (http://tools.ietf.org/html/rfc2445#section-4.8.1.1)
	 */
	const ATTACH = 'ATTACH';

	// Attachment attributes
	/**
	 * Inline Encoding (http://tools.ietf.org/html/rfc2445#section-4.2.7)
	 */
	const ENCODING = 'ENCODING';
	/**
	 * "8bit" text encoding is defined in [RFC 2045]
	 */
	const ENCODING_8BIT = '8BIT';
	/**
	 * "BASE64" binary encoding format is defined in [RFC 2045]
	 */
	const ENCODING_BASE64 = 'BASE64';
	/**
	 * Format Type (http://tools.ietf.org/html/rfc2445#section-4.2.8)
	 */
	const FMTTYPE = 'FMTTYPE';
	/**
	 * Size of the Attachment (http://tools.ietf.org/html/draft-daboo-caldav-attachments-00#page-11)
	 */
	const SIZE = 'SIZE';

	// Freebusy
	/**
	 * Free/Busy Time Type (http://tools.ietf.org/html/rfc2445#section-4.2.9)
	 * "FREE" / "BUSY"
                        / "BUSY-UNAVAILABLE" / "BUSY-TENTATIVE"
	 */
	const FBTYPE = 'FBTYPE';

	// Mozilla ICS
	/**
	 * Faked Master quand la récurrence maitre n'existe pas
	 * "1" / "0"
	 */
	const X_MOZ_FAKED_MASTER = 'X-MOZ-FAKED-MASTER';
	/**
	 * Nombre de modification par Lightning pour l'évènement
	 */
	const X_MOZ_GENERATION = 'X-MOZ-GENERATION';
	/**
	 * Date de dernier acquittement pour l'alarme depuis Lightning
	 */
	const X_MOZ_LASTACK = 'X-MOZ-LASTACK';
	/**
	 * Date de snooze pour l'alarme depuis Lightning
	 */
	const X_MOZ_SNOOZE_TIME = 'X-MOZ-SNOOZE-TIME';
	/**
	 * Lightning doit il envoyer les invitations aux participants
	 */
	const X_MOZ_SEND_INVITATIONS = 'X-MOZ-SEND-INVITATIONS';
  	/**
   	 * Lightning doit il envoyer les invitations aux nouveaux participants
   	 */
	const X_MOZ_SEND_INVITATIONS_UNDISCLOSED = 'X-MOZ-SEND-INVITATIONS-UNDISCLOSED';
	/**
	 * Nom d'une pièce jointe (non défini dans les RFC ??)
	 */
	const X_MOZILLA_CALDAV_ATTACHMENT_NAME = 'X-MOZILLA-CALDAV-ATTACHMENT-NAME';
	/**
	 * SEQUENCE recu par invitation
	 */
	const X_MOZ_RECEIVED_SEQUENCE = 'X-MOZ-RECEIVED-SEQUENCE';
	/**
	 * DTSTAMP recu par invitation
	 */
	const X_MOZ_RECEIVED_DTSTAMP = 'X-MOZ-RECEIVED-DTSTAMP';

	// EVOLUTION ICS
  	/**
	 * Attachment name for Evolution
   	 */
	const X_EVOLUTION_CALDAV_ATTACHMENT_NAME = 'X-EVOLUTION-CALDAV-ATTACHMENT-NAME';

	// WR ICS
	/**
	 * Specifies the description of the calendar
	 */
	const X_WR_CALDESC = 'X-WR-CALDESC';
	/**
	 * Specifies the name of the calendar
	 */
	const X_WR_CALNAME = 'X-WR-CALNAME';
	/**
	 * Specifies a globally unique identifier for the calendar
	 */
	const X_WR_RELCALID = 'X-WR-RELCALID';
	/**
	 * Specifies the timezone of the calendar
	 */
	const X_WR_TIMEZONE = 'X-WR-TIMEZONE';

	// Others X
	/**
	 * TimeZone location
	 */
	const X_LIC_LOCATION = 'X-LIC-LOCATION';

	// CM2V3 X
	/**
	 * Envoie de la pièce jointe dans l'invitation
	 */
	const X_CM2V3_SEND_ATTACH_INVITATION = 'X-CM2V3-SEND-ATTACH-INVITATION';
	/**
	 * Hash de la pièce jointe pour la mise en cache sur le client
	 */
	const X_CM2V3_ATTACH_HASH = 'X-CM2V3-ATTACH-HASH';
	/**
	 * Action effectuée par le Courrielleur : CREATE, MOVE, DELETE, COPY
	 */
	const X_CM2V3_ACTION = 'X-CM2V3-ACTION';
	
	// M2
	/**
	 * Gestion du champ X-M2-ORG-MAIL pour l'ORGANIZER
	 */
	const X_M2_ORG_MAIL = 'X-M2-ORG-MAIL';

	/**
	 * 0006294: Ajouter l'information dans un participant quand il a été enregistré en attente
	 */
	const X_MEL_EVENT_SAVED = 'X-MEL-EVENT-SAVED';

	// CALDAV X
	/**
	 * Calendar Owner
	 */
	const X_CALDAV_CALENDAR_OWNER = 'X-CALDAV-CALENDAR-OWNER';
	/**
	 * Calendar ID
	 */
	const X_CALDAV_CALENDAR_ID = 'X-CALDAV-CALENDAR-ID';
}
