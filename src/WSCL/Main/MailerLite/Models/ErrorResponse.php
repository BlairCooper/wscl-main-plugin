<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite\Models;

use RCS\Json\JsonEntity;

class ErrorResponse extends JsonEntity
{
    public ErrorDetail $error;
}
