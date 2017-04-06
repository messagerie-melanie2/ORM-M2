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
 * Classe listant les objets VCard pour les convertions
 * Elle se base sur la RFC 6350 VCard https://tools.ietf.org/html/rfc6350
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage Lib Mélanie2
 *
 */
class VCard {
	/****
	 * CONSTANTES
	 */
  // General Properties
  /**
   * VCard component (https://tools.ietf.org/html/rfc6350#section-6)
   */
  const VCARD = 'VCARD';
  /**
   * Purpose:  To denote the beginning of a syntactic entity within a
      text/vcard content-type.

   Value type:  text

   Cardinality:  1

   Special notes:  The content entity MUST begin with the BEGIN property
      with a value of "VCARD".  The value is case-insensitive.

      The BEGIN property is used in conjunction with the END property to
      delimit an entity containing a related set of properties within a
      text/vcard content-type.  This construct can be used instead of
      including multiple vCards as body parts inside of a multipart/
      alternative MIME message.  It is provided for applications that
      wish to define content that can contain multiple entities within
      the same text/vcard content-type or to define content that can be
      identifiable outside of a MIME environment.

   ABNF:

     BEGIN-param = 0" "  ; no parameter allowed
     BEGIN-value = "VCARD"

   Example:

         BEGIN:VCARD

   * https://tools.ietf.org/html/rfc6350#section-6.1.1
   */
  const BEGIN = 'BEGIN';
  /**
   * Purpose:  To denote the end of a syntactic entity within a text/vcard
      content-type.

   Value type:  text

   Cardinality:  1

   Special notes:  The content entity MUST end with the END type with a
      value of "VCARD".  The value is case-insensitive.
      The END property is used in conjunction with the BEGIN property to
      delimit an entity containing a related set of properties within a
      text/vcard content-type.  This construct can be used instead of or
      in addition to wrapping separate sets of information inside
      additional MIME headers.  It is provided for applications that
      wish to define content that can contain multiple entities within
      the same text/vcard content-type or to define content that can be
      identifiable outside of a MIME environment.

   ABNF:

     END-param = 0" "  ; no parameter allowed
     END-value = "VCARD"

   Example:

         END:VCARD

   * https://tools.ietf.org/html/rfc6350#section-6.1.2
   */
  const END = 'END';
  /**
   * Purpose:  To identify the source of directory information contained
      in the content type.

   Value type:  uri

   Cardinality:  *

   Special notes:  The SOURCE property is used to provide the means by
      which applications knowledgable in the given directory service
      protocol can obtain additional or more up-to-date information from
      the directory service.  It contains a URI as defined in [RFC3986]
      and/or other information referencing the vCard to which the
      information pertains.  When directory information is available
      from more than one source, the sending entity can pick what it
      considers to be the best source, or multiple SOURCE properties can
      be included.

   ABNF:

     SOURCE-param = "VALUE=uri" / pid-param / pref-param / altid-param
                  / mediatype-param / any-param
     SOURCE-value = URI

   Examples:

     SOURCE:ldap://ldap.example.com/cn=Babs%20Jensen,%20o=Babsco,%20c=US
     SOURCE:http://directory.example.com/addressbooks/jdoe/
      Jean%20Dupont.vcf

   * https://tools.ietf.org/html/rfc6350#section-6.1.3
   */
  const SOURCE = 'SOURCE';
  /**
   * Purpose:  To specify the kind of object the vCard represents.

   Value type:  A single text value.

   Cardinality:  *1

   Special notes:  The value may be one of the following:

      "individual"  for a vCard representing a single person or entity.
         This is the default kind of vCard.

      "group"  for a vCard representing a group of persons or entities.
         The group's member entities can be other vCards or other types
         of entities, such as email addresses or web sites.  A group
         vCard will usually contain MEMBER properties to specify the
         members of the group, but it is not required to.  A group vCard
         without MEMBER properties can be considered an abstract
         grouping, or one whose members are known empirically (perhaps
         "IETF Participants" or "Republican U.S. Senators").

         All properties in a group vCard apply to the group as a whole,
         and not to any particular MEMBER.  For example, an EMAIL
         property might specify the address of a mailing list associated
         with the group, and an IMPP property might refer to a group
         chat room.

      "org"  for a vCard representing an organization.  An organization
         vCard will not (in fact, MUST NOT) contain MEMBER properties,
         and so these are something of a cross between "individual" and
         "group".  An organization is a single entity, but not a person.
         It might represent a business or government, a department or
         division within a business or government, a club, an
         association, or the like.

         All properties in an organization vCard apply to the
         organization as a whole, as is the case with a group vCard.
         For example, an EMAIL property might specify the address of a
         contact point for the organization.

     "location"  for a named geographical place.  A location vCard will
         usually contain a GEO property, but it is not required to.  A
         location vCard without a GEO property can be considered an
         abstract location, or one whose definition is known empirically
         (perhaps "New England" or "The Seashore").

         All properties in a location vCard apply to the location
         itself, and not with any entity that might exist at that
         location.  For example, in a vCard for an office building, an
         ADR property might give the mailing address for the building,
         and a TEL property might specify the telephone number of the
         receptionist.

      An x-name.  vCards MAY include private or experimental values for
         KIND.  Remember that x-name values are not intended for general
         use and are unlikely to interoperate.

      An iana-token.  Additional values may be registered with IANA (see
         Section 10.3.4).  A new value's specification document MUST
         specify which properties make sense for that new kind of vCard
         and which do not.

      Implementations MUST support the specific string values defined
      above.  If this property is absent, "individual" MUST be assumed
      as the default.  If this property is present but the
      implementation does not understand its value (the value is an
      x-name or iana-token that the implementation does not support),
      the implementation SHOULD act in a neutral way, which usually
      means treating the vCard as though its kind were "individual".
      The presence of MEMBER properties MAY, however, be taken as an
      indication that the unknown kind is an extension of "group".

      Clients often need to visually distinguish contacts based on what
      they represent, and the KIND property provides a direct way for
      them to do so.  For example, when displaying contacts in a list,
      an icon could be displayed next to each one, using distinctive
      icons for the different kinds; a client might use an outline of a
      single person to represent an "individual", an outline of multiple
      people to represent a "group", and so on.  Alternatively, or in
      addition, a client might choose to segregate different kinds of
      vCards to different panes, tabs, or selections in the user
      interface.

      Some clients might also make functional distinctions among the
      kinds, ignoring "location" vCards for some purposes and
      considering only "location" vCards for others.

      When designing those sorts of visual and functional distinctions,
      client implementations have to decide how to fit unsupported kinds
      into the scheme.  What icon is used for them?  The one for
      "individual"?  A unique one, such as an icon of a question mark?
      Which tab do they go into?  It is beyond the scope of this
      specification to answer these questions, but these are things
      implementers need to consider.

   ABNF:

     KIND-param = "VALUE=text" / any-param
     KIND-value = "individual" / "group" / "org" / "location"
                / iana-token / x-name

   Example:

      This represents someone named Jane Doe working in the marketing
      department of the North American division of ABC Inc.

         BEGIN:VCARD
         VERSION:4.0
         KIND:individual
         FN:Jane Doe
         ORG:ABC\, Inc.;North American Division;Marketing
         END:VCARD

   This represents the department itself, commonly known as ABC
   Marketing.

         BEGIN:VCARD
         VERSION:4.0
         KIND:org
         FN:ABC Marketing
         ORG:ABC\, Inc.;North American Division;Marketing
         END:VCARD

   * https://tools.ietf.org/html/rfc6350#section-6.1.4
   */
  const KIND = 'KIND';
  /**
   * Purpose:  To include extended XML-encoded vCard data in a plain
      vCard.

   Value type:  A single text value.

   Cardinality:  *

   Special notes:  The content of this property is a single XML 1.0
      [W3C.REC-xml-20081126] element whose namespace MUST be explicitly
      specified using the xmlns attribute and MUST NOT be the vCard 4
      namespace ("urn:ietf:params:xml:ns:vcard-4.0").  (This implies
      that it cannot duplicate a standard vCard property.)  The element
      is to be interpreted as if it was contained in a <vcard> element,
      as defined in [RFC6351].

      The fragment is subject to normal line folding and escaping, i.e.,
      replace all backslashes with "\\", then replace all newlines with
      "\n", then fold long lines.

      Support for this property is OPTIONAL, but implementations of this
      specification MUST preserve instances of this property when
      propagating vCards.

      See [RFC6351] for more information on the intended use of this
      property.

   ABNF:

     XML-param = "VALUE=text" / altid-param
     XML-value = text

   * https://tools.ietf.org/html/rfc6350#section-6.1.5
   */
  const XML = 'XML';

