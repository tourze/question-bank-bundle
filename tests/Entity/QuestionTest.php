<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Entity\Option;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Entity\Tag;
use Tourze\QuestionBankBundle\Enum\QuestionStatus;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Exception\QuestionStateException;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

/**
 * @internal
 */
#[CoversClass(Question::class)]
final class QuestionTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        $difficulty = new Difficulty(3);
        $question = new Question();
        $question->setTitle('Test Question');
        $question->setContent('Test Content');
        $question->setType(QuestionType::SINGLE_CHOICE);
        $question->setDifficulty($difficulty);

        return $question;
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     *
     * @return iterable<string, array{0: string, 1: mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'title' => ['title', 'Updated Question Title'];
        yield 'content' => ['content', 'Updated question content'];
        yield 'score' => ['score', 15.50];
        yield 'explanation' => ['explanation', 'This is an explanation for the question'];
        yield 'metadata' => ['metadata', ['key1' => 'value1', 'key2' => 'value2']];
        yield 'valid' => ['valid', false];
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $difficulty = new Difficulty(3);
        $question = new Question();
        $question->setTitle('Test Title');
        $question->setContent('Test Content');
        $question->setType(QuestionType::SINGLE_CHOICE);
        $question->setDifficulty($difficulty);

        $this->assertEquals('Test Title', $question->getTitle());
        $this->assertEquals('Test Content', $question->getContent());
        $this->assertEquals(QuestionType::SINGLE_CHOICE, $question->getType());
        $this->assertEquals(3, $question->getDifficulty()->getLevel());
        $this->assertEquals(10.0, $question->getScore());
        $this->assertEquals(QuestionStatus::DRAFT, $question->getStatus());
        $this->assertTrue($question->isValid());
        $this->assertInstanceOf(\DateTimeImmutable::class, $question->getCreateTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $question->getUpdateTime());
        $this->assertCount(0, $question->getCategories());
        $this->assertCount(0, $question->getTags());
        $this->assertCount(0, $question->getOptions());
    }

    public function testSetTitleUpdatesTitle(): void
    {
        $question = $this->createTestQuestion();
        $originalUpdateTime = $question->getUpdateTime();

        usleep(1000); // 确保时间差异
        $question->setTitle('New Title');

        $this->assertEquals('New Title', $question->getTitle());
        $this->assertGreaterThan($originalUpdateTime, $question->getUpdateTime());
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

    public function testSetContentUpdatesContent(): void
    {
        $question = $this->createTestQuestion();

        $question->setContent('New Content');

        $this->assertEquals('New Content', $question->getContent());
    }

    public function testSetDifficultyUpdatesDifficulty(): void
    {
        $question = $this->createTestQuestion();
        $newDifficulty = new Difficulty(5);

        $question->setDifficulty($newDifficulty);

        $this->assertEquals(5, $question->getDifficulty()->getLevel());
    }

    public function testSetScoreUpdatesScore(): void
    {
        $question = $this->createTestQuestion();

        $question->setScore(15.5);

        $this->assertEquals(15.5, $question->getScore());
    }

    public function testSetExplanationUpdatesExplanation(): void
    {
        $question = $this->createTestQuestion();

        $question->setExplanation('Test Explanation');

        $this->assertEquals('Test Explanation', $question->getExplanation());
    }

    public function testSetMetadataUpdatesMetadata(): void
    {
        $question = $this->createTestQuestion();
        $metadata = ['key' => 'value'];

        $question->setMetadata($metadata);

        $this->assertEquals($metadata, $question->getMetadata());
    }

    public function testPublishChangesStatusFromDraft(): void
    {
        $question = $this->createTestQuestion();

        $question->publish();

        $this->assertEquals(QuestionStatus::PUBLISHED, $question->getStatus());
    }

    public function testPublishThrowsExceptionWhenNotDraft(): void
    {
        $question = $this->createTestQuestion();
        $question->publish(); // 先发布

        $this->expectException(QuestionStateException::class);
        $this->expectExceptionMessage('Only draft questions can be published');

        $question->publish(); // 再次发布应该失败
    }

    public function testArchiveChangesStatusFromPublished(): void
    {
        $question = $this->createTestQuestion();
        $question->publish();

        $question->archive();

        $this->assertEquals(QuestionStatus::ARCHIVED, $question->getStatus());
    }

    public function testArchiveThrowsExceptionWhenNotPublished(): void
    {
        $question = $this->createTestQuestion();

        $this->expectException(QuestionStateException::class);
        $this->expectExceptionMessage('Only published questions can be archived');

        $question->archive();
    }

    public function testAddCategoryAddsNewCategory(): void
    {
        $question = $this->createTestQuestion();
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test_category');

        $question->addCategory($category);

        $this->assertCount(1, $question->getCategories());
        $this->assertTrue($question->getCategories()->contains($category));
    }

    public function testAddCategoryDoesNotAddDuplicateCategory(): void
    {
        $question = $this->createTestQuestion();
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test_category');

        $question->addCategory($category);
        $question->addCategory($category); // 添加重复分类

        $this->assertCount(1, $question->getCategories());
    }

    public function testRemoveCategoryRemovesExistingCategory(): void
    {
        $question = $this->createTestQuestion();
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test_category');
        $question->addCategory($category);

        $question->removeCategory($category);

        $this->assertCount(0, $question->getCategories());
        $this->assertFalse($question->getCategories()->contains($category));
    }

    public function testAddTagAddsNewTagAndIncrementsUsageCount(): void
    {
        $question = $this->createTestQuestion();
        $tag = new Tag();
        $tag->setName('Test Tag');
        $originalUsageCount = $tag->getUsageCount();

        $question->addTag($tag);

        $this->assertCount(1, $question->getTags());
        $this->assertTrue($question->getTags()->contains($tag));
        $this->assertEquals($originalUsageCount + 1, $tag->getUsageCount());
    }

    public function testRemoveTagRemovesExistingTagAndDecrementsUsageCount(): void
    {
        $question = $this->createTestQuestion();
        $tag = new Tag();
        $tag->setName('Test Tag');
        $question->addTag($tag);
        $usageCountAfterAdd = $tag->getUsageCount();

        $question->removeTag($tag);

        $this->assertCount(0, $question->getTags());
        $this->assertFalse($question->getTags()->contains($tag));
        $this->assertEquals($usageCountAfterAdd - 1, $tag->getUsageCount());
    }

    public function testAddOptionAddsNewOption(): void
    {
        $question = $this->createTestQuestion();
        $option = new Option();
        $option->setContent('Test Option');
        $option->setIsCorrect(true);

        $question->addOption($option);

        $this->assertCount(1, $question->getOptions());
        $this->assertTrue($question->getOptions()->contains($option));
        $this->assertEquals($question, $option->getQuestion());
    }

    public function testRemoveOptionRemovesExistingOption(): void
    {
        $question = $this->createTestQuestion();
        $option = new Option();
        $option->setContent('Test Option');
        $option->setIsCorrect(true);
        $question->addOption($option);

        $question->removeOption($option);

        $this->assertCount(0, $question->getOptions());
        $this->assertFalse($question->getOptions()->contains($option));
        $this->assertNull($option->getQuestion());
    }

    public function testGetCorrectOptionsReturnsOnlyCorrectOptions(): void
    {
        $question = $this->createTestQuestion();
        $correctOption1 = new Option();
        $correctOption1->setContent('Correct 1');
        $correctOption1->setIsCorrect(true);
        $wrongOption = new Option();
        $wrongOption->setContent('Wrong');
        $wrongOption->setIsCorrect(false);
        $correctOption2 = new Option();
        $correctOption2->setContent('Correct 2');
        $correctOption2->setIsCorrect(true);

        $question->addOption($correctOption1);
        $question->addOption($wrongOption);
        $question->addOption($correctOption2);

        $correctOptions = $question->getCorrectOptions();

        $this->assertCount(2, $correctOptions);
        $this->assertTrue($correctOptions->contains($correctOption1));
        $this->assertTrue($correctOptions->contains($correctOption2));
        $this->assertFalse($correctOptions->contains($wrongOption));
    }

    public function testHasCorrectOptionReturnsTrueWhenCorrectOptionExists(): void
    {
        $question = $this->createTestQuestion();
        $correctOption = new Option();
        $correctOption->setContent('Correct');
        $correctOption->setIsCorrect(true);
        $question->addOption($correctOption);

        $this->assertTrue($question->hasCorrectOption());
    }

    public function testHasCorrectOptionReturnsFalseWhenNoCorrectOption(): void
    {
        $question = $this->createTestQuestion();
        $wrongOption = new Option();
        $wrongOption->setContent('Wrong');
        $wrongOption->setIsCorrect(false);
        $question->addOption($wrongOption);

        $this->assertFalse($question->hasCorrectOption());
    }

    public function testSetValidUpdatesValidFlag(): void
    {
        $question = $this->createTestQuestion();

        $question->setValid(false);

        $this->assertFalse($question->isValid());
    }

    public function testToStringReturnsTitle(): void
    {
        $question = $this->createTestQuestion();

        $this->assertEquals('Test Question', (string) $question);
    }

    public function testIsEditableReturnsTrueForDraftStatus(): void
    {
        $question = $this->createTestQuestion();

        $this->assertTrue($question->isEditable());
    }

    public function testIsEditableReturnsFalseForPublishedStatus(): void
    {
        $question = $this->createTestQuestion();
        $question->publish();

        $this->assertFalse($question->isEditable());
    }

    public function testIsUsableReturnsTrueForPublishedStatus(): void
    {
        $question = $this->createTestQuestion();
        $question->publish();

        $this->assertTrue($question->isUsable());
    }

    public function testIsUsableReturnsFalseForDraftStatus(): void
    {
        $question = $this->createTestQuestion();

        $this->assertFalse($question->isUsable());
    }
}
