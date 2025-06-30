<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Tourze\QuestionBankBundle\Event\TagMergedEvent;

class TagMergedEventTest extends TestCase
{
    public function testConstructor(): void
    {
        $sourceTagId = Uuid::v7();
        $targetTagId = Uuid::v7();
        $affectedQuestions = [(string) Uuid::v7(), (string) Uuid::v7()];
        
        $event = new TagMergedEvent($sourceTagId, $targetTagId, $affectedQuestions);
        
        $this->assertSame($sourceTagId, $event->getSourceTagId());
        $this->assertSame($targetTagId, $event->getTargetTagId());
        $this->assertSame($affectedQuestions, $event->getAffectedQuestions());
    }
    
    public function testWithEmptyAffectedQuestions(): void
    {
        $sourceTagId = Uuid::v7();
        $targetTagId = Uuid::v7();
        $affectedQuestions = [];
        
        $event = new TagMergedEvent($sourceTagId, $targetTagId, $affectedQuestions);
        
        $this->assertSame($sourceTagId, $event->getSourceTagId());
        $this->assertSame($targetTagId, $event->getTargetTagId());
        $this->assertSame($affectedQuestions, $event->getAffectedQuestions());
    }
}