<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

class QuestionAnswer
{
    public Question $question;

    /** @var Answer[] */
    public array $answers;
}