  // Identification Properties
  /**
   * Purpose:  To specify the formatted text corresponding to the name of
      the object the vCard represents.

   Value type:  A single text value.

   Cardinality:  1*

   Special notes:  This property is based on the semantics of the X.520
      Common Name attribute [CCITT.X520.1988].  The property MUST be
      present in the vCard object.

   ABNF:

     FN-param = "VALUE=text" / type-param / language-param / altid-param
              / pid-param / pref-param / any-param
     FN-value = text

   Example:

         FN:Mr. John Q. Public\, Esq.

   * https://tools.ietf.org/html/rfc6350#section-6.2.1
   */
  const FN = 'FN';
  /**
   * Purpose:  To specify the components of the name of the object the
      vCard represents.

   Value type:  A single structured text value.  Each component can have
      multiple values.

   Cardinality:  *1

   Special note:  The structured property value corresponds, in
      sequence, to the Family Names (also known as surnames), Given
      Names, Additional Names, Honorific Prefixes, and Honorific
      Suffixes.  The text components are separated by the SEMICOLON
      character (U+003B).  Individual text components can include
      multiple text values separated by the COMMA character (U+002C).
      This property is based on the semantics of the X.520 individual
      name attributes [CCITT.X520.1988].  The property SHOULD be present
      in the vCard object when the name of the object the vCard
      represents follows the X.520 model.

      The SORT-AS parameter MAY be applied to this property.

   ABNF:

     N-param = "VALUE=text" / sort-as-param / language-param
             / altid-param / any-param
     N-value = list-component 4(";" list-component)

   Examples:

             N:Public;John;Quinlan;Mr.;Esq.

             N:Stevenson;John;Philip,Paul;Dr.;Jr.,M.D.,A.C.P.

   * https://tools.ietf.org/html/rfc6350#section-6.2.2
   */
  const N = 'N';
  /**
   * Purpose:  To specify the text corresponding to the nickname of the
      object the vCard represents.

   Value type:  One or more text values separated by a COMMA character
      (U+002C).

   Cardinality:  *

   Special note:  The nickname is the descriptive name given instead of
      or in addition to the one belonging to the object the vCard
      represents.  It can also be used to specify a familiar form of a
      proper name specified by the FN or N properties.

   ABNF:

     NICKNAME-param = "VALUE=text" / type-param / language-param
                    / altid-param / pid-param / pref-param / any-param
     NICKNAME-value = text-list

   Examples:

             NICKNAME:Robbie

             NICKNAME:Jim,Jimmie

             NICKNAME;TYPE=work:Boss

   * https://tools.ietf.org/html/rfc6350#section-6.2.3
   */
  const NICKNAME = 'NICKNAME';
  /**
   * Purpose:  To specify an image or photograph information that
      annotates some aspect of the object the vCard represents.

   Value type:  A single URI.

   Cardinality:  *

   ABNF:

     PHOTO-param = "VALUE=uri" / altid-param / type-param
                 / mediatype-param / pref-param / pid-param / any-param
     PHOTO-value = URI

   Examples:

       PHOTO:http://www.example.com/pub/photos/jqpublic.gif

       PHOTO:data:image/jpeg;base64,MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhv
        AQEEBQAwdzELMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bm
        ljYXRpb25zIENvcnBvcmF0aW9uMRwwGgYDVQQLExNJbmZvcm1hdGlvbiBTeXN0
        <...remainder of base64-encoded data...>

   * https://tools.ietf.org/html/rfc6350#section-6.2.4
   */
  const PHOTO = 'PHOTO';
  /**
   * Purpose:  To specify the birth date of the object the vCard
      represents.

   Value type:  The default is a single date-and-or-time value.  It can
      also be reset to a single text value.

   Cardinality:  *1

   ABNF:

     BDAY-param = BDAY-param-date / BDAY-param-text
     BDAY-value = date-and-or-time / text
       ; Value and parameter MUST match.

     BDAY-param-date = "VALUE=date-and-or-time"
     BDAY-param-text = "VALUE=text" / language-param

     BDAY-param =/ altid-param / calscale-param / any-param
       ; calscale-param can only be present when BDAY-value is
       ; date-and-or-time and actually contains a date or date-time.

   Examples:

             BDAY:19960415
             BDAY:--0415
             BDAY;19531015T231000Z
             BDAY;VALUE=text:circa 1800
   * https://tools.ietf.org/html/rfc6350#section-6.2.5
   */
  const BDAY = 'BDAY';
  /**
   *  Purpose:  The date of marriage, or equivalent, of the object the
      vCard represents.

   Value type:  The default is a single date-and-or-time value.  It can
      also be reset to a single text value.

   Cardinality:  *1

   ABNF:

     ANNIVERSARY-param = "VALUE=" ("date-and-or-time" / "text")
     ANNIVERSARY-value = date-and-or-time / text
       ; Value and parameter MUST match.

     ANNIVERSARY-param =/ altid-param / calscale-param / any-param
       ; calscale-param can only be present when ANNIVERSARY-value is
       ; date-and-or-time and actually contains a date or date-time.

   Examples:

             ANNIVERSARY:19960415

   * https://tools.ietf.org/html/rfc6350#section-6.2.6
   */
  const ANNIVERSARY = 'ANNIVERSARY';
  /**
   * Purpose:  To specify the components of the sex and gender identity of
      the object the vCard represents.

   Value type:  A single structured value with two components.  Each
      component has a single text value.

   Cardinality:  *1

   Special notes:  The components correspond, in sequence, to the sex
      (biological), and gender identity.  Each component is optional.

      Sex component:  A single letter.  M stands for "male", F stands
         for "female", O stands for "other", N stands for "none or not
         applicable", U stands for "unknown".

      Gender identity component:  Free-form text.

   ABNF:

                   GENDER-param = "VALUE=text" / any-param
                   GENDER-value = sex [";" text]

                   sex = "" / "M" / "F" / "O" / "N" / "U"

   Examples:

     GENDER:M
     GENDER:F
     GENDER:M;Fellow
     GENDER:F;grrrl
     GENDER:O;intersex
     GENDER:;it's complicated

   * https://tools.ietf.org/html/rfc6350#section-6.2.7
   */
  const GENDER = 'GENDER';

