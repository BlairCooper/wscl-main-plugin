<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\BgTasks;

use Psr\Log\LoggerInterface;
use RCS\WP\BgProcess\BgProcessInterface;
use WSCL\Main\MailerLite\MailerLiteClient;
use WSCL\Main\MailerLite\Cron\MailerLiteSyncState;
use WSCL\Main\MailerLite\Entity\Subscriber;
use RCS\WP\BgProcess\BgTaskInterface;

class UpdateSubscriberTask implements BgTaskInterface
{
    protected string $syncStateId;
    protected Subscriber $subscriber;

    public function __construct(Subscriber $subscriber, string $syncStateId)
    {
        $this->syncStateId = $syncStateId;
        $this->subscriber = $subscriber;
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

        $syncState = MailerLiteSyncState::instance($this->syncStateId);

        $mlClient->updateSubscriberFields($this->subscriber);

        $syncState->markUpdateSubscriberComplete($this->subscriber->getId());
        $syncState->save();

        return true;
    }
}
