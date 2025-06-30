<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Integration\Service;

use Tourze\QuestionBankBundle\DTO\CategoryDTO;
use Tourze\QuestionBankBundle\Service\CategoryService;
use Tourze\QuestionBankBundle\Tests\BaseIntegrationTestCase;

class CategoryServiceTest extends BaseIntegrationTestCase
{
    private CategoryService $categoryService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryService = $this->getContainer()->get(CategoryService::class);
    }
    
    public function testCreateCategory(): void
    {
        $dto = CategoryDTO::create('Test Category', 'test_category');
        
        $category = $this->categoryService->createCategory($dto);
        
        $this->assertSame('Test Category', $category->getName());
        $this->assertSame('test_category', $category->getCode());
        $this->assertTrue($category->isValid());
    }
    
    public function testFindCategoryById(): void
    {
        $dto = CategoryDTO::create('Test Category', 'test_category');
        $createdCategory = $this->categoryService->createCategory($dto);
        
        $foundCategory = $this->categoryService->findCategory((string) $createdCategory->getId());
        
        $this->assertSame($createdCategory->getId(), $foundCategory->getId());
        $this->assertSame('Test Category', $foundCategory->getName());
    }
    
    public function testUpdateCategory(): void
    {
        $dto = CategoryDTO::create('Test Category', 'test_category');
        $category = $this->categoryService->createCategory($dto);
        
        $updateDto = CategoryDTO::create('Updated Category', 'updated_category');
        $updatedCategory = $this->categoryService->updateCategory((string) $category->getId(), $updateDto);
        
        $this->assertSame('Updated Category', $updatedCategory->getName());
        $this->assertSame('updated_category', $updatedCategory->getCode());
    }
}