  // Delivery Addressing Properties
  /**
   * Purpose:  To specify the components of the delivery address for the
      vCard object.

   Value type:  A single structured text value, separated by the
      SEMICOLON character (U+003B).

   Cardinality:  *

   Special notes:  The structured type value consists of a sequence of
      address components.  The component values MUST be specified in
      their corresponding position.  The structured type value
      corresponds, in sequence, to
         the post office box;
         the extended address (e.g., apartment or suite number);
         the street address;
         the locality (e.g., city);
         the region (e.g., state or province);
         the postal code;
         the country name (full name in the language specified in
         Section 5.1).

      When a component value is missing, the associated component
      separator MUST still be specified.

      Experience with vCard 3 has shown that the first two components
      (post office box and extended address) are plagued with many
      interoperability issues.  To ensure maximal interoperability,
      their values SHOULD be empty.

      The text components are separated by the SEMICOLON character
      (U+003B).  Where it makes semantic sense, individual text
      components can include multiple text values (e.g., a "street"
      component with multiple lines) separated by the COMMA character
      (U+002C).

      The property can include the "PREF" parameter to indicate the
      preferred delivery address when more than one address is
      specified.

      The GEO and TZ parameters MAY be used with this property.

      The property can also include a "LABEL" parameter to present a
      delivery address label for the address.  Its value is a plain-text
      string representing the formatted address.  Newlines are encoded
      as \n, as they are for property values.

   ABNF:

     label-param = "LABEL=" param-value

     ADR-param = "VALUE=text" / label-param / language-param
               / geo-parameter / tz-parameter / altid-param / pid-param
               / pref-param / type-param / any-param

     ADR-value = ADR-component-pobox ";" ADR-component-ext ";"
                 ADR-component-street ";" ADR-component-locality ";"
                 ADR-component-region ";" ADR-component-code ";"
                 ADR-component-country
     ADR-component-pobox    = list-component
     ADR-component-ext      = list-component
     ADR-component-street   = list-component
     ADR-component-locality = list-component
     ADR-component-region   = list-component
     ADR-component-code     = list-component
     ADR-component-country  = list-component

   Example: In this example, the post office box and the extended
   address are absent.

     ADR;GEO="geo:12.3457,78.910";LABEL="Mr. John Q. Public, Esq.\n
      Mail Drop: TNE QB\n123 Main Street\nAny Town, CA  91921-1234\n
      U.S.A.":;;123 Main Street;Any Town;CA;91921-1234;U.S.A.

   * https://tools.ietf.org/html/rfc6350#section-6.3.1
   */
  const ADR = 'ADR';

  // Communications Properties
  /**
   * Purpose:  To specify the telephone number for telephony communication
      with the object the vCard represents.

   Value type:  By default, it is a single free-form text value (for
      backward compatibility with vCard 3), but it SHOULD be reset to a
      URI value.  It is expected that the URI scheme will be "tel", as
      specified in [RFC3966], but other schemes MAY be used.

   Cardinality:  *

   Special notes:  This property is based on the X.520 Telephone Number
      attribute [CCITT.X520.1988].

      The property can include the "PREF" parameter to indicate a
      preferred-use telephone number.

      The property can include the parameter "TYPE" to specify intended
      use for the telephone number.  The predefined values for the TYPE
      parameter are:

   +-----------+-------------------------------------------------------+
   | Value     | Description                                           |
   +-----------+-------------------------------------------------------+
   | text      | Indicates that the telephone number supports text     |
   |           | messages (SMS).                                       |
   | voice     | Indicates a voice telephone number.                   |
   | fax       | Indicates a facsimile telephone number.               |
   | cell      | Indicates a cellular or mobile telephone number.      |
   | video     | Indicates a video conferencing telephone number.      |
   | pager     | Indicates a paging device telephone number.           |
   | textphone | Indicates a telecommunication device for people with  |
   |           | hearing or speech difficulties.                       |
   +-----------+-------------------------------------------------------+

      The default type is "voice".  These type parameter values can be
      specified as a parameter list (e.g., TYPE=text;TYPE=voice) or as a
      value list (e.g., TYPE="text,voice").  The default can be
      overridden to another set of values by specifying one or more
      alternate values.  For example, the default TYPE of "voice" can be
      reset to a VOICE and FAX telephone number by the value list
      TYPE="voice,fax".

      If this property's value is a URI that can also be used for
      instant messaging, the IMPP (Section 6.4.3) property SHOULD be
      used in addition to this property.

   ABNF:

     TEL-param = TEL-text-param / TEL-uri-param
     TEL-value = TEL-text-value / TEL-uri-value
       ; Value and parameter MUST match.

     TEL-text-param = "VALUE=text"
     TEL-text-value = text

     TEL-uri-param = "VALUE=uri" / mediatype-param
     TEL-uri-value = URI

     TEL-param =/ type-param / pid-param / pref-param / altid-param
                / any-param

     type-param-tel = "text" / "voice" / "fax" / "cell" / "video"
                    / "pager" / "textphone" / iana-token / x-name
       ; type-param-tel MUST NOT be used with a property other than TEL.

   Example:

     TEL;VALUE=uri;PREF=1;TYPE="voice,home":tel:+1-555-555-5555;ext=5555
     TEL;VALUE=uri;TYPE=home:tel:+33-01-23-45-67

   * https://tools.ietf.org/html/rfc6350#section-6.4.1
   */
  const TEL = 'TEL';
  /**
   * Purpose:  To specify the electronic mail address for communication
      with the object the vCard represents.

   Value type:  A single text value.

   Cardinality:  *

   Special notes:  The property can include tye "PREF" parameter to
      indicate a preferred-use email address when more than one is
      specified.

      Even though the value is free-form UTF-8 text, it is likely to be
      interpreted by a Mail User Agent (MUA) as an "addr-spec", as
      defined in [RFC5322], Section 3.4.1.  Readers should also be aware
      of the current work toward internationalized email addresses
      [RFC5335bis].

   ABNF:

     EMAIL-param = "VALUE=text" / pid-param / pref-param / type-param
                 / altid-param / any-param
     EMAIL-value = text

   Example:

           EMAIL;TYPE=work:jqpublic@xyz.example.com

           EMAIL;PREF=1:jane_doe@example.com

   * https://tools.ietf.org/html/rfc6350#section-6.4.2
   */
  const EMAIL = 'EMAIL';
  /**
   * Purpose:  To specify the URI for instant messaging and presence
      protocol communications with the object the vCard represents.

   Value type:  A single URI.

   Cardinality:  *

   Special notes:  The property may include the "PREF" parameter to
      indicate that this is a preferred address and has the same
      semantics as the "PREF" parameter in a TEL property.
      If this property's value is a URI that can be used for voice
      and/or video, the TEL property (Section 6.4.1) SHOULD be used in
      addition to this property.

      This property is adapted from [RFC4770], which is made obsolete by
      this document.

   ABNF:

     IMPP-param = "VALUE=uri" / pid-param / pref-param / type-param
                / mediatype-param / altid-param / any-param
     IMPP-value = URI

   Example:

       IMPP;PREF=1:xmpp:alice@example.com

   * https://tools.ietf.org/html/rfc6350#section-6.4.3
   */
  const IMPP = 'IMPP';
  /**
   * Purpose:  To specify the language(s) that may be used for contacting
      the entity associated with the vCard.

   Value type:  A single language-tag value.

   Cardinality:  *

   ABNF:

     LANG-param = "VALUE=language-tag" / pid-param / pref-param
                / altid-param / type-param / any-param
     LANG-value = Language-Tag

   Example:

       LANG;TYPE=work;PREF=1:en
       LANG;TYPE=work;PREF=2:fr
       LANG;TYPE=home:fr

   * https://tools.ietf.org/html/rfc6350#section-6.4.4
   */
  const LANG = 'LANG';

