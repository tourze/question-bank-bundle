<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Tourze\QuestionBankBundle\Event\CategoryReorganizedEvent;

class CategoryReorganizedEventTest extends TestCase
{
    public function testConstructor(): void
    {
        $categoryId = Uuid::v7();
        $parentId = Uuid::v7();
        $oldPath = 'old/path';
        $newPath = 'new/path';
        $affectedChildren = [(string) Uuid::v7(), (string) Uuid::v7()];
        
        $event = new CategoryReorganizedEvent($categoryId, $parentId, $oldPath, $newPath, $affectedChildren);
        
        $this->assertSame($categoryId, $event->getCategoryId());
        $this->assertSame($parentId, $event->getParentId());
        $this->assertSame($oldPath, $event->getOldPath());
        $this->assertSame($newPath, $event->getNewPath());
        $this->assertSame($affectedChildren, $event->getAffectedChildren());
    }
    
    public function testWithNullParent(): void
    {
        $categoryId = Uuid::v7();
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