<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Service;

use Tourze\QuestionBankBundle\DTO\OptionDTO;
use Tourze\QuestionBankBundle\DTO\QuestionDTO;
use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Entity\Tag;
use Tourze\QuestionBankBundle\Enum\QuestionStatus;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Exception\QuestionNotFoundException;
use Tourze\QuestionBankBundle\Exception\ValidationException;
use Tourze\QuestionBankBundle\Repository\CategoryRepository;
use Tourze\QuestionBankBundle\Repository\TagRepository;
use Tourze\QuestionBankBundle\Service\QuestionServiceInterface;
use Tourze\QuestionBankBundle\Tests\BaseIntegrationTestCase;

class QuestionServiceIntegrationTest extends BaseIntegrationTestCase
{
    private QuestionServiceInterface $questionService;
    private CategoryRepository $categoryRepository;
    private TagRepository $tagRepository;

    public function test_createQuestion_withValidData_createsQuestion(): void
    {
        $dto = $this->createQuestionDTO();

        $question = $this->questionService->createQuestion($dto);

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

    public function test_createQuestion_withCategoriesAndTags_associatesCorrectly(): void
    {
        $category = new Category('Test Category', 'test_category');
        $this->categoryRepository->save($category);
        
        $tag = new Tag('Test Tag');
        $this->tagRepository->save($tag);
        
        $dto = $this->createQuestionDTO();
        $dto->categoryIds = [(string) $category->getId()];
        $dto->tagIds = [(string) $tag->getId()];
        
        $question = $this->questionService->createQuestion($dto);

        $this->assertCount(1, $question->getCategories());
        $this->assertTrue($question->getCategories()->contains($category));
        $this->assertCount(1, $question->getTags());
        $this->assertTrue($question->getTags()->contains($tag));
        $this->assertEquals(1, $tag->getUsageCount());
    }

    public function test_createQuestion_withInvalidData_throwsValidationException(): void
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
        
        $this->questionService->createQuestion($dto);
    }

    public function test_createQuestion_singleChoiceWithoutOptions_throwsValidationException(): void
    {
        $dto = $this->createQuestionDTO();
        $dto->options = []; // 单选题需要选项

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('This question type requires options');
        
        $this->questionService->createQuestion($dto);
    }

