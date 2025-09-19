<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\Cron;

use Psr\Log\LoggerInterface;
use WSCL\Main\CcnBikes\CcnClient;
use WSCL\Main\MailerLite\MailerLiteClient;
use WSCL\Main\MailerLite\BgTasks\MailerLiteBgTask;
use WP_Background_Process;
use RCS\WP\BgProcess\BgProcessInterface;
use RCS\WP\BgProcess\BgTaskInterface;


class MailerLiteBgProcess extends WP_Background_Process implements BgProcessInterface
{
    private const ACTION_NAME = 'MailerLiteBackgroundProcess';

    // Override prefix and action properties
    protected $prefix = 'wscl';
    protected $action = self::ACTION_NAME;

    /** @var LoggerInterface */
    protected $logger;

    /** @var CcnClient */
    private $ccnApi;

    /** @var MailerLiteClient */
    private $mlApi;

    /**
     * {@inheritDoc}
     * @see \WP_Background_Process::unlock_process()
     */
    protected function unlock_process()
    {
        // if the tasks generated any new tasks, save them
        $this->save();

        return parent::unlock_process();
    }

    public function __construct(CcnClient $ccnApi, MailerLiteClient $mlApi, LoggerInterface $logger)
    {
        parent::__construct();
        $this->logger = $logger;

        $this->ccnApi = $ccnApi;
        $this->mlApi = $mlApi;
    }

    protected function task($item)
    {
        $result = $item;

        if ($item instanceof MailerLiteBgTask) {
            if ($item->run($this->ccnApi, $this->mlApi, $this)) {
                $result = false;
            }
        } else {
            $result = false;
        }

        return $result;
    }

    public function pushToQueue(BgTaskInterface $task): self
    {
        return $this->push_to_queue($task);
    }
}
