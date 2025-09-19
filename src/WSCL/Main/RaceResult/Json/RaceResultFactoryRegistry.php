<?php
declare(strict_types = 1);
namespace WSCL\Main\RaceResult\Json;

use JsonMapper\Handler\FactoryRegistry;
use RCS\Json\JsonDateTime;
use WSCL\Main\RaceResult\Entity\EventAttributes;

class RaceResultFactoryRegistry extends FactoryRegistry
{
    public static function withPhpClassesAdded(bool $includeNativeClasses = false): FactoryRegistry
    {
        if ($includeNativeClasses) {
            $factory = parent::withNativePhpClassesAdded();
        } else {
            $factory = new self();
        }

        $factory->addFactory(EventAttributes::class, EventAttributes::getClassFactory());

        JsonDateTime::addToFactory($factory);

        return $factory;
    }
}
