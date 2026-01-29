<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\Cron;

use Psr\Log\LoggerInterface;
use RCS\WP\BgProcess\BgProcess;
use WSCL\Main\CcnBikes\CcnClient;
use WSCL\Main\MailerLite\MailerLiteClient;

class MailerLiteBgProcess extends BgProcess
{
    private const ACTION_NAME = 'MailerLiteBgProcess';

    // Override prefix and action properties
    protected $prefix = 'wscl';
    protected $action = self::ACTION_NAME;

    public function __construct(
        CcnClient $ccnApi,
        MailerLiteClient $mlApi,
        LoggerInterface $logger
        )
    {
        parent::__construct(
            $logger,
            [
                CcnClient::class => $ccnApi,
                MailerLiteClient::class => $mlApi
            ]
            );
    }
}
