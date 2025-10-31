<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Service;

use Tourze\QuestionBankBundle\DTO\QuestionDTO;
use Tourze\QuestionBankBundle\DTO\SearchCriteria;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Exception\QuestionNotFoundException;
use Tourze\QuestionBankBundle\Exception\ValidationException;
use Tourze\QuestionBankBundle\ValueObject\PaginatedResult;

interface QuestionServiceInterface
{
    /**
     * @throws ValidationException
     */
    public function createQuestion(QuestionDTO $dto): Question;

    /**
     * @throws QuestionNotFoundException
     * @throws ValidationException
     */
    public function updateQuestion(string $id, QuestionDTO $dto): Question;

    /**
     * @throws QuestionNotFoundException
     */
    public function deleteQuestion(string $id): void;

    /**
     * @throws QuestionNotFoundException
     */
    public function findQuestion(string $id): Question;

    /**
     * @return PaginatedResult<Question>
     */
    public function searchQuestions(SearchCriteria $criteria): PaginatedResult;

    /**
     * @throws QuestionNotFoundException
     */
    public function publishQuestion(string $id): Question;

    /**
     * @throws QuestionNotFoundException
     */
    public function archiveQuestion(string $id): Question;

    /**
     * @return array<Question>
     */
    public function getRandomQuestions(int $count, ?SearchCriteria $criteria = null): array;
}
