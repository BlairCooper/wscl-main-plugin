<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

use RCS\Json\JsonEntity;
use WSCL\Main\SeasonEnum;

class MembershipOrgConfig extends JsonEntity
{
    public int $id;
    public string $name;

    public \DateTime $registrationOpens;

    public \DateTime $registrationCloses;

    public ?\DateTime $ageAsOfDate;

    public function getSeason(): SeasonEnum
    {
        $midYear = (new \DateTime())->setDate($this->getYear(), 7, 1);

        if ($this->registrationCloses < $midYear) {
            $season = SeasonEnum::SPRING;
        } else {
            $season = SeasonEnum::FALL;
        }

        return $season;
    }

    public function getYear(): int
    {
        return intval($this->registrationCloses->format('Y'));
    }

    public function isInWindow(): bool
    {
        $cutOffDate = (new \DateTime())->sub(new \DateInterval('P1M'));

        return $this->registrationCloses > $cutOffDate;
    }

    /*
     "slug":"washington-student-cycling-league-coach-level-1-fall-2022",
     "products":[],
     "taxes":[
     {"id":93182,"tax_id":12,"name":"0% - No Tax","type":"Order Item Domestic","rate_breakdown":""},
     {"id":93183,"tax_id":12,"name":"0% - No Tax","type":"Order Item International","rate_breakdown":""}
     ],
     "allowed_countries":[
     {"id":226,"name":"United States","iso3":"USA","iso2":"US","iso_numeric":"840","ioc":"USA","province_label_i18n_anchor":"PROFILE.ADDRESSES.STATE","flag":{"id":86720,"url":"https://eventsquare-ccn-prod.s3.amazonaws.com/srv/sites/ccnbikes.com/eventsquare/geolocation_app/data/country_flags/us-202106101346.svg"},"is_province_other_required":true}
     ],
     "allowed_provinces":[
     {"id":22,"name":"Idaho","abbrev":"ID","country":{"id":226,"name":"United States","iso3":"USA","iso2":"US","iso_numeric":"840","ioc":"USA","province_label_i18n_anchor":"PROFILE.ADDRESSES.STATE","flag":{"id":86720,"url":"https://eventsquare-ccn-prod.s3.amazonaws.com/srv/sites/ccnbikes.com/eventsquare/geolocation_app/data/country_flags/us-202106101346.svg"},"is_province_other_required":true}},
     {"id":62,"name":"Washington","abbrev":"WA","country":{"id":226,"name":"United States","iso3":"USA","iso2":"US","iso_numeric":"840","ioc":"USA","province_label_i18n_anchor":"PROFILE.ADDRESSES.STATE","flag":{"id":86720,"url":"https://eventsquare-ccn-prod.s3.amazonaws.com/srv/sites/ccnbikes.com/eventsquare/geolocation_app/data/country_flags/us-202106101346.svg"},"is_province_other_required":true}}
     ],
     "morg_type_display":"Association",
     "fee_structure":"G",
     "tax_structure":"G",
     "landing_page_html":"",
     "listing_description":"",
     "email_content":"<p>{first_name},</p>\r\n\r\n<p>Thank you for your registration for the Washington Student Cycling League for the 2022 Fall season with.&nbsp;Race schedule and race day information are posted on our website. We are excited to have fun-filled weekends with our families.</p>\r\n\r\n<p><em><strong>Be sure to visit your Coach Profile here - <a href=\"https://ccnbikes.com/my_pages/memberships\">https://ccnbikes.com/my_pages/memberships</a>&nbsp;- to view pending requirements and how to complete &amp; submit them.</strong></em></p>\r\n\r\n<p><u><em><strong>COCAH REQUIREMENTS;</strong></em></u></p>\r\n\r\n<p><strong>Level 1 Coach:</strong></p>\r\n\r\n<ul>\r\n\t<li>Background Check through Sterling Volunteers</li>\r\n\t<li>WSCL 100 Orientation</li>\r\n\t<li>Concussion Training</li>\r\n</ul>\r\n\r\n<div><strong>Leve 2 Coach:</strong></div>\r\n\r\n<ul>\r\n\t<li>Background Check through Sterling Volunteers</li>\r\n\t<li>WSCL 100 Orientation</li>\r\n\t<li>Concussion Training</li>\r\n\t<li>Basic First Aid</li>\r\n\t<li>CPR</li>\r\n\t<li>WSCL 201 : Basic MTB Skills</li>\r\n</ul>\r\n\r\n<div><strong>Level 3 Coach:</strong></div>\r\n\r\n<ul>\r\n\t<li>Background Check through Sterling Volunteers</li>\r\n\t<li>WSCL 100 Orientation</li>\r\n\t<li>Concussion Training</li>\r\n\t<li>Basic First Aid</li>\r\n\t<li>CPR</li>\r\n\t<li>WSCL 301 : Advanced MTB Skills</li>\r\n</ul>\r\n\r\n<div>&nbsp;</div>\r\n\r\n<p>Here is a summary of your registration:<br />\r\nTeam: {affiliates}<br />\r\n{summary}</p>\r\n\r\n<p><a href=\"mailto:info@washingtonleague.org\">info@washingtonleague.org</a><br />\r\n<a href=\"https://washingtonleague.org/\">washingtonleague.org</a><br />\r\n<a href=\"https://www.facebook.com/washingtonmtb\" target=\"_blank\"><img alt=\"\" src=\"https://eventsquare-ccn-prod.s3.amazonaws.com/uploads/files/button_facebook-202103010839.png\" /></a>&nbsp; &nbsp;<a href=\"https://www.instagram.com/washingtonmtb/\" target=\"_blank\"><img alt=\"\" src=\"https://eventsquare-ccn-prod.s3.amazonaws.com/uploads/membership/membercard/preview/button_instagram-202103010839.png\" /></a>&nbsp;</p>",
     "receipt_message":"<p>Your transaction is complete.&nbsp;</p>\r\n\r\n<p><em><strong>Be sure to visit your Coach Profile here - <a href=\"https://ccnbikes.com/my_pages/memberships\">https://ccnbikes.com/my_pages/memberships</a>&nbsp;- to view pending requirements and how to complete &amp; submit them.</strong></em></p>\r\n\r\n<p><u><em><strong>COCAH REQUIREMENTS;</strong></em></u></p>\r\n\r\n<p><strong>Level 1 Coach:</strong></p>\r\n\r\n<ul>\r\n\t<li>Background Check through Sterling Volunteers</li>\r\n\t<li>WSCL Orientation</li>\r\n\t<li>Concussion Training</li>\r\n\t<li>WSCL 101: Coaching Youth on MTB Rides</li>\r\n</ul>\r\n\r\n<div><strong>Leve 2 Coach:</strong></div>\r\n\r\n<ul>\r\n\t<li>Background Check through Sterling Volunteers</li>\r\n\t<li>WSCL Orientation</li>\r\n\t<li>Concussion Training</li>\r\n\t<li>Basic First Aid</li>\r\n\t<li>CPR</li>\r\n\t<li>WSCL 201 : Basic MTB Skills</li>\r\n</ul>\r\n\r\n<div><strong>Level 3 Coach:</strong></div>\r\n\r\n<ul>\r\n\t<li>Background Check through Sterling Volunteers</li>\r\n\t<li>WSCL Orientation</li>\r\n\t<li>Concussion Training</li>\r\n\t<li>Basic First Aid</li>\r\n\t<li>Wilderness First Aid</li>\r\n\t<li>CPR</li>\r\n\t<li>\r\n\t<style type=\"text/css\"><!--td {border: 1px solid #ccc;}br {mso-data-placement:same-cell;}-->\r\n\t</style>\r\n\tWSCL 301 : Advanced MTB Skills</li>\r\n</ul>\r\n\r\n<p>Make sure to check out our website for the race schedule and all of the details surrounding race weekends at each venue.</p>\r\n\r\n<p><a href=\"mailto:info@washingtonleague.org\">info@washingtonleague.org</a><br />\r\n<a href=\"https://washingtonleague.org/\">washingtonleague.org</a><br />\r\n<a href=\"https://www.facebook.com/washingtonmtb\" target=\"_blank\"><img alt=\"\" src=\"https://eventsquare-ccn-prod.s3.amazonaws.com/uploads/files/button_facebook-202103010839.png\" /></a>&nbsp; &nbsp;<a href=\"https://www.instagram.com/washingtonmtb/\" target=\"_blank\"><img alt=\"\" src=\"https://eventsquare-ccn-prod.s3.amazonaws.com/uploads/membership/membercard/preview/button_instagram-202103010839.png\" /></a>&nbsp;</p>",
     "cart_message":"","cart_order_message":null,"store_message":null,"builder_address_message":"In order to register for the Washington Student Cycling League you must be a resident of Washington State.","builder_photo_optional_message":"","builder_photo_required_message":"","builder_identity_message":"","broadcast_messages":[],"page_theme_colour":"blue","logo":{"id":22760,"alt":"","url":"https://eventsquare-ccn-prod.s3.amazonaws.com/uploads/images/WSCL_Logo-202110181406.jfif"},"background":null,"email_header":{"id":22760,"alt":"","url":"https://eventsquare-ccn-prod.s3.amazonaws.com/uploads/images/WSCL_Logo-202110181406.jfif"},"show_header_title":false,"defaults":{"email_default":{"id":75,"alt":"CCN Email Header","url":"https://eventsquare-ccn-prod.s3.amazonaws.com/uploads/images/ccn_email_header.png"},"google_pay_loyalty_settings_defaults":{"template":{"member_name_header":"Name","valid_until_header":"Valid until","valid_until":"...","barcode":{"type":"CODE128","value":"{{generatednumber_ID}}"},"long_text_field":{"header":"Waiver","body":"..."},"custom_text_fields":[{"body":"{{age_20201231}}","header":"Age"},{"body":"{{dob|date:'Y-m-d'}}","header":"Date Of Birth"},{"body":"{{sex}}","header":"Gender"}],"link_fields":{"location":{"description":"Nearby Locations","uri":"http://maps.google.com/map?q=google"},"contact":{"description":"Call Customer Service","uri":"tel:6505555555"},"website":{"description":"My Baconrista Account","uri":"http://www.baconrista.com/myaccount?id=1234567890"}}},"help_text":"\n    You can use filters (e.g. list_to_string:' ', date:'Y-m-d', format_uci_number, ...).\n    Available Variables: lname, fname, citizenship_country_name, citizenship_country_code,\n    citizenship_country_code_uci, citizenship_country_code_iso3, citizenship_country_code_ioc,\n    citizenship_name, dob, sex, is_citizenship_status_verified, citizenship_status,\n    usac_citizenship_character, issue_date, age_{YYMMDD}, codetype_{ID}, generatednumber_{ID},\n    surveyanswer_{ID}, affiliatetype_{CODE}, expiry_date, event_slug_through_required_nodes,\n    event_name_through_required_nodes, node_valid_to, node_type_name, identity_id.\n    Use like this - {{citizenship_name}}\n\n    Barcode types: QR, AZTEC, PDF417, CODE128\n    "},
     "apple_wallet_pass_settings_defaults":{"template":{"member_name_header":"Member","barcode":{"type":"PDF417","value":"{{generatednumber_ID}}"},
     "secondary_fields":[{"body":"{{age_20201231}}","header":"Age"},{"body":"{{dob|date:'Y-m-d'}}","header":"Date Of Birth"}],
     "auxiliary_fields":[{"body":"...","header":"Valid until"},{"body":"{{sex}}","header":"Gender"}],
     "back_fields":[{"body":"...","header":"Waiver"}],"link_fields":{"website":{"uri":"http://www.baconrista.com/myaccount?id=1234567890","description":"My Baconrista Account"},
     "contact":{"uri":"tel:6505555555","description":"Call Customer Service"},
     "location":{"uri":"http://maps.google.com/map?q=google","description":"Nearby Locations"}}},
     "help_text":"\n    You can use filters (e.g. list_to_string:' ', date:'Y-m-d', format_uci_number, ...).\n    Available Variables: lname, fname, citizenship_country_name, citizenship_country_code,\n    citizenship_country_code_uci, citizenship_country_code_iso3, citizenship_country_code_ioc,\n    citizenship_name, dob, sex, is_citizenship_status_verified, citizenship_status,\n    usac_citizenship_character, issue_date, age_{YYMMDD}, codetype_{ID}, generatednumber_{ID},\n    surveyanswer_{ID}, affiliatetype_{CODE}, expiry_date, event_slug_through_required_nodes,\n    event_name_through_required_nodes, node_valid_to, node_type_name, identity_id.\n    Use like this - {{citizenship_name}}\n\n    Barcode types: QR, AZTEC, PDF417, CODE128\n    "}},
     "google_pay_loyalty_settings":null,
     "apple_wallet_pass_settings":null,
     "translatable_fields":["builder_identity_message","builder_photo_required_message","store_message","builder_group_message_bottom","builder_address_message","email_content","email_confirmation_content_for_processing_status","cart_order_message","membership_issued_email_content","builder_photo_optional_message","builder_group_message_top","affiliate_message","landing_page_html","builder_node_message","email_confirmation_subject","membership_issued_email_subject","receipt_message","email_confirmation_subject_for_processing_status","cart_message"],
     "can_be_used_for_third_party_reg_rule_configuration":false,
     "third_party_reg_rule_configuration_cutoff_datetime":null,
     "landing_page_html_en":"",
     "landing_page_html_fr":"",
     "email_content_en":"<p>{first_name},</p>\r\n\r\n<p>Thank you for your registration for the Washington Student Cycling League for the 2022 Fall season with.&nbsp;Race schedule and race day information are posted on our website. We are excited to have fun-filled weekends with our families.</p>\r\n\r\n<p><em><strong>Be sure to visit your Coach Profile here - <a href=\"https://ccnbikes.com/my_pages/memberships\">https://ccnbikes.com/my_pages/memberships</a>&nbsp;- to view pending requirements and how to complete &amp; submit them.</strong></em></p>\r\n\r\n<p><u><em><strong>COCAH REQUIREMENTS;</strong></em></u></p>\r\n\r\n<p><strong>Level 1 Coach:</strong></p>\r\n\r\n<ul>\r\n\t<li>Background Check through Sterling Volunteers</li>\r\n\t<li>WSCL 100 Orientation</li>\r\n\t<li>Concussion Training</li>\r\n</ul>\r\n\r\n<div><strong>Leve 2 Coach:</strong></div>\r\n\r\n<ul>\r\n\t<li>Background Check through Sterling Volunteers</li>\r\n\t<li>WSCL 100 Orientation</li>\r\n\t<li>Concussion Training</li>\r\n\t<li>Basic First Aid</li>\r\n\t<li>CPR</li>\r\n\t<li>WSCL 201 : Basic MTB Skills</li>\r\n</ul>\r\n\r\n<div><strong>Level 3 Coach:</strong></div>\r\n\r\n<ul>\r\n\t<li>Background Check through Sterling Volunteers</li>\r\n\t<li>WSCL 100 Orientation</li>\r\n\t<li>Concussion Training</li>\r\n\t<li>Basic First Aid</li>\r\n\t<li>CPR</li>\r\n\t<li>WSCL 301 : Advanced MTB Skills</li>\r\n</ul>\r\n\r\n<div>&nbsp;</div>\r\n\r\n<p>Here is a summary of your registration:<br />\r\nTeam: {affiliates}<br />\r\n{summary}</p>\r\n\r\n<p><a href=\"mailto:info@washingtonleague.org\">info@washingtonleague.org</a><br />\r\n<a href=\"https://washingtonleague.org/\">washingtonleague.org</a><br />\r\n<a href=\"https://www.facebook.com/washingtonmtb\" target=\"_blank\"><img alt=\"\" src=\"https://eventsquare-ccn-prod.s3.amazonaws.com/uploads/files/button_facebook-202103010839.png\" /></a>&nbsp; &nbsp;<a href=\"https://www.instagram.com/washingtonmtb/\" target=\"_blank\"><img alt=\"\" src=\"https://eventsquare-ccn-prod.s3.amazonaws.com/uploads/membership/membercard/preview/button_instagram-202103010839.png\" /></a>&nbsp;</p>",
     "email_content_fr":"",
     "receipt_message_en":"<p>Your transaction is complete.&nbsp;</p>\r\n\r\n<p><em><strong>Be sure to visit your Coach Profile here - <a href=\"https://ccnbikes.com/my_pages/memberships\">https://ccnbikes.com/my_pages/memberships</a>&nbsp;- to view pending requirements and how to complete &amp; submit them.</strong></em></p>\r\n\r\n<p><u><em><strong>COCAH REQUIREMENTS;</strong></em></u></p>\r\n\r\n<p><strong>Level 1 Coach:</strong></p>\r\n\r\n<ul>\r\n\t<li>Background Check through Sterling Volunteers</li>\r\n\t<li>WSCL Orientation</li>\r\n\t<li>Concussion Training</li>\r\n\t<li>WSCL 101: Coaching Youth on MTB Rides</li>\r\n</ul>\r\n\r\n<div><strong>Leve 2 Coach:</strong></div>\r\n\r\n<ul>\r\n\t<li>Background Check through Sterling Volunteers</li>\r\n\t<li>WSCL Orientation</li>\r\n\t<li>Concussion Training</li>\r\n\t<li>Basic First Aid</li>\r\n\t<li>CPR</li>\r\n\t<li>WSCL 201 : Basic MTB Skills</li>\r\n</ul>\r\n\r\n<div><strong>Level 3 Coach:</strong></div>\r\n\r\n<ul>\r\n\t<li>Background Check through Sterling Volunteers</li>\r\n\t<li>WSCL Orientation</li>\r\n\t<li>Concussion Training</li>\r\n\t<li>Basic First Aid</li>\r\n\t<li>Wilderness First Aid</li>\r\n\t<li>CPR</li>\r\n\t<li>\r\n\t<style type=\"text/css\"><!--td {border: 1px solid #ccc;}br {mso-data-placement:same-cell;}-->\r\n\t</style>\r\n\tWSCL 301 : Advanced MTB Skills</li>\r\n</ul>\r\n\r\n<p>Make sure to check out our website for the race schedule and all of the details surrounding race weekends at each venue.</p>\r\n\r\n<p><a href=\"mailto:info@washingtonleague.org\">info@washingtonleague.org</a><br />\r\n<a href=\"https://washingtonleague.org/\">washingtonleague.org</a><br />\r\n<a href=\"https://www.facebook.com/washingtonmtb\" target=\"_blank\"><img alt=\"\" src=\"https://eventsquare-ccn-prod.s3.amazonaws.com/uploads/files/button_facebook-202103010839.png\" /></a>&nbsp; &nbsp;<a href=\"https://www.instagram.com/washingtonmtb/\" target=\"_blank\"><img alt=\"\" src=\"https://eventsquare-ccn-prod.s3.amazonaws.com/uploads/membership/membercard/preview/button_instagram-202103010839.png\" /></a>&nbsp;</p>",
     "receipt_message_fr":"",
     "cart_message_en":"",
     "cart_message_fr":"",
     "cart_order_message_en":null,
     "cart_order_message_fr":null,
     "store_message_en":null,
     "store_message_fr":null,
     "builder_address_message_en":"In order to register for the Washington Student Cycling League you must be a resident of Washington State.",
     "builder_address_message_fr":"",
     "builder_photo_optional_message_en":"",
     "builder_photo_optional_message_fr":"",
     "builder_photo_required_message_en":"",
     "builder_photo_required_message_fr":"",
     "builder_identity_message_en":"",
     "builder_identity_message_fr":""
     }
     */
}
