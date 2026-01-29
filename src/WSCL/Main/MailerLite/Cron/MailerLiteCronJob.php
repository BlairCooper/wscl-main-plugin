<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\Cron;

use Psr\Log\LoggerInterface;
use RCS\WP\CronJob;
use WSCL\Main\MailerLite\BgTasks\SyncSubscribersTask;

class MailerLiteCronJob extends CronJob
{
    public function __construct(
        private MailerLiteBgProcess $bgProcess,
        LoggerInterface $logger
        )
    {
        parent::__construct($logger);

        $this->initializeCronJob('WsclMailerLiteDailyCron', 'daily');
    }

    /**
     *
     */
    public function runJob(): void
    {
        $this->logger->debug('Running MailerLiteCronJob');

        if (!self::isJobActive($this->bgProcess)) {
            $this->bgProcess->pushToQueue(new SyncSubscribersTask())->save();
        }

        $this->bgProcess->dispatch();
    }
}
