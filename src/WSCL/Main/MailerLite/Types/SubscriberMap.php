<?php
declare(strict_types=1);
namespace WSCL\Main\MailerLite\Types;

use WSCL\Main\MailerLite\Entity\Subscriber;

/**
 * Implements a mapping of email address to MailerLite Subscriber.
 */
class SubscriberMap implements \Countable
{
    /** @var array<string, Subscriber> Key is the email address*/
    private array $subscriberMap = array();

    public function put(string $emailAddress, Subscriber $subscriber): void
    {
        $this->subscriberMap[strtolower($emailAddress)] = $subscriber;
    }

    public function get(string $emailAddress): ?Subscriber
    {
        return $this->subscriberMap[strtolower($emailAddress)] ?? null;
    }

    public function count(): int
    {
        return count($this->subscriberMap);
    }

    public function containsKey(string $key): bool
    {
        return array_key_exists(strtolower($key), $this->subscriberMap);
    }

    public function remove(string $key): ?Subscriber
    {
        $key = strtolower($key);

        $subscriber = $this->get($key);
        unset($this->subscriberMap[$key]);

        return $subscriber;
    }

    /**
     *
     * @return Subscriber[]
     */
    public function values(): array
    {
        return array_values($this->subscriberMap);
    }
}
