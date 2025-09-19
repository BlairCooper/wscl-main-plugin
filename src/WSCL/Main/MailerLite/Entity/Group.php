<?php
declare(strict_types=1);
namespace WSCL\Main\MailerLite\Entity;

use RCS\Json\JsonEntity;

class Group extends JsonEntity
{
    public int $id;
    public string $name;
    public int $total;
    public int $active;
    public int $unsubscribed;
    public int $bounced;
    public int $unconfirmed;
    public int $junk;
    public int $sent;
    public int $opened;
    public int $clicked;
    public int $parentId;

    public \DateTime $dateCreated;
    public \DateTime $dateUpdated;

    public static function create(string $name): Group
    {
        $obj = new Group();
        $obj->name = $name;
        $obj->id = -1;
        $obj->dateCreated = new \DateTime();
        $obj->dateUpdated = $obj->dateCreated;

        return $obj;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
