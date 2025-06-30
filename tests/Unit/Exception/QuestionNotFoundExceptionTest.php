<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Exception\QuestionBankException;
use Tourze\QuestionBankBundle\Exception\QuestionNotFoundException;

class QuestionNotFoundExceptionTest extends TestCase
{
    public function testInheritance(): void
    {
        $exception = new QuestionNotFoundException('Question not found');
        
        $this->assertInstanceOf(QuestionBankException::class, $exception);
        $this->assertSame('Question not found', $exception->getMessage());
    }
    
    public function testWithCode(): void
    {
        $exception = new QuestionNotFoundException('Question not found', 404);
        
        $this->assertSame('Question not found', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
    }
}