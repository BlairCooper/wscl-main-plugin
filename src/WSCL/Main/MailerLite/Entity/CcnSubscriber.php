<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\Entity;

class CcnSubscriber extends Subscriber
{
    private string $groupName;

//     /**
//      * Helper to keep default constructor.
//      *
//      * @param string $email
//      * @param string $name
//      *
//      * @return CcnSubscriber
//      */
//     public static function create(string $email, string $name, string $groupName = ''): self
//     {
//         $sub = new self();

//         $sub->setEmail($email);
//         $sub->setName($name);

//         $sub->groupName = $groupName;

//         return $sub;
//     }

    public function setGroupName(string $groupName): void
    {
        $this->groupName = $groupName;
    }

    public function getGroupName(): string
    {
        return $this->groupName;
    }
}
