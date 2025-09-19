<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\BgTasks;

use Psr\Log\LoggerInterface;
use RCS\WP\BgProcess\BgProcessInterface;
use WSCL\Main\MailerLite\MailerLiteClient;
use WSCL\Main\MailerLite\Cron\MailerLiteSyncState;
use WSCL\Main\MailerLite\Entity\Subscriber;
use RCS\WP\BgProcess\BgTaskInterface;

class AddSubscriberTask implements BgTaskInterface
{
    protected string $syncId;
    protected Subscriber $subscriber;

    public function __construct(Subscriber $subscriber, string $syncId)
    {
        $this->subscriber = $subscriber;
        $this->syncId = $syncId;
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\BgProcess\BgTaskInterface::run()
     */
    public function run(BgProcessInterface $bgProcess, LoggerInterface $logger, array $params) : bool
    {
        /** @var MailerLiteClient */
        $mlClient = $params[MailerLiteClient::class];

        $mlClient->addSubscriber($this->subscriber);

        $syncState = MailerLiteSyncState::instance($this->syncId);

        $syncState->markAddSubscriberComplete($this->subscriber->hashcode());
        $syncState->save();

        return true;
    }
}
