<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Exception\CategoryHierarchyException;
use Tourze\QuestionBankBundle\Exception\QuestionBankException;

class CategoryHierarchyExceptionTest extends TestCase
{
    public function testInheritance(): void
    {
        $exception = new CategoryHierarchyException('Category hierarchy error');
        
        $this->assertInstanceOf(QuestionBankException::class, $exception);
        $this->assertSame('Category hierarchy error', $exception->getMessage());
    }
    
    public function testWithCode(): void
    {
        $exception = new CategoryHierarchyException('Category hierarchy error', 400);
        
        $this->assertSame('Category hierarchy error', $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
    }
}