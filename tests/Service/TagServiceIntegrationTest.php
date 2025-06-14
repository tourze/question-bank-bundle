<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Service;

use Symfony\Component\Uid\Uuid;
use Tourze\QuestionBankBundle\DTO\TagDTO;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Entity\Tag;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Exception\TagNotFoundException;
use Tourze\QuestionBankBundle\Exception\ValidationException;
use Tourze\QuestionBankBundle\Service\TagService;
use Tourze\QuestionBankBundle\Tests\BaseIntegrationTestCase;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

class TagServiceIntegrationTest extends BaseIntegrationTestCase
{
    private TagService $tagService;

    public function test_createTag_withValidData_createsTag(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';
        $dto->description = 'PHP programming language';
        $dto->color = '#787CB5';

        // Act
        $tag = $this->tagService->createTag($dto);

        // Assert
        $this->assertNotNull($tag->getId());
        $this->assertEquals('PHP', $tag->getName());
        $this->assertEquals('php', $tag->getSlug());
        $this->assertEquals('PHP programming language', $tag->getDescription());
        $this->assertEquals('#787CB5', $tag->getColor());
        $this->assertEquals(0, $tag->getUsageCount());
    }

    public function test_createTag_withoutSlug_generatesSlugFromName(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'Object Oriented Programming';

        // Act
        $tag = $this->tagService->createTag($dto);

        // Assert
        $this->assertEquals('Object Oriented Programming', $tag->getName());
        $this->assertEquals('object-oriented-programming', $tag->getSlug());
    }

    public function test_createTag_withDuplicateSlug_throwsValidationException(): void
    {
        // Arrange
        $dto1 = new TagDTO();
        $dto1->name = 'PHP';
        $dto1->slug = 'php';

        $dto2 = new TagDTO();
        $dto2->name = 'PHP Programming';
        $dto2->slug = 'php'; // 重复的 slug

        $this->tagService->createTag($dto1);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Tag with slug "php" already exists');
        $this->tagService->createTag($dto2);
    }

    public function test_updateTag_withValidData_updatesTag(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';

        $tag = $this->tagService->createTag($dto);

        $updateDto = new TagDTO();
        $updateDto->name = 'PHP Language';
        $updateDto->slug = 'php-language';
        $updateDto->description = 'Updated description';
        $updateDto->color = '#FF0000';

        // Act
        $updatedTag = $this->tagService->updateTag((string) $tag->getId(), $updateDto);

        // Assert
        $this->assertEquals('PHP Language', $updatedTag->getName());
        $this->assertEquals('php-language', $updatedTag->getSlug());
        $this->assertEquals('Updated description', $updatedTag->getDescription());
        $this->assertEquals('#FF0000', $updatedTag->getColor());
    }

    public function test_updateTag_withDuplicateSlug_throwsValidationException(): void
    {
        // Arrange
        $dto1 = new TagDTO();
        $dto1->name = 'PHP';
        $dto1->slug = 'php';

        $dto2 = new TagDTO();
        $dto2->name = 'Java';
        $dto2->slug = 'java';

        $tag1 = $this->tagService->createTag($dto1);
        $tag2 = $this->tagService->createTag($dto2);

        $updateDto = new TagDTO();
        $updateDto->name = 'Java Updated';
        $updateDto->slug = 'php'; // 尝试使用已存在的 slug

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Tag with slug "php" already exists');
        $this->tagService->updateTag((string) $tag2->getId(), $updateDto);
    }

    public function test_deleteTag_withUnusedTag_deletesTag(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';

        $tag = $this->tagService->createTag($dto);
        $id = $tag->getId();

        // Act
        $this->tagService->deleteTag((string) $id);

        // Assert
        $this->expectException(TagNotFoundException::class);
        $this->tagService->findTag((string) $id);
    }

    public function test_deleteTag_withUsedTag_throwsValidationException(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';

        $tag = $this->tagService->createTag($dto);

        // 创建一个问题并添加标签
        $question = new Question(
            'Test Question',
            'Test content',
            QuestionType::SINGLE_CHOICE,
            new Difficulty(3)
        );
        $question->addTag($tag);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Tag is used by 1 questions');
        $this->tagService->deleteTag((string) $tag->getId());
    }

    public function test_findTag_withValidId_returnsTag(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';

        $created = $this->tagService->createTag($dto);

        // Act
        $found = $this->tagService->findTag((string) $created->getId());

        // Assert
        $this->assertEquals($created->getId(), $found->getId());
        $this->assertEquals('PHP', $found->getName());
    }

    public function test_findTag_withInvalidId_throwsNotFoundException(): void
    {
        // Arrange - 使用有效的 UUID 格式但不存在的 ID
        $nonExistentId = Uuid::v7();

        // Act & Assert
        $this->expectException(TagNotFoundException::class);
        $this->tagService->findTag((string) $nonExistentId);
    }

