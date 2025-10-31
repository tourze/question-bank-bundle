<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Enum;

use Tourze\EnumExtra\BadgeInterface;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum QuestionStatus: string implements Itemable, Labelable, Selectable, BadgeInterface
{
    use ItemTrait;
    use SelectTrait;

    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => '草稿',
            self::PUBLISHED => '已发布',
            self::ARCHIVED => '已归档',
        };
    }

    public function isEditable(): bool
    {
        return self::DRAFT === $this;
    }

    public function isUsable(): bool
    {
        return self::PUBLISHED === $this;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DRAFT => 'warning',
            self::PUBLISHED => 'success',
            self::ARCHIVED => 'secondary',
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return match ($this) {
            self::DRAFT => self::PUBLISHED === $status,
            self::PUBLISHED => self::ARCHIVED === $status,
            self::ARCHIVED => false,
        };
    }

    /**
     * @return self[]
     */
    public function getAvailableTransitions(): array
    {
        return match ($this) {
            self::DRAFT => [self::PUBLISHED],
            self::PUBLISHED => [self::ARCHIVED],
            self::ARCHIVED => [],
        };
    }

    /**
     * @return self[]
     */
    public static function getAllStatuses(): array
    {
        return self::cases();
    }

    public static function fromString(string $value): ?self
    {
        return self::tryFrom($value);
    }

    public static function isValidTransition(self $from, self $to): bool
    {
        return $from->canTransitionTo($to);
    }

    public function getBadge(): string
    {
        return $this->getColor();
    }
}