  // Geographical Properties
  /**
   * Purpose:  To specify information related to the time zone of the
      object the vCard represents.

   Value type:  The default is a single text value.  It can also be
      reset to a single URI or utc-offset value.

   Cardinality:  *

   Special notes:  It is expected that names from the public-domain
      Olson database [TZ-DB] will be used, but this is not a
      restriction.  See also [IANA-TZ].

      Efforts are currently being directed at creating a standard URI
      scheme for expressing time zone information.  Usage of such a
      scheme would ensure a high level of interoperability between
      implementations that support it.

      Note that utc-offset values SHOULD NOT be used because the UTC
      offset varies with time -- not just because of the usual daylight
      saving time shifts that occur in may regions, but often entire
      regions will "re-base" their overall offset.  The actual offset
      may be +/- 1 hour (or perhaps a little more) than the one given.

   ABNF:

     TZ-param = "VALUE=" ("text" / "uri" / "utc-offset")
     TZ-value = text / URI / utc-offset
       ; Value and parameter MUST match.

     TZ-param =/ altid-param / pid-param / pref-param / type-param
               / mediatype-param / any-param

   Examples:

     TZ:Raleigh/North America

     TZ;VALUE=utc-offset:-0500
       ; Note: utc-offset format is NOT RECOMMENDED.

   * https://tools.ietf.org/html/rfc6350#section-6.5.1
   */
  const TZ = 'TZ';
  /**
   * Purpose:  To specify information related to the global positioning of
      the object the vCard represents.

   Value type:  A single URI.

   Cardinality:  *

   Special notes:  The "geo" URI scheme [RFC5870] is particularly well
      suited for this property, but other schemes MAY be used.

   ABNF:

     GEO-param = "VALUE=uri" / pid-param / pref-param / type-param
               / mediatype-param / altid-param / any-param
     GEO-value = URI

   Example:

           GEO:geo:37.386013,-122.082932

   * https://tools.ietf.org/html/rfc6350#section-6.5.2
   */
  const GEO = 'GEO';

  // Organizational Properties
  /**
   * Purpose:  To specify the position or job of the object the vCard
      represents.

   Value type:  A single text value.

   Cardinality:  *

   Special notes:  This property is based on the X.520 Title attribute
      [CCITT.X520.1988].

   ABNF:

     TITLE-param = "VALUE=text" / language-param / pid-param
                 / pref-param / altid-param / type-param / any-param
     TITLE-value = text

   Example:

           TITLE:Research Scientist

   * https://tools.ietf.org/html/rfc6350#section-6.6.1
   */
  const TITLE = 'TITLE';
  /**
   * Purpose:  To specify the function or part played in a particular
      situation by the object the vCard represents.

   Value type:  A single text value.

   Cardinality:  *

   Special notes:  This property is based on the X.520 Business Category
      explanatory attribute [CCITT.X520.1988].  This property is
      included as an organizational type to avoid confusion with the
      semantics of the TITLE property and incorrect usage of that
      property when the semantics of this property is intended.

   ABNF:

     ROLE-param = "VALUE=text" / language-param / pid-param / pref-param
                / type-param / altid-param / any-param
     ROLE-value = text

   Example:

           ROLE:Project Leader

   * https://tools.ietf.org/html/rfc6350#section-6.6.2
   */
  const ROLE = 'ROLE';
  /**
   * Purpose:  To specify a graphic image of a logo associated with the
      object the vCard represents.

   Value type:  A single URI.

   Cardinality:  *

   ABNF:

     LOGO-param = "VALUE=uri" / language-param / pid-param / pref-param
                / type-param / mediatype-param / altid-param / any-param
     LOGO-value = URI

   Examples:

     LOGO:http://www.example.com/pub/logos/abccorp.jpg

     LOGO:data:image/jpeg;base64,MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvc
      AQEEBQAwdzELMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bm
      ljYXRpb25zIENvcnBvcmF0aW9uMRwwGgYDVQQLExNJbmZvcm1hdGlvbiBTeXN0
      <...the remainder of base64-encoded data...>

   * https://tools.ietf.org/html/rfc6350#section-6.6.3
   */
  const LOGO = 'LOGO';
  /**
   * Purpose:  To specify the organizational name and units associated
      with the vCard.

   Value type:  A single structured text value consisting of components
      separated by the SEMICOLON character (U+003B).

   Cardinality:  *

   Special notes:  The property is based on the X.520 Organization Name
      and Organization Unit attributes [CCITT.X520.1988].  The property
      value is a structured type consisting of the organization name,
      followed by zero or more levels of organizational unit names.

      The SORT-AS parameter MAY be applied to this property.

   ABNF:

     ORG-param = "VALUE=text" / sort-as-param / language-param
               / pid-param / pref-param / altid-param / type-param
               / any-param
     ORG-value = component *(";" component)

   Example: A property value consisting of an organizational name,
   organizational unit #1 name, and organizational unit #2 name.

           ORG:ABC\, Inc.;North American Division;Marketing

   * https://tools.ietf.org/html/rfc6350#section-6.6.4
   */
  const ORG = 'ORG';
  /**
   * Purpose:  To include a member in the group this vCard represents.

   Value type:  A single URI.  It MAY refer to something other than a
      vCard object.  For example, an email distribution list could
      employ the "mailto" URI scheme [RFC6068] for efficiency.

   Cardinality:  *

   Special notes:  This property MUST NOT be present unless the value of
      the KIND property is "group".

   ABNF:

     MEMBER-param = "VALUE=uri" / pid-param / pref-param / altid-param
                  / mediatype-param / any-param
     MEMBER-value = URI

   Examples:

     BEGIN:VCARD
     VERSION:4.0
     KIND:group
     FN:The Doe family
     MEMBER:urn:uuid:03a0e51f-d1aa-4385-8a53-e29025acd8af
     MEMBER:urn:uuid:b8767877-b4a1-4c70-9acc-505d3819e519
     END:VCARD
     BEGIN:VCARD
     VERSION:4.0
     FN:John Doe
     UID:urn:uuid:03a0e51f-d1aa-4385-8a53-e29025acd8af
     END:VCARD
     BEGIN:VCARD
     VERSION:4.0
     FN:Jane Doe
     UID:urn:uuid:b8767877-b4a1-4c70-9acc-505d3819e519
     END:VCARD

     BEGIN:VCARD
     VERSION:4.0
     KIND:group
     FN:Funky distribution list
     MEMBER:mailto:subscriber1@example.com
     MEMBER:xmpp:subscriber2@example.com
     MEMBER:sip:subscriber3@example.com
     MEMBER:tel:+1-418-555-5555
     END:VCARD

   * https://tools.ietf.org/html/rfc6350#section-6.6.5
   */
  const MEMBER = 'MEMBER';
  /**
   * Purpose:  To specify a relationship between another entity and the
      entity represented by this vCard.

   Value type:  A single URI.  It can also be reset to a single text
      value.  The text value can be used to specify textual information.

   Cardinality:  *

   Special notes:  The TYPE parameter MAY be used to characterize the
      related entity.  It contains a comma-separated list of values that
      are registered with IANA as described in Section 10.2.  The
      registry is pre-populated with the values defined in [xfn].  This
      document also specifies two additional values:

      agent:  an entity who may sometimes act on behalf of the entity
         associated with the vCard.

      emergency:  indicates an emergency contact

   ABNF:

     RELATED-param = RELATED-param-uri / RELATED-param-text
     RELATED-value = URI / text
       ; Parameter and value MUST match.

     RELATED-param-uri = "VALUE=uri" / mediatype-param
     RELATED-param-text = "VALUE=text" / language-param

     RELATED-param =/ pid-param / pref-param / altid-param / type-param
                    / any-param

     type-param-related = related-type-value *("," related-type-value)
       ; type-param-related MUST NOT be used with a property other than
       ; RELATED.

     related-type-value = "contact" / "acquaintance" / "friend" / "met"
                        / "co-worker" / "colleague" / "co-resident"
                        / "neighbor" / "child" / "parent"
                        / "sibling" / "spouse" / "kin" / "muse"
                        / "crush" / "date" / "sweetheart" / "me"
                        / "agent" / "emergency"

   Examples:

   RELATED;TYPE=friend:urn:uuid:f81d4fae-7dec-11d0-a765-00a0c91e6bf6
   RELATED;TYPE=contact:http://example.com/directory/jdoe.vcf
   RELATED;TYPE=co-worker;VALUE=text:Please contact my assistant Jane
    Doe for any inquiries.

   * https://tools.ietf.org/html/rfc6350#section-6.6.6
   */
  const RELATED = 'RELATED';

