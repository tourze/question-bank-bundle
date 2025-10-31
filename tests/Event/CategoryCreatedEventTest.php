<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Event\CategoryCreatedEvent;

/**
 * @internal
 */
#[CoversClass(CategoryCreatedEvent::class)]
final class CategoryCreatedEventTest extends AbstractEventTestCase
{
    public function testConstructorSetsCategory(): void
    {
        // Arrange
        $category = new Category();
        $category->setName('Programming');
        $category->setCode('programming');

        // Act
        $event = new CategoryCreatedEvent($category);

        // Assert
        $this->assertSame($category, $event->getCategory());
    }

    public function testGetCategoryReturnsCorrectCategory(): void
    {
        // Arrange
        $category1 = new Category();
        $category1->setName('Math');
        $category1->setCode('math');
        $category2 = new Category();
        $category2->setName('Science');
        $category2->setCode('science');

        $event1 = new CategoryCreatedEvent($category1);
        $event2 = new CategoryCreatedEvent($category2);

        // Act & Assert
        $this->assertSame($category1, $event1->getCategory());
        $this->assertSame($category2, $event2->getCategory());
        $this->assertNotSame($event1->getCategory(), $event2->getCategory());
    }

    public function testEventCarriesCategoryData(): void
    {
        // Arrange
        $category = new Category();
        $category->setName('Database');
        $category->setCode('database');
        $category->setDescription('Database related questions');
        $category->setSortOrder(10);

        // Act
        $event = new CategoryCreatedEvent($category);
        $eventCategory = $event->getCategory();

        // Assert - Verify event carries all category data
        $this->assertEquals('Database', $eventCategory->getName());
        $this->assertEquals('database', $eventCategory->getCode());
        $this->assertEquals('Database related questions', $eventCategory->getDescription());
        $this->assertEquals(10, $eventCategory->getSortOrder());
        $this->assertTrue($eventCategory->isValid());
    }

    public function testEventWithCategoryHierarchy(): void
    {
        // Arrange
        $parent = new Category();
        $parent->setName('Languages');
        $parent->setCode('languages');
        $child = new Category();
        $child->setName('PHP');
        $child->setCode('php');
        $child->setParent($parent);

        // Act
        $event = new CategoryCreatedEvent($child);
        $eventCategory = $event->getCategory();

        // Assert
        $this->assertEquals('PHP', $eventCategory->getName());
        $this->assertSame($parent, $eventCategory->getParent());
        $this->assertEquals(1, $eventCategory->getLevel());
        $this->assertEquals('/languages/php', $eventCategory->getPath());
    }
}
