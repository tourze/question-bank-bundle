<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\QuestionBankBundle\DTO\OptionDTO;
use Tourze\QuestionBankBundle\DTO\QuestionDTO;
use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Entity\Tag;
use Tourze\QuestionBankBundle\Enum\QuestionStatus;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Exception\QuestionNotFoundException;
use Tourze\QuestionBankBundle\Exception\QuestionStateException;
use Tourze\QuestionBankBundle\Exception\ValidationException;
use Tourze\QuestionBankBundle\Service\QuestionServiceInterface;

/**
 * @internal
 */
#[CoversClass(QuestionServiceInterface::class)]
#[RunTestsInSeparateProcesses]
final class QuestionServiceIntegrationTest extends AbstractIntegrationTestCase
{
    public function testCreateQuestionWithValidDataCreatesQuestion(): void
    {
        $dto = $this->createQuestionDTO();

        $question = self::getService(QuestionServiceInterface::class)->createQuestion($dto);

        $this->assertNotNull($question->getId());
        $this->assertEquals('Test Question', $question->getTitle());
        $this->assertEquals('Test Content', $question->getContent());
        $this->assertEquals(QuestionType::SINGLE_CHOICE, $question->getType());
        $this->assertEquals(3, $question->getDifficulty()->getLevel());
        $this->assertEquals(10.0, $question->getScore());
        $this->assertEquals(QuestionStatus::DRAFT, $question->getStatus());
        $this->assertCount(2, $question->getOptions());
    }

    private function createQuestionDTO(): QuestionDTO
    {
        $dto = new QuestionDTO();
        $dto->title = 'Test Question';
        $dto->content = 'Test Content';
        $dto->type = QuestionType::SINGLE_CHOICE;
        $dto->difficulty = 3;
        $dto->score = 10.0;
        $dto->explanation = 'Test Explanation';
        $dto->metadata = ['key' => 'value'];
        $dto->options = [
            OptionDTO::create('Correct Option', true),
            OptionDTO::create('Wrong Option', false),
        ];
        $dto->categoryIds = [];
        $dto->tagIds = [];

        return $dto;
    }

    public function testCreateQuestionWithCategoriesAndTagsAssociatesCorrectly(): void
    {
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test_category');
        self::getEntityManager()->persist($category);
        self::getEntityManager()->flush();

        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setSlug('test-tag');
        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        $dto = $this->createQuestionDTO();
        $dto->categoryIds = [$category->getId()];
        $dto->tagIds = [$tag->getId()];

        $question = self::getService(QuestionServiceInterface::class)->createQuestion($dto);

        $this->assertCount(1, $question->getCategories());
        $this->assertTrue($question->getCategories()->contains($category));
        $this->assertCount(1, $question->getTags());
        $this->assertTrue($question->getTags()->contains($tag));
        $this->assertEquals(1, $tag->getUsageCount());
    }

    public function testCreateQuestionWithInvalidDataThrowsValidationException(): void
    {
        $dto = new QuestionDTO();
        $dto->title = ''; // 空标题应该失败
        $dto->content = 'Test Content';
        $dto->type = QuestionType::SINGLE_CHOICE;
        $dto->difficulty = 3;
        $dto->score = 10.0;
        $dto->options = [];
        $dto->categoryIds = [];
        $dto->tagIds = [];

        $this->expectException(ValidationException::class);

        self::getService(QuestionServiceInterface::class)->createQuestion($dto);
    }

    public function testCreateQuestionSingleChoiceWithoutOptionsThrowsValidationException(): void
    {
        $dto = $this->createQuestionDTO();
        $dto->options = []; // 单选题需要选项

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('This question type requires options');

        self::getService(QuestionServiceInterface::class)->createQuestion($dto);
    }