  // Explanatory Properties
  /**
   * Purpose:  To specify application category information about the
      vCard, also known as "tags".

   Value type:  One or more text values separated by a COMMA character
      (U+002C).

   Cardinality:  *

   ABNF:

     CATEGORIES-param = "VALUE=text" / pid-param / pref-param
                      / type-param / altid-param / any-param
     CATEGORIES-value = text-list

   Example:

           CATEGORIES:TRAVEL AGENT

           CATEGORIES:INTERNET,IETF,INDUSTRY,INFORMATION TECHNOLOGY

   * https://tools.ietf.org/html/rfc6350#section-6.7.1
   */
  const CATEGORIES = 'CATEGORIES';
  /**
   * Purpose:  To specify supplemental information or a comment that is
      associated with the vCard.

   Value type:  A single text value.

   Cardinality:  *

   Special notes:  The property is based on the X.520 Description
      attribute [CCITT.X520.1988].

   ABNF:

     NOTE-param = "VALUE=text" / language-param / pid-param / pref-param
                / type-param / altid-param / any-param
     NOTE-value = text

   Example:

           NOTE:This fax number is operational 0800 to 1715
             EST\, Mon-Fri.

   * https://tools.ietf.org/html/rfc6350#section-6.7.2
   */
  const NOTE = 'NOTE';
  /**
   * Purpose:  To specify the identifier for the product that created the
      vCard object.

   Type value:  A single text value.

   Cardinality:  *1

   Special notes:  Implementations SHOULD use a method such as that
      specified for Formal Public Identifiers in [ISO9070] or for
      Universal Resource Names in [RFC3406] to ensure that the text
      value is unique.

   ABNF:

     PRODID-param = "VALUE=text" / any-param
     PRODID-value = text

   Example:

           PRODID:-//ONLINE DIRECTORY//NONSGML Version 1//EN

   * https://tools.ietf.org/html/rfc6350#section-6.7.3
   */
  const PRODID = 'PRODID';
  /**
   * Purpose:  To specify revision information about the current vCard.

   Value type:  A single timestamp value.

   Cardinality:  *1

   Special notes:  The value distinguishes the current revision of the
      information in this vCard for other renditions of the information.

   ABNF:

     REV-param = "VALUE=timestamp" / any-param
     REV-value = timestamp

   Example:

           REV:19951031T222710Z

   * https://tools.ietf.org/html/rfc6350#section-6.7.4
   */
  const REV = 'REV';
  /**
   * Purpose:  To specify a digital sound content information that
      annotates some aspect of the vCard.  This property is often used
      to specify the proper pronunciation of the name property value of
      the vCard.

   Value type:  A single URI.

   Cardinality:  *

   ABNF:

     SOUND-param = "VALUE=uri" / language-param / pid-param / pref-param
                 / type-param / mediatype-param / altid-param
                 / any-param
     SOUND-value = URI

   Example:

     SOUND:CID:JOHNQPUBLIC.part8.19960229T080000.xyzMail@example.com

     SOUND:data:audio/basic;base64,MIICajCCAdOgAwIBAgICBEUwDQYJKoZIh
      AQEEBQAwdzELMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bm
      ljYXRpb25zIENvcnBvcmF0aW9uMRwwGgYDVQQLExNJbmZvcm1hdGlvbiBTeXN0
      <...the remainder of base64-encoded data...>

   * https://tools.ietf.org/html/rfc6350#section-6.7.5
   */
  const SOUND = 'SOUND';
  /**
   * Purpose:  To specify a value that represents a globally unique
      identifier corresponding to the entity associated with the vCard.

   Value type:  A single URI value.  It MAY also be reset to free-form
      text.

   Cardinality:  *1

   Special notes:  This property is used to uniquely identify the object
      that the vCard represents.  The "uuid" URN namespace defined in
      [RFC4122] is particularly well suited to this task, but other URI
      schemes MAY be used.  Free-form text MAY also be used.

   ABNF:

     UID-param = UID-uri-param / UID-text-param
     UID-value = UID-uri-value / UID-text-value
       ; Value and parameter MUST match.

     UID-uri-param = "VALUE=uri"
     UID-uri-value = URI

     UID-text-param = "VALUE=text"
     UID-text-value = text

     UID-param =/ any-param

   Example:

           UID:urn:uuid:f81d4fae-7dec-11d0-a765-00a0c91e6bf6

   * https://tools.ietf.org/html/rfc6350#section-6.7.6
   */
  const UID = 'UID';
  /**
   * Purpose:  To give a global meaning to a local PID source identifier.

   Value type:  A semicolon-separated pair of values.  The first field
      is a small integer corresponding to the second field of a PID
      parameter instance.  The second field is a URI.  The "uuid" URN
      namespace defined in [RFC4122] is particularly well suited to this
      task, but other URI schemes MAY be used.

   Cardinality:  *

   Special notes:  PID source identifiers (the source identifier is the
      second field in a PID parameter instance) are small integers that
      only have significance within the scope of a single vCard
      instance.  Each distinct source identifier present in a vCard MUST
      have an associated CLIENTPIDMAP.  See Section 7 for more details
      on the usage of CLIENTPIDMAP.

      PID source identifiers MUST be strictly positive.  Zero is not
      allowed.

      As a special exception, the PID parameter MUST NOT be applied to
      this property.

   ABNF:

     CLIENTPIDMAP-param = any-param
     CLIENTPIDMAP-value = 1*DIGIT ";" URI

   Example:

     TEL;PID=3.1,4.2;VALUE=uri:tel:+1-555-555-5555
     EMAIL;PID=4.1,5.2:jdoe@example.com
     CLIENTPIDMAP:1;urn:uuid:3df403f4-5924-4bb7-b077-3c711d9eb34b
     CLIENTPIDMAP:2;urn:uuid:d89c9c7a-2e1b-4832-82de-7e992d95faa5

   * https://tools.ietf.org/html/rfc6350#section-6.7.7
   */
  const CLIENTPIDMAP = 'CLIENTPIDMAP';
  /**
   * Purpose:  To specify a uniform resource locator associated with the
      object to which the vCard refers.  Examples for individuals
      include personal web sites, blogs, and social networking site
      identifiers.

   Cardinality:  *

   Value type:  A single uri value.

   ABNF:

     URL-param = "VALUE=uri" / pid-param / pref-param / type-param
               / mediatype-param / altid-param / any-param
     URL-value = URI

   Example:

           URL:http://example.org/restaurant.french/~chezchic.html

   * https://tools.ietf.org/html/rfc6350#section-6.7.8
   */
  const URL = 'URL';
  /**
   * Purpose:  To specify the version of the vCard specification used to
      format this vCard.

   Value type:  A single text value.

   Cardinality:  1

   Special notes:  This property MUST be present in the vCard object,
      and it must appear immediately after BEGIN:VCARD.  The value MUST
      be "4.0" if the vCard corresponds to this specification.  Note
      that earlier versions of vCard allowed this property to be placed
      anywhere in the vCard object, or even to be absent.

   ABNF:

     VERSION-param = "VALUE=text" / any-param
     VERSION-value = "4.0"

   Example:

           VERSION:4.0

   * https://tools.ietf.org/html/rfc6350#section-6.7.9
   */
  const VERSION = 'VERSION';

