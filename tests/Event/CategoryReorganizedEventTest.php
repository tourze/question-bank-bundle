<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Uid\Uuid;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use Tourze\QuestionBankBundle\Event\CategoryReorganizedEvent;

/**
 * @internal
 */
#[CoversClass(CategoryReorganizedEvent::class)]
final class CategoryReorganizedEventTest extends AbstractEventTestCase
{
    public function testConstructor(): void
    {
        $categoryId = Uuid::v7()->toRfc4122();
        $parentId = Uuid::v7()->toRfc4122();
        $oldPath = 'old/path';
        $newPath = 'new/path';
        $affectedChildren = [
            Uuid::v7()->toRfc4122() => 'old/path/child1',
            Uuid::v7()->toRfc4122() => 'old/path/child2',
        ];

        $event = new CategoryReorganizedEvent($categoryId, $parentId, $oldPath, $newPath, $affectedChildren);

        $this->assertSame($categoryId, $event->getCategoryId());
        $this->assertSame($parentId, $event->getParentId());
        $this->assertSame($oldPath, $event->getOldPath());
        $this->assertSame($newPath, $event->getNewPath());
        $this->assertSame($affectedChildren, $event->getAffectedChildren());
    }

    public function testWithNullParent(): void
    {
        $categoryId = Uuid::v7()->toRfc4122();
        $oldPath = 'old/path';
        $newPath = 'new/path';
        $affectedChildren = [];

        $event = new CategoryReorganizedEvent($categoryId, null, $oldPath, $newPath, $affectedChildren);

        $this->assertSame($categoryId, $event->getCategoryId());
        $this->assertNull($event->getParentId());
        $this->assertSame($oldPath, $event->getOldPath());
        $this->assertSame($newPath, $event->getNewPath());
        $this->assertSame($affectedChildren, $event->getAffectedChildren());
    }
}
