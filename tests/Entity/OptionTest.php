<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Entity\Option;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

class OptionTest extends TestCase
{
    public function test_constructor_setsDefaultValues(): void
    {
        $option = new Option('Test Content');

        $this->assertEquals('Test Content', $option->getContent());
        $this->assertFalse($option->isCorrect());
        $this->assertEquals(0, $option->getSortOrder());
        $this->assertNull($option->getExplanation());
        $this->assertNull($option->getQuestion());
        $this->assertTrue($option->isValid());
        $this->assertInstanceOf(\DateTimeImmutable::class, $option->getCreateTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $option->getUpdateTime());
    }

    public function test_constructor_withCorrectAndSortOrder(): void
    {
        $option = new Option('Test Content', true, 5);

        $this->assertEquals('Test Content', $option->getContent());
        $this->assertTrue($option->isCorrect());
        $this->assertEquals(5, $option->getSortOrder());
    }

    public function test_setContent_updatesContent(): void
    {
        $option = new Option('Test Content');
        $originalUpdateTime = $option->getUpdateTime();

        usleep(1000); // 确保时间差异
        $option->setContent('New Content');

        $this->assertEquals('New Content', $option->getContent());
        $this->assertGreaterThan($originalUpdateTime, $option->getUpdateTime());
    }

    public function test_setIsCorrect_updatesCorrectFlag(): void
    {
        $option = new Option('Test Content', false);

        $option->setIsCorrect(true);

        $this->assertTrue($option->isCorrect());
    }

    public function test_setSortOrder_updatesSortOrder(): void
    {
        $option = new Option('Test Content');

        $option->setSortOrder(10);

        $this->assertEquals(10, $option->getSortOrder());
    }

    public function test_setExplanation_updatesExplanation(): void
    {
        $option = new Option('Test Content');

        $option->setExplanation('Test Explanation');

        $this->assertEquals('Test Explanation', $option->getExplanation());
    }

    public function test_setExplanation_withNull_setsNull(): void
    {
        $option = new Option('Test Content');
        $option->setExplanation('Some explanation');

        $option->setExplanation(null);

        $this->assertNull($option->getExplanation());
    }

    public function test_setQuestion_updatesQuestion(): void
    {
        $option = new Option('Test Content');
        $question = $this->createTestQuestion();

        $option->setQuestion($question);

        $this->assertEquals($question, $option->getQuestion());
    }

    private function createTestQuestion(): Question
    {
        $difficulty = new Difficulty(3);
        return new Question('Test Question', 'Test Content', QuestionType::SINGLE_CHOICE, $difficulty);
    }

    public function test_setQuestion_withNull_setsNull(): void
    {
        $option = new Option('Test Content');
        $question = $this->createTestQuestion();
        $option->setQuestion($question);

        $option->setQuestion(null);

        $this->assertNull($option->getQuestion());
    }

    public function test_setValid_updatesValidFlag(): void
    {
        $option = new Option('Test Content');

        $option->setValid(false);

        $this->assertFalse($option->isValid());
    }

    public function test_toString_returnsContent(): void
    {
        $option = new Option('Test Content');

        $this->assertEquals('Test Content', (string) $option);
    }

    public function test_correctOptionBehavior(): void
    {
        $correctOption = new Option('Correct Answer', true);
        $wrongOption = new Option('Wrong Answer', false);

        $this->assertTrue($correctOption->isCorrect());
        $this->assertFalse($wrongOption->isCorrect());
    }

    public function test_sortOrderBehavior(): void
    {
        $option1 = new Option('Option 1', false, 1);
        $option2 = new Option('Option 2', false, 2);
        $option3 = new Option('Option 3', false, 0);

        $this->assertEquals(1, $option1->getSortOrder());
        $this->assertEquals(2, $option2->getSortOrder());
        $this->assertEquals(0, $option3->getSortOrder());
    }

    public function test_optionWithLongContent(): void
    {
        $longContent = str_repeat('This is a very long option content. ', 50);
        $option = new Option($longContent);

        $this->assertEquals($longContent, $option->getContent());
        $this->assertEquals($longContent, (string) $option);
    }

    public function test_optionWithEmptyContent(): void
    {
        $option = new Option('');

        $this->assertEquals('', $option->getContent());
        $this->assertEquals('', (string) $option);
    }

    public function test_multiplePropertyUpdates(): void
    {
        $option = new Option('Initial Content');
        $question = $this->createTestQuestion();

        $option->setContent('Updated Content');
        $option->setIsCorrect(true);
        $option->setSortOrder(5);
        $option->setExplanation('This is the explanation');
        $option->setQuestion($question);
        $option->setValid(true);

        $this->assertEquals('Updated Content', $option->getContent());
        $this->assertTrue($option->isCorrect());
        $this->assertEquals(5, $option->getSortOrder());
        $this->assertEquals('This is the explanation', $option->getExplanation());
        $this->assertEquals($question, $option->getQuestion());
        $this->assertTrue($option->isValid());
    }

    public function test_updateTime_changesOnPropertyUpdate(): void
    {
        $option = new Option('Test Content');
        $originalUpdateTime = $option->getUpdateTime();

        usleep(1000); // 确保时间差异
        $option->setIsCorrect(true);

        $this->assertGreaterThan($originalUpdateTime, $option->getUpdateTime());
    }
}
