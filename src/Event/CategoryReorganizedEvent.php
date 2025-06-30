<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Event;

use Symfony\Component\Uid\Uuid;

class CategoryReorganizedEvent extends QuestionBankEvent
{
    public function __construct(
        private readonly Uuid $categoryId,
        private readonly ?Uuid $parentId,
        private readonly string $oldPath,
        private readonly string $newPath,
        private readonly array $affectedChildren
    ) {
    }

    public function getCategoryId(): Uuid
    {
        return $this->categoryId;
    }
    
    public function getParentId(): ?Uuid
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
    
    public function getAffectedChildren(): array
    {
        return $this->affectedChildren;
    }
}