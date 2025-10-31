<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Service;

use Tourze\QuestionBankBundle\DTO\CategoryDTO;
use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Exception\CategoryNotFoundException;
use Tourze\QuestionBankBundle\Exception\ValidationException;

interface CategoryServiceInterface
{
    /**
     * @throws ValidationException
     */
    public function createCategory(CategoryDTO $dto): Category;

    /**
     * @throws CategoryNotFoundException
     * @throws ValidationException
     */
    public function updateCategory(string $id, CategoryDTO $dto): Category;

    /**
     * @throws CategoryNotFoundException
     */
    public function deleteCategory(string $id): void;

    /**
     * @throws CategoryNotFoundException
     */
    public function findCategory(string $id): Category;

    /**
     * @throws CategoryNotFoundException
     * @throws ValidationException
     */
    public function moveCategory(string $id, ?string $newParentId): void;

    /**
     * @return array<Category>
     */
    public function getCategoryTree(): array;

    /**
     * @return array<Category>
     */
    public function getCategoryPath(string $id): array;

    public function findCategoryByCode(string $code): ?Category;
}
