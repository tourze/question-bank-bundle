<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Exception;

class CategoryNotFoundException extends QuestionBankException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Category with ID "%s" not found', $id));
    }

    public static function withCode(string $code): self
    {
        return new self(sprintf('Category with code "%s" not found', $code));
    }
}