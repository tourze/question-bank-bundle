<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Exception\QuestionBankException;

class QuestionBankExceptionTest extends TestCase
{
    public function testAbstractClassInheritance(): void
    {
        $reflection = new \ReflectionClass(QuestionBankException::class);
        
        $this->assertTrue($reflection->isAbstract());
        $this->assertTrue($reflection->isSubclassOf(Exception::class));
    }
}