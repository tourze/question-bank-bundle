<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Event\QuestionCreatedEvent;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

/**
 * @internal
 */
#[CoversClass(QuestionCreatedEvent::class)]
final class QuestionCreatedEventTest extends AbstractEventTestCase
{
    public function testConstructor(): void
    {
        $question = new Question();
        $question->setTitle('Test Question');
        $question->setContent('Test content');
        $question->setType(QuestionType::MULTIPLE_CHOICE);
        $question->setDifficulty(Difficulty::easy());

        $event = new QuestionCreatedEvent($question);

        $this->assertSame($question, $event->getQuestion());
    }
}
