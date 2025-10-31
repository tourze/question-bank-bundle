<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Uid\Uuid;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use Tourze\QuestionBankBundle\Event\TagMergedEvent;

/**
 * @internal
 */
#[CoversClass(TagMergedEvent::class)]
final class TagMergedEventTest extends AbstractEventTestCase
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
