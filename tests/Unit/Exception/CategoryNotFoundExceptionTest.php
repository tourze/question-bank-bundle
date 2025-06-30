<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Exception\CategoryNotFoundException;
use Tourze\QuestionBankBundle\Exception\QuestionBankException;

class CategoryNotFoundExceptionTest extends TestCase
{
    public function testInheritance(): void
    {
        $exception = new CategoryNotFoundException('Category not found');
        
        $this->assertInstanceOf(QuestionBankException::class, $exception);
        $this->assertSame('Category not found', $exception->getMessage());
    }
    
    public function testWithCode(): void
    {
        $exception = new CategoryNotFoundException('Category not found', 404);
        
        $this->assertSame('Category not found', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
    }
}