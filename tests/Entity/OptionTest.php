<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\QuestionBankBundle\Entity\Option;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

/**
 * @internal
 */
#[CoversClass(Option::class)]
final class OptionTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        $option = new Option();
        $option->setContent('Test Content');

        return $option;
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     *
     * @return iterable<string, array{0: string, 1: mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'content' => ['content', 'Updated option content'];
        yield 'sortOrder' => ['sortOrder', 15];
        yield 'explanation' => ['explanation', 'This is an explanation for the option'];
        yield 'valid' => ['valid', false];
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $option = new Option();
        $option->setContent('Test Content');
        $option->setIsCorrect(false);

        $this->assertEquals('Test Content', $option->getContent());
        $this->assertFalse($option->isCorrect());
        $this->assertEquals(0, $option->getSortOrder());
        $this->assertNull($option->getExplanation());
        $this->assertNull($option->getQuestion());
        $this->assertTrue($option->isValid());
        $this->assertInstanceOf(\DateTimeImmutable::class, $option->getCreateTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $option->getUpdateTime());
    }

    public function testConstructorWithCorrectAndSortOrder(): void
    {
        $option = new Option();
        $option->setContent('Test Content');
        $option->setIsCorrect(true);
        $option->setSortOrder(5);

        $this->assertEquals('Test Content', $option->getContent());
        $this->assertTrue($option->isCorrect());
        $this->assertEquals(5, $option->getSortOrder());
    }

    public function testSetContentUpdatesContent(): void
    {
        $option = new Option();
        $option->setContent('Test Content');
        $originalUpdateTime = $option->getUpdateTime();

        usleep(1000); // 确保时间差异
        $option->setContent('New Content');

        $this->assertEquals('New Content', $option->getContent());
        $this->assertGreaterThan($originalUpdateTime, $option->getUpdateTime());
    }

    public function testSetIsCorrectUpdatesCorrectFlag(): void
    {
        $option = new Option();
        $option->setContent('Test Content');
        $option->setIsCorrect(false);

        $option->setIsCorrect(true);

        $this->assertTrue($option->isCorrect());
    }

    public function testSetSortOrderUpdatesSortOrder(): void
    {
        $option = new Option();
        $option->setContent('Test Content');

        $option->setSortOrder(10);

        $this->assertEquals(10, $option->getSortOrder());
    }

    public function testSetExplanationUpdatesExplanation(): void
    {
        $option = new Option();
        $option->setContent('Test Content');

        $option->setExplanation('Test Explanation');

        $this->assertEquals('Test Explanation', $option->getExplanation());
    }

    public function testSetExplanationWithNullSetsNull(): void
    {
        $option = new Option();
        $option->setContent('Test Content');
        $option->setExplanation('Some explanation');

        $option->setExplanation(null);

        $this->assertNull($option->getExplanation());
    }

    public function testSetQuestionUpdatesQuestion(): void
    {
        $option = new Option();
        $option->setContent('Test Content');
        $question = $this->createTestQuestion();

        $option->setQuestion($question);

        $this->assertEquals($question, $option->getQuestion());
    }

    private function createTestQuestion(): Question
    {
        $difficulty = new Difficulty(3);

        $question = new Question();
        $question->setTitle('Test Question');
        $question->setContent('Test Content');
        $question->setType(QuestionType::SINGLE_CHOICE);
        $question->setDifficulty($difficulty);

        return $question;
    }

    public function testSetQuestionWithNullSetsNull(): void
    {
        $option = new Option();
        $option->setContent('Test Content');
        $question = $this->createTestQuestion();
        $option->setQuestion($question);

        $option->setQuestion(null);

        $this->assertNull($option->getQuestion());
    }

    public function testSetValidUpdatesValidFlag(): void
    {
        $option = new Option();
        $option->setContent('Test Content');

        $option->setValid(false);

        $this->assertFalse($option->isValid());
    }

    public function testToStringReturnsContent(): void
    {
        $option = new Option();
        $option->setContent('Test Content');

        $this->assertEquals('Test Content', (string) $option);
    }

    public function testCorrectOptionBehavior(): void
    {
        $correctOption = new Option();
        $correctOption->setContent('Correct Answer');
        $correctOption->setIsCorrect(true);
        $wrongOption = new Option();
        $wrongOption->setContent('Wrong Answer');
        $wrongOption->setIsCorrect(false);

        $this->assertTrue($correctOption->isCorrect());
        $this->assertFalse($wrongOption->isCorrect());
    }

    public function testSortOrderBehavior(): void
    {
        $option1 = new Option();
        $option1->setContent('Option 1');
        $option1->setIsCorrect(false);
        $option1->setSortOrder(1);
        $option2 = new Option();
        $option2->setContent('Option 2');
        $option2->setIsCorrect(false);
        $option2->setSortOrder(2);
        $option3 = new Option();
        $option3->setContent('Option 3');
        $option3->setIsCorrect(false);
        $option3->setSortOrder(0);

        $this->assertEquals(1, $option1->getSortOrder());
        $this->assertEquals(2, $option2->getSortOrder());
        $this->assertEquals(0, $option3->getSortOrder());
    }

    public function testOptionWithLongContent(): void
    {
        $longContent = str_repeat('This is a very long option content. ', 50);
        $option = new Option();
        $option->setContent($longContent);

        $this->assertEquals($longContent, $option->getContent());
        $this->assertEquals($longContent, (string) $option);
    }

    public function testOptionWithEmptyContent(): void
    {
        $option = new Option();
        $option->setContent('');

        $this->assertEquals('', $option->getContent());
        $this->assertEquals('', (string) $option);
    }

    public function testMultiplePropertyUpdates(): void
    {
        $option = new Option();
        $option->setContent('Initial Content');
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

    public function testUpdateTimeChangesOnPropertyUpdate(): void
    {
        $option = new Option();
        $option->setContent('Test Content');
        $originalUpdateTime = $option->getUpdateTime();

        usleep(1000); // 确保时间差异
        $option->setIsCorrect(true);

        $this->assertGreaterThan($originalUpdateTime, $option->getUpdateTime());
    }
}
