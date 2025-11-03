<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\QuestionBankBundle\Entity\Tag;
use Tourze\QuestionBankBundle\Repository\TagRepository;

/**
 * @extends AbstractRepositoryTestCase<Tag>
 *
 * @internal
 */
#[CoversClass(TagRepository::class)]
#[RunTestsInSeparateProcesses]
final class TagRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 不再清理数据，让 fixtures 数据保留
    }

    public function testSaveWithValidTagPersistsToDatabase(): void
    {
        // Arrange - 使用随机的唯一 slug 避免与 fixtures 冲突
        $uniqueSlug = 'php-' . uniqid();
        $tag = new Tag();
        $tag->setName('PHP');
        $tag->setSlug($uniqueSlug);
        $tag->setDescription('PHP programming language');
        $tag->setColor('#8892be');

        // Act
        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        // Assert
        $this->assertNotNull($tag->getId());
        $saved = self::getService(TagRepository::class)->find($tag->getId());
        $this->assertNotNull($saved);
        $this->assertEquals('PHP', $saved->getName());
        $this->assertEquals($uniqueSlug, $saved->getSlug());
        $this->assertEquals('PHP programming language', $saved->getDescription());
        $this->assertEquals('#8892be', $saved->getColor());
    }

    public function testRemoveWithValidTagDeletesFromDatabase(): void
    {
        // Arrange - 使用随机的唯一 slug 避免与 fixtures 冲突
        $tag = new Tag();
        $tag->setName('Test');
        $tag->setSlug('test-' . uniqid());
        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();
        $id = $tag->getId();

        // Act
        self::getEntityManager()->remove($tag);
        self::getEntityManager()->flush();

        // Assert
        $this->assertNull(self::getService(TagRepository::class)->find($id));
    }

    public function testFindBySlugWithExistingSlugReturnsTag(): void
    {
        // Arrange - 使用随机的唯一 slug 避免与 fixtures 冲突
        $uniqueSlug = 'javascript-' . uniqid();
        $tag = new Tag();
        $tag->setName('JavaScript');
        $tag->setSlug($uniqueSlug);
        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        // Act
        $found = self::getService(TagRepository::class)->findBySlug($uniqueSlug);

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals('JavaScript', $found->getName());
    }

    public function testFindBySlugWithNonExistentSlugReturnsNull(): void
    {
        // Act
        $result = self::getService(TagRepository::class)->findBySlug('non-existent');

        // Assert
        $this->assertNull($result);
    }

    public function testFindPopularTagsReturnsTagsSortedByUsageCount(): void
    {
        // Arrange - 使用随机的唯一 slug 避免与 fixtures 冲突
        $tag1 = new Tag();
        $tag1->setName('Popular');
        $tag1->setSlug('popular-' . uniqid());
        $tag1->incrementUsageCount()->incrementUsageCount()->incrementUsageCount();

        $tag2 = new Tag();
        $tag2->setName('MostPopular');
        $tag2->setSlug('most-popular-' . uniqid());
        for ($i = 0; $i < 5; ++$i) {
            $tag2->incrementUsageCount();
        }

        $tag3 = new Tag();
        $tag3->setName('LessPopular');
        $tag3->setSlug('less-popular-' . uniqid());
        $tag3->incrementUsageCount();

        self::getEntityManager()->persist($tag1);
        self::getEntityManager()->persist($tag2);
        self::getEntityManager()->persist($tag3);
        self::getEntityManager()->flush();

        // Act
        $popularTags = self::getService(TagRepository::class)->findPopularTags(2);

        // Assert
        $this->assertCount(2, $popularTags);
        $this->assertEquals('MostPopular', $popularTags[0]->getName());
        $this->assertEquals('Popular', $popularTags[1]->getName());
    }

    public function testFindByNamesWithExistingNamesReturnsTags(): void
    {
        // Arrange - 使用随机的唯一 slug 避免与 fixtures 冲突
        $tag1 = new Tag();
        $tag1->setName('PHP Test');
        $tag1->setSlug('php-test-' . uniqid());
        $tag2 = new Tag();
        $tag2->setName('MySQL Test');
        $tag2->setSlug('mysql-test-' . uniqid());
        $tag3 = new Tag();
        $tag3->setName('Redis Test');
        $tag3->setSlug('redis-test-' . uniqid());

        self::getEntityManager()->persist($tag1);
        self::getEntityManager()->persist($tag2);
        self::getEntityManager()->persist($tag3);
        self::getEntityManager()->flush();

        // Act
        $tags = self::getService(TagRepository::class)->findByNames(['PHP Test', 'Redis Test']);

        // Assert
        $this->assertCount(2, $tags);
        $names = array_map(fn ($t) => $t->getName(), $tags);
        $this->assertContains('PHP Test', $names);
        $this->assertContains('Redis Test', $names);
    }

    public function testFindByNamesWithEmptyArrayReturnsEmptyArray(): void
    {
        // Act
        $tags = self::getService(TagRepository::class)->findByNames([]);

        // Assert
        $this->assertEmpty($tags);
    }

    public function testSearchWithMatchingKeywordReturnsTags(): void
    {
        // Arrange - 使用随机的唯一 slug 避免与 fixtures 冲突
        $tag1 = new Tag();
        $tag1->setName('PHP Search');
        $tag1->setSlug('php-search-' . uniqid());
        $tag2 = new Tag();
        $tag2->setName('PHP Framework Search');
        $tag2->setSlug('php-framework-search-' . uniqid());
        $tag3 = new Tag();
        $tag3->setName('JavaScript Search');
        $tag3->setSlug('javascript-search-' . uniqid());

        self::getEntityManager()->persist($tag1);
        self::getEntityManager()->persist($tag2);
        self::getEntityManager()->persist($tag3);
        self::getEntityManager()->flush();

        // Act
        $results = self::getService(TagRepository::class)->search('php', 10);

        // Assert - 过滤出我们创建的标签
        $createdResults = array_filter($results, function ($tag) {
            return false !== strpos($tag->getName(), 'Search');
        });
        $this->assertCount(2, $createdResults);
        foreach ($createdResults as $tag) {
            $this->assertStringContainsStringIgnoringCase('php', $tag->getName() . $tag->getSlug());
        }
    }

    public function testSearchRespectsLimit(): void
    {
        // Arrange
        for ($i = 1; $i <= 10; ++$i) {
            $tag = new Tag();
            $tag->setName("Tag{$i}");
            $tag->setSlug("tag-{$i}");
            self::getEntityManager()->persist($tag);
            self::getEntityManager()->flush();
        }

        // Act
        $results = self::getService(TagRepository::class)->search('tag', 3);

        // Assert
        $this->assertCount(3, $results);
    }

    public function testUsageCountIncrementAndDecrement(): void
    {
        // Arrange - 使用随机的唯一 slug 避免与 fixtures 冲突
        $tag = new Tag();
        $tag->setName('Test');
        $tag->setSlug('test-' . uniqid());
        $this->assertEquals(0, $tag->getUsageCount());

        // Act & Assert - Increment
        $tag->incrementUsageCount();
        $this->assertEquals(1, $tag->getUsageCount());

        $tag->incrementUsageCount();
        $this->assertEquals(2, $tag->getUsageCount());

        // Act & Assert - Decrement
        $tag->decrementUsageCount();
        $this->assertEquals(1, $tag->getUsageCount());

        // 不应该小于0
        $tag->decrementUsageCount();
        $tag->decrementUsageCount();
        $this->assertEquals(0, $tag->getUsageCount());
    }

    // 基础 find 方法测试

    // findAll 方法测试

    // findBy 方法测试

    // findOneBy 方法测试

    public function testFindOneByWithOrderBy(): void
    {
        $tag1 = new Tag();
        $tag1->setName('Z Tag');
        $tag1->setSlug('z-tag');
        $tag2 = new Tag();
        $tag2->setName('A Tag');
        $tag2->setSlug('a-tag');

        self::getEntityManager()->persist($tag1);
        self::getEntityManager()->persist($tag2);
        self::getEntityManager()->flush();

        $result = self::getService(TagRepository::class)->findOneBy(
            ['valid' => true],
            ['name' => 'ASC']
        );

        $this->assertNotNull($result);
        $this->assertEquals('A Tag', $result->getName());
    }

    public function testFindByWithNullDescription(): void
    {
        $tag1 = new Tag();
        $tag1->setName('With Desc');
        $tag1->setSlug('with-desc');
        $tag1->setDescription('Some description');

        $tag2 = new Tag();
        $tag2->setName('No Desc');
        $tag2->setSlug('no-desc');

        self::getEntityManager()->persist($tag1);
        self::getEntityManager()->persist($tag2);
        self::getEntityManager()->flush();

        $result = self::getService(TagRepository::class)->findBy(['description' => null]);

        $this->assertCount(1, $result);
        $this->assertEquals('No Desc', $result[0]->getName());
    }

    public function testCountWithNullDescription(): void
    {
        $tag1 = new Tag();
        $tag1->setName('With Desc');
        $tag1->setSlug('with-desc');
        $tag1->setDescription('Some description');

        $tag2 = new Tag();
        $tag2->setName('No Desc');
        $tag2->setSlug('no-desc');

        self::getEntityManager()->persist($tag1);
        self::getEntityManager()->persist($tag2);
        self::getEntityManager()->flush();

        $count = self::getService(TagRepository::class)->count(['description' => null]);

        $this->assertEquals(1, $count);
    }

    public function testFindByWithNullColor(): void
    {
        $tag1 = new Tag();
        $tag1->setName('With Color');
        $tag1->setSlug('with-color');
        $tag1->setColor('#FF0000');

        $tag2 = new Tag();
        $tag2->setName('No Color');
        $tag2->setSlug('no-color');

        self::getEntityManager()->persist($tag1);
        self::getEntityManager()->persist($tag2);
        self::getEntityManager()->flush();

        $result = self::getService(TagRepository::class)->findBy(['color' => null]);

        $this->assertCount(1, $result);
        $this->assertEquals('No Color', $result[0]->getName());
    }

    public function testCountWithNullColor(): void
    {
        $tag1 = new Tag();
        $tag1->setName('With Color');
        $tag1->setSlug('with-color');
        $tag1->setColor('#FF0000');

        $tag2 = new Tag();
        $tag2->setName('No Color');
        $tag2->setSlug('no-color');

        self::getEntityManager()->persist($tag1);
        self::getEntityManager()->persist($tag2);
        self::getEntityManager()->flush();

        $count = self::getService(TagRepository::class)->count(['color' => null]);

        $this->assertEquals(1, $count);
    }

    protected function createNewEntity(): object
    {
        // 使用随机的唯一 slug 避免与 fixtures 冲突
        $tag = new Tag();
        $tag->setName('Test Tag Name');
        $tag->setSlug('test-tag-name-' . uniqid());

        return $tag;
    }

    protected function getRepository(): TagRepository
    {
        return self::getService(TagRepository::class);
    }
}
