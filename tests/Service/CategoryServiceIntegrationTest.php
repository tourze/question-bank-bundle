<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Uid\Uuid;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\QuestionBankBundle\DTO\CategoryDTO;
use Tourze\QuestionBankBundle\Exception\CategoryNotFoundException;
use Tourze\QuestionBankBundle\Exception\ValidationException;
use Tourze\QuestionBankBundle\Service\CategoryService;

/**
 * @internal
 */
#[CoversClass(CategoryService::class)]
#[RunTestsInSeparateProcesses]
final class CategoryServiceIntegrationTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 清理测试数据，确保测试隔离
        self::getEntityManager()->createQuery('DELETE FROM Tourze\QuestionBankBundle\Entity\Category c')->execute();
    }

    public function testCreateCategoryWithValidDataCreatesCategory(): void
    {
        // Arrange
        $dto = new CategoryDTO();
        $dto->name = 'Programming';
        $dto->code = 'programming';
        $dto->description = 'Programming questions';
        $dto->sortOrder = 10;
        $dto->isActive = true;

        // Act
        $category = self::getService(CategoryService::class)->createCategory($dto);

        // Assert
        $this->assertNotNull($category->getId());
        $this->assertEquals('Programming', $category->getName());
        $this->assertEquals('programming', $category->getCode());
        $this->assertEquals('Programming questions', $category->getDescription());
        $this->assertEquals(10, $category->getSortOrder());
        $this->assertTrue($category->isValid());
    }

    public function testCreateCategoryWithParentSetsParentCorrectly(): void
    {
        // Arrange
        $parentDto = new CategoryDTO();
        $parentDto->name = 'Technology';
        $parentDto->code = 'tech';
        $parentDto->sortOrder = 5;
        $parentDto->isActive = true;

        $parent = self::getService(CategoryService::class)->createCategory($parentDto);

        $childDto = new CategoryDTO();
        $childDto->name = 'Programming';
        $childDto->code = 'programming';
        $childDto->sortOrder = 10;
        $childDto->isActive = true;
        $childDto->parentId = (string) $parent->getId();

        // Act
        $child = self::getService(CategoryService::class)->createCategory($childDto);

        // Assert
        $this->assertNotNull($child->getParent());
        $this->assertEquals($parent->getId(), $child->getParent()->getId());
        $this->assertEquals('Technology', $child->getParent()->getName());
    }

    public function testCreateCategoryWithDuplicateCodeThrowsValidationException(): void
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

        self::getService(CategoryService::class)->createCategory($dto1);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Category with code "programming" already exists');
        self::getService(CategoryService::class)->createCategory($dto2);
    }

    public function testUpdateCategoryWithValidDataUpdatesCategory(): void
    {
        // Arrange
        $dto = new CategoryDTO();
        $dto->name = 'Programming';
        $dto->code = 'programming';
        $dto->sortOrder = 10;
        $dto->isActive = true;

        $category = self::getService(CategoryService::class)->createCategory($dto);

        $updateDto = new CategoryDTO();
        $updateDto->name = 'Software Development';
        $updateDto->code = 'software_dev';
        $updateDto->description = 'Updated description';
        $updateDto->sortOrder = 15;
        $updateDto->isActive = false;

        // Act
        $updatedCategory = self::getService(CategoryService::class)->updateCategory((string) $category->getId(), $updateDto);

        // Assert
        $this->assertEquals('Software Development', $updatedCategory->getName());
        $this->assertEquals('software_dev', $updatedCategory->getCode());
        $this->assertEquals('Updated description', $updatedCategory->getDescription());
        $this->assertEquals(15, $updatedCategory->getSortOrder());
        $this->assertFalse($updatedCategory->isValid());
    }

    public function testUpdateCategoryWithDuplicateCodeThrowsValidationException(): void
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

        $category1 = self::getService(CategoryService::class)->createCategory($dto1);
        $category2 = self::getService(CategoryService::class)->createCategory($dto2);

        $updateDto = new CategoryDTO();
        $updateDto->name = 'Database Updated';
        $updateDto->code = 'programming'; // 尝试使用已存在的 code
        $updateDto->sortOrder = 25;
        $updateDto->isActive = true;

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Category with code "programming" already exists');
        self::getService(CategoryService::class)->updateCategory((string) $category2->getId(), $updateDto);
    }

    public function testDeleteCategoryWithValidCategoryDeletesCategory(): void
    {
        // Arrange
        $dto = new CategoryDTO();
        $dto->name = 'Programming';
        $dto->code = 'programming';
        $dto->sortOrder = 10;
        $dto->isActive = true;

        $category = self::getService(CategoryService::class)->createCategory($dto);
        $id = $category->getId();

        // Act
        self::getService(CategoryService::class)->deleteCategory((string) $id);

        // Assert
        $this->expectException(CategoryNotFoundException::class);
        self::getService(CategoryService::class)->findCategory((string) $id);
    }

    public function testDeleteCategoryWithChildrenThrowsValidationException(): void
    {
        // Arrange
        $parentDto = new CategoryDTO();
        $parentDto->name = 'Technology';
        $parentDto->code = 'tech';
        $parentDto->sortOrder = 5;
        $parentDto->isActive = true;

        $parent = self::getService(CategoryService::class)->createCategory($parentDto);

        $childDto = new CategoryDTO();
        $childDto->name = 'Programming';
        $childDto->code = 'programming';
        $childDto->sortOrder = 10;
        $childDto->isActive = true;
        $childDto->parentId = (string) $parent->getId();

        self::getService(CategoryService::class)->createCategory($childDto);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cannot delete category with children');
        self::getService(CategoryService::class)->deleteCategory((string) $parent->getId());
    }

    public function testFindCategoryWithValidIdReturnsCategory(): void
    {
        // Arrange
        $dto = new CategoryDTO();
        $dto->name = 'Programming';
        $dto->code = 'programming';
        $dto->sortOrder = 10;
        $dto->isActive = true;

        $created = self::getService(CategoryService::class)->createCategory($dto);

        // Act
        $found = self::getService(CategoryService::class)->findCategory((string) $created->getId());

        // Assert
        $this->assertEquals($created->getId(), $found->getId());
        $this->assertEquals('Programming', $found->getName());
    }

    public function testFindCategoryWithInvalidIdThrowsNotFoundException(): void
    {
        // Arrange - 使用有效的 UUID 格式但不存在的 ID
        $nonExistentId = Uuid::v7();

        // Act & Assert
        $this->expectException(CategoryNotFoundException::class);
        self::getService(CategoryService::class)->findCategory((string) $nonExistentId);
    }

    public function testMoveCategoryToNewParentUpdatesParent(): void
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

        $parent = self::getService(CategoryService::class)->createCategory($parentDto);
        $newParent = self::getService(CategoryService::class)->createCategory($newParentDto);
        $child = self::getService(CategoryService::class)->createCategory($childDto);

        $child->setParent($parent);
        self::getEntityManager()->persist($child);
        self::getEntityManager()->flush();

        // Act
        self::getService(CategoryService::class)->moveCategory((string) $child->getId(), (string) $newParent->getId());

        // Assert
        self::getEntityManager()->refresh($child);
        $this->assertNotNull($child->getParent());
        $this->assertEquals($newParent->getId(), $child->getParent()->getId());
    }

    public function testMoveCategoryToRootRemovesParent(): void
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

        $parent = self::getService(CategoryService::class)->createCategory($parentDto);
        $child = self::getService(CategoryService::class)->createCategory($childDto);

        $child->setParent($parent);
        self::getEntityManager()->persist($child);
        self::getEntityManager()->flush();

        // Act
        self::getService(CategoryService::class)->moveCategory((string) $child->getId(), null);

        // Assert
        self::getEntityManager()->refresh($child);
        $this->assertNull($child->getParent());
    }

    public function testGetCategoryTreeReturnsHierarchicalStructure(): void
    {
        // Arrange
        $parentDto = new CategoryDTO();
        $parentDto->name = 'Technology';
        $parentDto->code = 'tech';
        $parentDto->sortOrder = 5;
        $parentDto->isActive = true;

        $parent = self::getService(CategoryService::class)->createCategory($parentDto);

        $childDto = new CategoryDTO();
        $childDto->name = 'Programming';
        $childDto->code = 'programming';
        $childDto->sortOrder = 10;
        $childDto->isActive = true;
        $childDto->parentId = (string) $parent->getId();

        self::getService(CategoryService::class)->createCategory($childDto);

        // Act
        $tree = self::getService(CategoryService::class)->getCategoryTree();

        // Assert
        $this->assertNotEmpty($tree);
    }

    public function testFindCategoryByCodeWithExistingCodeReturnsCategory(): void
    {
        // Arrange
        $dto = new CategoryDTO();
        $dto->name = 'Programming';
        $dto->code = 'programming';
        $dto->sortOrder = 10;
        $dto->isActive = true;

        $created = self::getService(CategoryService::class)->createCategory($dto);

        // Act
        $found = self::getService(CategoryService::class)->findCategoryByCode('programming');

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals($created->getId(), $found->getId());
        $this->assertEquals('programming', $found->getCode());
    }

    public function testFindCategoryByCodeWithNonExistentCodeReturnsNull(): void
    {
        // Act
        $result = self::getService(CategoryService::class)->findCategoryByCode('non-existent');

        // Assert
        $this->assertNull($result);
    }

    public function testGetCategoryPathReturnsFullPath(): void
    {
        // Arrange
        $parentDto = new CategoryDTO();
        $parentDto->name = 'Technology';
        $parentDto->code = 'tech';
        $parentDto->sortOrder = 5;
        $parentDto->isActive = true;

        $parent = self::getService(CategoryService::class)->createCategory($parentDto);

        $childDto = new CategoryDTO();
        $childDto->name = 'Programming';
        $childDto->code = 'programming';
        $childDto->sortOrder = 10;
        $childDto->isActive = true;
        $childDto->parentId = (string) $parent->getId();

        $child = self::getService(CategoryService::class)->createCategory($childDto);

        // Act
        $path = self::getService(CategoryService::class)->getCategoryPath((string) $child->getId());

        // Assert
        $this->assertCount(2, $path);
        $this->assertEquals('Technology', $path[0]->getName());
        $this->assertEquals('Programming', $path[1]->getName());
    }
}
