<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\BgTasks;

use Psr\Log\LoggerInterface;
use RCS\WP\BgProcess\BgProcessInterface;
use RCS\WP\BgProcess\BgTaskInterface;
use WSCL\Main\MailerLite\MailerLiteClient;

class AddSubscribersToGroupTask implements BgTaskInterface
{
    protected int $groupId;
    /** @var string[] */
    protected array $emailList;

    /**
     *
     * @param int $groupId
     * @param string[] $emailList
     */
    public function __construct(int $groupId, array $emailList)
    {
        $this->groupId = $groupId;
        $this->emailList = $emailList;
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

        $mlClient->assignSubscribersToGroup($this->groupId, $this->emailList);

        return true;
    }
}
