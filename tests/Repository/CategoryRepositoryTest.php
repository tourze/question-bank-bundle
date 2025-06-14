<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Repository;

use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Repository\CategoryRepository;
use Tourze\QuestionBankBundle\Tests\BaseIntegrationTestCase;

class CategoryRepositoryTest extends BaseIntegrationTestCase
{
    private CategoryRepository $repository;

    public function test_save_withValidCategory_persistsToDatabase(): void
    {
        // Arrange
        $category = new Category('Programming', 'programming');

        // Act
        $this->repository->save($category);

        // Assert
        $this->assertNotNull($category->getId());
        $saved = $this->repository->find($category->getId());
        $this->assertNotNull($saved);
        $this->assertEquals('Programming', $saved->getName());
        $this->assertEquals('programming', $saved->getCode());
    }

    public function test_save_withParentCategory_maintainsHierarchy(): void
    {
        // Arrange
        $parent = new Category('Languages', 'languages');
        $child = new Category('PHP', 'php');

        $this->repository->save($parent);
        $child->setParent($parent);

        // Act
        $this->repository->save($child);

        // Assert
        $saved = $this->repository->find($child->getId());
        $this->assertNotNull($saved->getParent());
        $this->assertEquals($parent->getId(), $saved->getParent()->getId());
        $this->assertEquals(1, $saved->getLevel());
        $this->assertEquals('/languages/php', $saved->getPath());
    }

    public function test_remove_withValidCategory_deletesFromDatabase(): void
    {
        // Arrange
        $category = new Category('Test', 'test');
        $this->repository->save($category);
        $id = $category->getId();

        // Act
        $this->repository->remove($category);

        // Assert
        $this->assertNull($this->repository->find($id));
    }

    public function test_findByCode_withExistingCode_returnsCategory(): void
    {
        // Arrange
        $category = new Category('Database', 'database');
        $this->repository->save($category);

        // Act
        $found = $this->repository->findByCode('database');

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals('Database', $found->getName());
    }

    public function test_findByCode_withNonExistentCode_returnsNull(): void
    {
        // Act
        $result = $this->repository->findByCode('non-existent');

        // Assert
        $this->assertNull($result);
    }

    public function test_findRootCategories_returnsOnlyRootCategories(): void
    {
        // Arrange
        $root1 = new Category('Programming', 'programming');
        $root2 = new Category('Database', 'database');
        $child = new Category('PHP', 'php');

        $this->repository->save($root1);
        $this->repository->save($root2);
        $child->setParent($root1);
        $this->repository->save($child);

        // Act
        $roots = $this->repository->findRootCategories();

        // Assert
        $this->assertCount(2, $roots);
        $names = array_map(fn($c) => $c->getName(), $roots);
        $this->assertContains('Programming', $names);
        $this->assertContains('Database', $names);
    }

    public function test_findActiveCategories_returnsOnlyActiveCategories(): void
    {
        // Arrange
        $active1 = new Category('Active1', 'active1');
        $active2 = new Category('Active2', 'active2');
        $inactive = new Category('Inactive', 'inactive');
        $inactive->setValid(false);

        $this->repository->save($active1);
        $this->repository->save($active2);
        $this->repository->save($inactive);

        // Act
        $activeCategories = $this->repository->findActiveCategories();

        // Assert
        $this->assertCount(2, $activeCategories);
        foreach ($activeCategories as $category) {
            $this->assertTrue($category->isValid());
        }
    }

    public function test_getCategoryTree_returnsHierarchicalStructure(): void
    {
        // Arrange
        $root = new Category('Languages', 'languages');
        $php = new Category('PHP', 'php');
        $javascript = new Category('JavaScript', 'javascript');

        $this->repository->save($root);

        $php->setParent($root);
        $javascript->setParent($root);

        $this->repository->save($php);
        $this->repository->save($javascript);

        // Act
        $tree = $this->repository->getCategoryTree();

        // Assert
        $this->assertCount(1, $tree);
        $this->assertEquals('Languages', $tree[0]->getName());
        $this->assertCount(2, $tree[0]->getChildren());
    }

    public function test_save_withSortOrder_maintainsOrder(): void
    {
        // Arrange
        $cat1 = new Category('First', 'first');
        $cat1->setSortOrder(10);

        $cat2 = new Category('Second', 'second');
        $cat2->setSortOrder(5);

        $cat3 = new Category('Third', 'third');
        $cat3->setSortOrder(15);

        // Act
        $this->repository->save($cat1);
        $this->repository->save($cat2);
        $this->repository->save($cat3);

        // Assert
        $roots = $this->repository->findRootCategories();
        $this->assertEquals('Second', $roots[0]->getName());
        $this->assertEquals('First', $roots[1]->getName());
        $this->assertEquals('Third', $roots[2]->getName());
    }

    public function test_categoryPath_updatesCorrectlyOnParentChange(): void
    {
        // Arrange
        $root1 = new Category('Root1', 'root1');
        $root2 = new Category('Root2', 'root2');
        $child = new Category('Child', 'child');

        $this->repository->save($root1);
        $this->repository->save($root2);

        $child->setParent($root1);
        $this->repository->save($child);

        $this->assertEquals('/root1/child', $child->getPath());

        // Act - 移动到新父级
        $child->setParent($root2);
        $this->repository->save($child);

        // Assert
        $this->assertEquals('/root2/child', $child->getPath());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->container->get(CategoryRepository::class);
    }
}