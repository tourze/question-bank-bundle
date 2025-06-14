<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Exception;

class TagNotFoundException extends QuestionBankException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Tag with ID "%s" not found', $id));
    }

    public static function withSlug(string $slug): self
    {
        return new self(sprintf('Tag with slug "%s" not found', $slug));
    }
}