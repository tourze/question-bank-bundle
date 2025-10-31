<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\QuestionBankBundle\DTO\TagDTO;
use Tourze\QuestionBankBundle\Exception\TagNotFoundException;
use Tourze\QuestionBankBundle\Service\TagService;

/**
 * @internal
 */
#[CoversClass(TagService::class)]
#[RunTestsInSeparateProcesses]
final class TagServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 清理测试数据，确保测试隔离
        self::getEntityManager()->createQuery('DELETE FROM Tourze\QuestionBankBundle\Entity\Tag t')->execute();
    }

    public function testCreateTag(): void
    {
        $dto = TagDTO::create('Test Tag');

        $tag = self::getService(TagService::class)->createTag($dto);

        $this->assertSame('Test Tag', $tag->getName());
        $this->assertTrue($tag->isValid());
        $this->assertSame(0, $tag->getUsageCount());
    }

    public function testFindTagById(): void
    {
        $dto = TagDTO::create('Test Tag');
        $createdTag = self::getService(TagService::class)->createTag($dto);

        $foundTag = self::getService(TagService::class)->findTag((string) $createdTag->getId());

        $this->assertSame($createdTag->getId(), $foundTag->getId());
        $this->assertSame('Test Tag', $foundTag->getName());
    }

    public function testUpdateTag(): void
    {
        $dto = TagDTO::create('Test Tag');
        $tag = self::getService(TagService::class)->createTag($dto);

        $updateDto = TagDTO::create('Updated Tag');
        $updateDto->slug = 'updated-tag';
        $updateDto->description = 'Updated description';
        $updateDto->color = '#FF0000';

        $updatedTag = self::getService(TagService::class)->updateTag((string) $tag->getId(), $updateDto);

        $this->assertSame('Updated Tag', $updatedTag->getName());
        $this->assertSame('updated-tag', $updatedTag->getSlug());
        $this->assertSame('Updated description', $updatedTag->getDescription());
        $this->assertSame('#FF0000', $updatedTag->getColor());
    }

    public function testDeleteTag(): void
    {
        $dto = TagDTO::create('Test Tag');
        $tag = self::getService(TagService::class)->createTag($dto);
        $tagId = (string) $tag->getId();

        // 标签未被使用时可以删除
        self::getService(TagService::class)->deleteTag($tagId);

        $this->expectException(TagNotFoundException::class);
        self::getService(TagService::class)->findTag($tagId);
    }

    public function testFindOrCreateTag(): void
    {
        // 测试创建新标签
        $newTag = self::getService(TagService::class)->findOrCreateTag('New Tag');
        $this->assertSame('New Tag', $newTag->getName());
        $this->assertSame('new-tag', $newTag->getSlug());

        // 测试查找已存在的标签
        $existingTag = self::getService(TagService::class)->findOrCreateTag('New Tag');
        $this->assertSame($newTag->getId(), $existingTag->getId());
        $this->assertSame('New Tag', $existingTag->getName());
    }

    public function testFindTagBySlug(): void
    {
        $dto = TagDTO::create('Test Tag');
        $dto->slug = 'test-tag-slug';
        $createdTag = self::getService(TagService::class)->createTag($dto);

        $foundTag = self::getService(TagService::class)->findTagBySlug('test-tag-slug');

        $this->assertNotNull($foundTag);
        $this->assertSame($createdTag->getId(), $foundTag->getId());
        $this->assertSame('Test Tag', $foundTag->getName());
        $this->assertSame('test-tag-slug', $foundTag->getSlug());

        // 测试不存在的 slug
        $nonExistentTag = self::getService(TagService::class)->findTagBySlug('non-existent-slug');
        $this->assertNull($nonExistentTag);
    }

    public function testMergeTag(): void
    {
        // 创建两个标签
        $sourceDto = TagDTO::create('Source Tag');
        $sourceTag = self::getService(TagService::class)->createTag($sourceDto);

        $targetDto = TagDTO::create('Target Tag');
        $targetTag = self::getService(TagService::class)->createTag($targetDto);

        // 合并标签
        self::getService(TagService::class)->mergeTag((string) $sourceTag->getId(), (string) $targetTag->getId());

        // 验证源标签被删除
        $this->expectException(TagNotFoundException::class);
        self::getService(TagService::class)->findTag((string) $sourceTag->getId());

        // 目标标签仍然存在
        $remainingTag = self::getService(TagService::class)->findTag((string) $targetTag->getId());
        $this->assertSame('Target Tag', $remainingTag->getName());
    }

    public function testSearchTags(): void
    {
        // 创建几个测试标签
        $tags = [];
        for ($i = 1; $i <= 3; ++$i) {
            $dto = TagDTO::create("Search Tag {$i}");
            $tags[] = self::getService(TagService::class)->createTag($dto);
        }

        // 创建不匹配的标签
        $otherDto = TagDTO::create('Other Tag');
        self::getService(TagService::class)->createTag($otherDto);

        // 测试搜索
        $results = self::getService(TagService::class)->searchTags('Search', 10);

        $this->assertGreaterThanOrEqual(3, count($results));

        // 验证结果中包含我们创建的标签
        $resultNames = array_map(fn ($tag) => $tag->getName(), $results);
        $this->assertContains('Search Tag 1', $resultNames);
        $this->assertContains('Search Tag 2', $resultNames);
        $this->assertContains('Search Tag 3', $resultNames);
    }
}
