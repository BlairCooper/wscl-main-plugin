<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\BgTasks;

enum UpdateMailerLiteStates {
    case DETERMINE_MISSING_GROUPS;
    case FETCH_GROUP_SUBSCRIBERS;
    case FETCH_SUBSCRIBERS;
    case ADD_NEW_SUBSCRIBERS;
    case WAIT_FOR_SUBSCRIBERS_TASKS;
    case ADD_TO_GROUPS;
    case TASK_COMPLETE;
    case TASK_FAILED;
}
