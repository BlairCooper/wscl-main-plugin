<?php
declare(strict_types=1);
namespace WSCL\Main\MailerLite\Types;

/**
 * Implements a mapping of group name to list of emails for that group.
 *
 */
class EmailsMap
{
    /** @var array<string, string[]> */
    private array $emailsMap = array();

    public function put(string $groupName, ?string $email): void
    {
        if (!is_null($email)) {
            if (!isset($this->emailsMap[$groupName])) {
                $this->emailsMap[$groupName] = [];
            }

            $list = &$this->emailsMap[$groupName];

            $email = strtolower(trim($email));

            if (!in_array($email, $list)) {
                array_push($list, $email);
            }
        }
    }

    /**
     *
     * @param string $groupName
     *
     * @return string[]|NULL
     */
    public function get(string $groupName): ?array
    {
        return $this->emailsMap[$groupName] ?? null;
    }

    public function remove(string $groupName, string $email): ?string
    {
        $foundEmail = null;

        if (isset($this->emailsMap[$groupName])) {
            $list = &$this->emailsMap[$groupName];

            if (in_array($email, $list))
            {
                $list = array_diff($list, [$email]);
                $foundEmail = $email;
            }
        }

        return $foundEmail;
    }

    public function isEmpty(): bool
    {
        return empty($this->emailsMap);
    }

    /**
     *
     * @return string[]
     */
    public function values(string $groupName): array
    {
        return array_values($this->emailsMap[$groupName]);
    }

    /**
     *
     * @return string[]
     */
    public function getGroupNames(): array
    {
        return array_keys($this->emailsMap);
    }
}
