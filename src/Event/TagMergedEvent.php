<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Event;

use Symfony\Component\Uid\Uuid;

class TagMergedEvent extends QuestionBankEvent
{
    /**
     * @param array<string> $affectedQuestions
     */
    public function __construct(
        private readonly Uuid $sourceTagId,
        private readonly Uuid $targetTagId,
        private readonly array $affectedQuestions,
    ) {
    }

    public function getSourceTagId(): Uuid
    {
        return $this->sourceTagId;
    }

    public function getTargetTagId(): Uuid
    {
        return $this->targetTagId;
    }

    /**
     * @return array<string>
     */
    public function getAffectedQuestions(): array
    {
        return $this->affectedQuestions;
    }
}
