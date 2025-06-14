<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\DTO;

use Tourze\QuestionBankBundle\Enum\QuestionStatus;
use Tourze\QuestionBankBundle\Enum\QuestionType;

class SearchCriteria
{
    private ?string $keyword = null;
    
    /** @var array<QuestionType> */
    private array $types = [];
    
    /** @var array<QuestionStatus> */
    private array $statuses = [];
    
    private ?int $minDifficulty = null;
    private ?int $maxDifficulty = null;
    
    /** @var array<string> */
    private array $categoryIds = [];
    
    /** @var array<string> */
    private array $tagIds = [];
    
    private bool $requireAllTags = false;
    private bool $includeArchived = false;
    
    /** @var array<string, string> */
    private array $orderBy = ['createTime' => 'DESC'];
    
    private int $page = 1;
    private int $limit = 20;

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function setKeyword(?string $keyword): self
    {
        $this->keyword = $keyword;
        return $this;
    }

    /**
     * @return array<QuestionType>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param array<QuestionType> $types
     */
    public function setTypes(array $types): self
    {
        $this->types = $types;
        return $this;
    }

    /**
     * @return array<QuestionStatus>
     */
    public function getStatuses(): array
    {
        return $this->statuses;
    }

    /**
     * @param array<QuestionStatus> $statuses
     */
    public function setStatuses(array $statuses): self
    {
        $this->statuses = $statuses;
        return $this;
    }

    public function getMinDifficulty(): ?int
    {
        return $this->minDifficulty;
    }

    public function setMinDifficulty(?int $minDifficulty): self
    {
        $this->minDifficulty = $minDifficulty;
        return $this;
    }

    public function getMaxDifficulty(): ?int
    {
        return $this->maxDifficulty;
    }

    public function setMaxDifficulty(?int $maxDifficulty): self
    {
        $this->maxDifficulty = $maxDifficulty;
        return $this;
    }

    /**
     * @return array<string>
     */
    public function getCategoryIds(): array
    {
        return $this->categoryIds;
    }

    /**
     * @param array<string> $categoryIds
     */
    public function setCategoryIds(array $categoryIds): self
    {
        $this->categoryIds = $categoryIds;
        return $this;
    }

    /**
     * @return array<string>
     */
    public function getTagIds(): array
    {
        return $this->tagIds;
    }

    /**
     * @param array<string> $tagIds
     */
    public function setTagIds(array $tagIds): self
    {
        $this->tagIds = $tagIds;
        return $this;
    }

    public function requireAllTags(): bool
    {
        return $this->requireAllTags;
    }

    public function setRequireAllTags(bool $requireAllTags): self
    {
        $this->requireAllTags = $requireAllTags;
        return $this;
    }

    public function includeArchived(): bool
    {
        return $this->includeArchived;
    }

    public function setIncludeArchived(bool $includeArchived): self
    {
        $this->includeArchived = $includeArchived;
        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    /**
     * @param array<string, string> $orderBy
     */
    public function setOrderBy(array $orderBy): self
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): self
    {
        $this->page = max(1, $page);
        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = max(1, min(100, $limit));
        return $this;
    }
}