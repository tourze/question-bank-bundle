<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Entity\Option;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Entity\Tag;
use Tourze\QuestionBankBundle\Enum\QuestionStatus;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

class QuestionTest extends TestCase
{
    public function test_constructor_setsDefaultValues(): void
    {
        $difficulty = new Difficulty(3);
        $question = new Question('Test Title', 'Test Content', QuestionType::SINGLE_CHOICE, $difficulty);

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

    public function test_setTitle_updatesTitle(): void
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
        return new Question('Test Question', 'Test Content', QuestionType::SINGLE_CHOICE, $difficulty);
    }

    public function test_setContent_updatesContent(): void
    {
        $question = $this->createTestQuestion();

        $question->setContent('New Content');

        $this->assertEquals('New Content', $question->getContent());
    }

    public function test_setDifficulty_updatesDifficulty(): void
    {
        $question = $this->createTestQuestion();
        $newDifficulty = new Difficulty(5);

        $question->setDifficulty($newDifficulty);

        $this->assertEquals(5, $question->getDifficulty()->getLevel());
    }

    public function test_setScore_updatesScore(): void
    {
        $question = $this->createTestQuestion();

        $question->setScore(15.5);

        $this->assertEquals(15.5, $question->getScore());
    }

    public function test_setExplanation_updatesExplanation(): void
    {
        $question = $this->createTestQuestion();

        $question->setExplanation('Test Explanation');

        $this->assertEquals('Test Explanation', $question->getExplanation());
    }

    public function test_setMetadata_updatesMetadata(): void
    {
        $question = $this->createTestQuestion();
        $metadata = ['key' => 'value'];

        $question->setMetadata($metadata);

        $this->assertEquals($metadata, $question->getMetadata());
    }

    public function test_publish_changesStatusFromDraft(): void
    {
        $question = $this->createTestQuestion();

        $question->publish();

        $this->assertEquals(QuestionStatus::PUBLISHED, $question->getStatus());
    }

    public function test_publish_throwsExceptionWhenNotDraft(): void
    {
        $question = $this->createTestQuestion();
        $question->publish(); // 先发布

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Only draft questions can be published');

        $question->publish(); // 再次发布应该失败
    }

    public function test_archive_changesStatusFromPublished(): void
    {
        $question = $this->createTestQuestion();
        $question->publish();

        $question->archive();

        $this->assertEquals(QuestionStatus::ARCHIVED, $question->getStatus());
    }

    public function test_archive_throwsExceptionWhenNotPublished(): void
    {
        $question = $this->createTestQuestion();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Only published questions can be archived');

        $question->archive();
    }

    public function test_addCategory_addsNewCategory(): void
    {
        $question = $this->createTestQuestion();
        $category = new Category('Test Category', 'test_category');

        $question->addCategory($category);

        $this->assertCount(1, $question->getCategories());
        $this->assertTrue($question->getCategories()->contains($category));
    }

    public function test_addCategory_doesNotAddDuplicateCategory(): void
    {
        $question = $this->createTestQuestion();
        $category = new Category('Test Category', 'test_category');

        $question->addCategory($category);
        $question->addCategory($category); // 添加重复分类

        $this->assertCount(1, $question->getCategories());
    }

    public function test_removeCategory_removesExistingCategory(): void
    {
        $question = $this->createTestQuestion();
        $category = new Category('Test Category', 'test_category');
        $question->addCategory($category);

        $question->removeCategory($category);

        $this->assertCount(0, $question->getCategories());
        $this->assertFalse($question->getCategories()->contains($category));
    }

    public function test_addTag_addsNewTagAndIncrementsUsageCount(): void
    {
        $question = $this->createTestQuestion();
        $tag = new Tag('Test Tag');
        $originalUsageCount = $tag->getUsageCount();

        $question->addTag($tag);

        $this->assertCount(1, $question->getTags());
        $this->assertTrue($question->getTags()->contains($tag));
        $this->assertEquals($originalUsageCount + 1, $tag->getUsageCount());
    }

    public function test_removeTag_removesExistingTagAndDecrementsUsageCount(): void
    {
        $question = $this->createTestQuestion();
        $tag = new Tag('Test Tag');
        $question->addTag($tag);
        $usageCountAfterAdd = $tag->getUsageCount();

        $question->removeTag($tag);

        $this->assertCount(0, $question->getTags());
        $this->assertFalse($question->getTags()->contains($tag));
        $this->assertEquals($usageCountAfterAdd - 1, $tag->getUsageCount());
    }

    public function test_addOption_addsNewOption(): void
    {
        $question = $this->createTestQuestion();
        $option = new Option('Test Option', true);

        $question->addOption($option);

        $this->assertCount(1, $question->getOptions());
        $this->assertTrue($question->getOptions()->contains($option));
        $this->assertEquals($question, $option->getQuestion());
    }

    public function test_removeOption_removesExistingOption(): void
    {
        $question = $this->createTestQuestion();
        $option = new Option('Test Option', true);
        $question->addOption($option);

        $question->removeOption($option);

        $this->assertCount(0, $question->getOptions());
        $this->assertFalse($question->getOptions()->contains($option));
        $this->assertNull($option->getQuestion());
    }

    public function test_getCorrectOptions_returnsOnlyCorrectOptions(): void
    {
        $question = $this->createTestQuestion();
        $correctOption1 = new Option('Correct 1', true);
        $wrongOption = new Option('Wrong', false);
        $correctOption2 = new Option('Correct 2', true);

        $question->addOption($correctOption1);
        $question->addOption($wrongOption);
        $question->addOption($correctOption2);

        $correctOptions = $question->getCorrectOptions();

        $this->assertCount(2, $correctOptions);
        $this->assertTrue($correctOptions->contains($correctOption1));
        $this->assertTrue($correctOptions->contains($correctOption2));
        $this->assertFalse($correctOptions->contains($wrongOption));
    }

    public function test_hasCorrectOption_returnsTrueWhenCorrectOptionExists(): void
    {
        $question = $this->createTestQuestion();
        $correctOption = new Option('Correct', true);
        $question->addOption($correctOption);

        $this->assertTrue($question->hasCorrectOption());
    }

    public function test_hasCorrectOption_returnsFalseWhenNoCorrectOption(): void
    {
        $question = $this->createTestQuestion();
        $wrongOption = new Option('Wrong', false);
        $question->addOption($wrongOption);

        $this->assertFalse($question->hasCorrectOption());
    }

    public function test_setValid_updatesValidFlag(): void
    {
        $question = $this->createTestQuestion();

        $question->setValid(false);

        $this->assertFalse($question->isValid());
    }

    public function test_toString_returnsTitle(): void
    {
        $question = $this->createTestQuestion();

        $this->assertEquals('Test Question', (string) $question);
    }

    public function test_isEditable_returnsTrueForDraftStatus(): void
    {
        $question = $this->createTestQuestion();

        $this->assertTrue($question->isEditable());
    }

    public function test_isEditable_returnsFalseForPublishedStatus(): void
    {
        $question = $this->createTestQuestion();
        $question->publish();

        $this->assertFalse($question->isEditable());
    }

    public function test_isUsable_returnsTrueForPublishedStatus(): void
    {
        $question = $this->createTestQuestion();
        $question->publish();

        $this->assertTrue($question->isUsable());
    }

    public function test_isUsable_returnsFalseForDraftStatus(): void
    {
        $question = $this->createTestQuestion();

        $this->assertFalse($question->isUsable());
    }
}
