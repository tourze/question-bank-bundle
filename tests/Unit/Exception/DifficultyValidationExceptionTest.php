<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Exception\DifficultyValidationException;
use Tourze\QuestionBankBundle\Exception\QuestionBankException;

class DifficultyValidationExceptionTest extends TestCase
{
    public function testInheritance(): void
    {
        $exception = new DifficultyValidationException('Difficulty validation error');
        
        $this->assertInstanceOf(QuestionBankException::class, $exception);
        $this->assertSame('Difficulty validation error', $exception->getMessage());
    }
    
    public function testWithCode(): void
    {
        $exception = new DifficultyValidationException('Difficulty validation error', 400);
        
        $this->assertSame('Difficulty validation error', $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
    }
}