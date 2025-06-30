<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Service;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\QuestionBankBundle\DTO\CategoryDTO;
use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Event\CategoryReorganizedEvent;
use Tourze\QuestionBankBundle\Exception\CategoryNotFoundException;
use Tourze\QuestionBankBundle\Exception\ValidationException;
use Tourze\QuestionBankBundle\Repository\CategoryRepository;

class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly ValidatorInterface $validator,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function createCategory(CategoryDTO $dto): Category
    {
        $this->validateCategoryDTO($dto);
        
        // 检查 code 唯一性
        if ($this->categoryRepository->findByCode($dto->code) !== null) {
            throw new ValidationException(
                $this->createViolationList('code', sprintf('Category with code "%s" already exists', $dto->code))
            );
        }
        
        $category = new Category($dto->name, $dto->code);
        
        if ($dto->description !== null) {
            $category->setDescription($dto->description);
        }
        
        $category->setSortOrder($dto->sortOrder);
        $category->setValid($dto->isActive);
        
        // 设置父分类
        if ($dto->parentId !== null) {
            $parent = $this->findCategory($dto->parentId);
            $category->setParent($parent);
        }
        
        $this->categoryRepository->save($category);
        
        return $category;
    }

    public function updateCategory(string $id, CategoryDTO $dto): Category
    {
        $category = $this->findCategory($id);
        
        $this->validateCategoryDTO($dto);
        
        // 检查 code 唯一性（排除自身）
        $existingCategory = $this->categoryRepository->findByCode($dto->code);
        if ($existingCategory !== null && (string) $existingCategory->getId() !== $id) {
            throw new ValidationException(
                $this->createViolationList('code', sprintf('Category with code "%s" already exists', $dto->code))
            );
        }
        
        $category->setName($dto->name)
            ->setCode($dto->code)
            ->setSortOrder($dto->sortOrder)
            ->setValid($dto->isActive);
        
        if ($dto->description !== null) {
            $category->setDescription($dto->description);
        }
        
        // 更新父分类
        if ($dto->parentId !== null) {
            $parent = $this->findCategory($dto->parentId);
            $category->setParent($parent);
        } else {
            $category->setParent(null);
        }
        
        $this->categoryRepository->save($category);
        
        return $category;
    }

    public function deleteCategory(string $id): void
    {
        $category = $this->findCategory($id);
        
        // 检查是否有子分类
        if (!$category->getChildren()->isEmpty()) {
            throw new ValidationException(
                $this->createViolationList('children', 'Cannot delete category with children')
            );
        }
        
        $this->categoryRepository->remove($category);
    }

    public function findCategory(string $id): Category
    {
        $category = $this->categoryRepository->find($id);
        
        if ($category === null) {
            throw CategoryNotFoundException::withId($id);
        }
        
        return $category;
    }

    public function moveCategory(string $id, ?string $newParentId): void
    {
        $category = $this->findCategory($id);
        
        $newParent = null;
        if ($newParentId !== null) {
            $newParent = $this->findCategory($newParentId);
        }
        
        $category->setParent($newParent);
        
        $this->categoryRepository->save($category);
        
        $this->eventDispatcher->dispatch(new CategoryReorganizedEvent(
            $category->getId(),
            $category->getParent()?->getId(),
            '', // old path - would need to be tracked
            $category->getPath(),
            [] // affected children - would need to be calculated
        ));
    }

    public function getCategoryTree(): array
    {
        return $this->categoryRepository->getCategoryTree();
    }

    public function getCategoryPath(string $id): array
    {
        $category = $this->findCategory($id);
        
        return $category->getFullPath();
    }

    public function findCategoryByCode(string $code): ?Category
    {
        return $this->categoryRepository->findByCode($code);
    }

    private function validateCategoryDTO(CategoryDTO $dto): void
    {
        $violations = $this->validator->validate($dto);
        
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }
    }

    private function createViolationList(string $property, string $message): \Symfony\Component\Validator\ConstraintViolationListInterface
    {
        $violationList = new \Symfony\Component\Validator\ConstraintViolationList();
        $violation = new \Symfony\Component\Validator\ConstraintViolation(
            $message,
            null,
            [],
            null,
            $property,
            null
        );
        $violationList->add($violation);
        
        return $violationList;
    }
}