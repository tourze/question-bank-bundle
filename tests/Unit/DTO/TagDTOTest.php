<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\DTO;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\DTO\TagDTO;

class TagDTOTest extends TestCase
{
    public function testCreate(): void
    {
        $dto = TagDTO::create('Test Tag');
        
        $this->assertSame('Test Tag', $dto->name);
        $this->assertNull($dto->slug);
        $this->assertNull($dto->description);
        $this->assertNull($dto->color);
    }
    
    public function testWithOptionalFields(): void
    {
        $dto = TagDTO::create('Test Tag');
        $dto->slug = 'test-tag';
        $dto->description = 'Test description';
        $dto->color = '#FF0000';
        
        $this->assertSame('Test Tag', $dto->name);
        $this->assertSame('test-tag', $dto->slug);
        $this->assertSame('Test description', $dto->description);
        $this->assertSame('#FF0000', $dto->color);
    }
}