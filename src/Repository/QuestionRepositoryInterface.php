<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Repository;

use Tourze\QuestionBankBundle\DTO\SearchCriteria;
use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\ValueObject\PaginatedResult;

interface QuestionRepositoryInterface
{
    public function save(Question $question): void;
    
    public function remove(Question $question): void;
    
    public function find(string $id): ?Question;
    
    /**
     * @return array<Question>
     */
    public function findByCategory(Category $category): array;
    
    /**
     * @param array<string> $tagIds
     * @return array<Question>
     */
    public function findByTags(array $tagIds): array;
    
    public function search(SearchCriteria $criteria): PaginatedResult;
    
    /**
     * @return array<string, int>
     */
    public function countByType(): array;
    
    /**
     * @param array<string> $ids
     * @return array<Question>
     */
    public function findByIds(array $ids): array;
    
    /**
     * @return array<Question>
     */
    public function findRandom(int $limit, ?SearchCriteria $criteria = null): array;
}