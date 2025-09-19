<?php
declare(strict_types=1);
namespace WSCL\Main\MailerLite\Entity;

use WSCL\Main\MailerLite\Enums\FieldType;

class SubscriberField
{
    public string $key;
    public ?string $value;
    public FieldType $type;

    public function __construct(string $key, FieldType $type, ?string $value)
    {
        $this->key = $key;
        $this->type = $type;
        $this->value = $value ?? null;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getType(): FieldType
    {
        return $this->type;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return sprintf('%s : %s', $this->key, $this->value);
    }
}
