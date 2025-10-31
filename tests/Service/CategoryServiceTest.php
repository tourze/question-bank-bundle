<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\QuestionBankBundle\DTO\CategoryDTO;
use Tourze\QuestionBankBundle\Exception\CategoryNotFoundException;
use Tourze\QuestionBankBundle\Service\CategoryService;

/**
 * @internal
 */
#[CoversClass(CategoryService::class)]
#[RunTestsInSeparateProcesses]
final class CategoryServiceTest extends AbstractIntegrationTestCase
{
    public function testCreateCategory(): void
    {
        $dto = CategoryDTO::create('Test Category', 'test_category');

        $category = self::getService(CategoryService::class)->createCategory($dto);

        $this->assertSame('Test Category', $category->getName());
        $this->assertSame('test_category', $category->getCode());
        $this->assertTrue($category->isValid());
    }

    public function testFindCategoryById(): void
    {
        $dto = CategoryDTO::create('Test Category', 'test_category');
        $createdCategory = self::getService(CategoryService::class)->createCategory($dto);

        $foundCategory = self::getService(CategoryService::class)->findCategory((string) $createdCategory->getId());

        $this->assertSame($createdCategory->getId(), $foundCategory->getId());
        $this->assertSame('Test Category', $foundCategory->getName());
    }

    public function testUpdateCategory(): void
    {
        $dto = CategoryDTO::create('Test Category', 'test_category');
        $category = self::getService(CategoryService::class)->createCategory($dto);

        $updateDto = CategoryDTO::create('Updated Category', 'updated_category');
        $updatedCategory = self::getService(CategoryService::class)->updateCategory((string) $category->getId(), $updateDto);

        $this->assertSame('Updated Category', $updatedCategory->getName());
        $this->assertSame('updated_category', $updatedCategory->getCode());
    }

    public function testDeleteCategory(): void
    {
        $dto = CategoryDTO::create('Test Category', 'test_category');
        $category = self::getService(CategoryService::class)->createCategory($dto);
        $categoryId = (string) $category->getId();

        self::getService(CategoryService::class)->deleteCategory($categoryId);

        $this->expectException(CategoryNotFoundException::class);
        self::getService(CategoryService::class)->findCategory($categoryId);
    }

    public function testFindCategoryByCode(): void
    {
        $dto = CategoryDTO::create('Test Category', 'test_category');
        $createdCategory = self::getService(CategoryService::class)->createCategory($dto);

        $foundCategory = self::getService(CategoryService::class)->findCategoryByCode('test_category');

        $this->assertNotNull($foundCategory);
        $this->assertSame($createdCategory->getId(), $foundCategory->getId());
        $this->assertSame('Test Category', $foundCategory->getName());
        $this->assertSame('test_category', $foundCategory->getCode());

        // 测试不存在的代码
        $nonExistentCategory = self::getService(CategoryService::class)->findCategoryByCode('non_existent');
        $this->assertNull($nonExistentCategory);
    }

    public function testMoveCategory(): void
    {
        // 创建父分类
        $parentDto = CategoryDTO::create('Parent Category', 'parent_category');
        $parentCategory = self::getService(CategoryService::class)->createCategory($parentDto);

        // 创建子分类
        $childDto = CategoryDTO::create('Child Category', 'child_category');
        $childCategory = self::getService(CategoryService::class)->createCategory($childDto);

        // 移动子分类到父分类下
        self::getService(CategoryService::class)->moveCategory((string) $childCategory->getId(), (string) $parentCategory->getId());

        // 重新获取子分类验证移动结果
        $updatedChild = self::getService(CategoryService::class)->findCategory((string) $childCategory->getId());
        $this->assertNotNull($updatedChild->getParent());
        $this->assertSame($parentCategory->getId(), $updatedChild->getParent()->getId());

        // 测试移动到根级别（无父分类）
        self::getService(CategoryService::class)->moveCategory((string) $childCategory->getId(), null);
        $updatedChild = self::getService(CategoryService::class)->findCategory((string) $childCategory->getId());
        $this->assertNull($updatedChild->getParent());
    }

    protected function onSetUp(): void
    {
        // 清理测试数据，确保测试隔离
        self::getEntityManager()->createQuery('DELETE FROM Tourze\QuestionBankBundle\Entity\Category c')->execute();
    }
}