    public function testCreateQuestionSingleChoiceWithoutCorrectOptionThrowsValidationException(): void
    {
        $dto = $this->createQuestionDTO();
        $dto->options = [
            OptionDTO::create('Option 1', false),
            OptionDTO::create('Option 2', false),
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('At least one correct option required');

        self::getService(QuestionServiceInterface::class)->createQuestion($dto);
    }

    public function testCreateQuestionSingleChoiceWithMultipleCorrectOptionsThrowsValidationException(): void
    {
        $dto = $this->createQuestionDTO();
        $dto->options = [
            OptionDTO::create('Option 1', true),
            OptionDTO::create('Option 2', true),
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Single choice question can only have one correct option');

        self::getService(QuestionServiceInterface::class)->createQuestion($dto);
    }

    public function testUpdateQuestionWithValidDataUpdatesQuestion(): void
    {
        $question = $this->createTestQuestion();

        $dto = $this->createQuestionDTO();
        $dto->title = 'Updated Title';
        $dto->content = 'Updated Content';
        $dto->difficulty = 5;

        $updatedQuestion = self::getService(QuestionServiceInterface::class)->updateQuestion($question->getId(), $dto);

        $this->assertEquals('Updated Title', $updatedQuestion->getTitle());
        $this->assertEquals('Updated Content', $updatedQuestion->getContent());
        $this->assertEquals(5, $updatedQuestion->getDifficulty()->getLevel());
    }

    private function createTestQuestion(): Question
    {
        $dto = $this->createQuestionDTO();

        return self::getService(QuestionServiceInterface::class)->createQuestion($dto);
    }

    public function testUpdateQuestionPublishedQuestionThrowsValidationException(): void
    {
        $question = $this->createTestQuestion();
        self::getService(QuestionServiceInterface::class)->publishQuestion($question->getId());

        $dto = $this->createQuestionDTO();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Only draft questions can be edited');

        self::getService(QuestionServiceInterface::class)->updateQuestion($question->getId(), $dto);
    }

    public function testDeleteQuestionWithValidIdDeletesQuestion(): void
    {
        $question = $this->createTestQuestion();
        $questionId = $question->getId();

        self::getService(QuestionServiceInterface::class)->deleteQuestion($questionId);

        $this->expectException(QuestionNotFoundException::class);
        self::getService(QuestionServiceInterface::class)->findQuestion($questionId);
    }

    public function testFindQuestionWithValidIdReturnsQuestion(): void
    {
        $question = $this->createTestQuestion();

        $foundQuestion = self::getService(QuestionServiceInterface::class)->findQuestion($question->getId());

        $this->assertEquals($question->getId(), $foundQuestion->getId());
        $this->assertEquals($question->getTitle(), $foundQuestion->getTitle());
    }

    public function testFindQuestionWithInvalidIdThrowsNotFoundException(): void
    {
        $this->expectException(QuestionNotFoundException::class);

        self::getService(QuestionServiceInterface::class)->findQuestion('00000000-0000-0000-0000-000000000000');
    }

    public function testPublishQuestionWithValidDraftQuestionPublishesQuestion(): void
    {
        $question = $this->createTestQuestion();

        $publishedQuestion = self::getService(QuestionServiceInterface::class)->publishQuestion($question->getId());

        $this->assertEquals(QuestionStatus::PUBLISHED, $publishedQuestion->getStatus());
    }

    public function testPublishQuestionWithoutCorrectOptionThrowsValidationException(): void
    {
        // 首先创建一个有正确选项的问题
        $question = $this->createTestQuestion();

        // 手动移除所有正确选项来测试验证
        foreach ($question->getOptions() as $option) {
            $option->setIsCorrect(false);
        }
        self::getEntityManager()->flush();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Question must have at least one correct option');

        self::getService(QuestionServiceInterface::class)->publishQuestion($question->getId());
    }

    public function testArchiveQuestionWithPublishedQuestionArchivesQuestion(): void
    {
        $question = $this->createTestQuestion();
        self::getService(QuestionServiceInterface::class)->publishQuestion($question->getId());

        $archivedQuestion = self::getService(QuestionServiceInterface::class)->archiveQuestion($question->getId());

        $this->assertEquals(QuestionStatus::ARCHIVED, $archivedQuestion->getStatus());
    }

    public function testArchiveQuestionWithDraftQuestionThrowsException(): void
    {
        $question = $this->createTestQuestion();

        $this->expectException(QuestionStateException::class);
        $this->expectExceptionMessage('Only published questions can be archived');

        self::getService(QuestionServiceInterface::class)->archiveQuestion($question->getId());
    }

    public function testGetRandomQuestionsReturnsRequestedNumber(): void
    {
        $this->createMultipleTestQuestions(5);

        $randomQuestions = self::getService(QuestionServiceInterface::class)->getRandomQuestions(3);

        $this->assertCount(3, $randomQuestions);
        foreach ($randomQuestions as $question) {
            $this->assertInstanceOf(Question::class, $question);
        }
    }

    /**
     * @return array<Question>
     */
    private function createMultipleTestQuestions(int $count): array
    {
        $questions = [];
        for ($i = 0; $i < $count; ++$i) {
            $dto = $this->createQuestionDTO();
            $dto->title = "Test Question {$i}";
            $questions[] = self::getService(QuestionServiceInterface::class)->createQuestion($dto);
        }

        return $questions;
    }

    public function testGetRandomQuestionsWithMoreRequestedThanAvailableReturnsAvailable(): void
    {
        $this->createMultipleTestQuestions(2);

        $randomQuestions = self::getService(QuestionServiceInterface::class)->getRandomQuestions(5);

        $this->assertCount(2, $randomQuestions);
    }

    protected function onSetUp(): void
    {
        // 清理测试数据，确保测试隔离
        self::getEntityManager()->createQuery('DELETE FROM Tourze\QuestionBankBundle\Entity\Option o')->execute();
        self::getEntityManager()->createQuery('DELETE FROM Tourze\QuestionBankBundle\Entity\Question q')->execute();
        self::getEntityManager()->createQuery('DELETE FROM Tourze\QuestionBankBundle\Entity\Category c')->execute();
        self::getEntityManager()->createQuery('DELETE FROM Tourze\QuestionBankBundle\Entity\Tag t')->execute();
    }
}
