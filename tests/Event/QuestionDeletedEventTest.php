<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Uid\Uuid;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use Tourze\QuestionBankBundle\Event\QuestionDeletedEvent;

/**
 * @internal
 */
#[CoversClass(QuestionDeletedEvent::class)]
final class QuestionDeletedEventTest extends AbstractEventTestCase
{
    public function testConstructorSetsQuestionId(): void
    {
        // Arrange
        $questionId = (string) Uuid::v7();

        // Act
        $event = new QuestionDeletedEvent($questionId);

        // Assert
        $this->assertEquals($questionId, $event->getQuestionId());
    }

    public function testGetQuestionIdReturnsCorrectId(): void
    {
        // Arrange
        $id1 = (string) Uuid::v7();
        $id2 = (string) Uuid::v7();

        $event1 = new QuestionDeletedEvent($id1);
        $event2 = new QuestionDeletedEvent($id2);

        // Act & Assert
        $this->assertEquals($id1, $event1->getQuestionId());
        $this->assertEquals($id2, $event2->getQuestionId());
        $this->assertNotEquals($event1->getQuestionId(), $event2->getQuestionId());
    }

    public function testMultipleEventsHaveDifferentIds(): void
    {
        // Arrange
        $ids = [];
        $events = [];

        // Create multiple events
        for ($i = 0; $i < 5; ++$i) {
            $id = (string) Uuid::v7();
            $ids[] = $id;
            $events[] = new QuestionDeletedEvent($id);
        }

        // Act & Assert - Each event should have its corresponding ID
        for ($i = 0; $i < 5; ++$i) {
            $this->assertEquals($ids[$i], $events[$i]->getQuestionId());
        }
    }
}
