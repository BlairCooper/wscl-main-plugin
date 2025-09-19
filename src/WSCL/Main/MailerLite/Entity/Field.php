<?php
declare(strict_types=1);
namespace WSCL\Main\MailerLite\Entity;

use RCS\Json\JsonEntity;
use WSCL\Main\MailerLite\Enums\FieldType;

class Field extends JsonEntity
{
    public int $id;
    public string $title;
    public string $key;
    public FieldType $type;
    public ?\DateTime $dateCreated;
    public ?\DateTime $dateUpdated;

    public static function create(string $title, string $key, FieldType $type): Field
    {
        $obj = new Field();
        $obj->title = $title;
        $obj->key = $key;
        $obj->type = $type;

        return $obj;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getType(): FieldType
    {
        return $this->type;
    }
}
