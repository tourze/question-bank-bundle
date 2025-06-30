<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Event\QuestionCreatedEvent;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

class QuestionCreatedEventTest extends TestCase
{
    public function testConstructor(): void
    {
        $question = new Question('Test Question', 'Test content', QuestionType::MULTIPLE_CHOICE, Difficulty::easy());
        
        $event = new QuestionCreatedEvent($question);
        
        $this->assertSame($question, $event->getQuestion());
    }
}