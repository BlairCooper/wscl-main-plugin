<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

class SurveyApplications
{
    public int $id;
    public string $shortDescription;

    /** @var QuestionAnswer[] */
    public array $questionsAnswers;

    public function getDesc(): string
    {
        return $this->shortDescription;
    }

    /**
     * @return QuestionAnswer[]
     */
    public function getQuestions(): array
    {
        return $this->questionsAnswers;
    }
}
