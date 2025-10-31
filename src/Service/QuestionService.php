<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\QuestionBankBundle\DTO\OptionDTO;
use Tourze\QuestionBankBundle\DTO\QuestionDTO;
use Tourze\QuestionBankBundle\DTO\SearchCriteria;
use Tourze\QuestionBankBundle\Entity\Option;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Event\QuestionCreatedEvent;
use Tourze\QuestionBankBundle\Event\QuestionDeletedEvent;
use Tourze\QuestionBankBundle\Event\QuestionUpdatedEvent;
use Tourze\QuestionBankBundle\Exception\QuestionNotFoundException;
use Tourze\QuestionBankBundle\Exception\ValidationException;
use Tourze\QuestionBankBundle\Repository\CategoryRepository;
use Tourze\QuestionBankBundle\Repository\QuestionRepository;
use Tourze\QuestionBankBundle\Repository\TagRepository;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;
use Tourze\QuestionBankBundle\ValueObject\PaginatedResult;

#[Autoconfigure(public: true)]
class QuestionService implements QuestionServiceInterface
{
    public function __construct(
        private readonly QuestionRepository $questionRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly TagRepository $tagRepository,
        private readonly ValidatorInterface $validator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createQuestion(QuestionDTO $dto): Question
    {
        $this->validateQuestionDTO($dto);

        $question = $this->buildQuestionFromDTO($dto);
        $this->setQuestionRelations($question, $dto);
        $this->validateAndSaveQuestion($question);

        $this->eventDispatcher->dispatch(new QuestionCreatedEvent($question));

        return $question;
    }

    public function updateQuestion(string $id, QuestionDTO $dto): Question
    {
        $question = $this->findQuestion($id);

        if (!$question->isEditable()) {
            throw new ValidationException($this->createViolationList('status', 'Only draft questions can be edited'));
        }

        $this->validateQuestionDTO($dto);
        $this->updateQuestionFromDTO($question, $dto);
        $this->updateQuestionRelations($question, $dto);
        $this->validateAndSaveQuestion($question);

        $this->eventDispatcher->dispatch(new QuestionUpdatedEvent($question));

        return $question;
    }

    private function buildQuestionFromDTO(QuestionDTO $dto): Question
    {
        $question = new Question();
        $question->setTitle($dto->title);
        $question->setContent($dto->content);
        $question->setType($dto->type);
        $question->setDifficulty(new Difficulty($dto->difficulty));

        $question->setScore($dto->score);

        if (null !== $dto->explanation) {
            $question->setExplanation($dto->explanation);
        }

        if (null !== $dto->metadata) {
            $question->setMetadata($dto->metadata);
        }

        return $question;
    }

    private function updateQuestionFromDTO(Question $question, QuestionDTO $dto): void
    {
        $question->setTitle($dto->title);
        $question->setContent($dto->content);
        $question->setDifficulty(new Difficulty($dto->difficulty));
        $question->setScore($dto->score);

        if (null !== $dto->explanation) {
            $question->setExplanation($dto->explanation);
        }

        if (null !== $dto->metadata) {
            $question->setMetadata($dto->metadata);
        }
    }

    private function setQuestionRelations(Question $question, QuestionDTO $dto): void
    {
        $this->setQuestionCategories($question, $dto);
        $this->setQuestionTags($question, $dto);
        $this->setQuestionOptions($question, $dto);
    }

    private function updateQuestionRelations(Question $question, QuestionDTO $dto): void
    {
        $this->clearQuestionRelations($question);
        $this->setQuestionRelations($question, $dto);
    }

    private function clearQuestionRelations(Question $question): void
    {
        foreach ($question->getCategories() as $category) {
            $question->removeCategory($category);
        }

        foreach ($question->getTags() as $tag) {
            $question->removeTag($tag);
        }

        foreach ($question->getOptions() as $option) {
            $question->removeOption($option);
        }
    }

    private function setQuestionCategories(Question $question, QuestionDTO $dto): void
    {
        if (0 === count($dto->categoryIds)) {
            return;
        }

        foreach ($dto->categoryIds as $categoryId) {
            $category = $this->categoryRepository->find($categoryId);
            if (null !== $category) {
                $question->addCategory($category);
            }
        }
    }

    private function setQuestionTags(Question $question, QuestionDTO $dto): void
    {
        foreach ($dto->tagIds as $tagId) {
            $tag = $this->tagRepository->find($tagId);
            if (null !== $tag) {
                $question->addTag($tag);
            }
        }
    }

    private function setQuestionOptions(Question $question, QuestionDTO $dto): void
    {
        foreach ($dto->options as $index => $optionDTO) {
            $option = $this->createOptionFromDTO($optionDTO, $index);
            $question->addOption($option);
        }
    }

    private function validateAndSaveQuestion(Question $question): void
    {
        $this->validateQuestionOptions($question);
        $this->entityManager->persist($question);
        $this->entityManager->flush();
    }

    public function deleteQuestion(string $id): void
    {
        $question = $this->findQuestion($id);

        $this->entityManager->remove($question);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new QuestionDeletedEvent($id));
    }

