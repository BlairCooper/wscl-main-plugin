<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

use RCS\Json\JsonEntity;
use WSCL\Main\CcnBikes\Enums\ListingStatusEnum;

class MembershipOrgListing extends JsonEntity
{
    public int $id;
    public string $name;
    public string $slug;
    public ListingStatusEnum $status;

/*
    "guid": "305-20792",
    "description": "",
    "visibility": false,
    "type": "AS",
    "owner": 100232364,
    "logo": null,
    "image_collection": null,
    "main_content": "",
    "contact_info": null,
    "morg_type": "AS",
    "schema_description": "Washington Student Cycling League Coach Level 3 Fall 2023",
    "schema_location": "Washington, USA",
    "schema_category": "N/A",
    "action_url": "",
    "theme_template": null,
    "banner_image": null,
    "categories": [],
    "disciplines": [],
    "attribute_values": [],
    "location": {
        "user_input": "Washington, USA",
        "latitude": 48.51045,
        "longitude": -122.61214,
        "street2": "",
        "country": null
    },
    "settings_json": "{}",
    "is_deleted": false,
    "is_favorite": false,
    "valid_from": null,
    "valid_to": null,
    "name_en": "Washington Student Cycling League Coach Level 3 Fall 2023",
    "name_fr": ""
*/
}
