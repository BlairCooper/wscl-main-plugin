<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

use RCS\Json\JsonEntity;
use WSCL\Main\CcnBikes\Enums\MembershipStatusEnum;


class MemberDetails extends JsonEntity
{
    public int $id;
    public MembershipStatusEnum $status;
    public IdentitySnapshot $identitySnapshot;
    public AddressSnapshot $addressSnapshot;

    /** @var SurveyApplications[] */
    public array $surveyApplications;

    public function getEmail(): string
    {
        return $this->identitySnapshot->getEmail();
    }

    public function getFirstName(): string
    {
        return $this->identitySnapshot->getFirstName();
    }

    public function getLastName(): string
    {
        return $this->identitySnapshot->getLastName();
    }

    public function getAddress(): string
    {
        return $this->addressSnapshot->getAddress();
    }

    public function getCity(): string
    {
        return $this->addressSnapshot->getCity();
    }

    public function getState():string
    {
        return $this->addressSnapshot->getState();
    }

    public function getZip(): string
    {
        return $this->addressSnapshot->getZip();
    }

    public function getPhone(): string
    {
        return $this->addressSnapshot->getPhone();
    }

    /**
     * @return SurveyApplications[]
     */
    public function getSurveys(): array
    {
        return $this->surveyApplications;
    }

/*
    {
        "id": 499785,
        "status": "ISSU",
        "status_display": "Issued",
        "identity_snapshot": {
            "id": 1519288,
            "first_name": "Vincent",
            "last_name": "Bang",
            "email": "marlitabang@yahoo.com",
            "gender": "M",
            "date_of_birth": "2010-06-23",
            "nationality": null,
            "original": {
                "id": 100438079,
                "first_name": "Vincent",
                "last_name": "Bang",
                "email": "marlitabang@yahoo.com",
                "gender": "M",
                "date_of_birth": "2010-06-23",
                "nationality": null,
                "citizenship_status": null,
                "is_citizenship_status_verified": false,
                "editable_fields": {
                    "first_name": true,
                    "last_name": true,
                    "date_of_birth": true,
                    "email": true,
                    "gender": true,
                    "nationality": true
                },
                "user": {
                    "id": 100275155,
                    "username": "marlitabang@yahoo.com",
                    "first_name": "Vincent",
                    "last_name": "Bang",
                    "email": "marlitabang@yahoo.com"
                },
                "is_profile_identity": true,
                "is_looked_up_by_user": false,
                "photo": {},
                "full_name": "Vincent Bang",
                "identity_type": "HUMAN"
            },
            "user": {
                "id": 100275155,
                "username": "marlitabang@yahoo.com",
                "first_name": "Vincent",
                "last_name": "Bang",
                "email": "marlitabang@yahoo.com"
            },
            "citizenship_status": null,
            "is_citizenship_status_verified": false,
            "age": 12,
            "identity_type": "HUMAN"
        },
        "address_snapshot": {
            "id": 1294005,
            "street1": "7101 84th Ave SE",
            "street2": "",
            "country": {
                "id": 226,
                "name": "United States",
                "iso3": "USA",
                "iso2": "US",
                "iso_numeric": "840",
                "ioc": "USA",
                "province_label_i18n_anchor": "PROFILE.ADDRESSES.STATE",
                "flag": {
                    "id": 86720,
                    "url": "https://eventsquare-ccn-prod.s3.amazonaws.com/srv/sites/ccnbikes.com/eventsquare/geolocation_app/data/country_flags/us-202106101346.svg"
                },
                "is_province_other_required": true
            },
            "province": {
                "id": 62,
                "name": "Washington",
                "abbrev": "WA",
                "country": {
                    "id": 226,
                    "name": "United States",
                    "iso3": "USA",
                    "iso2": "US",
                    "iso_numeric": "840",
                    "ioc": "USA",
                    "province_label_i18n_anchor": "PROFILE.ADDRESSES.STATE",
                    "flag": {
                        "id": 86720,
                        "url": "https://eventsquare-ccn-prod.s3.amazonaws.com/srv/sites/ccnbikes.com/eventsquare/geolocation_app/data/country_flags/us-202106101346.svg"
                    },
                    "is_province_other_required": true
                }
            },
            "province_other": null,
            "city": "Mercer island",
            "postal_code": "98040",
            "phone_number": "2064997872",
            "address": 101021349
        },
        "shipping_address": {
            "id": 1294010,
            "street1": "7101 84th Ave SE",
            "street2": "",
            "country": {
                "id": 226,
                "name": "United States",
                "iso3": "USA",
                "iso2": "US",
                "iso_numeric": "840",
                "ioc": "USA",
                "province_label_i18n_anchor": "PROFILE.ADDRESSES.STATE",
                "flag": {
                    "id": 86720,
                    "url": "https://eventsquare-ccn-prod.s3.amazonaws.com/srv/sites/ccnbikes.com/eventsquare/geolocation_app/data/country_flags/us-202106101346.svg"
                },
                "is_province_other_required": true
            },
            "province": {
                "id": 62,
                "name": "Washington",
                "abbrev": "WA",
                "country": {
                    "id": 226,
                    "name": "United States",
                    "iso3": "USA",
                    "iso2": "US",
                    "iso_numeric": "840",
                    "ioc": "USA",
                    "province_label_i18n_anchor": "PROFILE.ADDRESSES.STATE",
                    "flag": {
                        "id": 86720,
                        "url": "https://eventsquare-ccn-prod.s3.amazonaws.com/srv/sites/ccnbikes.com/eventsquare/geolocation_app/data/country_flags/us-202106101346.svg"
                    },
                    "is_province_other_required": true
                }
            },
            "province_other": null,
            "city": "Mercer island",
            "postal_code": "98040",
            "phone_number": "2064997872",
            "address": 101021349
        },
        "membership_organization": {
            "id": 409,
            "name": "Washington Student Cycling League Student Athletes Fall 2022",
            "organization_name": "Washington Student Cycling League",
            "slug": "washington-student-cycling-league",
            "enable_membership_effective_date_override": false,
            "allow_member_to_edit_membership": "N",
            "email_confirmation_type": "pm",
            "show_citizenship_status": false,
            "manual_membership_creation_permission": "ALL",
            "is_purchased_group_foreign_id_editable": false
        },
        "nationality": null,
        "photo": null,
        "renewable": false,
        "valid_to": null,
        "admin_notes": "",
        "generated_numbers": [],
        "waiver_applications": [
            {
                "id": 1514160,
                "waiver_signature": {
                    "id": 1319666,
                    "waiver_title":
                        "Washington Student Cycling League : Agreement to Participate and Release of Liability",
                    "signature": "Marlita bang",
                    "physical_waiver_received": false,
                    "physical_waiver_required": false,
                    "created_at": "2022-09-14T16:02:10.896724"
                }
            },
            {
                "id": 1514161,
                "waiver_signature": {
                    "id": 1319667,
                    "waiver_title": "Washington Student Cycling League : Policies, Releases, and Rules",
                    "signature": "Marlita bang",
                    "physical_waiver_received": false,
                    "physical_waiver_required": false,
                    "created_at": "2022-09-14T16:02:27.859592"
                }
            }
        ],
        "purchased_groups": [
            {
                "id": 544331,
                "uuid": "20220914155755EF6UIN-VKW",
                "name": "Student Athlete",
                "affiliates": [
                    {
                        "id": 219551,
                        "name": "Mercer Island",
                        "creation_type": "CM",
                        "affiliate_type_display_name": "Club",
                        "node_selection": {
                            "id": 67678,
                            "node_id": 67678,
                            "name": "Mercer Island",
                            "display_name": "Team :: Mercer Island",
                            "display_name_override": null,
                            "short_display_name": "Mercer Island",
                            "valid_from": null,
                            "valid_to": null,
                            "dob_to": null,
                            "is_public": false,
                            "desc": "",
                            "dob_from": null,
                            "sex": "O",
                            "child_select_type": "1",
                            "sort_order": 0,
                            "breadcrumb_name": "Team :: Mercer Island",
                            "resolved_must_be_approved": false,
                            "must_be_approved": false,
                            "options": [],
                            "template_type": null
                        },
                        "affiliate_type": "CL",
                        "is_primary": true
                    }
                ],
                "nodes": [
                    {
                        "id": 67696,
                        "name": "Student Athlete",
                        "breadcrumb_name_without_override": "Washington Student Cycling League :: Student Athlete"
                    }
                ],
                "valid_from": null,
                "valid_to": null,
                "order": {
                    "id": 1464195,
                    "cart_id": 1174149,
                    "created_at": "2022-09-14T16:00:54.172444",
                    "checked_out_at": "2022-09-14T16:03:45.124300",
                    "payment_group": null
                },
                "created_at": "2022-09-14T16:00:54.113813",
                "morg": {
                    "id": 409,
                    "name": "Washington Student Cycling League Student Athletes Fall 2022",
                    "slug": "washington-student-cycling-league",
                    "contact_info": null,
                    "address": null
                },
                "im": {
                    "id": 499785,
                    "status": "ISSU"
                },
                "long_description": null,
                "short_description": null,
                "grant_snapshots": [
                    {
                        "id": 686612,
                        "is_required": false,
                        "original": {
                            "id": 8490,
                            "is_required": false,
                            "group_id": 5946,
                            "node_id": 67696
                        },
                        "node": {
                            "id": 67696,
                            "node_id": 67696,
                            "name": "Student Athlete",
                            "display_name": "Student Athlete",
                            "display_name_override": "Student Athlete",
                            "short_display_name": "Student Athlete",
                            "valid_from": null,
                            "valid_to": null,
                            "dob_to": null,
                            "is_public": false,
                            "desc": "",
                            "dob_from": null,
                            "sex": "O",
                            "child_select_type": "1",
                            "sort_order": 0,
                            "breadcrumb_name": "Student Athlete",
                            "resolved_must_be_approved": false,
                            "must_be_approved": false,
                            "options": [],
                            "template_type": null
                        },
                        "purchased_group_nodes": [
                            {
                                "id": 701166,
                                "valid_from": null,
                                "valid_to": null,
                                "groupgrantsnapshot_id": 686612,
                                "was_approved": null,
                                "node": {
                                    "id": 67696,
                                    "node_id": 67696,
                                    "name": "Student Athlete",
                                    "display_name": "Student Athlete",
                                    "display_name_override": "Student Athlete",
                                    "short_display_name": "Student Athlete",
                                    "valid_from": null,
                                    "valid_to": null,
                                    "dob_to": null,
                                    "is_public": false,
                                    "desc": "",
                                    "dob_from": null,
                                    "sex": "O",
                                    "child_select_type": "1",
                                    "sort_order": 0,
                                    "breadcrumb_name": "Student Athlete",
                                    "resolved_must_be_approved": false,
                                    "must_be_approved": false,
                                    "options": [],
                                    "template_type": null
                                }
                            }
                        ]
                    }
                ],
                "anchor": {
                    "id": 2180516,
                    "status": "CP",
                    "get_status_display": "Complete",
                    "creation_type": "CU",
                    "get_creation_type_display": "Created by User",
                    "quantity": 1
                },
                "original": {
                    "id": 5946,
                    "name": "Student Athlete",
                    "long_description": null,
                    "has_limited_membership_validity": false,
                    "available_nodes": [
                        {
                            "id": 8490,
                            "required": false,
                            "title": "Student Athlete",
                            "root": {
                                "id": 67696,
                                "node_id": 67696,
                                "name": "Student Athlete",
                                "display_name": "Student Athlete",
                                "display_name_override": "Student Athlete",
                                "short_display_name": "Student Athlete",
                                "valid_from": null,
                                "valid_to": null,
                                "dob_to": null,
                                "is_public": false,
                                "desc": "",
                                "dob_from": null,
                                "sex": "O",
                                "child_select_type": "1",
                                "sort_order": 0,
                                "breadcrumb_name": "Student Athlete",
                                "resolved_must_be_approved": false,
                                "must_be_approved": false,
                                "options": [],
                                "template_type": null
                            }
                        }
                    ],
                    "available_affiliates": [
                        {
                            "id": 67663,
                            "node_id": 67663,
                            "name": "Team",
                            "description": "",
                            "excluded_node_type_ids": [],
                            "self_applicable_node_type_ids": [],
                            "options": [
                                {
                                    "id": 67664,
                                    "node_id": 67664,
                                    "name": "Anacortes Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67665,
                                    "node_id": 67665,
                                    "name": "Bainbridge Island",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67666,
                                    "node_id": 67666,
                                    "name": "Bonner County Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67667,
                                    "node_id": 67667,
                                    "name": "Capital City Mountain Bike Team",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67668,
                                    "node_id": 67668,
                                    "name": "Cascade High School | Icicle River Middle School",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67669,
                                    "node_id": 67669,
                                    "name": "Cedarcrest MTB",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67670,
                                    "node_id": 67670,
                                    "name": "Edmonds Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67671,
                                    "node_id": 67671,
                                    "name": "Icicle Bicycle",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67902,
                                    "node_id": 67902,
                                    "name": "Independent",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67672,
                                    "node_id": 67672,
                                    "name": "Inland NW Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67673,
                                    "node_id": 67673,
                                    "name": "Issaquah Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67674,
                                    "node_id": 67674,
                                    "name": "Key Pen Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67675,
                                    "node_id": 67675,
                                    "name": "Kittitas County Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67676,
                                    "node_id": 67676,
                                    "name": "Kootenai Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67677,
                                    "node_id": 67677,
                                    "name": "Lake Washington Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67678,
                                    "node_id": 67678,
                                    "name": "Mercer Island",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67679,
                                    "node_id": 67679,
                                    "name": "Methow Valley Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67680,
                                    "node_id": 67680,
                                    "name": "Metro Seattle MTB",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67681,
                                    "node_id": 67681,
                                    "name": "Monroe Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67682,
                                    "node_id": 67682,
                                    "name": "Mt. Si Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67683,
                                    "node_id": 67683,
                                    "name": "North Kitsap Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67684,
                                    "node_id": 67684,
                                    "name": "Northshore Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67685,
                                    "node_id": 67685,
                                    "name": "Palouse Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67686,
                                    "node_id": 67686,
                                    "name": "Pilchuck Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67687,
                                    "node_id": 67687,
                                    "name": "Prosser Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67688,
                                    "node_id": 67688,
                                    "name": "SW Washington Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67689,
                                    "node_id": 67689,
                                    "name": "Snohomish Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67690,
                                    "node_id": 67690,
                                    "name": "Spokane Valley Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67691,
                                    "node_id": 67691,
                                    "name": "Tahoma Mountain Bike Team",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67692,
                                    "node_id": 67692,
                                    "name": "Three Rivers Devo",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67693,
                                    "node_id": 67693,
                                    "name": "Wenatchee Valley Devo",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                },
                                {
                                    "id": 67694,
                                    "node_id": 67694,
                                    "name": "Yakima Composite",
                                    "description": "",
                                    "excluded_node_type_ids": [],
                                    "self_applicable_node_type_ids": [],
                                    "options": [],
                                    "is_division_required": false,
                                    "divisions": []
                                }
                            ],
                            "is_division_required": false,
                            "divisions": [],
                            "affiliation_id": 1021,
                            "affiliation_type": "group",
                            "affiliate_type": "CL",
                            "affiliate_type_display_name": "Club",
                            "object_id_affiliation_id_map": {
                                "8490": "1021"
                            }
                        }
                    ]
                },
                "addons": [],
                "group_id": 5946,
                "photo_requirement": "N",
                "builder_photo_optional_message": "",
                "builder_photo_required_message": "",
                "groupgroup": {
                    "id": 985,
                    "name": "Student Athlete"
                }
            }
        ],
        "order_version": 2,
        "survey_applications": [
            {
                "id": 2706009,
                "question_answers": [
                    {
                        "answers": [
                            {
                                "id": 8424617,
                                "answer": "MIHS",
                                "option": null
                            }
                        ],
                        "question": {
                            "id": 53078,
                            "question": "What high school do you or will you attend?",
                            "widget": "TextInput",
                            "require": true,
                            "selectable_options": [],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1,
                            "survey_id": 12757,
                            "updated_at": "2022-07-20T13:11:14.771306",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "What high school do you or will you attend?",
                            "question_fr": "What high school do you or will you attend?"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424618,
                                "answer": "7",
                                "option": 95733
                            }
                        ],
                        "question": {
                            "id": 53079,
                            "question": "What grade will you be in during the season? (Sept / Oct 2022)",
                            "widget": "Select",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95732,
                                    "question": 53079,
                                    "label": "6",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "6",
                                    "label_fr": "6",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95733,
                                    "question": 53079,
                                    "label": "7",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "7",
                                    "label_fr": "7",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95734,
                                    "question": 53079,
                                    "label": "8",
                                    "require_additional": false,
                                    "sort_order": 2,
                                    "message": "",
                                    "label_en": "8",
                                    "label_fr": "8",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95735,
                                    "question": 53079,
                                    "label": "9",
                                    "require_additional": false,
                                    "sort_order": 3,
                                    "message": "",
                                    "label_en": "9",
                                    "label_fr": "9",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95736,
                                    "question": 53079,
                                    "label": "10",
                                    "require_additional": false,
                                    "sort_order": 4,
                                    "message": "",
                                    "label_en": "10",
                                    "label_fr": "10",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95737,
                                    "question": 53079,
                                    "label": "11",
                                    "require_additional": false,
                                    "sort_order": 5,
                                    "message": "",
                                    "label_en": "11",
                                    "label_fr": "11",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95738,
                                    "question": 53079,
                                    "label": "12",
                                    "require_additional": false,
                                    "sort_order": 6,
                                    "message": "",
                                    "label_en": "12",
                                    "label_fr": "12",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 2,
                            "survey_id": 12757,
                            "updated_at": "2022-08-02T15:01:50.353992",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "What grade will you be in during the season? (Sept / Oct 2022)",
                            "question_fr": ""
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424619,
                                "answer": "Intermediate",
                                "option": 95740
                            }
                        ],
                        "question": {
                            "id": 53080,
                            "question": "What skill level do you consider yourself?",
                            "widget": "Select",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95739,
                                    "question": 53080,
                                    "label": "Beginner",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "Beginner",
                                    "label_fr": "Beginner",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95740,
                                    "question": 53080,
                                    "label": "Intermediate",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "Intermediate",
                                    "label_fr": "Intermediate",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95741,
                                    "question": 53080,
                                    "label": "Advanced",
                                    "require_additional": false,
                                    "sort_order": 2,
                                    "message": "",
                                    "label_en": "Advanced",
                                    "label_fr": "Advanced",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 3,
                            "survey_id": 12757,
                            "updated_at": "2022-07-20T13:11:14.795046",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "What skill level do you consider yourself?",
                            "question_fr": "What skill level do you consider yourself?"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424620,
                                "answer": "I am comfortable riding varying and somewhat technical trails",
                                "option": 95743
                            }
                        ],
                        "question": {
                            "id": 53081,
                            "question": "Please select which best describes you and your riding.",
                            "widget": "Select",
                            "require": false,
                            "selectable_options": [
                                {
                                    "id": 95742,
                                    "question": 53081,
                                    "label": "I can handle riding on smooth trails but hesitate",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "I can handle riding on smooth trails but hesitate",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95743,
                                    "question": 53081,
                                    "label": "I am comfortable riding varying and somewhat technical trails",
                                    "require_additional": false,
                                    "sort_order": 2,
                                    "message": "",
                                    "label_en": "I am comfortable riding varying and somewhat technical trails",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95744,
                                    "question": 53081,
                                    "label": "I am new to mountain biking",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "I am new to mountain biking",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 4,
                            "survey_id": 12757,
                            "updated_at": "2022-07-20T13:11:14.803332",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Please select which best describes you and your riding.",
                            "question_fr": ""
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424621,
                                "answer": "Very Comfortable",
                                "option": 95747
                            }
                        ],
                        "question": {
                            "id": 53082,
                            "question": "How comfortable are you using both the front and rear brake?",
                            "widget": "Select",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95745,
                                    "question": 53082,
                                    "label": "Not Comfortable",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "Not Comfortable",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95746,
                                    "question": 53082,
                                    "label": "Comfortable",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "Comfortable",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95747,
                                    "question": 53082,
                                    "label": "Very Comfortable",
                                    "require_additional": false,
                                    "sort_order": 2,
                                    "message": "",
                                    "label_en": "Very Comfortable",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 10,
                            "survey_id": 12757,
                            "updated_at": "2022-07-20T13:11:14.812084",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "How comfortable are you using both the front and rear brake?",
                            "question_fr": ""
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424622,
                                "answer": "1 - 2 hours",
                                "option": 95751
                            }
                        ],
                        "question": {
                            "id": 53083,
                            "question": "How long are you comfortably riding without getting too tired?",
                            "widget": "Select",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95748,
                                    "question": 53083,
                                    "label": "2+ hours",
                                    "require_additional": false,
                                    "sort_order": 3,
                                    "message": "",
                                    "label_en": "2+ hours",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95749,
                                    "question": 53083,
                                    "label": "Less than 30 minutes",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "Less than 30 minutes",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95750,
                                    "question": 53083,
                                    "label": "30 minutes - 1 hour",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "30 minutes - 1 hour",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95751,
                                    "question": 53083,
                                    "label": "1 - 2 hours",
                                    "require_additional": false,
                                    "sort_order": 2,
                                    "message": "",
                                    "label_en": "1 - 2 hours",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 11,
                            "survey_id": 12757,
                            "updated_at": "2022-07-20T13:11:14.820529",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "How long are you comfortably riding without getting too tired?",
                            "question_fr": ""
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424623,
                                "answer": "I like to take advantage of all my gears",
                                "option": 95755
                            }
                        ],
                        "question": {
                            "id": 53084,
                            "question": "Which best describes how you use your gears?",
                            "widget": "Select",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95752,
                                    "question": 53084,
                                    "label": "I mostly stay in same gear the whole ride",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "I mostly stay in same gear the whole ride",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95753,
                                    "question": 53084,
                                    "label": "I often shift gears using just a few gears",
                                    "require_additional": false,
                                    "sort_order": 2,
                                    "message": "",
                                    "label_en": "I often shift gears using just a few gears",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95754,
                                    "question": 53084,
                                    "label": "I sometimes shift gears using just a couple gears",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "I sometimes shift gears using just a couple gears",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95755,
                                    "question": 53084,
                                    "label": "I like to take advantage of all my gears",
                                    "require_additional": false,
                                    "sort_order": 3,
                                    "message": "",
                                    "label_en": "I like to take advantage of all my gears",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 12,
                            "survey_id": 12757,
                            "updated_at": "2022-07-20T13:11:14.832633",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Which best describes how you use your gears?",
                            "question_fr": ""
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424624,
                                "answer": "2 - 4 years",
                                "option": 95757
                            }
                        ],
                        "question": {
                            "id": 53085,
                            "question": "How long have you been mountain biking?",
                            "widget": "Select",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95756,
                                    "question": 53085,
                                    "label": "Less than 1 year",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "Less than 1 year",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95757,
                                    "question": 53085,
                                    "label": "2 - 4 years",
                                    "require_additional": false,
                                    "sort_order": 3,
                                    "message": "",
                                    "label_en": "2 - 4 years",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95758,
                                    "question": 53085,
                                    "label": "5+ years",
                                    "require_additional": false,
                                    "sort_order": 4,
                                    "message": "",
                                    "label_en": "5+ years",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95759,
                                    "question": 53085,
                                    "label": "I am new to mountain biking",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "I am new to mountain biking",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95760,
                                    "question": 53085,
                                    "label": "1 - 2 years",
                                    "require_additional": false,
                                    "sort_order": 2,
                                    "message": "",
                                    "label_en": "1 - 2 years",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 14,
                            "survey_id": 12757,
                            "updated_at": "2022-07-20T13:11:14.842911",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "How long have you been mountain biking?",
                            "question_fr": ""
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424625,
                                "answer": "deuces wild",
                                "option": null
                            }
                        ],
                        "question": {
                            "id": 53086,
                            "question": "What is your favorite trail?",
                            "widget": "TextInput",
                            "require": true,
                            "selectable_options": [],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 15,
                            "survey_id": 12757,
                            "updated_at": "2022-07-20T13:11:14.854275",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "What is your favorite trail?",
                            "question_fr": ""
                        }
                    }
                ],
                "created_at": "2022-09-14T16:00:54.333484",
                "updated_at": "2022-09-14T16:00:54.333494",
                "short_description": "Additional Student Information"
            },
            {
                "id": 2706010,
                "question_answers": [
                    {
                        "answers": [
                            {
                                "id": 8424626,
                                "answer": "marlitabang@yahoo.com",
                                "option": null
                            }
                        ],
                        "question": {
                            "id": 53089,
                            "question": "Parent/Guardian 1 : Email",
                            "widget": "TextInput",
                            "require": true,
                            "selectable_options": [],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1000,
                            "survey_id": 12758,
                            "updated_at": "2022-07-20T13:11:14.878310",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Parent/Guardian 1 : Email",
                            "question_fr": "Parent/Guardian 1 : Email"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424627,
                                "answer": "marlita",
                                "option": null
                            }
                        ],
                        "question": {
                            "id": 53090,
                            "question": "Parent/Guardian 1 : First Name",
                            "widget": "TextInput",
                            "require": true,
                            "selectable_options": [],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1002,
                            "survey_id": 12758,
                            "updated_at": "2022-07-20T13:11:14.883020",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Parent/Guardian 1 : First Name",
                            "question_fr": "Parent/Guardian 1 : First Name"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424628,
                                "answer": "bang",
                                "option": null
                            }
                        ],
                        "question": {
                            "id": 53091,
                            "question": "Parent/Guardian 1 : Last Name",
                            "widget": "TextInput",
                            "require": true,
                            "selectable_options": [],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1003,
                            "survey_id": 12758,
                            "updated_at": "2022-07-20T13:11:14.888202",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Parent/Guardian 1 : Last Name",
                            "question_fr": "Parent/Guardian 1 : Last Name"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424629,
                                "answer": "2064997872",
                                "option": null
                            }
                        ],
                        "question": {
                            "id": 53092,
                            "question": "Parent/Guardian 1 : Cell Phone Number (xxx-xxx-xxxx)",
                            "widget": "TextInput",
                            "require": true,
                            "selectable_options": [],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "(^[\\d{1}]?[\\s.-]?[.(]?\\d{3}?[.)]?[\\s.-]?\\d{3}?[\\s.-]?\\d{4}?$)",
                            "sort_order": 1004,
                            "survey_id": 12758,
                            "updated_at": "2022-07-20T13:11:14.892263",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Parent/Guardian 1 : Cell Phone Number (xxx-xxx-xxxx)",
                            "question_fr": "Parent/Guardian 1 : Cell Phone Number (xxx-xxx-xxxx)"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424630,
                                "answer": "2066018950",
                                "option": null
                            }
                        ],
                        "question": {
                            "id": 53093,
                            "question": "Parent/Guardian 1 : Home Phone Number (xxx-xxx-xxxx)",
                            "widget": "TextInput",
                            "require": true,
                            "selectable_options": [],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "(^[\\d{1}]?[\\s.-]?[.(]?\\d{3}?[.)]?[\\s.-]?\\d{3}?[\\s.-]?\\d{4}?$)",
                            "sort_order": 1005,
                            "survey_id": 12758,
                            "updated_at": "2022-07-20T13:11:14.896257",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Parent/Guardian 1 : Home Phone Number (xxx-xxx-xxxx)",
                            "question_fr": "Parent/Guardian 1 : Home Phone Number (xxx-xxx-xxxx)"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424631,
                                "answer": "kbang@amazon.com",
                                "option": null
                            }
                        ],
                        "question": {
                            "id": 53095,
                            "question": "Parent/Guardian 2 : Email",
                            "widget": "TextInput",
                            "require": false,
                            "selectable_options": [],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1010,
                            "survey_id": 12758,
                            "updated_at": "2022-07-20T13:11:14.905185",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Parent/Guardian 2 : Email",
                            "question_fr": "Parent/Guardian 2 : Email"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424632,
                                "answer": "Ken",
                                "option": null
                            }
                        ],
                        "question": {
                            "id": 53096,
                            "question": "Parent/Guardian 2 : First Name",
                            "widget": "TextInput",
                            "require": false,
                            "selectable_options": [],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1011,
                            "survey_id": 12758,
                            "updated_at": "2022-07-20T13:11:14.909976",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Parent/Guardian 2 : First Name",
                            "question_fr": "Parent/Guardian 2 : First Name"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424633,
                                "answer": "bang",
                                "option": null
                            }
                        ],
                        "question": {
                            "id": 53097,
                            "question": "Parent/Guardian 2 : Last Name",
                            "widget": "TextInput",
                            "require": false,
                            "selectable_options": [],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1012,
                            "survey_id": 12758,
                            "updated_at": "2022-07-20T13:11:14.914459",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Parent/Guardian 2 : Last Name",
                            "question_fr": "Parent/Guardian 2 : Last Name"
                        }
                    }
                ],
                "created_at": "2022-09-14T16:00:54.597026",
                "updated_at": "2022-09-14T16:00:54.597036",
                "short_description": "Family Contacts"
            },
            {
                "id": 2706011,
                "question_answers": [
                    {
                        "answers": [
                            {
                                "id": 8424634,
                                "answer": "No",
                                "option": 95761
                            }
                        ],
                        "question": {
                            "id": 53101,
                            "question": "Do you want to list additional emergency contacts for your child in case both parents cannot be reached?",
                            "widget": "Select",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95761,
                                    "question": 53101,
                                    "label": "No",
                                    "require_additional": false,
                                    "sort_order": 2,
                                    "message": "",
                                    "label_en": "No",
                                    "label_fr": "No",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95762,
                                    "question": 53101,
                                    "label": "Yes",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "Yes",
                                    "label_fr": "Yes",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1020,
                            "survey_id": 12759,
                            "updated_at": "2022-07-20T13:11:14.940517",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Do you want to list additional emergency contacts for your child in case both parents cannot be reached?",
                            "question_fr": ""
                        }
                    }
                ],
                "created_at": "2022-09-14T16:00:54.766109",
                "updated_at": "2022-09-14T16:00:54.766119",
                "short_description": "Parent 1 and/or Parent 2 are, by default, the primary emergency contacts"
            },
            {
                "id": 2706012,
                "question_answers": [
                    {
                        "answers": [
                            {
                                "id": 8424635,
                                "answer": "Yes",
                                "option": 95764
                            }
                        ],
                        "question": {
                            "id": 53110,
                            "question": "My Child does have health insurance",
                            "widget": "Select",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95763,
                                    "question": 53110,
                                    "label": "No",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "No",
                                    "label_fr": "No",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95764,
                                    "question": 53110,
                                    "label": "Yes",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "Yes",
                                    "label_fr": "Yes",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1030,
                            "survey_id": 12760,
                            "updated_at": "2022-07-20T13:11:15.026132",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "My Child does have health insurance",
                            "question_fr": "My Child does have health insurance"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424636,
                                "answer": "Aetna",
                                "option": null
                            }
                        ],
                        "question": {
                            "id": 53111,
                            "question": "Health Insurance Provider",
                            "widget": "TextInput",
                            "require": true,
                            "selectable_options": [],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [
                                95764
                            ],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1032,
                            "survey_id": 12760,
                            "updated_at": "2022-07-20T13:11:15.033837",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Health Insurance Provider",
                            "question_fr": "Health Insurance Provider"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424637,
                                "answer": "W1234567",
                                "option": null
                            }
                        ],
                        "question": {
                            "id": 53112,
                            "question": "Health Insurance Group",
                            "widget": "TextInput",
                            "require": true,
                            "selectable_options": [],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [
                                95764
                            ],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1033,
                            "survey_id": 12760,
                            "updated_at": "2022-07-20T13:11:15.042777",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Health Insurance Group",
                            "question_fr": "Health Insurance Group"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424638,
                                "answer": "12345678",
                                "option": null
                            }
                        ],
                        "question": {
                            "id": 53113,
                            "question": "Health Insurance Policy Number",
                            "widget": "TextInput",
                            "require": true,
                            "selectable_options": [],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [
                                95764
                            ],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1034,
                            "survey_id": 12760,
                            "updated_at": "2022-07-20T13:11:15.050812",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Health Insurance Policy Number",
                            "question_fr": "Health Insurance Policy Number"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424639,
                                "answer": "Yes",
                                "option": 95765
                            }
                        ],
                        "question": {
                            "id": 53114,
                            "question": "My child is in good physical and mental health and is able to fully participate in WSCL / Team events and practices",
                            "widget": "CheckboxSelectMultiple",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95765,
                                    "question": 53114,
                                    "label": "Yes",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "Yes",
                                    "label_fr": "Yes",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1035,
                            "survey_id": 12760,
                            "updated_at": "2022-07-20T13:11:15.058725",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "My child is in good physical and mental health and is able to fully participate in WSCL / Team events and practices",
                            "question_fr": ""
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424640,
                                "answer": "No",
                                "option": 95767
                            }
                        ],
                        "question": {
                            "id": 53115,
                            "question": "My child has medical conditions or allergies",
                            "widget": "Select",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95766,
                                    "question": 53115,
                                    "label": "Yes",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "Yes",
                                    "label_fr": "Yes",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95767,
                                    "question": 53115,
                                    "label": "No",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "No",
                                    "label_fr": "No",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1036,
                            "survey_id": 12760,
                            "updated_at": "2022-07-20T13:11:15.064312",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "My child has medical conditions or allergies",
                            "question_fr": "My child has medical conditions or allergies"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424641,
                                "answer": "No",
                                "option": 95769
                            }
                        ],
                        "question": {
                            "id": 53117,
                            "question": "My child has asthma and will have an inhaler with him or her",
                            "widget": "Select",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95768,
                                    "question": 53117,
                                    "label": "Yes",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "Yes",
                                    "label_fr": "Yes",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95769,
                                    "question": 53117,
                                    "label": "No",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "No",
                                    "label_fr": "No",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1038,
                            "survey_id": 12760,
                            "updated_at": "2022-07-20T13:11:15.080624",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "My child has asthma and will have an inhaler with him or her",
                            "question_fr": "My child has asthma and will have an inhaler with him or her"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424642,
                                "answer": "No",
                                "option": 95771
                            }
                        ],
                        "question": {
                            "id": 53119,
                            "question": "My child is taking prescription medication",
                            "widget": "Select",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95770,
                                    "question": 53119,
                                    "label": "Yes",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "Yes",
                                    "label_fr": "Yes",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95771,
                                    "question": 53119,
                                    "label": "No",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "No",
                                    "label_fr": "No",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1040,
                            "survey_id": 12760,
                            "updated_at": "2022-07-20T13:11:15.097106",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "My child is taking prescription medication",
                            "question_fr": "My child is taking prescription medication"
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424643,
                                "answer": "No",
                                "option": 95773
                            }
                        ],
                        "question": {
                            "id": 53121,
                            "question": "Do you have any food allergies? (This information is used at our events where food is provided)",
                            "widget": "Select",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95772,
                                    "question": 53121,
                                    "label": "Yes",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "Yes",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95773,
                                    "question": 53121,
                                    "label": "No",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "No",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1044,
                            "survey_id": 12760,
                            "updated_at": "2022-07-20T13:11:15.114263",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Do you have any food allergies? (This information is used at our events where food is provided)",
                            "question_fr": ""
                        }
                    },
                    {
                        "answers": [
                            {
                                "id": 8424644,
                                "answer": "Yes",
                                "option": 95774
                            }
                        ],
                        "question": {
                            "id": 53123,
                            "question": "I authorize WSCL  / Team Staff to give my child / dependent ibuprofen in the event he or she needs it",
                            "widget": "Select",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95774,
                                    "question": 53123,
                                    "label": "Yes",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "Yes",
                                    "label_fr": "Yes",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95775,
                                    "question": 53123,
                                    "label": "No",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "No",
                                    "label_fr": "No",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1049,
                            "survey_id": 12760,
                            "updated_at": "2022-07-20T13:11:15.130392",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "I authorize WSCL  / Team Staff to give my child / dependent ibuprofen in the event he or she needs it",
                            "question_fr": ""
                        }
                    }
                ],
                "created_at": "2022-09-14T16:00:54.775008",
                "updated_at": "2022-09-14T16:00:54.775017",
                "short_description": "Student Health Information"
            },
            {
                "id": 2706013,
                "question_answers": [
                    {
                        "answers": [
                            {
                                "id": 8424645,
                                "answer": "Yes",
                                "option": 95776
                            }
                        ],
                        "question": {
                            "id": 53124,
                            "question":
                                "I have read the Survey Consent carefully before signing and give consent to survey.",
                            "widget": "Select",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95776,
                                    "question": 53124,
                                    "label": "Yes",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "Yes",
                                    "label_fr": "Yes",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95777,
                                    "question": 53124,
                                    "label": "No",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "No",
                                    "label_fr": "No",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1050,
                            "survey_id": 12761,
                            "updated_at": "2022-07-20T13:11:15.148174",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en":
                                "I have read the Survey Consent carefully before signing and give consent to survey.",
                            "question_fr": ""
                        }
                    }
                ],
                "created_at": "2022-09-14T16:00:55.016932",
                "updated_at": "2022-09-14T16:00:55.016946",
                "short_description":
                    "Survey Consent of Minor<br><br>\r\n<font size=\"2\"> I\r\nunderstand the Participant may be asked to complete a confidential pre and post-program survey at the\r\nbeginning and conclusion of the program. The survey measures student attitudes toward school, family,\r\nself and peers, as well as personal outcomes and opinions. Participant will not be asked to provide their\r\nname on the survey. The purpose of the survey is to measure any group attitudinal and health changes\r\nthat occur because of participation in Washington Student Cycling League programs.</font>"
            },
            {
                "id": 2706014,
                "question_answers": [
                    {
                        "answers": [
                            {
                                "id": 8424646,
                                "answer": "I understand that if my child or ward is injured in a way that falls under the definition of a WSCL injury during any WSCL / Team activity, an incident report will be submitted by the teams designated reporter to the WSCL incident report platform. An incident report is necessary if the injury requires one of the following; *a referral to a medical provider beyond on site first aid or EMS. *Time loss from training or competition beyond the day of injury. *Time loss from school or work. I understand the incident reporting process for all WSCL student-athletes.",
                                "option": 95778
                            }
                        ],
                        "question": {
                            "id": 53125,
                            "question": "Reportable Injury",
                            "widget": "CheckboxSelectMultiple",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95778,
                                    "question": 53125,
                                    "label": "I understand that if my child or ward is injured in a way that falls under the definition of a WSCL injury during any WSCL / Team activity, an incident report will be submitted by the teams designated reporter to the WSCL incident report platform. An incident report is necessary if the injury requires one of the following; *a referral to a medical provider beyond on site first aid or EMS. *Time loss from training or competition beyond the day of injury. *Time loss from school or work. I understand the incident reporting process for all WSCL student-athletes.",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "I understand that if my child or ward is injured in a way that falls under the definition of a WSCL injury during any WSCL / Team activity, an incident report will be submitted by the teams designated reporter to the WSCL incident report platform. An incident report is necessary if the injury requires one of the following; *a referral to a medical provider beyond on site first aid or EMS. *Time loss from training or competition beyond the day of injury. *Time loss from school or work. I understand the incident reporting process for all WSCL student-athletes.",
                                    "label_fr": "",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1051,
                            "survey_id": 12762,
                            "updated_at": "2022-07-20T13:11:15.165443",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "Reportable Injury",
                            "question_fr": "Reportable Injury"
                        }
                    }
                ],
                "created_at": "2022-09-14T16:00:55.026102",
                "updated_at": "2022-09-14T16:00:55.026112",
                "short_description": "Release - Reportable Injury"
            },
            {
                "id": 2706015,
                "question_answers": [
                    {
                        "answers": [
                            {
                                "id": 8424647,
                                "answer": "Yes",
                                "option": 95780
                            }
                        ],
                        "question": {
                            "id": 53126,
                            "question": "From time to time WSCL and Association sponsors request our membership information for promotional purposes. Your information will not be provided unless you opt in by checking the respective boxes below. By opting in you give us permission to release your mailing address, phone number and email address to receive the following: I am interested in receiving information from WSCL  education partners about collegiate Cycling Programs.",
                            "widget": "Select",
                            "require": true,
                            "selectable_options": [
                                {
                                    "id": 95779,
                                    "question": 53126,
                                    "label": "No",
                                    "require_additional": false,
                                    "sort_order": 1,
                                    "message": "",
                                    "label_en": "No",
                                    "label_fr": "No",
                                    "message_en": "",
                                    "message_fr": ""
                                },
                                {
                                    "id": 95780,
                                    "question": 53126,
                                    "label": "Yes",
                                    "require_additional": false,
                                    "sort_order": 0,
                                    "message": "",
                                    "label_en": "Yes",
                                    "label_fr": "Yes",
                                    "message_en": "",
                                    "message_fr": ""
                                }
                            ],
                            "permission": "2",
                            "selectable_timer_range": null,
                            "requires": [],
                            "allow_i_dont_know": false,
                            "validation": "",
                            "sort_order": 1052,
                            "survey_id": 12763,
                            "updated_at": "2022-07-20T13:11:15.180321",
                            "lockdown_answer_after_registration_completed": false,
                            "question_en": "From time to time WSCL and Association sponsors request our membership information for promotional purposes. Your information will not be provided unless you opt in by checking the respective boxes below. By opting in you give us permission to release your mailing address, phone number and email address to receive the following: I am interested in receiving information from WSCL  education partners about collegiate Cycling Programs.",
                            "question_fr": ""
                        }
                    }
                ],
                "created_at": "2022-09-14T16:00:55.037237",
                "updated_at": "2022-09-14T16:00:55.037246",
                "short_description": "Release - Release of contact information"
            }
        ],
        "documents_data": [],
        "content_type_id": 93,
        "notes": [],
        "number_generators": [],
        "membership_effective_date_override": null,
        "purchased_products": [],
        "membership_first_issued_date": "2022-09-14",
        "age_as_of_date": "2022-12-31",
        "calculated_age_as_of_date": 12,
        "purchaser": {
            "id": 100275155,
            "first_name": "Vincent",
            "last_name": "Bang",
            "email": "marlitabang@yahoo.com"
        }
    }
*/
}