    public function findQuestion(string $id): Question
    {
        $question = $this->questionRepository->find($id);

        if (null === $question) {
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

        $this->entityManager->persist($question);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new QuestionUpdatedEvent($question));

        return $question;
    }

    public function archiveQuestion(string $id): Question
    {
        $question = $this->findQuestion($id);

        $question->archive();

        $this->entityManager->persist($question);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new QuestionUpdatedEvent($question));

        return $question;
    }

    /**
     * @return array<Question>
     */
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
        if ($dto->type->requiresOptions() && 0 === count($dto->options)) {
            throw new ValidationException($this->createViolationList('options', 'This question type requires options'));
        }

        // 验证选项数量
        if ($dto->type->requiresOptions()) {
            $optionCount = count($dto->options);
            $minOptions = $dto->type->getMinOptions();
            $maxOptions = $dto->type->getMaxOptions();

            if ($optionCount < $minOptions) {
                throw new ValidationException($this->createViolationList('options', sprintf('At least %d options required', $minOptions)));
            }

            if ($optionCount > $maxOptions) {
                throw new ValidationException($this->createViolationList('options', sprintf('Maximum %d options allowed', $maxOptions)));
            }
        }
    }

    private function validateQuestionOptions(Question $question): void
    {
        if (!$question->getType()->requiresOptions()) {
            return;
        }

        if (!$question->hasCorrectOption()) {
            throw new ValidationException($this->createViolationList('options', 'At least one correct option required'));
        }

        // 单选题只能有一个正确答案
        if (QuestionType::SINGLE_CHOICE === $question->getType()) {
            $correctCount = $question->getCorrectOptions()->count();
            if ($correctCount > 1) {
                throw new ValidationException($this->createViolationList('options', 'Single choice question can only have one correct option'));
            }
        }
    }

    private function validateQuestionForPublish(Question $question): void
    {
        if (!$question->isEditable()) {
            throw new ValidationException($this->createViolationList('status', 'Only draft questions can be published'));
        }

        if ($question->getType()->requiresOptions() && !$question->hasCorrectOption()) {
            throw new ValidationException($this->createViolationList('options', 'Question must have at least one correct option'));
        }
    }

    private function createOptionFromDTO(OptionDTO $dto, int $index): Option
    {
        $option = new Option();
        $option->setContent($dto->content);
        $option->setIsCorrect($dto->isCorrect);
        $option->setSortOrder($dto->sortOrder ?? $index);

        if (null !== $dto->explanation) {
            $option->setExplanation($dto->explanation);
        }

        return $option;
    }

    private function createViolationList(string $property, string $message): ConstraintViolationListInterface
    {
        $violationList = new ConstraintViolationList();
        $violation = new ConstraintViolation(
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
