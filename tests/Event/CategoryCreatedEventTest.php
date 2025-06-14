<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Event\CategoryCreatedEvent;

class CategoryCreatedEventTest extends TestCase
{
    public function test_constructor_setsCategory(): void
    {
        // Arrange
        $category = new Category('Programming', 'programming');

        // Act
        $event = new CategoryCreatedEvent($category);

        // Assert
        $this->assertSame($category, $event->getCategory());
    }

    public function test_getCategory_returnsCorrectCategory(): void
    {
        // Arrange
        $category1 = new Category('Math', 'math');
        $category2 = new Category('Science', 'science');

        $event1 = new CategoryCreatedEvent($category1);
        $event2 = new CategoryCreatedEvent($category2);

        // Act & Assert
        $this->assertSame($category1, $event1->getCategory());
        $this->assertSame($category2, $event2->getCategory());
        $this->assertNotSame($event1->getCategory(), $event2->getCategory());
    }

    public function test_eventCarriesCategoryData(): void
    {
        // Arrange
        $category = new Category('Database', 'database');
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

    public function test_eventWithCategoryHierarchy(): void
    {
        // Arrange
        $parent = new Category('Languages', 'languages');
        $child = new Category('PHP', 'php');
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