<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Exception\QuestionBankException;
use Tourze\QuestionBankBundle\Exception\TagValidationException;

class TagValidationExceptionTest extends TestCase
{
    public function testInheritance(): void
    {
        $exception = new TagValidationException('Tag validation error');
        
        $this->assertInstanceOf(QuestionBankException::class, $exception);
        $this->assertSame('Tag validation error', $exception->getMessage());
    }
    
    public function testWithCode(): void
    {
        $exception = new TagValidationException('Tag validation error', 400);
        
        $this->assertSame('Tag validation error', $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
    }
}