<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

use RCS\Json\JsonDateTime;
use RCS\Json\JsonEntity;


class Coupon extends JsonEntity implements \JsonSerializable
{
    public int $id;
    public ?string $shortDescription;
    public string $value;
    public string $code;
    public string $name;
    public string $modelName;
    public string $appLabel;
    public string $objectId;
    public int $useCount;
    public int $maxUseCount;
    public bool $hasCarryingBalance;
    public bool $isPercentage;
    /** @var string[] */
    public array $notApplicableForItems;
    public Currency $currency;
    public ?JsonDateTime $fromDate;  // "2024-07-30"
    public ?JsonDateTime $toDate;    // "2024-09-30"

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->shortDescription ?? '';
    }

    public function getValue(): int
    {
        return intVal($this->value);
    }

    public function setValue(int $value): void
    {
        $this->value = sprintf('%d.00', $value);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMaxUse(): int
    {
        return $this->maxUseCount;
    }

    public static function initCoupon(string $code, string $description, int $value): Coupon
    {
        $coupon = new Coupon();

        $coupon->code = $code;
        $coupon->shortDescription = $description;
        $coupon->value = intval($value) . '.00';
        $coupon->currency = Currency::initUSD();
        $coupon->maxUseCount = 1;
        $coupon->hasCarryingBalance = false;
        $coupon->isPercentage = false;
        $coupon->notApplicableForItems = [ 'PP'];
        $coupon->name = 'Washington Student Cycling League';
        $coupon->modelName = 'organization';
        $coupon->appLabel = 'event_app';
        $coupon->objectId = '6799';   //TODO: this should be passed in along with name and model

        $today = new JsonDateTime();
        $today->setTime(0, 0);

        $coupon->fromDate = clone $today;   // make a copy
        $coupon->toDate = $today->add(new \DateInterval('P3M')); // 3 months from now

        return $coupon;
    }

    public function jsonSerialize(): mixed
    {
        $data = [
        //            'id' => $this->id,
            'short_description' => $this->shortDescription,
            'value' => $this->value,
            'code' => $this->code,
            'max_use_count' => $this->maxUseCount,
            'has_carrying_balance' => $this->hasCarryingBalance,
            'currency' => $this->currency->id,
            'percentage' => $this->isPercentage,
        //            'name' => $this->name,      // Name associated with object_id/coupling_object
        //            'model_name' => $this->modelName,
        //            'app_label' => $this->appLabel,
            'object_id' => $this->objectId,
            'content_type' => 2,    // Financial Organization
            'coupling_type' => 2,   // Financial Organization
            'coupling_object' => $this->objectId,
        ];

        if (isset($this->fromDate)) {
            $data['from_date'] = $this->fromDate->format('Y-m-d');
        }

        if (isset($this->toDate)) {
            $data['to_date'] = $this->toDate->format('Y-m-d');
        }

        return $data;
    }


// From Update request
//     {
//         "short_description": "Blair Description",
//         "code": "BlairTest1",
//         "coupling_type": 2,
//         "content_type": 2,
//         "coupling_object": 6799,
//         "object_id": 6799,
//         "currency": 3,
//         "value": 20,
//         "has_carrying_balance": false,
//         "max_use_count": 1,
//         "not_applicable_for_items": [
//             "PP"
//         ],
//         "percentage": false,
//         "to_date": "2024-09-30",
//         "from_date": "2024-07-30",
//         "email_subject": "",
//         "email_body": ""
//     }

// From get request
//     {
//         "id": 48177,
//         "short_description": "Blair Description",
//         "currency": {
//              "id": 3,
//              "symbol": "$",
//              "currency": "USD",
//              "name": "USD $"
//         },
//         "value": "20.00",
//         "is_percentage": false,
//         "created_at": "2024-07-23T11:33:14.987384",
//         "updated_at": "2024-07-23T20:54:05.611473",
//         "code": "BlairTest1",
//         "max_use_count": 1,
//         "use_count": 0,
//         "name": "Washington Student Cycling League",
//         "model_name": "organization",
//         "app_label": "event_app",
//         "object_id": 6799,
//         "reg_org": null,
//         "to_date": null,
//         "from_date": null,
//         "promo": null,
//         "not_applicable_for_items": [
//             "PP"
//         ],
//         "has_carrying_balance": false
//     }
}
