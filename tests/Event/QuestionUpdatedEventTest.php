<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Event\QuestionUpdatedEvent;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

/**
 * @internal
 */
#[CoversClass(QuestionUpdatedEvent::class)]
final class QuestionUpdatedEventTest extends AbstractEventTestCase
{
    public function testConstructorSetsQuestion(): void
    {
        // Arrange
        $question = $this->createQuestion();

        // Act
        $event = new QuestionUpdatedEvent($question);

        // Assert
        $this->assertEquals($question, $event->getQuestion());
        $this->assertSame($question, $event->getQuestion());
    }

    private function createQuestion(string $title = 'Test Question'): Question
    {
        $question = new Question();
        $question->setTitle($title);
        $question->setContent('Test content');
        $question->setType(QuestionType::SINGLE_CHOICE);
        $question->setDifficulty(new Difficulty(3));

        return $question;
    }

    public function testGetQuestionReturnsCorrectQuestion(): void
    {
        // Arrange
        $question1 = $this->createQuestion('Question 1');
        $question2 = $this->createQuestion('Question 2');

        $event1 = new QuestionUpdatedEvent($question1);
        $event2 = new QuestionUpdatedEvent($question2);

        // Act & Assert
        $this->assertEquals($question1, $event1->getQuestion());
        $this->assertEquals($question2, $event2->getQuestion());
        $this->assertNotSame($event1->getQuestion(), $event2->getQuestion());
    }

    public function testEventCarriesQuestionData(): void
    {
        // Arrange
        $question = $this->createQuestion('Updated Question');
        $question->setScore(15.5);
        $question->setExplanation('Test explanation');

        // Act
        $event = new QuestionUpdatedEvent($question);

        // Assert
        $this->assertEquals('Updated Question', $event->getQuestion()->getTitle());
        $this->assertEquals(15.5, $event->getQuestion()->getScore());
        $this->assertEquals('Test explanation', $event->getQuestion()->getExplanation());
    }

    public function testMultipleEventsWithDifferentQuestions(): void
    {
        // Arrange
        $questions = [];
        $events = [];

        for ($i = 1; $i <= 3; ++$i) {
            $question = $this->createQuestion("Question {$i}");
            $questions[] = $question;
            $events[] = new QuestionUpdatedEvent($question);
        }

        // Act & Assert
        for ($i = 0; $i < 3; ++$i) {
            $this->assertEquals($questions[$i], $events[$i]->getQuestion());
            $this->assertEquals('Question ' . ($i + 1), $events[$i]->getQuestion()->getTitle());
        }
    }

    public function testEventWithPublishedQuestion(): void
    {
        // Arrange
        $question = $this->createQuestion('Published Question');
        $question->publish();

        // Act
        $event = new QuestionUpdatedEvent($question);

        // Assert
        $this->assertTrue($event->getQuestion()->isUsable());
        $this->assertFalse($event->getQuestion()->isEditable());
    }
}
