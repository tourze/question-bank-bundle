<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Event;

use Tourze\QuestionBankBundle\Entity\Question;

class QuestionCreatedEvent extends QuestionBankEvent
{
    public function __construct(
        private readonly Question $question,
    ) {
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }
}
