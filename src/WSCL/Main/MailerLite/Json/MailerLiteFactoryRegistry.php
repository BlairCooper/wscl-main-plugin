<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\Json;

use JsonMapper\Handler\FactoryRegistry;
use WSCL\Main\MailerLite\Enums\FieldType;
use WSCL\Main\MailerLite\Enums\SubscriberType;

class MailerLiteFactoryRegistry extends FactoryRegistry
{
    public static function withPhpClassesAdded(bool $includeNativeClasses = false): FactoryRegistry
    {
        if ($includeNativeClasses) {
            $factory = parent::withNativePhpClassesAdded();
        } else {
            $factory = new self();
        }

        $factory->addFactory(FieldType::class, static function (string $value) {
            return FieldType::from($value);
        });

        $factory->addFactory(SubscriberType::class, static function (string $value) {
            return SubscriberType::from($value);
        });

        return $factory;
    }
}
