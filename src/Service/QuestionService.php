<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Service;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\QuestionBankBundle\DTO\OptionDTO;
use Tourze\QuestionBankBundle\DTO\QuestionDTO;
use Tourze\QuestionBankBundle\DTO\SearchCriteria;
use Tourze\QuestionBankBundle\Entity\Option;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Event\QuestionCreatedEvent;
use Tourze\QuestionBankBundle\Event\QuestionDeletedEvent;
use Tourze\QuestionBankBundle\Event\QuestionUpdatedEvent;
use Tourze\QuestionBankBundle\Exception\QuestionNotFoundException;
use Tourze\QuestionBankBundle\Exception\ValidationException;
use Tourze\QuestionBankBundle\Repository\CategoryRepository;
use Tourze\QuestionBankBundle\Repository\QuestionRepositoryInterface;
use Tourze\QuestionBankBundle\Repository\TagRepository;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;
use Tourze\QuestionBankBundle\ValueObject\PaginatedResult;

class QuestionService implements QuestionServiceInterface
{
    public function __construct(
        private readonly QuestionRepositoryInterface $questionRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly TagRepository $tagRepository,
        private readonly ValidatorInterface $validator,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function createQuestion(QuestionDTO $dto): Question
    {
        $this->validateQuestionDTO($dto);
        
        $question = new Question(
            $dto->title,
            $dto->content,
            $dto->type,
            new Difficulty($dto->difficulty)
        );
        
        $question->setScore($dto->score);
        
        if ($dto->explanation !== null) {
            $question->setExplanation($dto->explanation);
        }
        
        if ($dto->metadata !== null) {
            $question->setMetadata($dto->metadata);
        }
        
        // 设置分类
        if (!empty($dto->categoryIds)) {
            foreach ($dto->categoryIds as $categoryId) {
                $category = $this->categoryRepository->find($categoryId);
                if ($category !== null) {
                    $question->addCategory($category);
                }
            }
        }
        
        // 添加标签
        foreach ($dto->tagIds as $tagId) {
            $tag = $this->tagRepository->find($tagId);
            if ($tag !== null) {
                $question->addTag($tag);
            }
        }
        
        // 添加选项
        foreach ($dto->options as $index => $optionDTO) {
            $option = $this->createOptionFromDTO($optionDTO, $index);
            $question->addOption($option);
        }
        
        // 验证题目选项
        $this->validateQuestionOptions($question);
        
        $this->questionRepository->save($question);
        
        $this->eventDispatcher->dispatch(new QuestionCreatedEvent($question));
        
        return $question;
    }

    public function updateQuestion(string $id, QuestionDTO $dto): Question
    {
        $question = $this->findQuestion($id);
        
        if (!$question->isEditable()) {
            throw new ValidationException(
                $this->createViolationList('status', 'Only draft questions can be edited')
            );
        }
        
        $this->validateQuestionDTO($dto);
        
        $question->setTitle($dto->title)
            ->setContent($dto->content)
            ->setDifficulty(new Difficulty($dto->difficulty))
            ->setScore($dto->score);
        
        if ($dto->explanation !== null) {
            $question->setExplanation($dto->explanation);
        }
        
        if ($dto->metadata !== null) {
            $question->setMetadata($dto->metadata);
        }
        
        // 更新分类
        foreach ($question->getCategories() as $category) {
            $question->removeCategory($category);
        }
        if (!empty($dto->categoryIds)) {
            foreach ($dto->categoryIds as $categoryId) {
                $category = $this->categoryRepository->find($categoryId);
                if ($category !== null) {
                    $question->addCategory($category);
                }
            }
        }
        
        // 更新标签
        foreach ($question->getTags() as $tag) {
            $question->removeTag($tag);
        }
        foreach ($dto->tagIds as $tagId) {
            $tag = $this->tagRepository->find($tagId);
            if ($tag !== null) {
                $question->addTag($tag);
            }
        }
        
        // 更新选项
        foreach ($question->getOptions() as $option) {
            $question->removeOption($option);
        }
        foreach ($dto->options as $index => $optionDTO) {
            $option = $this->createOptionFromDTO($optionDTO, $index);
            $question->addOption($option);
        }
        
        // 验证题目选项
        $this->validateQuestionOptions($question);
        
        $this->questionRepository->save($question);
        
        $this->eventDispatcher->dispatch(new QuestionUpdatedEvent($question));
        
        return $question;
    }

    public function deleteQuestion(string $id): void
    {
        $question = $this->findQuestion($id);
        
        $this->questionRepository->remove($question);
        
        $this->eventDispatcher->dispatch(new QuestionDeletedEvent($id));
    }

    public function findQuestion(string $id): Question
    {
        $question = $this->questionRepository->find($id);
        
        if ($question === null) {
            throw QuestionNotFoundException::withId($id);
        }
        
        return $question;
    }

    public function searchQuestions(SearchCriteria $criteria): PaginatedResult
    {
        return $this->questionRepository->search($criteria);
    }

    public function publishQuestion(string $id): Question
    {
        $question = $this->findQuestion($id);
        
        // 验证题目是否完整
        $this->validateQuestionForPublish($question);
        
        $question->publish();
        
        $this->questionRepository->save($question);
        
        $this->eventDispatcher->dispatch(new QuestionUpdatedEvent($question));
        
        return $question;
    }

    public function archiveQuestion(string $id): Question
    {
        $question = $this->findQuestion($id);
        
        $question->archive();
        
        $this->questionRepository->save($question);
        
        $this->eventDispatcher->dispatch(new QuestionUpdatedEvent($question));
        
        return $question;
    }

    public function getRandomQuestions(int $count, ?SearchCriteria $criteria = null): array
    {
        return $this->questionRepository->findRandom($count, $criteria);
    }

    private function validateQuestionDTO(QuestionDTO $dto): void
    {
        $violations = $this->validator->validate($dto);
        
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }
        
        // 验证题型是否需要选项
        if ($dto->type->requiresOptions() && empty($dto->options)) {
            throw new ValidationException(
                $this->createViolationList('options', 'This question type requires options')
            );
        }
        
        // 验证选项数量
        if ($dto->type->requiresOptions()) {
            $optionCount = count($dto->options);
            $minOptions = $dto->type->getMinOptions();
            $maxOptions = $dto->type->getMaxOptions();
            
            if ($optionCount < $minOptions) {
                throw new ValidationException(
                    $this->createViolationList('options', sprintf('At least %d options required', $minOptions))
                );
            }
            
            if ($optionCount > $maxOptions) {
                throw new ValidationException(
                    $this->createViolationList('options', sprintf('Maximum %d options allowed', $maxOptions))
                );
            }
        }
    }

