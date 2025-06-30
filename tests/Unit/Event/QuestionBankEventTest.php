<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;
use Tourze\QuestionBankBundle\Event\QuestionBankEvent;

class QuestionBankEventTest extends TestCase
{
    public function testAbstractClassInheritance(): void
    {
        $reflection = new \ReflectionClass(QuestionBankEvent::class);
        
        $this->assertTrue($reflection->isAbstract());
        $this->assertTrue($reflection->isSubclassOf(Event::class));
    }
}