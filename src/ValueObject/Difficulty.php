<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\ValueObject;

use Tourze\QuestionBankBundle\Exception\DifficultyValidationException;

final class Difficulty
{
    private const MIN_LEVEL = 1;
    private const MAX_LEVEL = 5;
    
    private const LABELS = [
        1 => '简单',
        2 => '较易',
        3 => '中等',
        4 => '较难',
        5 => '困难',
    ];

    private int $level;

    public function __construct(int $level)
    {
        if ($level < self::MIN_LEVEL || $level > self::MAX_LEVEL) {
            throw new DifficultyValidationException(
                sprintf('Difficulty level must be between %d and %d, %d given', 
                    self::MIN_LEVEL, 
                    self::MAX_LEVEL, 
                    $level
                )
            );
        }

        $this->level = $level;
    }

    public static function easy(): self
    {
        return new self(1);
    }

    public static function medium(): self
    {
        return new self(3);
    }

    public static function hard(): self
    {
        return new self(5);
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getLabel(): string
    {
        return self::LABELS[$this->level];
    }

    public function equals(self $other): bool
    {
        return $this->level === $other->level;
    }

    public function isHarderThan(self $other): bool
    {
        return $this->level > $other->level;
    }

    public function isEasierThan(self $other): bool
    {
        return $this->level < $other->level;
    }

    public function __toString(): string
    {
        return (string) $this->level;
    }
}