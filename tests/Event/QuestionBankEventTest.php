<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\EventDispatcher\Event;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use Tourze\QuestionBankBundle\Event\QuestionBankEvent;

/**
 * @internal
 */
#[CoversClass(QuestionBankEvent::class)]
final class QuestionBankEventTest extends AbstractEventTestCase
{
    public function testAbstractClassInheritance(): void
    {
        $reflection = new \ReflectionClass(QuestionBankEvent::class);

        $this->assertTrue($reflection->isAbstract());
        $this->assertTrue($reflection->isSubclassOf(Event::class));
    }
}
