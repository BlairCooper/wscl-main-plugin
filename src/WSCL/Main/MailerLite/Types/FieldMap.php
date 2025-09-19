<?php
declare(strict_types=1);
namespace WSCL\Main\MailerLite\Types;

use WSCL\Main\MailerLite\Entity\Field;

/**
 * Implements a mapping of field key to MailerLite Field.
 */
class FieldMap implements \Countable
{
    /** @var Field[] */
    private array $fieldMap = array();

    public function put(string $fieldKey, Field $field): void
    {
        $this->fieldMap[$fieldKey] = $field;
    }

    public function get(string $fieldKey): ?Field
    {
        return $this->fieldMap[$fieldKey] ?? null;
    }

    public function count(): int
    {
        return count($this->fieldMap);
    }

    public function containsKey(string $key): bool
    {
        return array_key_exists($key, $this->fieldMap);
    }

    public function remove(string $key): ?Field
    {
        $field = $this->get($key);
        unset($this->fieldMap[$key]);

        return $field;
    }

    public function isEmpty(): bool
    {
        return empty($this->fieldMap);
    }

    /**
     *
     * @return Field[]
     */
    public function values(): array
    {
        return array_values($this->fieldMap);
    }
}
