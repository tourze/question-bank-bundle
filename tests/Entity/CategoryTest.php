<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Exception\CategoryHierarchyException;

/**
 * @internal
 */
#[CoversClass(Category::class)]
final class CategoryTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test_category');

        return $category;
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     *
     * @return iterable<string, array{0: string, 1: mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'name' => ['name', 'New Category Name'];
        yield 'code' => ['code', 'new_category_code'];
        yield 'description' => ['description', 'Test description for category'];
        yield 'sortOrder' => ['sortOrder', 10];
        yield 'valid' => ['valid', false];
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test_category');

        $this->assertEquals('Test Category', $category->getName());
        $this->assertEquals('test_category', $category->getCode());
        $this->assertNull($category->getDescription());
        $this->assertEquals(0, $category->getSortOrder());
        $this->assertTrue($category->isValid());
        $this->assertNull($category->getParent());
        $this->assertCount(0, $category->getChildren());
        $this->assertCount(0, $category->getQuestions());
        $this->assertEquals(0, $category->getLevel());
        $this->assertEquals('/test_category', $category->getPath());
        $this->assertInstanceOf(\DateTimeImmutable::class, $category->getCreateTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $category->getUpdateTime());
    }

    public function testSetNameUpdatesName(): void
    {
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test_category');

        $category->setName('New Name');

        $this->assertEquals('New Name', $category->getName());
    }

    public function testSetCodeUpdatesCodeAndPath(): void
    {
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test_category');

        $category->setCode('new_code');

        $this->assertEquals('new_code', $category->getCode());
        $this->assertEquals('/new_code', $category->getPath());
    }

    public function testSetDescriptionUpdatesDescription(): void
    {
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test_category');

        $category->setDescription('Test Description');

        $this->assertEquals('Test Description', $category->getDescription());
    }

    public function testSetSortOrderUpdatesSortOrder(): void
    {
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test_category');

        $category->setSortOrder(10);

        $this->assertEquals(10, $category->getSortOrder());
    }

    public function testSetValidUpdatesValidFlag(): void
    {
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test_category');

        $category->setValid(false);

        $this->assertFalse($category->isValid());
    }

    public function testSetParentUpdatesParentAndLevel(): void
    {
        $parent = new Category();
        $parent->setName('Parent');
        $parent->setCode('parent');
        $child = new Category();
        $child->setName('Child');
        $child->setCode('child');

        $child->setParent($parent);

        $this->assertEquals($parent, $child->getParent());
        $this->assertEquals(1, $child->getLevel());
        $this->assertEquals('/parent/child', $child->getPath());
    }

    public function testSetParentThrowsExceptionWhenSettingSelfAsParent(): void
    {
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test_category');

        $this->expectException(CategoryHierarchyException::class);
        $this->expectExceptionMessage('Category cannot be its own parent');

        $category->setParent($category);
    }

    public function testSetParentThrowsExceptionWhenSettingDescendantAsParent(): void
    {
        $grandparent = new Category();
        $grandparent->setName('Grandparent');
        $grandparent->setCode('grandparent');
        $parent = new Category();
        $parent->setName('Parent');
        $parent->setCode('parent');
        $child = new Category();
        $child->setName('Child');
        $child->setCode('child');

        $parent->setParent($grandparent);
        $child->setParent($parent);

        $this->expectException(CategoryHierarchyException::class);
        $this->expectExceptionMessage('Cannot set descendant as parent');

        $grandparent->setParent($child);
    }

    public function testAddChildAddsChildAndSetsParent(): void
    {
        $parent = new Category();
        $parent->setName('Parent');
        $parent->setCode('parent');
        $child = new Category();
        $child->setName('Child');
        $child->setCode('child');

        $parent->addChild($child);

        $this->assertCount(1, $parent->getChildren());
        $this->assertTrue($parent->getChildren()->contains($child));
        $this->assertEquals($parent, $child->getParent());
    }

    public function testAddChildDoesNotAddDuplicateChild(): void
    {
        $parent = new Category();
        $parent->setName('Parent');
        $parent->setCode('parent');
        $child = new Category();
        $child->setName('Child');
        $child->setCode('child');

        $parent->addChild($child);
        $parent->addChild($child); // 添加重复子分类

        $this->assertCount(1, $parent->getChildren());
    }

    public function testRemoveChildRemovesChildAndClearsParent(): void
    {
        $parent = new Category();
        $parent->setName('Parent');
        $parent->setCode('parent');
        $child = new Category();
        $child->setName('Child');
        $child->setCode('child');
        $parent->addChild($child);

        $parent->removeChild($child);

        $this->assertCount(0, $parent->getChildren());
        $this->assertFalse($parent->getChildren()->contains($child));
        $this->assertNull($child->getParent());
    }

    public function testIsAncestorOfReturnsTrueForDescendant(): void
    {
        $grandparent = new Category();
        $grandparent->setName('Grandparent');
        $grandparent->setCode('grandparent');
        $parent = new Category();
        $parent->setName('Parent');
        $parent->setCode('parent');
        $child = new Category();
        $child->setName('Child');
        $child->setCode('child');

        $parent->setParent($grandparent);
        $child->setParent($parent);

        $this->assertTrue($grandparent->isAncestorOf($child));
        $this->assertTrue($parent->isAncestorOf($child));
        $this->assertFalse($child->isAncestorOf($grandparent));
    }

    public function testIsAncestorOfReturnsFalseForSelf(): void
    {
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test_category');

        $this->assertFalse($category->isAncestorOf($category));
    }

    public function testIsDescendantOfReturnsTrueForAncestor(): void
    {
        $grandparent = new Category();
        $grandparent->setName('Grandparent');
        $grandparent->setCode('grandparent');
        $parent = new Category();
        $parent->setName('Parent');
        $parent->setCode('parent');
        $child = new Category();
        $child->setName('Child');
        $child->setCode('child');

        $parent->setParent($grandparent);
        $child->setParent($parent);

        $this->assertTrue($child->isDescendantOf($grandparent));
        $this->assertTrue($child->isDescendantOf($parent));
        $this->assertFalse($grandparent->isDescendantOf($child));
    }

    public function testGetAncestorsReturnsAncestorsInOrder(): void
    {
        $grandparent = new Category();
        $grandparent->setName('Grandparent');
        $grandparent->setCode('grandparent');
        $parent = new Category();
        $parent->setName('Parent');
        $parent->setCode('parent');
        $child = new Category();
        $child->setName('Child');
        $child->setCode('child');

        $parent->setParent($grandparent);
        $child->setParent($parent);

        $ancestors = $child->getAncestors();

        $this->assertCount(2, $ancestors);
        $this->assertEquals($grandparent, $ancestors[0]);
        $this->assertEquals($parent, $ancestors[1]);
    }

    public function testGetAncestorsReturnsEmptyArrayForRootCategory(): void
    {
        $category = new Category();
        $category->setName('Root');
        $category->setCode('root');

        $ancestors = $category->getAncestors();

        $this->assertCount(0, $ancestors);
    }

    public function testGetFullPathReturnsCompletePathIncludingSelf(): void
    {
        $grandparent = new Category();
        $grandparent->setName('Grandparent');
        $grandparent->setCode('grandparent');
        $parent = new Category();
        $parent->setName('Parent');
        $parent->setCode('parent');
        $child = new Category();
        $child->setName('Child');
        $child->setCode('child');

        $parent->setParent($grandparent);
        $child->setParent($parent);

        $fullPath = $child->getFullPath();

        $this->assertCount(3, $fullPath);
        $this->assertEquals($grandparent, $fullPath[0]);
        $this->assertEquals($parent, $fullPath[1]);
        $this->assertEquals($child, $fullPath[2]);
    }

    public function testToStringReturnsName(): void
    {
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test_category');

        $this->assertEquals('Test Category', (string) $category);
    }

    public function testHierarchicalPathUpdateWithParentRelationshipChanges(): void
    {
        $parent = new Category();
        $parent->setName('Parent');
        $parent->setCode('parent');
        $child = new Category();
        $child->setName('Child');
        $child->setCode('child');
        $grandchild = new Category();
        $grandchild->setName('Grandchild');
        $grandchild->setCode('grandchild');

        // 验证独立创建的分类路径
        $this->assertEquals('/parent', $parent->getPath());
        $this->assertEquals('/child', $child->getPath());
        $this->assertEquals('/grandchild', $grandchild->getPath());

        // 设置层级关系会更新路径
        $child->setParent($parent);
        $this->assertEquals('/parent/child', $child->getPath());

        $grandchild->setParent($child);
        $this->assertEquals('/parent/child/grandchild', $grandchild->getPath());

        // 移动子分类到根级别
        $child->setParent(null);
        $this->assertEquals('/child', $child->getPath());
        $this->assertEquals('/child/grandchild', $grandchild->getPath());
    }

    public function testHierarchicalLevelUpdateUpdatesChildrenLevels(): void
    {
        $root = new Category();
        $root->setName('Root');
        $root->setCode('root');
        $level1 = new Category();
        $level1->setName('Level1');
        $level1->setCode('level1');
        $level2 = new Category();
        $level2->setName('Level2');
        $level2->setCode('level2');

        // 验证初始层级
        $this->assertEquals(0, $root->getLevel());
        $this->assertEquals(0, $level1->getLevel());
        $this->assertEquals(0, $level2->getLevel());

        // 设置层级关系
        $level1->setParent($root);
        $level2->setParent($level1);

        $this->assertEquals(0, $root->getLevel());
        $this->assertEquals(1, $level1->getLevel());
        $this->assertEquals(2, $level2->getLevel());

        // 将 level1 移动到根级别，这会递归更新其子级
        $level1->setParent(null);

        $this->assertEquals(0, $level1->getLevel());
        $this->assertEquals(1, $level2->getLevel()); // level2 仍然是 level1 的子级
    }
}
