<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Uid\Uuid;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\QuestionBankBundle\DTO\TagDTO;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Entity\Tag;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Exception\TagNotFoundException;
use Tourze\QuestionBankBundle\Exception\ValidationException;
use Tourze\QuestionBankBundle\Service\TagService;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

/**
 * @internal
 */
#[CoversClass(TagService::class)]
#[RunTestsInSeparateProcesses]
final class TagServiceIntegrationTest extends AbstractIntegrationTestCase
{
    public function testCreateTagWithValidDataCreatesTag(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';
        $dto->description = 'PHP programming language';
        $dto->color = '#787CB5';

        // Act
        $tag = self::getService(TagService::class)->createTag($dto);

        // Assert
        $this->assertNotNull($tag->getId());
        $this->assertEquals('PHP', $tag->getName());
        $this->assertEquals('php', $tag->getSlug());
        $this->assertEquals('PHP programming language', $tag->getDescription());
        $this->assertEquals('#787CB5', $tag->getColor());
        $this->assertEquals(0, $tag->getUsageCount());
    }

    public function testCreateTagWithoutSlugGeneratesSlugFromName(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'Object Oriented Programming';

        // Act
        $tag = self::getService(TagService::class)->createTag($dto);

        // Assert
        $this->assertEquals('Object Oriented Programming', $tag->getName());
        $this->assertEquals('object-oriented-programming', $tag->getSlug());
    }

    public function testCreateTagWithDuplicateSlugThrowsValidationException(): void
    {
        // Arrange
        $dto1 = new TagDTO();
        $dto1->name = 'PHP';
        $dto1->slug = 'php';

        $dto2 = new TagDTO();
        $dto2->name = 'PHP Programming';
        $dto2->slug = 'php'; // 重复的 slug

        self::getService(TagService::class)->createTag($dto1);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Tag with slug "php" already exists');
        self::getService(TagService::class)->createTag($dto2);
    }

    public function testUpdateTagWithValidDataUpdatesTag(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';

        $tag = self::getService(TagService::class)->createTag($dto);

        $updateDto = new TagDTO();
        $updateDto->name = 'PHP Language';
        $updateDto->slug = 'php-language';
        $updateDto->description = 'Updated description';
        $updateDto->color = '#FF0000';

        // Act
        $updatedTag = self::getService(TagService::class)->updateTag((string) $tag->getId(), $updateDto);

        // Assert
        $this->assertEquals('PHP Language', $updatedTag->getName());
        $this->assertEquals('php-language', $updatedTag->getSlug());
        $this->assertEquals('Updated description', $updatedTag->getDescription());
        $this->assertEquals('#FF0000', $updatedTag->getColor());
    }

    public function testUpdateTagWithDuplicateSlugThrowsValidationException(): void
    {
        // Arrange
        $dto1 = new TagDTO();
        $dto1->name = 'PHP';
        $dto1->slug = 'php';

        $dto2 = new TagDTO();
        $dto2->name = 'Java';
        $dto2->slug = 'java';

        $tag1 = self::getService(TagService::class)->createTag($dto1);
        $tag2 = self::getService(TagService::class)->createTag($dto2);

        $updateDto = new TagDTO();
        $updateDto->name = 'Java Updated';
        $updateDto->slug = 'php'; // 尝试使用已存在的 slug

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Tag with slug "php" already exists');
        self::getService(TagService::class)->updateTag((string) $tag2->getId(), $updateDto);
    }

    public function testDeleteTagWithUnusedTagDeletesTag(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';

        $tag = self::getService(TagService::class)->createTag($dto);
        $id = $tag->getId();

        // Act
        self::getService(TagService::class)->deleteTag((string) $id);

        // Assert
        $this->expectException(TagNotFoundException::class);
        self::getService(TagService::class)->findTag((string) $id);
    }

    public function testDeleteTagWithUsedTagThrowsValidationException(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';

        $tag = self::getService(TagService::class)->createTag($dto);

        // 创建一个问题并添加标签
        $question = new Question();
        $question->setTitle('Test Question');
        $question->setContent('Test content');
        $question->setType(QuestionType::SINGLE_CHOICE);
        $question->setDifficulty(new Difficulty(3));
        $question->addTag($tag);
        self::getEntityManager()->persist($question);
        self::getEntityManager()->flush();

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Tag is used by 1 questions');
        self::getService(TagService::class)->deleteTag((string) $tag->getId());
    }

    public function testFindTagWithValidIdReturnsTag(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';

        $created = self::getService(TagService::class)->createTag($dto);

        // Act
        $found = self::getService(TagService::class)->findTag((string) $created->getId());

        // Assert
        $this->assertEquals($created->getId(), $found->getId());
        $this->assertEquals('PHP', $found->getName());
    }

