<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Service;

use Symfony\Component\Uid\Uuid;
use Tourze\QuestionBankBundle\DTO\CategoryDTO;
use Tourze\QuestionBankBundle\Exception\CategoryNotFoundException;
use Tourze\QuestionBankBundle\Exception\ValidationException;
use Tourze\QuestionBankBundle\Service\CategoryService;
use Tourze\QuestionBankBundle\Tests\BaseIntegrationTestCase;

class CategoryServiceIntegrationTest extends BaseIntegrationTestCase
{
    private CategoryService $categoryService;

    public function test_createCategory_withValidData_createsCategory(): void
    {
        // Arrange
        $dto = new CategoryDTO();
        $dto->name = 'Programming';
        $dto->code = 'programming';
        $dto->description = 'Programming questions';
        $dto->sortOrder = 10;
        $dto->isActive = true;

        // Act
        $category = $this->categoryService->createCategory($dto);

        // Assert
        $this->assertNotNull($category->getId());
        $this->assertEquals('Programming', $category->getName());
        $this->assertEquals('programming', $category->getCode());
        $this->assertEquals('Programming questions', $category->getDescription());
        $this->assertEquals(10, $category->getSortOrder());
        $this->assertTrue($category->isValid());
    }

    public function test_createCategory_withParent_setsParentCorrectly(): void
    {
        // Arrange
        $parentDto = new CategoryDTO();
        $parentDto->name = 'Technology';
        $parentDto->code = 'tech';
        $parentDto->sortOrder = 5;
        $parentDto->isActive = true;

        $parent = $this->categoryService->createCategory($parentDto);

        $childDto = new CategoryDTO();
        $childDto->name = 'Programming';
        $childDto->code = 'programming';
        $childDto->sortOrder = 10;
        $childDto->isActive = true;
        $childDto->parentId = (string) $parent->getId();

        // Act
        $child = $this->categoryService->createCategory($childDto);

        // Assert
        $this->assertNotNull($child->getParent());
        $this->assertEquals($parent->getId(), $child->getParent()->getId());
        $this->assertEquals('Technology', $child->getParent()->getName());
    }

    public function test_createCategory_withDuplicateCode_throwsValidationException(): void
    {
        // Arrange
        $dto1 = new CategoryDTO();
        $dto1->name = 'Programming';
        $dto1->code = 'programming';
        $dto1->sortOrder = 10;
        $dto1->isActive = true;

        $dto2 = new CategoryDTO();
        $dto2->name = 'Programming II';
        $dto2->code = 'programming'; // 重复的 code
        $dto2->sortOrder = 20;
        $dto2->isActive = true;

        $this->categoryService->createCategory($dto1);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Category with code "programming" already exists');
        $this->categoryService->createCategory($dto2);
    }

    public function test_updateCategory_withValidData_updatesCategory(): void
    {
        // Arrange
        $dto = new CategoryDTO();
        $dto->name = 'Programming';
        $dto->code = 'programming';
        $dto->sortOrder = 10;
        $dto->isActive = true;

        $category = $this->categoryService->createCategory($dto);

        $updateDto = new CategoryDTO();
        $updateDto->name = 'Software Development';
        $updateDto->code = 'software_dev';
        $updateDto->description = 'Updated description';
        $updateDto->sortOrder = 15;
        $updateDto->isActive = false;

        // Act
        $updatedCategory = $this->categoryService->updateCategory((string) $category->getId(), $updateDto);

        // Assert
        $this->assertEquals('Software Development', $updatedCategory->getName());
        $this->assertEquals('software_dev', $updatedCategory->getCode());
        $this->assertEquals('Updated description', $updatedCategory->getDescription());
        $this->assertEquals(15, $updatedCategory->getSortOrder());
        $this->assertFalse($updatedCategory->isValid());
    }

    public function test_updateCategory_withDuplicateCode_throwsValidationException(): void
    {
        // Arrange
        $dto1 = new CategoryDTO();
        $dto1->name = 'Programming';
        $dto1->code = 'programming';
        $dto1->sortOrder = 10;
        $dto1->isActive = true;

        $dto2 = new CategoryDTO();
        $dto2->name = 'Database';
        $dto2->code = 'database';
        $dto2->sortOrder = 20;
        $dto2->isActive = true;

        $category1 = $this->categoryService->createCategory($dto1);
        $category2 = $this->categoryService->createCategory($dto2);

        $updateDto = new CategoryDTO();
        $updateDto->name = 'Database Updated';
        $updateDto->code = 'programming'; // 尝试使用已存在的 code
        $updateDto->sortOrder = 25;
        $updateDto->isActive = true;

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Category with code "programming" already exists');
        $this->categoryService->updateCategory((string) $category2->getId(), $updateDto);
    }

    public function test_deleteCategory_withValidCategory_deletesCategory(): void
    {
        // Arrange
        $dto = new CategoryDTO();
        $dto->name = 'Programming';
        $dto->code = 'programming';
        $dto->sortOrder = 10;
        $dto->isActive = true;

        $category = $this->categoryService->createCategory($dto);
        $id = $category->getId();

        // Act
        $this->categoryService->deleteCategory((string) $id);

        // Assert
        $this->expectException(CategoryNotFoundException::class);
        $this->categoryService->findCategory((string) $id);
    }

    public function test_deleteCategory_withChildren_throwsValidationException(): void
    {
        // Arrange
        $parentDto = new CategoryDTO();
        $parentDto->name = 'Technology';
        $parentDto->code = 'tech';
        $parentDto->sortOrder = 5;
        $parentDto->isActive = true;

        $parent = $this->categoryService->createCategory($parentDto);

        $childDto = new CategoryDTO();
        $childDto->name = 'Programming';
        $childDto->code = 'programming';
        $childDto->sortOrder = 10;
        $childDto->isActive = true;
        $childDto->parentId = (string) $parent->getId();

        $this->categoryService->createCategory($childDto);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cannot delete category with children');
        $this->categoryService->deleteCategory((string) $parent->getId());
    }

