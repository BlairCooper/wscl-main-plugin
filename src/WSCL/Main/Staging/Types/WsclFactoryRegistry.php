<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Types;

use JsonMapper\Handler\FactoryRegistry;
use RCS\Json\JsonDateTime;
use WSCL\Main\Staging\Models\NameMapEntry;

class WsclFactoryRegistry extends FactoryRegistry
{
    public static function withPhpClassesAdded(bool $includeNativeClasses = false): FactoryRegistry
    {
        if ($includeNativeClasses) {
            $factory = parent::withNativePhpClassesAdded();
        } else {
            $factory = new self();
        }

        JsonDateTime::addToFactory($factory);

        $factory->addFactory(NameMapEntry::class, NameMapEntry::getClassFactory());

        return $factory;
    }
}

