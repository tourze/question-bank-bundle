<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Entity\Category;

class CategoryTest extends TestCase
{
    public function test_constructor_setsDefaultValues(): void
    {
        $category = new Category('Test Category', 'test_category');

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

    public function test_setName_updatesName(): void
    {
        $category = new Category('Test Category', 'test_category');
        
        $category->setName('New Name');

        $this->assertEquals('New Name', $category->getName());
    }

    public function test_setCode_updatesCodeAndPath(): void
    {
        $category = new Category('Test Category', 'test_category');
        
        $category->setCode('new_code');

        $this->assertEquals('new_code', $category->getCode());
        $this->assertEquals('/new_code', $category->getPath());
    }

    public function test_setDescription_updatesDescription(): void
    {
        $category = new Category('Test Category', 'test_category');
        
        $category->setDescription('Test Description');

        $this->assertEquals('Test Description', $category->getDescription());
    }

    public function test_setSortOrder_updatesSortOrder(): void
    {
        $category = new Category('Test Category', 'test_category');
        
        $category->setSortOrder(10);

        $this->assertEquals(10, $category->getSortOrder());
    }

    public function test_setValid_updatesValidFlag(): void
    {
        $category = new Category('Test Category', 'test_category');
        
        $category->setValid(false);

        $this->assertFalse($category->isValid());
    }

    public function test_setParent_updatesParentAndLevel(): void
    {
        $parent = new Category('Parent', 'parent');
        $child = new Category('Child', 'child');
        
        $child->setParent($parent);

        $this->assertEquals($parent, $child->getParent());
        $this->assertEquals(1, $child->getLevel());
        $this->assertEquals('/parent/child', $child->getPath());
    }

    public function test_setParent_throwsExceptionWhenSettingSelfAsParent(): void
    {
        $category = new Category('Test Category', 'test_category');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Category cannot be its own parent');
        
        $category->setParent($category);
    }

    public function test_setParent_throwsExceptionWhenSettingDescendantAsParent(): void
    {
        $grandparent = new Category('Grandparent', 'grandparent');
        $parent = new Category('Parent', 'parent');
        $child = new Category('Child', 'child');
        
        $parent->setParent($grandparent);
        $child->setParent($parent);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot set descendant as parent');
        
        $grandparent->setParent($child);
    }

    public function test_addChild_addsChildAndSetsParent(): void
    {
        $parent = new Category('Parent', 'parent');
        $child = new Category('Child', 'child');
        
        $parent->addChild($child);

        $this->assertCount(1, $parent->getChildren());
        $this->assertTrue($parent->getChildren()->contains($child));
        $this->assertEquals($parent, $child->getParent());
    }

    public function test_addChild_doesNotAddDuplicateChild(): void
    {
        $parent = new Category('Parent', 'parent');
        $child = new Category('Child', 'child');
        
        $parent->addChild($child);
        $parent->addChild($child); // 添加重复子分类

        $this->assertCount(1, $parent->getChildren());
    }

    public function test_removeChild_removesChildAndClearsParent(): void
    {
        $parent = new Category('Parent', 'parent');
        $child = new Category('Child', 'child');
        $parent->addChild($child);
        
        $parent->removeChild($child);

        $this->assertCount(0, $parent->getChildren());
        $this->assertFalse($parent->getChildren()->contains($child));
        $this->assertNull($child->getParent());
    }

    public function test_isAncestorOf_returnsTrueForDescendant(): void
    {
        $grandparent = new Category('Grandparent', 'grandparent');
        $parent = new Category('Parent', 'parent');
        $child = new Category('Child', 'child');
        
        $parent->setParent($grandparent);
        $child->setParent($parent);

        $this->assertTrue($grandparent->isAncestorOf($child));
        $this->assertTrue($parent->isAncestorOf($child));
        $this->assertFalse($child->isAncestorOf($grandparent));
    }

    public function test_isAncestorOf_returnsFalseForSelf(): void
    {
        $category = new Category('Test Category', 'test_category');

        $this->assertFalse($category->isAncestorOf($category));
    }

    public function test_isDescendantOf_returnsTrueForAncestor(): void
    {
        $grandparent = new Category('Grandparent', 'grandparent');
        $parent = new Category('Parent', 'parent');
        $child = new Category('Child', 'child');
        
        $parent->setParent($grandparent);
        $child->setParent($parent);

        $this->assertTrue($child->isDescendantOf($grandparent));
        $this->assertTrue($child->isDescendantOf($parent));
        $this->assertFalse($grandparent->isDescendantOf($child));
    }

    public function test_getAncestors_returnsAncestorsInOrder(): void
    {
        $grandparent = new Category('Grandparent', 'grandparent');
        $parent = new Category('Parent', 'parent');
        $child = new Category('Child', 'child');
        
        $parent->setParent($grandparent);
        $child->setParent($parent);
        
        $ancestors = $child->getAncestors();

        $this->assertCount(2, $ancestors);
        $this->assertEquals($grandparent, $ancestors[0]);
        $this->assertEquals($parent, $ancestors[1]);
    }

    public function test_getAncestors_returnsEmptyArrayForRootCategory(): void
    {
        $category = new Category('Root', 'root');
        
        $ancestors = $category->getAncestors();

        $this->assertCount(0, $ancestors);
    }

    public function test_getFullPath_returnsCompletePathIncludingSelf(): void
    {
        $grandparent = new Category('Grandparent', 'grandparent');
        $parent = new Category('Parent', 'parent');
        $child = new Category('Child', 'child');
        
        $parent->setParent($grandparent);
        $child->setParent($parent);
        
        $fullPath = $child->getFullPath();

        $this->assertCount(3, $fullPath);
        $this->assertEquals($grandparent, $fullPath[0]);
        $this->assertEquals($parent, $fullPath[1]);
        $this->assertEquals($child, $fullPath[2]);
    }

    public function test_toString_returnsName(): void
    {
        $category = new Category('Test Category', 'test_category');

        $this->assertEquals('Test Category', (string) $category);
    }

    public function test_hierarchicalPathUpdate_withParentRelationshipChanges(): void
    {
        $parent = new Category('Parent', 'parent');
        $child = new Category('Child', 'child');
        $grandchild = new Category('Grandchild', 'grandchild');
        
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

    public function test_hierarchicalLevelUpdate_updatesChildrenLevels(): void
    {
        $root = new Category('Root', 'root');
        $level1 = new Category('Level1', 'level1');
        $level2 = new Category('Level2', 'level2');
        
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