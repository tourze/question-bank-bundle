<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Event;

class CategoryReorganizedEvent extends QuestionBankEvent
{
    /**
     * @param array<string, string> $affectedChildren
     */
    public function __construct(
        private readonly string $categoryId,
        private readonly ?string $parentId,
        private readonly string $oldPath,
        private readonly string $newPath,
        private readonly array $affectedChildren,
    ) {
    }

    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function getOldPath(): string
    {
        return $this->oldPath;
    }

    public function getNewPath(): string
    {
        return $this->newPath;
    }

    /**
     * @return array<string, string>
     */
    public function getAffectedChildren(): array
    {
        return $this->affectedChildren;
    }
}
