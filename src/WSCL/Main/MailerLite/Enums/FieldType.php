<?php
declare(strict_types=1);
namespace WSCL\Main\MailerLite\Enums;

enum FieldType : string {
    case TEXT = 'TEXT';
    case NUMBER = 'NUMBER';
    case DATE = 'DATE';
}
