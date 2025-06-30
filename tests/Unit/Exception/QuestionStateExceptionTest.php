<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Exception\QuestionBankException;
use Tourze\QuestionBankBundle\Exception\QuestionStateException;

class QuestionStateExceptionTest extends TestCase
{
    public function testInheritance(): void
    {
        $exception = new QuestionStateException('Question state error');
        
        $this->assertInstanceOf(QuestionBankException::class, $exception);
        $this->assertSame('Question state error', $exception->getMessage());
    }
    
    public function testWithCode(): void
    {
        $exception = new QuestionStateException('Question state error', 400);
        
        $this->assertSame('Question state error', $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
    }
}