    public function test_findCategory_withValidId_returnsCategory(): void
    {
        // Arrange
        $dto = new CategoryDTO();
        $dto->name = 'Programming';
        $dto->code = 'programming';
        $dto->sortOrder = 10;
        $dto->isActive = true;

        $created = $this->categoryService->createCategory($dto);

        // Act
        $found = $this->categoryService->findCategory((string) $created->getId());

        // Assert
        $this->assertEquals($created->getId(), $found->getId());
        $this->assertEquals('Programming', $found->getName());
    }

    public function test_findCategory_withInvalidId_throwsNotFoundException(): void
    {
        // Arrange - 使用有效的 UUID 格式但不存在的 ID
        $nonExistentId = Uuid::v7();

        // Act & Assert
        $this->expectException(CategoryNotFoundException::class);
        $this->categoryService->findCategory((string) $nonExistentId);
    }

    public function test_moveCategory_toNewParent_updatesParent(): void
    {
        // Arrange
        $parentDto = new CategoryDTO();
        $parentDto->name = 'Technology';
        $parentDto->code = 'tech';
        $parentDto->sortOrder = 5;
        $parentDto->isActive = true;

        $newParentDto = new CategoryDTO();
        $newParentDto->name = 'Science';
        $newParentDto->code = 'science';
        $newParentDto->sortOrder = 3;
        $newParentDto->isActive = true;

        $childDto = new CategoryDTO();
        $childDto->name = 'Programming';
        $childDto->code = 'programming';
        $childDto->sortOrder = 10;
        $childDto->isActive = true;

        $parent = $this->categoryService->createCategory($parentDto);
        $newParent = $this->categoryService->createCategory($newParentDto);
        $child = $this->categoryService->createCategory($childDto);

        $child->setParent($parent);
        $this->entityManager->persist($child);
        $this->entityManager->flush();

        // Act
        $this->categoryService->moveCategory((string) $child->getId(), (string) $newParent->getId());

        // Assert
        $this->entityManager->refresh($child);
        $this->assertNotNull($child->getParent());
        $this->assertEquals($newParent->getId(), $child->getParent()->getId());
    }

    public function test_moveCategory_toRoot_removesParent(): void
    {
        // Arrange
        $parentDto = new CategoryDTO();
        $parentDto->name = 'Technology';
        $parentDto->code = 'tech';
        $parentDto->sortOrder = 5;
        $parentDto->isActive = true;

        $childDto = new CategoryDTO();
        $childDto->name = 'Programming';
        $childDto->code = 'programming';
        $childDto->sortOrder = 10;
        $childDto->isActive = true;

        $parent = $this->categoryService->createCategory($parentDto);
        $child = $this->categoryService->createCategory($childDto);

        $child->setParent($parent);
        $this->entityManager->persist($child);
        $this->entityManager->flush();

        // Act
        $this->categoryService->moveCategory((string) $child->getId(), null);

        // Assert
        $this->entityManager->refresh($child);
        $this->assertNull($child->getParent());
    }

    public function test_getCategoryTree_returnsHierarchicalStructure(): void
    {
        // Arrange
        $parentDto = new CategoryDTO();
        $parentDto->name = 'Technology';
        $parentDto->code = 'tech';
        $parentDto->sortOrder = 5;
        $parentDto->isActive = true;

        $parent = $this->categoryService->createCategory($parentDto);

        $childDto = new CategoryDTO();
        $childDto->name = 'Programming';
        $childDto->code = 'programming';
        $childDto->sortOrder = 10;
        $childDto->isActive = true;
        $childDto->parentId = (string) $parent->getId();

        $this->categoryService->createCategory($childDto);

        // Act
        $tree = $this->categoryService->getCategoryTree();

        // Assert
        $this->assertNotEmpty($tree);
    }

    public function test_findCategoryByCode_withExistingCode_returnsCategory(): void
    {
        // Arrange
        $dto = new CategoryDTO();
        $dto->name = 'Programming';
        $dto->code = 'programming';
        $dto->sortOrder = 10;
        $dto->isActive = true;

        $created = $this->categoryService->createCategory($dto);

        // Act
        $found = $this->categoryService->findCategoryByCode('programming');

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals($created->getId(), $found->getId());
        $this->assertEquals('programming', $found->getCode());
    }

    public function test_findCategoryByCode_withNonExistentCode_returnsNull(): void
    {
        // Act
        $result = $this->categoryService->findCategoryByCode('non-existent');

        // Assert
        $this->assertNull($result);
    }

    public function test_getCategoryPath_returnsFullPath(): void
    {
        // Arrange
        $parentDto = new CategoryDTO();
        $parentDto->name = 'Technology';
        $parentDto->code = 'tech';
        $parentDto->sortOrder = 5;
        $parentDto->isActive = true;

        $parent = $this->categoryService->createCategory($parentDto);

        $childDto = new CategoryDTO();
        $childDto->name = 'Programming';
        $childDto->code = 'programming';
        $childDto->sortOrder = 10;
        $childDto->isActive = true;
        $childDto->parentId = (string) $parent->getId();

        $child = $this->categoryService->createCategory($childDto);

        // Act
        $path = $this->categoryService->getCategoryPath((string) $child->getId());

        // Assert
        $this->assertCount(2, $path);
        $this->assertEquals('Technology', $path[0]->getName());
        $this->assertEquals('Programming', $path[1]->getName());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryService = $this->container->get(CategoryService::class);
    }
}