<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Event;

class TagMergedEvent extends QuestionBankEvent
{
    public function __construct(
        private readonly string $sourceTagId,
        private readonly string $targetTagId
    ) {
    }

    public function getSourceTagId(): string
    {
        return $this->sourceTagId;
    }

    public function getTargetTagId(): string
    {
        return $this->targetTagId;
    }
}