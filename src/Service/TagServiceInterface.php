<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Service;

use Tourze\QuestionBankBundle\DTO\TagDTO;
use Tourze\QuestionBankBundle\Entity\Tag;
use Tourze\QuestionBankBundle\Exception\TagNotFoundException;
use Tourze\QuestionBankBundle\Exception\ValidationException;

interface TagServiceInterface
{
    /**
     * @throws ValidationException
     */
    public function createTag(TagDTO $dto): Tag;

    /**
     * @throws TagNotFoundException
     * @throws ValidationException
     */
    public function updateTag(string $id, TagDTO $dto): Tag;

    /**
     * @throws TagNotFoundException
     */
    public function deleteTag(string $id): void;

    /**
     * @throws TagNotFoundException
     */
    public function findTag(string $id): Tag;

    public function findOrCreateTag(string $name): Tag;

    /**
     * @return array<Tag>
     */
    public function getPopularTags(int $limit = 20): array;

    /**
     * @throws TagNotFoundException
     */
    public function mergeTag(string $sourceId, string $targetId): void;

    /**
     * @return array<Tag>
     */
    public function searchTags(string $keyword, int $limit = 10): array;

    public function findTagBySlug(string $slug): ?Tag;
}