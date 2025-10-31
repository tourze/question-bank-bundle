<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\QuestionBankBundle\DTO\OptionDTO;
use Tourze\QuestionBankBundle\DTO\QuestionDTO;
use Tourze\QuestionBankBundle\DTO\SearchCriteria;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Exception\QuestionNotFoundException;
use Tourze\QuestionBankBundle\Service\QuestionService;

/**
 * @internal
 */
#[CoversClass(QuestionService::class)]
#[RunTestsInSeparateProcesses]
final class QuestionServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 清理测试数据，确保测试隔离
        self::getEntityManager()->createQuery('DELETE FROM Tourze\QuestionBankBundle\Entity\Option o')->execute();
        self::getEntityManager()->createQuery('DELETE FROM Tourze\QuestionBankBundle\Entity\Question q')->execute();
        self::getEntityManager()->createQuery('DELETE FROM Tourze\QuestionBankBundle\Entity\Category c')->execute();
        self::getEntityManager()->createQuery('DELETE FROM Tourze\QuestionBankBundle\Entity\Tag t')->execute();
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

        $question = self::getService(QuestionService::class)->createQuestion($dto);

        $this->assertSame('Test Question', $question->getTitle());
        $this->assertSame('What is the test?', $question->getContent());
        $this->assertSame(QuestionType::MULTIPLE_CHOICE, $question->getType());
        $this->assertCount(2, $question->getOptions());
    }

    public function testFindQuestionById(): void
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
        $dto->difficulty = 1;
        $dto->options = $options;

        $createdQuestion = self::getService(QuestionService::class)->createQuestion($dto);
        $foundQuestion = self::getService(QuestionService::class)->findQuestion((string) $createdQuestion->getId());

        $this->assertSame($createdQuestion->getId(), $foundQuestion->getId());
        $this->assertSame('Test Question', $foundQuestion->getTitle());
    }

    public function testUpdateQuestion(): void
    {
        $options = [
            OptionDTO::create('Option A', true),
            OptionDTO::create('Option B', false),
        ];
        $dto = QuestionDTO::create(
            'Test Question',
            'What is the test?',
            QuestionType::SINGLE_CHOICE
        );
        $dto->difficulty = 1;
        $dto->options = $options;

        $question = self::getService(QuestionService::class)->createQuestion($dto);

        // 更新题目（保持相同类型，因为Question实体的type不能修改）
        $updateDto = QuestionDTO::create(
            'Updated Test Question',
            'What is the updated test?',
            QuestionType::SINGLE_CHOICE
        );
        $updateDto->difficulty = 2;
        $updateDto->options = [
            OptionDTO::create('Updated Option A', true),
            OptionDTO::create('Updated Option B', false),
        ];

        $updatedQuestion = self::getService(QuestionService::class)->updateQuestion((string) $question->getId(), $updateDto);

        $this->assertSame('Updated Test Question', $updatedQuestion->getTitle());
        $this->assertSame('What is the updated test?', $updatedQuestion->getContent());
        $this->assertSame(QuestionType::SINGLE_CHOICE, $updatedQuestion->getType());
        $this->assertSame(2, $updatedQuestion->getDifficulty()->getLevel());
    }

    public function testDeleteQuestion(): void
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
        $dto->difficulty = 1;
        $dto->options = $options;

        $question = self::getService(QuestionService::class)->createQuestion($dto);
        $questionId = (string) $question->getId();

        self::getService(QuestionService::class)->deleteQuestion($questionId);

        $this->expectException(QuestionNotFoundException::class);
        self::getService(QuestionService::class)->findQuestion($questionId);
    }

    public function testPublishQuestion(): void
    {
        $options = [
            OptionDTO::create('Option A', true),
            OptionDTO::create('Option B', false),
        ];
        $dto = QuestionDTO::create(
            'Test Question',
            'What is the test?',
            QuestionType::SINGLE_CHOICE
        );
        $dto->difficulty = 1;
        $dto->options = $options;

        $question = self::getService(QuestionService::class)->createQuestion($dto);

        // 题目初始状态应该是草稿
        $this->assertTrue($question->getStatus()->isEditable());
        $this->assertFalse($question->getStatus()->isUsable());

        // 发布题目
        $publishedQuestion = self::getService(QuestionService::class)->publishQuestion((string) $question->getId());

        $this->assertFalse($publishedQuestion->getStatus()->isEditable());
        $this->assertTrue($publishedQuestion->getStatus()->isUsable());
    }

    public function testArchiveQuestion(): void
    {
        $options = [
            OptionDTO::create('Option A', true),
            OptionDTO::create('Option B', false),
        ];
        $dto = QuestionDTO::create(
            'Test Question',
            'What is the test?',
            QuestionType::SINGLE_CHOICE
        );
        $dto->difficulty = 1;
        $dto->options = $options;

        $question = self::getService(QuestionService::class)->createQuestion($dto);

        // 先发布题目
        $publishedQuestion = self::getService(QuestionService::class)->publishQuestion((string) $question->getId());
        $this->assertTrue($publishedQuestion->getStatus()->isUsable());

        // 然后归档题目
        $archivedQuestion = self::getService(QuestionService::class)->archiveQuestion((string) $publishedQuestion->getId());

        $this->assertFalse($archivedQuestion->getStatus()->isEditable());
        $this->assertFalse($archivedQuestion->getStatus()->isUsable());
    }

    public function testSearchQuestions(): void
    {
        // 创建几个测试题目
        $questions = [];
        for ($i = 1; $i <= 3; ++$i) {
            $options = [
                OptionDTO::create("Option A {$i}", true),
                OptionDTO::create("Option B {$i}", false),
            ];
            $dto = QuestionDTO::create(
                "Test Question {$i}",
                "What is test {$i}?",
                QuestionType::SINGLE_CHOICE
            );
            $dto->difficulty = $i;
            $dto->options = $options;

            $questions[] = self::getService(QuestionService::class)->createQuestion($dto);
        }

        // 测试搜索
        $criteria = new SearchCriteria();
        $criteria->setKeyword('Test Question');
        $criteria->setPage(1);
        $criteria->setLimit(10);

        $result = self::getService(QuestionService::class)->searchQuestions($criteria);

        $this->assertGreaterThanOrEqual(3, $result->getTotal());
        $this->assertGreaterThanOrEqual(3, count($result->getItems()));
    }
}