    public function test_createQuestion_singleChoiceWithoutCorrectOption_throwsValidationException(): void
    {
        $dto = $this->createQuestionDTO();
        $dto->options = [
            OptionDTO::create('Option 1', false),
            OptionDTO::create('Option 2', false),
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('At least one correct option required');
        
        $this->questionService->createQuestion($dto);
    }

    public function test_createQuestion_singleChoiceWithMultipleCorrectOptions_throwsValidationException(): void
    {
        $dto = $this->createQuestionDTO();
        $dto->options = [
            OptionDTO::create('Option 1', true),
            OptionDTO::create('Option 2', true),
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Single choice question can only have one correct option');
        
        $this->questionService->createQuestion($dto);
    }

    public function test_updateQuestion_withValidData_updatesQuestion(): void
    {
        $question = $this->createTestQuestion();
        
        $dto = $this->createQuestionDTO();
        $dto->title = 'Updated Title';
        $dto->content = 'Updated Content';
        $dto->difficulty = 5;
        
        $updatedQuestion = $this->questionService->updateQuestion((string) $question->getId(), $dto);

        $this->assertEquals('Updated Title', $updatedQuestion->getTitle());
        $this->assertEquals('Updated Content', $updatedQuestion->getContent());
        $this->assertEquals(5, $updatedQuestion->getDifficulty()->getLevel());
    }

    private function createTestQuestion(): \Tourze\QuestionBankBundle\Entity\Question
    {
        $dto = $this->createQuestionDTO();
        return $this->questionService->createQuestion($dto);
    }

    public function test_updateQuestion_publishedQuestion_throwsValidationException(): void
    {
        $question = $this->createTestQuestion();
        $this->questionService->publishQuestion((string) $question->getId());

        $dto = $this->createQuestionDTO();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Only draft questions can be edited');

        $this->questionService->updateQuestion((string) $question->getId(), $dto);
    }

    public function test_deleteQuestion_withValidId_deletesQuestion(): void
    {
        $question = $this->createTestQuestion();
        $questionId = $question->getId();

        $this->questionService->deleteQuestion((string) $questionId);

        $this->expectException(QuestionNotFoundException::class);
        $this->questionService->findQuestion((string) $questionId);
    }

    public function test_findQuestion_withValidId_returnsQuestion(): void
    {
        $question = $this->createTestQuestion();

        $foundQuestion = $this->questionService->findQuestion((string) $question->getId());

        $this->assertEquals($question->getId(), $foundQuestion->getId());
        $this->assertEquals($question->getTitle(), $foundQuestion->getTitle());
    }

    public function test_findQuestion_withInvalidId_throwsNotFoundException(): void
    {
        $this->expectException(QuestionNotFoundException::class);

        $this->questionService->findQuestion('00000000-0000-0000-0000-000000000000');
    }

    public function test_publishQuestion_withValidDraftQuestion_publishesQuestion(): void
    {
        $question = $this->createTestQuestion();

        $publishedQuestion = $this->questionService->publishQuestion((string) $question->getId());

        $this->assertEquals(QuestionStatus::PUBLISHED, $publishedQuestion->getStatus());
    }

    public function test_publishQuestion_withoutCorrectOption_throwsValidationException(): void
    {
        // 首先创建一个有正确选项的问题
        $question = $this->createTestQuestion();

        // 手动移除所有正确选项来测试验证
        foreach ($question->getOptions() as $option) {
            $option->setIsCorrect(false);
        }
        $this->entityManager->flush();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Question must have at least one correct option');

        $this->questionService->publishQuestion((string) $question->getId());
    }

    public function test_archiveQuestion_withPublishedQuestion_archivesQuestion(): void
    {
        $question = $this->createTestQuestion();
        $this->questionService->publishQuestion((string) $question->getId());

        $archivedQuestion = $this->questionService->archiveQuestion((string) $question->getId());

        $this->assertEquals(QuestionStatus::ARCHIVED, $archivedQuestion->getStatus());
    }

    public function test_archiveQuestion_withDraftQuestion_throwsException(): void
    {
        $question = $this->createTestQuestion();

        $this->expectException(\Tourze\QuestionBankBundle\Exception\QuestionStateException::class);
        $this->expectExceptionMessage('Only published questions can be archived');

        $this->questionService->archiveQuestion((string) $question->getId());
    }

    public function test_getRandomQuestions_returnsRequestedNumber(): void
    {
        $this->createMultipleTestQuestions(5);

        $randomQuestions = $this->questionService->getRandomQuestions(3);

        $this->assertCount(3, $randomQuestions);
        foreach ($randomQuestions as $question) {
            $this->assertInstanceOf(\Tourze\QuestionBankBundle\Entity\Question::class, $question);
        }
    }

    private function createMultipleTestQuestions(int $count): array
    {
        $questions = [];
        for ($i = 0; $i < $count; $i++) {
            $dto = $this->createQuestionDTO();
            $dto->title = "Test Question {$i}";
            $questions[] = $this->questionService->createQuestion($dto);
        }
        return $questions;
    }

    public function test_getRandomQuestions_withMoreRequestedThanAvailable_returnsAvailable(): void
    {
        $this->createMultipleTestQuestions(2);

        $randomQuestions = $this->questionService->getRandomQuestions(5);

        $this->assertCount(2, $randomQuestions);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->questionService = $this->container->get(QuestionServiceInterface::class);
        $this->categoryRepository = $this->container->get(CategoryRepository::class);
        $this->tagRepository = $this->container->get(TagRepository::class);
    }
}