<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Repository;

use Tourze\QuestionBankBundle\Entity\Tag;
use Tourze\QuestionBankBundle\Repository\TagRepository;
use Tourze\QuestionBankBundle\Tests\BaseIntegrationTestCase;

class TagRepositoryTest extends BaseIntegrationTestCase
{
    private TagRepository $repository;

    public function test_save_withValidTag_persistsToDatabase(): void
    {
        // Arrange
        $tag = new Tag('PHP', 'php');
        $tag->setDescription('PHP programming language');
        $tag->setColor('#8892be');

        // Act
        $this->repository->save($tag);

        // Assert
        $this->assertNotNull($tag->getId());
        $saved = $this->repository->find($tag->getId());
        $this->assertNotNull($saved);
        $this->assertEquals('PHP', $saved->getName());
        $this->assertEquals('php', $saved->getSlug());
        $this->assertEquals('PHP programming language', $saved->getDescription());
        $this->assertEquals('#8892be', $saved->getColor());
    }

    public function test_remove_withValidTag_deletesFromDatabase(): void
    {
        // Arrange
        $tag = new Tag('Test', 'test');
        $this->repository->save($tag);
        $id = $tag->getId();

        // Act
        $this->repository->remove($tag);

        // Assert
        $this->assertNull($this->repository->find($id));
    }

    public function test_findBySlug_withExistingSlug_returnsTag(): void
    {
        // Arrange
        $tag = new Tag('JavaScript', 'javascript');
        $this->repository->save($tag);

        // Act
        $found = $this->repository->findBySlug('javascript');

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals('JavaScript', $found->getName());
    }

    public function test_findBySlug_withNonExistentSlug_returnsNull(): void
    {
        // Act
        $result = $this->repository->findBySlug('non-existent');

        // Assert
        $this->assertNull($result);
    }

    public function test_findPopularTags_returnsTagsSortedByUsageCount(): void
    {
        // Arrange
        $tag1 = new Tag('Popular', 'popular');
        $tag1->incrementUsageCount()->incrementUsageCount()->incrementUsageCount();

        $tag2 = new Tag('MostPopular', 'most-popular');
        for ($i = 0; $i < 5; $i++) {
            $tag2->incrementUsageCount();
        }

        $tag3 = new Tag('LessPopular', 'less-popular');
        $tag3->incrementUsageCount();

        $this->repository->save($tag1);
        $this->repository->save($tag2);
        $this->repository->save($tag3);

        // Act
        $popularTags = $this->repository->findPopularTags(2);

        // Assert
        $this->assertCount(2, $popularTags);
        $this->assertEquals('MostPopular', $popularTags[0]->getName());
        $this->assertEquals('Popular', $popularTags[1]->getName());
    }

    public function test_findByNames_withExistingNames_returnsTags(): void
    {
        // Arrange
        $tag1 = new Tag('PHP', 'php');
        $tag2 = new Tag('MySQL', 'mysql');
        $tag3 = new Tag('Redis', 'redis');

        $this->repository->save($tag1);
        $this->repository->save($tag2);
        $this->repository->save($tag3);

        // Act
        $tags = $this->repository->findByNames(['PHP', 'Redis']);

        // Assert
        $this->assertCount(2, $tags);
        $names = array_map(fn($t) => $t->getName(), $tags);
        $this->assertContains('PHP', $names);
        $this->assertContains('Redis', $names);
    }

    public function test_findByNames_withEmptyArray_returnsEmptyArray(): void
    {
        // Act
        $tags = $this->repository->findByNames([]);

        // Assert
        $this->assertIsArray($tags);
        $this->assertEmpty($tags);
    }

    public function test_search_withMatchingKeyword_returnsTags(): void
    {
        // Arrange
        $tag1 = new Tag('PHP', 'php');
        $tag2 = new Tag('PHP Framework', 'php-framework');
        $tag3 = new Tag('JavaScript', 'javascript');

        $this->repository->save($tag1);
        $this->repository->save($tag2);
        $this->repository->save($tag3);

        // Act
        $results = $this->repository->search('php', 10);

        // Assert
        $this->assertCount(2, $results);
        foreach ($results as $tag) {
            $this->assertStringContainsStringIgnoringCase('php', $tag->getName() . $tag->getSlug());
        }
    }

    public function test_search_respectsLimit(): void
    {
        // Arrange
        for ($i = 1; $i <= 10; $i++) {
            $tag = new Tag("Tag{$i}", "tag-{$i}");
            $this->repository->save($tag);
        }

        // Act
        $results = $this->repository->search('tag', 3);

        // Assert
        $this->assertCount(3, $results);
    }

    public function test_usageCount_incrementAndDecrement(): void
    {
        // Arrange
        $tag = new Tag('Test', 'test');
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->container->get(TagRepository::class);
    }
}