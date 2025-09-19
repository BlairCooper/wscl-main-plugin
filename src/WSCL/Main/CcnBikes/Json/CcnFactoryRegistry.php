<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Json;

use JsonMapper\Handler\FactoryRegistry;
use RCS\Json\JsonDateTime;
use WSCL\Main\CcnBikes\Enums\IdentityAttributeType;
use WSCL\Main\CcnBikes\Enums\ListingStatusEnum;
use WSCL\Main\CcnBikes\Enums\MembershipReportStatus;
use WSCL\Main\CcnBikes\Enums\MembershipStatusEnum;
use WSCL\Main\CcnBikes\Enums\ReportStateEnum;

class CcnFactoryRegistry extends FactoryRegistry
{
    public static function withPhpClassesAdded(bool $includeNativeClasses = false): FactoryRegistry
    {
        /** @var ?FactoryRegistry */
        $factory = null;

        if ($includeNativeClasses) {
            $factory = parent::withNativePhpClassesAdded();
        } else {
            $factory = new self();
        }

        $factory->addFactory(IdentityAttributeType::class, static function (string $value) {
            return IdentityAttributeType::from($value);
        });

        $factory->addFactory(MembershipStatusEnum::class, static function (string $value) {
            return MembershipStatusEnum::from($value);
        });

        $factory->addFactory(MembershipReportStatus::class, static function (string $value) {
            return MembershipReportStatus::from($value);
        });

        $factory->addFactory(ReportStateEnum::class, static function (string $value) {
            return ReportStateEnum::from($value);
        });

        $factory->addFactory(ListingStatusEnum::class, static function (string $value) {
            return ListingStatusEnum::from($value);
        });

        JsonDateTime::addToFactory($factory);

        return $factory;
    }
}
