<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Exception\QuestionBankException;
use Tourze\QuestionBankBundle\Exception\TagNotFoundException;

class TagNotFoundExceptionTest extends TestCase
{
    public function testInheritance(): void
    {
        $exception = new TagNotFoundException('Tag not found');
        
        $this->assertInstanceOf(QuestionBankException::class, $exception);
        $this->assertSame('Tag not found', $exception->getMessage());
    }
    
    public function testWithCode(): void
    {
        $exception = new TagNotFoundException('Tag not found', 404);
        
        $this->assertSame('Tag not found', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
    }
}