  // Security Properties
  /**
   * Purpose:  To specify a public key or authentication certificate
      associated with the object that the vCard represents.

   Value type:  A single URI.  It can also be reset to a text value.

   Cardinality:  *

   ABNF:

     KEY-param = KEY-uri-param / KEY-text-param
     KEY-value = KEY-uri-value / KEY-text-value
       ; Value and parameter MUST match.

     KEY-uri-param = "VALUE=uri" / mediatype-param
     KEY-uri-value = URI

     KEY-text-param = "VALUE=text"
     KEY-text-value = text

     KEY-param =/ altid-param / pid-param / pref-param / type-param
                / any-param

   Examples:

     KEY:http://www.example.com/keys/jdoe.cer

     KEY;MEDIATYPE=application/pgp-keys:ftp://example.com/keys/jdoe

     KEY:data:application/pgp-keys;base64,MIICajCCAdOgAwIBAgICBE
      UwDQYJKoZIhvcNAQEEBQAwdzELMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05l
      <... remainder of base64-encoded data ...>

   * https://tools.ietf.org/html/rfc6350#section-6.8.1
   */
  const KEY = 'KEY';

  // Calendar Properties
  /**
   * Purpose:  To specify the URI for the busy time associated with the
      object that the vCard represents.

   Value type:  A single URI value.

   Cardinality:  *

   Special notes:  Where multiple FBURL properties are specified, the
      default FBURL property is indicated with the PREF parameter.  The
      FTP [RFC1738] or HTTP [RFC2616] type of URI points to an iCalendar
      [RFC5545] object associated with a snapshot of the next few weeks
      or months of busy time data.  If the iCalendar object is
      represented as a file or document, its file extension should be
      ".ifb".

   ABNF:

     FBURL-param = "VALUE=uri" / pid-param / pref-param / type-param
                 / mediatype-param / altid-param / any-param
     FBURL-value = URI

   Examples:

     FBURL;PREF=1:http://www.example.com/busy/janedoe
     FBURL;MEDIATYPE=text/calendar:ftp://example.com/busy/project-a.ifb

   * https://tools.ietf.org/html/rfc6350#section-6.9.1
   */
  const FBURL = 'FBURL';
  /**
   * Purpose:  To specify the calendar user address [RFC5545] to which a
      scheduling request [RFC5546] should be sent for the object
      represented by the vCard.

   Value type:  A single URI value.

   Cardinality:  *

   Special notes:  Where multiple CALADRURI properties are specified,
      the default CALADRURI property is indicated with the PREF
      parameter.

   ABNF:

     CALADRURI-param = "VALUE=uri" / pid-param / pref-param / type-param
                     / mediatype-param / altid-param / any-param
     CALADRURI-value = URI

   Example:

     CALADRURI;PREF=1:mailto:janedoe@example.com
     CALADRURI:http://example.com/calendar/jdoe

   * https://tools.ietf.org/html/rfc6350#section-6.9.2
   */
  const CALADRURI = 'CALADRURI';
  /**
   * Purpose:  To specify the URI for a calendar associated with the
      object represented by the vCard.

   Value type:  A single URI value.

   Cardinality:  *

   Special notes:  Where multiple CALURI properties are specified, the
      default CALURI property is indicated with the PREF parameter.  The
      property should contain a URI pointing to an iCalendar [RFC5545]
      object associated with a snapshot of the user's calendar store.
      If the iCalendar object is represented as a file or document, its
      file extension should be ".ics".

   ABNF:

     CALURI-param = "VALUE=uri" / pid-param / pref-param / type-param
                  / mediatype-param / altid-param / any-param
     CALURI-value = URI

   Examples:

     CALURI;PREF=1:http://cal.example.com/calA
     CALURI;MEDIATYPE=text/calendar:ftp://ftp.example.com/calA.ics

   * https://tools.ietf.org/html/rfc6350#section-6.9.3
   */
  const CALURI = 'CALURI';

