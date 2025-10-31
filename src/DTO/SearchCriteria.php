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

    public function setKeyword(?string $keyword): void
    {
        $this->keyword = $keyword;
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
    public function setTypes(array $types): void
    {
        $this->types = $types;
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
    public function setStatuses(array $statuses): void
    {
        $this->statuses = $statuses;
    }

    public function getMinDifficulty(): ?int
    {
        return $this->minDifficulty;
    }

    public function setMinDifficulty(?int $minDifficulty): void
    {
        $this->minDifficulty = $minDifficulty;
    }

    public function getMaxDifficulty(): ?int
    {
        return $this->maxDifficulty;
    }

    public function setMaxDifficulty(?int $maxDifficulty): void
    {
        $this->maxDifficulty = $maxDifficulty;
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
    public function setCategoryIds(array $categoryIds): void
    {
        $this->categoryIds = $categoryIds;
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
    public function setTagIds(array $tagIds): void
    {
        $this->tagIds = $tagIds;
    }

    public function requireAllTags(): bool
    {
        return $this->requireAllTags;
    }

    public function setRequireAllTags(bool $requireAllTags): void
    {
        $this->requireAllTags = $requireAllTags;
    }

    public function includeArchived(): bool
    {
        return $this->includeArchived;
    }

    public function setIncludeArchived(bool $includeArchived): void
    {
        $this->includeArchived = $includeArchived;
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
    public function setOrderBy(array $orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = max(1, $page);
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = max(1, min(100, $limit));
    }
}