    public function test_findOrCreateTag_withExistingTag_returnsExistingTag(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';

        $existing = $this->tagService->createTag($dto);

        // Act
        $found = $this->tagService->findOrCreateTag('PHP');

        // Assert
        $this->assertEquals($existing->getId(), $found->getId());
        $this->assertEquals('PHP', $found->getName());
    }

    public function test_findOrCreateTag_withNewTag_createsNewTag(): void
    {
        // Act
        $tag = $this->tagService->findOrCreateTag('JavaScript');

        // Assert
        $this->assertNotNull($tag->getId());
        $this->assertEquals('JavaScript', $tag->getName());
        $this->assertEquals('javascript', $tag->getSlug());
    }

    public function test_getPopularTags_returnsTagsByUsage(): void
    {
        // Arrange
        $tag1 = $this->createTagWithUsage('PHP', 5);
        $tag2 = $this->createTagWithUsage('Java', 3);
        $tag3 = $this->createTagWithUsage('Python', 7);

        // Act
        $popularTags = $this->tagService->getPopularTags(3);

        // Assert
        $this->assertCount(3, $popularTags);
        $this->assertEquals('Python', $popularTags[0]->getName()); // 最多使用
        $this->assertEquals('PHP', $popularTags[1]->getName());
        $this->assertEquals('Java', $popularTags[2]->getName());
    }

    private function createTagWithUsage(string $name, int $usageCount): Tag
    {
        $dto = TagDTO::create($name);
        $tag = $this->tagService->createTag($dto);

        // 直接设置使用计数（用于测试）
        $reflection = new \ReflectionClass($tag);
        $usageProperty = $reflection->getProperty('usageCount');
        $usageProperty->setAccessible(true);
        $usageProperty->setValue($tag, $usageCount);

        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        return $tag;
    }

    public function test_mergeTag_withValidTags_mergesTags(): void
    {
        // Arrange
        $sourceTag = $this->createTagWithUsage('JavaScript', 2);
        $targetTag = $this->createTagWithUsage('JS', 1);

        // 创建问题并分别添加标签
        $question1 = $this->createQuestionWithTag('Q1', $sourceTag);
        $question2 = $this->createQuestionWithTag('Q2', $sourceTag);
        $question3 = $this->createQuestionWithTag('Q3', $targetTag);

        $sourceId = $sourceTag->getId();
        $targetId = $targetTag->getId();

        // Act
        $this->tagService->mergeTag((string) $sourceId, (string) $targetId);

        // Assert
        $this->expectException(TagNotFoundException::class);
        $this->tagService->findTag((string) $sourceId); // 源标签应该被删除

        // 目标标签应该包含所有问题
        $updatedTargetTag = $this->tagService->findTag((string) $targetId);
        $this->assertEquals(3, $updatedTargetTag->getUsageCount());
    }

    private function createQuestionWithTag(string $title, Tag $tag): Question
    {
        $question = new Question(
            $title,
            'Test content',
            QuestionType::SINGLE_CHOICE,
            new Difficulty(3)
        );
        $question->addTag($tag);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        return $question;
    }

    public function test_mergeTag_withSameTag_throwsValidationException(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';

        $tag = $this->tagService->createTag($dto);
        $id = $tag->getId();

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cannot merge tag with itself');
        $this->tagService->mergeTag((string) $id, (string) $id);
    }

    public function test_searchTags_withKeyword_returnsMatchingTags(): void
    {
        // Arrange
        $this->tagService->createTag(TagDTO::create('PHP'));
        $this->tagService->createTag(TagDTO::create('PHP Framework'));
        $this->tagService->createTag(TagDTO::create('Java'));
        $this->tagService->createTag(TagDTO::create('JavaScript'));

        // Act
        $results = $this->tagService->searchTags('PHP', 10);

        // Assert
        $this->assertCount(2, $results);
        $names = array_map(fn($tag) => $tag->getName(), $results);
        $this->assertContains('PHP', $names);
        $this->assertContains('PHP Framework', $names);
    }

    public function test_findTagBySlug_withExistingSlug_returnsTag(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';

        $created = $this->tagService->createTag($dto);

        // Act
        $found = $this->tagService->findTagBySlug('php');

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals($created->getId(), $found->getId());
        $this->assertEquals('php', $found->getSlug());
    }

    public function test_findTagBySlug_withNonExistentSlug_returnsNull(): void
    {
        // Act
        $result = $this->tagService->findTagBySlug('non-existent');

        // Assert
        $this->assertNull($result);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagService = $this->container->get(TagService::class);
    }
}