  // Property Value Data Types
  /**
   *  "text": The "text" value type should be used to identify values that
   contain human-readable text.  As for the language, it is controlled
   by the LANGUAGE property parameter defined in Section 5.1.

   Examples for "text":

       this is a text value
       this is one value,this is another
       this is a single value\, with a comma encoded

   A formatted text line break in a text value type MUST be represented
   as the character sequence backslash (U+005C) followed by a Latin
   small letter n (U+006E) or a Latin capital letter N (U+004E), that
   is, "\n" or "\N".

   For example, a multiple line NOTE value of:

       Mythical Manager
       Hyjinx Software Division
       BabsCo, Inc.

   could be represented as:

       NOTE:Mythical Manager\nHyjinx Software Division\n
        BabsCo\, Inc.\n

   demonstrating the \n literal formatted line break technique, the
   CRLF-followed-by-space line folding technique, and the backslash
   escape technique.

   * https://tools.ietf.org/html/rfc6350#section-4.1
   */
  const TEXT = 'TEXT';
  /**
   * "uri": The "uri" value type should be used to identify values that
   are referenced by a Uniform Resource Identifier (URI) instead of
   encoded in-line.  These value references might be used if the value
   is too large, or otherwise undesirable to include directly.  The
   format for the URI is as defined in Section 3 of [RFC3986].  Note
   that the value of a property of type "uri" is what the URI points to,
   not the URI itself.

   Examples for "uri":

       http://www.example.com/my/picture.jpg
       ldap://ldap.example.com/cn=babs%20jensen

   * https://tools.ietf.org/html/rfc6350#section-4.2
   */
  const URI = 'URI';
  /**
   * A calendar date as specified in [ISO.8601.2004], Section 4.1.2.

   Reduced accuracy, as specified in [ISO.8601.2004], Sections 4.1.2.3
   a) and b), but not c), is permitted.

   Expanded representation, as specified in [ISO.8601.2004], Section
   4.1.4, is forbidden.

   Truncated representation, as specified in [ISO.8601.2000], Sections
   5.2.1.3 d), e), and f), is permitted.

   Examples for "date":

             19850412
             1985-04
             1985
             --0412
             ---12

   Note the use of YYYY-MM in the second example above.  YYYYMM is
   disallowed to prevent confusion with YYMMDD.  Note also that
   YYYY-MM-DD is disallowed since we are using the basic format instead
   of the extended format.

   * https://tools.ietf.org/html/rfc6350#section-4.3.1
   */
  const DATE = 'DATE';
  /**
   * A time of day as specified in [ISO.8601.2004], Section 4.2.

   Reduced accuracy, as specified in [ISO.8601.2004], Section 4.2.2.3,
   is permitted.

   Representation with decimal fraction, as specified in
   [ISO.8601.2004], Section 4.2.2.4, is forbidden.

   The midnight hour is always represented by 00, never 24 (see
   [ISO.8601.2004], Section 4.2.3).

   Truncated representation, as specified in [ISO.8601.2000], Sections
   5.3.1.4 a), b), and c), is permitted.

   Examples for "time":

             102200
             1022
             10
             -2200
             --00
             102200Z
             102200-0800

   * https://tools.ietf.org/html/rfc6350#section-4.3.2
   */
  const TIME = 'TIME';
  /**
   * A date and time of day combination as specified in [ISO.8601.2004],
   Section 4.3.

   Truncation of the date part, as specified in [ISO.8601.2000], Section
   5.4.2 c), is permitted.

   Examples for "date-time":

             19961022T140000
             --1022T1400
             ---22T14

   * https://tools.ietf.org/html/rfc6350#section-4.3.3
   */
  const DATE_TIME = 'DATE-TIME';
  /**
   * Either a DATE-TIME, a DATE, or a TIME value.  To allow unambiguous
   interpretation, a stand-alone TIME value is always preceded by a "T".

   Examples for "date-and-or-time":

             19961022T140000
             --1022T1400
             ---22T14
             19850412
             1985-04
             1985
             --0412
             ---12
             T102200
             T1022
             T10
             T-2200
             T--00
             T102200Z
             T102200-0800

   * https://tools.ietf.org/html/rfc6350#section-4.3.4
   */
  const DATE_AND_OR_TIME = 'DATE-AND-OR-TIME';
  /**
   * A complete date and time of day combination as specified in
   [ISO.8601.2004], Section 4.3.2.

   Examples for "timestamp":

             19961022T140000
             19961022T140000Z
             19961022T140000-05
             19961022T140000-0500

   * https://tools.ietf.org/html/rfc6350#section-4.3.5
   */
  const TIMESTAMP = 'TIMESTAMP';
  /**
   * "boolean": The "boolean" value type is used to express boolean
   values.  These values are case-insensitive.

   Examples:

       TRUE
       false
       True

   * https://tools.ietf.org/html/rfc6350#section-4.4
   */
  const BOOLEAN = 'BOOLEAN';
  /**
   * "integer": The "integer" value type is used to express signed
   integers in decimal format.  If sign is not specified, the value is
   assumed positive "+".  Multiple "integer" values can be specified
   using the comma-separated notation.  The maximum value is
   9223372036854775807, and the minimum value is -9223372036854775808.
   These limits correspond to a signed 64-bit integer using two's-
   complement arithmetic.

   Examples:

       1234567890
       -1234556790
       +1234556790,432109876

   * https://tools.ietf.org/html/rfc6350#section-4.5
   */
  const INTEGER = 'INTEGER';
  /**
   * "float": The "float" value type is used to express real numbers.  If
   sign is not specified, the value is assumed positive "+".  Multiple
   "float" values can be specified using the comma-separated notation.
   Implementations MUST support a precision equal or better than that of
   the IEEE "binary64" format [IEEE.754.2008].

      Note: Scientific notation is disallowed.  Implementers wishing to
      use their favorite language's %f formatting should be careful.

   Examples:

       20.30
       1000000.0000001
       1.333,3.14

   * https://tools.ietf.org/html/rfc6350#section-4.6
   */
  const FLOAT = 'FLOAT';
  /**
   * "utc-offset": The "utc-offset" value type specifies that the property
   value is a signed offset from UTC.  This value type can be specified
   in the TZ property.

   The value type is an offset from Coordinated Universal Time (UTC).
   It is specified as a positive or negative difference in units of
   hours and minutes (e.g., +hhmm).  The time is specified as a 24-hour
   clock.  Hour values are from 00 to 23, and minute values are from 00
   to 59.  Hour and minutes are 2 digits with high-order zeroes required
   to maintain digit count.  The basic format for ISO 8601 UTC offsets
   MUST be used.

   * https://tools.ietf.org/html/rfc6350#section-4.7
   */
  const UTC_OFFSET = 'UTC-OFFSET';
  /**
   * "language-tag": A single language tag, as defined in [RFC5646].
   *
   * https://tools.ietf.org/html/rfc6350#section-4.8
   */
  const LANGUAGE_TAG = 'LANGUAGE-TAG';

