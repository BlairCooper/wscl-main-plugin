<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\BgTasks;

enum SyncSubscribersStates {
    case FETCH_MEMBERSHIP_ORGS;
    case WAIT_FOR_FETCH_SUBSCRIBERS_TASKS;
    case FETCH_MEMBERSHIP_SUBSCRIBERS;
    case TASK_FAILED;
}
