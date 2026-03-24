<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\BgTasks;

use Psr\Log\LoggerInterface;
use RCS\WP\BgProcess\BgProcess;
use WSCL\Main\CcnBikes\CcnClient;

class CcnBikesBgProcess extends BgProcess
{
    private const ACTION_NAME = 'CcnBikesBgProcess';

    // Override prefix and action properties
    protected $prefix = 'wscl';
    protected $action = self::ACTION_NAME;

    public function __construct(
        CcnClient $ccnApi,
        LoggerInterface $logger
        )
    {
        parent::__construct(
            $logger,
            [
                CcnClient::class => $ccnApi
            ]
            );
    }
}
