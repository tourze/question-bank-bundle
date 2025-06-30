<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\DTO;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\DTO\CategoryDTO;

class CategoryDTOTest extends TestCase
{
    public function testCreate(): void
    {
        $dto = CategoryDTO::create('Test Category', 'test-code');
        
        $this->assertSame('Test Category', $dto->name);
        $this->assertSame('test-code', $dto->code);
        $this->assertNull($dto->description);
        $this->assertSame(0, $dto->sortOrder);
        $this->assertTrue($dto->isActive);
        $this->assertNull($dto->parentId);
    }
    
    public function testWithOptionalFields(): void
    {
        $dto = CategoryDTO::create('Test Category', 'test-code');
        $dto->description = 'Test description';
        $dto->sortOrder = 5;
        $dto->isActive = false;
        $dto->parentId = 'parent-uuid';
        
        $this->assertSame('Test Category', $dto->name);
        $this->assertSame('test-code', $dto->code);
        $this->assertSame('Test description', $dto->description);
        $this->assertSame(5, $dto->sortOrder);
        $this->assertFalse($dto->isActive);
        $this->assertSame('parent-uuid', $dto->parentId);
    }
}