    public function testFindTagWithInvalidIdThrowsNotFoundException(): void
    {
        // Arrange - 使用有效的 UUID 格式但不存在的 ID
        $nonExistentId = Uuid::v7();

        // Act & Assert
        $this->expectException(TagNotFoundException::class);
        self::getService(TagService::class)->findTag((string) $nonExistentId);
    }

    public function testFindOrCreateTagWithExistingTagReturnsExistingTag(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';

        $existing = self::getService(TagService::class)->createTag($dto);

        // Act
        $found = self::getService(TagService::class)->findOrCreateTag('PHP');

        // Assert
        $this->assertEquals($existing->getId(), $found->getId());
        $this->assertEquals('PHP', $found->getName());
    }

    public function testFindOrCreateTagWithNewTagCreatesNewTag(): void
    {
        // Act
        $tag = self::getService(TagService::class)->findOrCreateTag('JavaScript');

        // Assert
        $this->assertNotNull($tag->getId());
        $this->assertEquals('JavaScript', $tag->getName());
        $this->assertEquals('javascript', $tag->getSlug());
    }

    public function testGetPopularTagsReturnsTagsByUsage(): void
    {
        // Arrange
        $tag1 = $this->createTagWithUsage('PHP', 5);
        $tag2 = $this->createTagWithUsage('Java', 3);
        $tag3 = $this->createTagWithUsage('Python', 7);

        // Act
        $popularTags = self::getService(TagService::class)->getPopularTags(3);

        // Assert
        $this->assertCount(3, $popularTags);
        $this->assertEquals('Python', $popularTags[0]->getName()); // 最多使用
        $this->assertEquals('PHP', $popularTags[1]->getName());
        $this->assertEquals('Java', $popularTags[2]->getName());
    }

    private function createTagWithUsage(string $name, int $usageCount): Tag
    {
        $dto = TagDTO::create($name);
        $tag = self::getService(TagService::class)->createTag($dto);

        // 直接设置使用计数（用于测试）
        $reflection = new \ReflectionClass($tag);
        $usageProperty = $reflection->getProperty('usageCount');
        $usageProperty->setAccessible(true);
        $usageProperty->setValue($tag, $usageCount);

        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        return $tag;
    }

    public function testMergeTagWithValidTagsMergesTags(): void
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
        self::getService(TagService::class)->mergeTag($sourceId, $targetId);

        // Assert
        $this->expectException(TagNotFoundException::class);
        self::getService(TagService::class)->findTag($sourceId); // 源标签应该被删除

        // 目标标签应该包含所有问题
        $updatedTargetTag = self::getService(TagService::class)->findTag($targetId);
        $this->assertEquals(3, $updatedTargetTag->getUsageCount());
    }

    private function createQuestionWithTag(string $title, Tag $tag): Question
    {
        $question = new Question();
        $question->setTitle($title);
        $question->setContent('Test content');
        $question->setType(QuestionType::SINGLE_CHOICE);
        $question->setDifficulty(new Difficulty(3));
        $question->addTag($tag);
        self::getEntityManager()->persist($question);
        self::getEntityManager()->flush();

        return $question;
    }

    public function testMergeTagWithSameTagThrowsValidationException(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';

        $tag = self::getService(TagService::class)->createTag($dto);
        $id = $tag->getId();

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cannot merge tag with itself');
        self::getService(TagService::class)->mergeTag((string) $id, (string) $id);
    }

    public function testSearchTagsWithKeywordReturnsMatchingTags(): void
    {
        // Arrange
        self::getService(TagService::class)->createTag(TagDTO::create('PHP'));
        self::getService(TagService::class)->createTag(TagDTO::create('PHP Framework'));
        self::getService(TagService::class)->createTag(TagDTO::create('Java'));
        self::getService(TagService::class)->createTag(TagDTO::create('JavaScript'));

        // Act
        $results = self::getService(TagService::class)->searchTags('PHP', 10);

        // Assert
        $this->assertCount(2, $results);
        $names = array_map(fn ($tag) => $tag->getName(), $results);
        $this->assertContains('PHP', $names);
        $this->assertContains('PHP Framework', $names);
    }

    public function testFindTagBySlugWithExistingSlugReturnsTag(): void
    {
        // Arrange
        $dto = new TagDTO();
        $dto->name = 'PHP';
        $dto->slug = 'php';

        $created = self::getService(TagService::class)->createTag($dto);

        // Act
        $found = self::getService(TagService::class)->findTagBySlug('php');

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals($created->getId(), $found->getId());
        $this->assertEquals('php', $found->getSlug());
    }

    public function testFindTagBySlugWithNonExistentSlugReturnsNull(): void
    {
        // Act
        $result = self::getService(TagService::class)->findTagBySlug('non-existent');

        // Assert
        $this->assertNull($result);
    }

    protected function onSetUp(): void
    {
        // 清理测试数据，确保测试隔离
        self::getEntityManager()->createQuery('DELETE FROM Tourze\QuestionBankBundle\Entity\Tag t')->execute();
    }
}
