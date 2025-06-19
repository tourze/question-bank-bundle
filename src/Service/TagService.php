<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Service;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\QuestionBankBundle\DTO\TagDTO;
use Tourze\QuestionBankBundle\Entity\Tag;
use Tourze\QuestionBankBundle\Event\TagMergedEvent;
use Tourze\QuestionBankBundle\Exception\TagNotFoundException;
use Tourze\QuestionBankBundle\Exception\ValidationException;
use Tourze\QuestionBankBundle\Repository\TagRepository;

class TagService implements TagServiceInterface
{
    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly ValidatorInterface $validator,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function createTag(TagDTO $dto): Tag
    {
        $this->validateTagDTO($dto);
        
        // 生成 slug
        if ($dto->slug === null) {
            $dto->slug = $this->generateSlug($dto->name);
        }
        
        // 检查 slug 唯一性
        if ($this->tagRepository->findBySlug($dto->slug) !== null) {
            throw new ValidationException(
                $this->createViolationList('slug', sprintf('Tag with slug "%s" already exists', $dto->slug))
            );
        }
        
        $tag = new Tag($dto->name, $dto->slug);
        
        if ($dto->description !== null) {
            $tag->setDescription($dto->description);
        }
        
        if ($dto->color !== null) {
            $tag->setColor($dto->color);
        }
        
        $this->tagRepository->save($tag);
        
        return $tag;
    }

    public function updateTag(string $id, TagDTO $dto): Tag
    {
        $tag = $this->findTag($id);
        
        $this->validateTagDTO($dto);
        
        // 生成 slug
        if ($dto->slug === null) {
            $dto->slug = $this->generateSlug($dto->name);
        }
        
        // 检查 slug 唯一性（排除自身）
        $existingTag = $this->tagRepository->findBySlug($dto->slug);
        if ($existingTag !== null && (string) $existingTag->getId() !== $id) {
            throw new ValidationException(
                $this->createViolationList('slug', sprintf('Tag with slug "%s" already exists', $dto->slug))
            );
        }
        
        $tag->setName($dto->name)
            ->setSlug($dto->slug);
        
        if ($dto->description !== null) {
            $tag->setDescription($dto->description);
        }
        
        if ($dto->color !== null) {
            $tag->setColor($dto->color);
        }
        
        $this->tagRepository->save($tag);
        
        return $tag;
    }

    public function deleteTag(string $id): void
    {
        $tag = $this->findTag($id);
        
        // 检查是否被使用
        if ($tag->getUsageCount() > 0) {
            throw new ValidationException(
                $this->createViolationList('usage', sprintf('Tag is used by %d questions', $tag->getUsageCount()))
            );
        }
        
        $this->tagRepository->remove($tag);
    }

    public function findTag(string $id): Tag
    {
        $tag = $this->tagRepository->find($id);
        
        if ($tag === null) {
            throw TagNotFoundException::withId($id);
        }
        
        return $tag;
    }

    public function findOrCreateTag(string $name): Tag
    {
        // 先按名称查找
        $tags = $this->tagRepository->findByNames([$name]);
        if (!empty($tags)) {
            return $tags[0];
        }
        
        // 创建新标签
        $dto = TagDTO::create($name);
        
        return $this->createTag($dto);
    }

    public function getPopularTags(int $limit = 20): array
    {
        return $this->tagRepository->findPopularTags($limit);
    }

    public function mergeTag(string $sourceId, string $targetId): void
    {
        if ($sourceId === $targetId) {
            throw new ValidationException(
                $this->createViolationList('target', 'Cannot merge tag with itself')
            );
        }
        
        $sourceTag = $this->findTag($sourceId);
        $targetTag = $this->findTag($targetId);
        
        // 将源标签的所有题目转移到目标标签
        foreach ($sourceTag->getQuestions() as $question) {
            $question->removeTag($sourceTag);
            $question->addTag($targetTag);
        }
        
        // 删除源标签
        $this->tagRepository->remove($sourceTag);
        
        $this->eventDispatcher->dispatch(new TagMergedEvent($sourceId, $targetId));
    }

    public function searchTags(string $keyword, int $limit = 10): array
    {
        return $this->tagRepository->search($keyword, $limit);
    }

    public function findTagBySlug(string $slug): ?Tag
    {
        return $this->tagRepository->findBySlug($slug);
    }

    private function validateTagDTO(TagDTO $dto): void
    {
        $violations = $this->validator->validate($dto);
        
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }
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

    private function generateSlug(string $text): string
    {
        $slug = strtolower($text);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug) ?? '';
        $slug = preg_replace('/[\s]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        return $slug;
    }
}