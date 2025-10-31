<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Exception;

class QuestionNotFoundException extends QuestionBankException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Question with ID "%s" not found', $id));
    }
}
