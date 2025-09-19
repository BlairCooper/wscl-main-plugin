<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\BgTasks;

enum FetchSubsStates {
    case GET_MEMBERSHIP_CONFIG;
    case GET_MEMBERSHIP_REPORT_DATA;
    case WAIT_FOR_REPORT;
    case DOWNLOAD_REPORT;
    case PROCESS_REPORT;
    case SUBTASK_COMPLETE;
    case SUBTASK_FAILED;
}