  // Property Parameters
  /**
   * The LANGUAGE property parameter is used to identify data in multiple
   languages.  There is no concept of "default" language, except as
   specified by any "Content-Language" MIME header parameter that is
   present [RFC3282].  The value of the LANGUAGE property parameter is a
   language tag as defined in Section 2 of [RFC5646].

   Examples:

     ROLE;LANGUAGE=tr:hoca

   ABNF:

           language-param = "LANGUAGE=" Language-Tag
             ; Language-Tag is defined in section 2.1 of RFC 5646

   * https://tools.ietf.org/html/rfc6350#section-5.1
   */
  const LANGUAGE = 'LANGUAGE';
  /**
   * The VALUE parameter is OPTIONAL, used to identify the value type
   (data type) and format of the value.  The use of these predefined
   formats is encouraged even if the value parameter is not explicitly
   used.  By defining a standard set of value types and their formats,
   existing parsing and processing code can be leveraged.  The
   predefined data type values MUST NOT be repeated in COMMA-separated
   value lists except within the N, NICKNAME, ADR, and CATEGORIES
   properties.

   ABNF:

     value-param = "VALUE=" value-type

     value-type = "text"
                / "uri"
                / "date"
                / "time"
                / "date-time"
                / "date-and-or-time"
                / "timestamp"
                / "boolean"
                / "integer"
                / "float"
                / "utc-offset"
                / "language-tag"
                / iana-token  ; registered as described in section 12


                / x-name

   * https://tools.ietf.org/html/rfc6350#section-5.2
   */
  const VALUE = 'VALUE';
  /**
   * The PREF parameter is OPTIONAL and is used to indicate that the
   corresponding instance of a property is preferred by the vCard
   author.  Its value MUST be an integer between 1 and 100 that
   quantifies the level of preference.  Lower values correspond to a
   higher level of preference, with 1 being most preferred.

   When the parameter is absent, the default MUST be to interpret the
   property instance as being least preferred.

   Note that the value of this parameter is to be interpreted only in
   relation to values assigned to other instances of the same property
   in the same vCard.  A given value, or the absence of a value, MUST
   NOT be interpreted on its own.

   This parameter MAY be applied to any property that allows multiple
   instances.

   ABNF:

           pref-param = "PREF=" (1*2DIGIT / "100")
                                ; An integer between 1 and 100.

   * https://tools.ietf.org/html/rfc6350#section-5.3
   */
  const PREF = 'PREF';
  /**
   * The ALTID parameter is used to "tag" property instances as being
   alternative representations of the same logical property.  For
   example, translations of a property in multiple languages generates
   multiple property instances having different LANGUAGE (Section 5.1)
   parameter that are tagged with the same ALTID value.

   This parameter's value is treated as an opaque string.  Its sole
   purpose is to be compared for equality against other ALTID parameter
   values.

   Two property instances are considered alternative representations of
   the same logical property if and only if their names as well as the
   value of their ALTID parameters are identical.  Property instances
   without the ALTID parameter MUST NOT be considered an alternative
   representation of any other property instance.  Values for the ALTID
   parameter are not globally unique: they MAY be reused for different
   property names.

   Property instances having the same ALTID parameter value count as 1
   toward cardinality.  Therefore, since N (Section 6.2.2) has
   cardinality *1 and TITLE (Section 6.6.1) has cardinality *, these
   three examples would be legal:

     N;ALTID=1;LANGUAGE=jp:<U+5C71><U+7530>;<U+592A><U+90CE>;;;
     N;ALTID=1;LANGUAGE=en:Yamada;Taro;;;
     (<U+XXXX> denotes a UTF8-encoded Unicode character.)

     TITLE;ALTID=1;LANGUAGE=fr:Patron
     TITLE;ALTID=1;LANGUAGE=en:Boss

     TITLE;ALTID=1;LANGUAGE=fr:Patron
     TITLE;ALTID=1;LANGUAGE=en:Boss
     TITLE;ALTID=2;LANGUAGE=en:Chief vCard Evangelist

   while this one would not:

     N;ALTID=1;LANGUAGE=jp:<U+5C71><U+7530>;<U+592A><U+90CE>;;;
     N:Yamada;Taro;;;
     (Two instances of the N property.)

   and these three would be legal but questionable:

     TITLE;ALTID=1;LANGUAGE=fr:Patron
     TITLE;ALTID=2;LANGUAGE=en:Boss
     (Should probably have the same ALTID value.)
     TITLE;ALTID=1;LANGUAGE=fr:Patron
     TITLE:LANGUAGE=en:Boss
     (Second line should probably have ALTID=1.)

     N;ALTID=1;LANGUAGE=jp:<U+5C71><U+7530>;<U+592A><U+90CE>;;;
     N;ALTID=1;LANGUAGE=en:Yamada;Taro;;;
     N;ALTID=1;LANGUAGE=en:Smith;John;;;
     (The last line should probably have ALTID=2.  But that would be
      illegal because N has cardinality *1.)

   The ALTID property MAY also be used in may contexts other than with
   the LANGUAGE parameter.  Here's an example with two representations
   of the same photo in different file formats:

     PHOTO;ALTID=1:data:image/jpeg;base64,...
     PHOTO;ALTID=1;data:image/jp2;base64,...

   ABNF:

           altid-param = "ALTID=" param-value

   * https://tools.ietf.org/html/rfc6350#section-5.4
   */
  const ALTID = 'ALTID';
  /**
   * The PID parameter is used to identify a specific property among
   multiple instances.  It plays a role analogous to the UID property
   (Section 6.7.6) on a per-property instead of per-vCard basis.  It MAY
   appear more than once in a given property.  It MUST NOT appear on
   properties that may have only one instance per vCard.  Its value is
   either a single small positive integer or a pair of small positive
   integers separated by a dot.  Multiple values may be encoded in a
   single PID parameter by separating the values with a comma ",".  See
   Section 7 for more details on its usage.

   ABNF:

           pid-param = "PID=" pid-value *("," pid-value)
           pid-value = 1*DIGIT ["." 1*DIGIT]

   * https://tools.ietf.org/html/rfc6350#section-5.5
   */
  const PID = 'PID';
  /**
   * The TYPE parameter has multiple, different uses.  In general, it is a
   way of specifying class characteristics of the associated property.
   Most of the time, its value is a comma-separated subset of a
   predefined enumeration.  In this document, the following properties
   make use of this parameter: FN, NICKNAME, PHOTO, ADR, TEL, EMAIL,
   IMPP, LANG, TZ, GEO, TITLE, ROLE, LOGO, ORG, RELATED, CATEGORIES,
   NOTE, SOUND, URL, KEY, FBURL, CALADRURI, and CALURI.  The TYPE
   parameter MUST NOT be applied on other properties defined in this
   document.

   The "work" and "home" values act like tags.  The "work" value implies
   that the property is related to an individual's work place, while the
   "home" value implies that the property is related to an individual's
   personal life.  When neither "work" nor "home" is present, it is
   implied that the property is related to both an individual's work
   place and personal life in the case that the KIND property's value is
   "individual", or to none in other cases.

   ABNF:

           type-param = "TYPE=" type-value *("," type-value)

           type-value = "work" / "home" / type-param-tel
                      / type-param-related / iana-token / x-name
             ; This is further defined in individual property sections.

   * https://tools.ietf.org/html/rfc6350#section-5.6
   */
  const TYPE = 'TYPE';
  /**
   * The MEDIATYPE parameter is used with properties whose value is a URI.
   Its use is OPTIONAL.  It provides a hint to the vCard consumer
   application about the media type [RFC2046] of the resource identified
   by the URI.  Some URI schemes do not need this parameter.  For
   example, the "data" scheme allows the media type to be explicitly
   indicated as part of the URI [RFC2397].  Another scheme, "http",
   provides the media type as part of the URI resolution process, with
   the Content-Type HTTP header [RFC2616].  The MEDIATYPE parameter is
   intended to be used with URI schemes that do not provide such
   functionality (e.g., "ftp" [RFC1738]).

   ABNF:

     mediatype-param = "MEDIATYPE=" mediatype
     mediatype = type-name "/" subtype-name *( ";" attribute "=" value )
       ; "attribute" and "value" are from [RFC2045]
       ; "type-name" and "subtype-name" are from [RFC4288]

   * https://tools.ietf.org/html/rfc6350#section-5.7
   */
  const MEDIATYPE = 'MEDIATYPE';
  /**
   * The CALSCALE parameter is identical to the CALSCALE property in
   iCalendar (see [RFC5545], Section 3.7.1).  It is used to define the
   calendar system in which a date or date-time value is expressed.  The
   only value specified by iCalendar is "gregorian", which stands for
   the Gregorian system.  It is the default when the parameter is
   absent.  Additional values may be defined in extension documents and
   registered with IANA (see Section 10.3.4).  A vCard implementation
   MUST ignore properties with a CALSCALE parameter value that it does
   not understand.

   ABNF:

           calscale-param = "CALSCALE=" calscale-value

           calscale-value = "gregorian" / iana-token / x-name

   * https://tools.ietf.org/html/rfc6350#section-5.8
   */
  const CALSCALE = 'CALSCALE';
  /**
   * The "sort-as" parameter is used to specify the string to be used for
   national-language-specific sorting.  Without this information,
   sorting algorithms could incorrectly sort this vCard within a
   sequence of sorted vCards.  When this property is present in a vCard,
   then the given strings are used for sorting the vCard.

   This parameter's value is a comma-separated list that MUST have as
   many or fewer elements as the corresponding property value has
   components.  This parameter's value is case-sensitive.

   ABNF:

     sort-as-param = "SORT-AS=" sort-as-value

     sort-as-value = param-value *("," param-value)

   Examples: For the case of surname and given name sorting, the
   following examples define common sort string usage with the N
   property.

           FN:Rene van der Harten
           N;SORT-AS="Harten,Rene":van der Harten;Rene,J.;Sir;R.D.O.N.

           FN:Robert Pau Shou Chang
           N;SORT-AS="Pau Shou Chang,Robert":Shou Chang;Robert,Pau;;

           FN:Osamu Koura
           N;SORT-AS="Koura,Osamu":Koura;Osamu;;

           FN:Oscar del Pozo
           N;SORT-AS="Pozo,Oscar":del Pozo Triscon;Oscar;;

           FN:Chistine d'Aboville
           N;SORT-AS="Aboville,Christine":d'Aboville;Christine;;

           FN:H. James de Mann
           N;SORT-AS="Mann,James":de Mann;Henry,James;;

   If sorted by surname, the results would be:

           Christine d'Aboville
           Rene van der Harten
           Osamu Koura
           H. James de Mann
           Robert Pau Shou Chang
           Oscar del Pozo

   If sorted by given name, the results would be:

           Christine d'Aboville
           H. James de Mann
           Osamu Koura
           Oscar del Pozo
           Rene van der Harten
           Robert Pau Shou Chang

   * https://tools.ietf.org/html/rfc6350#section-5.9
   */
  const SORT_AS = 'SORT-AS';
  /**
   * The GEO parameter can be used to indicate global positioning
   information that is specific to an address.  Its value is the same as
   that of the GEO property (see Section 6.5.2).

   ABNF:

     geo-parameter = "GEO=" DQUOTE URI DQUOTE

   * https://tools.ietf.org/html/rfc6350#section-5.10
   */
  //const GEO = 'GEO';
  /**
   * The TZ parameter can be used to indicate time zone information that
   is specific to an address.  Its value is the same as that of the TZ
   property.

   ABNF:

     tz-parameter = "TZ=" (param-value / DQUOTE URI DQUOTE)

   * https://tools.ietf.org/html/rfc6350#section-5.11
   */
  //const TZ = 'TZ';
}