    private function validateQuestionOptions(Question $question): void
    {
        if (!$question->getType()->requiresOptions()) {
            return;
        }
        
        if (!$question->hasCorrectOption()) {
            throw new ValidationException(
                $this->createViolationList('options', 'At least one correct option required')
            );
        }
        
        // 单选题只能有一个正确答案
        if ($question->getType() === \Tourze\QuestionBankBundle\Enum\QuestionType::SINGLE_CHOICE) {
            $correctCount = $question->getCorrectOptions()->count();
            if ($correctCount > 1) {
                throw new ValidationException(
                    $this->createViolationList('options', 'Single choice question can only have one correct option')
                );
            }
        }
    }

    private function validateQuestionForPublish(Question $question): void
    {
        if (!$question->isEditable()) {
            throw new ValidationException(
                $this->createViolationList('status', 'Only draft questions can be published')
            );
        }
        
        if ($question->getType()->requiresOptions() && !$question->hasCorrectOption()) {
            throw new ValidationException(
                $this->createViolationList('options', 'Question must have at least one correct option')
            );
        }
    }

    private function createOptionFromDTO(OptionDTO $dto, int $index): Option
    {
        $option = new Option($dto->content, $dto->isCorrect, $dto->sortOrder ?: $index);
        
        if ($dto->explanation !== null) {
            $option->setExplanation($dto->explanation);
        }
        
        return $option;
    }

    private function createViolationList(string $property, string $message): \Symfony\Component\Validator\ConstraintViolationListInterface
    {
        $violationList = new \Symfony\Component\Validator\ConstraintViolationList();
        $violation = new \Symfony\Component\Validator\ConstraintViolation(
            $message,
            null,
            [],
            null,
            $property,
            null
        );
        $violationList->add($violation);
        
        return $violationList;
    }
}