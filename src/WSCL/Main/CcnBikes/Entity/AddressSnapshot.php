<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

class AddressSnapshot {
    public int $id;
    public string $street1;
    public string $street2;
    public Province $province;
    public string $city;
    public string $postalCode;
    public string $phoneNumber;

    public function getAddress(): string
    {
        return $this->street1;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getState(): string
    {
        return $this->province->getAbbreviation();
    }

    public function getZip(): string
    {
        return $this->postalCode;
    }

    public function getPhone(): string
    {
        return $this->phoneNumber;
    }

/*
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
                "is_province_other_required": true
            }
        },
        "province_other": null,
        "city": "Mercer island",
        "postal_code": "98040",
        "phone_number": "2064997872",
        "address": 101021349
    }
*/
}
