<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Integration\Service;

use Tourze\QuestionBankBundle\DTO\OptionDTO;
use Tourze\QuestionBankBundle\DTO\QuestionDTO;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Service\QuestionService;
use Tourze\QuestionBankBundle\Tests\BaseIntegrationTestCase;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

class QuestionServiceTest extends BaseIntegrationTestCase
{
    private QuestionService $questionService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->questionService = $this->getContainer()->get(QuestionService::class);
    }
    
    public function testCreateQuestion(): void
    {
        $options = [
            OptionDTO::create('Option A', true),
            OptionDTO::create('Option B', false),
        ];
        
        $dto = QuestionDTO::create(
            'Test Question',
            'What is the test?',
            QuestionType::MULTIPLE_CHOICE
        );
        $dto->difficulty = 3;
        $dto->options = $options;
        
        $question = $this->questionService->createQuestion($dto);
        
        $this->assertSame('Test Question', $question->getTitle());
        $this->assertSame('What is the test?', $question->getContent());
        $this->assertSame(QuestionType::MULTIPLE_CHOICE, $question->getType());
        $this->assertCount(2, $question->getOptions());
    }
    
    public function testFindQuestionById(): void
    {
        $options = [
            OptionDTO::create('Option A', true),
            OptionDTO::create('Option B', false)
        ];
        $dto = QuestionDTO::create(
            'Test Question',
            'What is the test?',
            QuestionType::MULTIPLE_CHOICE
        );
        $dto->difficulty = 1;
        $dto->options = $options;
        
        $createdQuestion = $this->questionService->createQuestion($dto);
        $foundQuestion = $this->questionService->findQuestion((string) $createdQuestion->getId());
        
        $this->assertSame($createdQuestion->getId(), $foundQuestion->getId());
        $this->assertSame('Test Question', $foundQuestion->getTitle());
    }
}