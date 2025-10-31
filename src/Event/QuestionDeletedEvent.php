<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Event;

class QuestionDeletedEvent extends QuestionBankEvent
{
    public function __construct(
        private readonly string $questionId,
    ) {
    }

    public function getQuestionId(): string
    {
        return $this->questionId;
    }
}
