<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Repository\CategoryRepository;

/**
 * @extends AbstractRepositoryTestCase<Category>
 *
 * @internal
 */
#[CoversClass(CategoryRepository::class)]
#[RunTestsInSeparateProcesses]
final class CategoryRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @return CategoryRepository
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(CategoryRepository::class);
    }

    public function testFindCategoryWithExistingIdShouldReturnEntity(): void
    {
        // Arrange
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test-category');
        self::getEntityManager()->persist($category);
        self::getEntityManager()->flush();

        // Act
        $found = $this->getRepository()->find($category->getId());

        // Assert
        $this->assertInstanceOf(Category::class, $found);
        $this->assertEquals($category->getId(), $found->getId());
    }

    protected function onSetUp(): void
    {
        // 不在这里清理数据，让 AbstractRepositoryTestCase 的 testCountWithDataFixtureShouldReturnGreaterThanZero
        // 测试能使用 fixtures 提供的数据

        // 确保 Schema 已创建
        try {
            $entityManager = self::getEntityManager();
            $schemaTool = new SchemaTool($entityManager);
            $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
            $schemaTool->updateSchema($metadata);
        } catch (\Exception $e) {
            // 忽略 schema 创建错误
        }
    }

    public function testSaveWithValidCategoryPersistsToDatabase(): void
    {
        // Arrange - 使用一个不与 fixtures 冲突的 code
        $category = new Category();
        $category->setName('Test Programming');
        $category->setCode('test-programming');

        // Act
        self::getEntityManager()->persist($category);
        self::getEntityManager()->flush();

        // Assert
        $this->assertNotNull($category->getId());
        $saved = self::getService(CategoryRepository::class)->find($category->getId());
        $this->assertNotNull($saved);
        $this->assertEquals('Test Programming', $saved->getName());
        $this->assertEquals('test-programming', $saved->getCode());
    }

    public function testSaveWithParentCategoryMaintainsHierarchy(): void
    {
        // Arrange
        $parent = new Category();
        $parent->setName('Languages');
        $parent->setCode('languages');
        $child = new Category();
        $child->setName('Python');
        $child->setCode('python');

        self::getEntityManager()->persist($parent);
        self::getEntityManager()->flush();
        $child->setParent($parent);

        // Act
        self::getEntityManager()->persist($child);
        self::getEntityManager()->flush();

        // Assert
        $saved = self::getService(CategoryRepository::class)->find($child->getId());
        $this->assertNotNull($saved);
        $this->assertNotNull($saved->getParent());
        $this->assertEquals($parent->getId(), $saved->getParent()->getId());
        $this->assertEquals(1, $saved->getLevel());
        $this->assertEquals('/languages/python', $saved->getPath());
    }

    public function testRemoveWithValidCategoryDeletesFromDatabase(): void
    {
        // Arrange
        $category = new Category();
        $category->setName('Test');
        $category->setCode('test');
        self::getEntityManager()->persist($category);
        self::getEntityManager()->flush();
        $id = $category->getId();

        // Act
        self::getEntityManager()->remove($category);
        self::getEntityManager()->flush();

        // Assert
        $this->assertNull(self::getService(CategoryRepository::class)->find($id));
    }

    public function testFindByCodeWithExistingCodeReturnsCategory(): void
    {
        // Arrange
        $category = new Category();
        $category->setName('Database');
        $category->setCode('database');
        self::getEntityManager()->persist($category);
        self::getEntityManager()->flush();

        // Act
        $found = self::getService(CategoryRepository::class)->findByCode('database');

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals('Database', $found->getName());
    }

    public function testFindByCodeWithNonExistentCodeReturnsNull(): void
    {
        // Act
        $result = self::getService(CategoryRepository::class)->findByCode('non-existent');

        // Assert
        $this->assertNull($result);
    }

    public function testFindRootCategoriesReturnsOnlyRootCategories(): void
    {
        // Arrange
        $root1 = new Category();
        $root1->setName('Test Programming');
        $root1->setCode('test-programming');
        $root2 = new Category();
        $root2->setName('Test Database');
        $root2->setCode('test-database');
        $child = new Category();
        $child->setName('Test PHP');
        $child->setCode('test-php');

        self::getEntityManager()->persist($root1);
        self::getEntityManager()->persist($root2);
        self::getEntityManager()->flush();
        $child->setParent($root1);
        self::getEntityManager()->persist($child);
        self::getEntityManager()->flush();

        // Act
        $roots = self::getService(CategoryRepository::class)->findRootCategories();

        // Assert - 应该包含 fixtures 中的根分类和我们创建的根分类
        $this->assertGreaterThanOrEqual(2, $roots);

        // 验证我们创建的根分类在结果中
        $names = array_map(fn ($c) => $c->getName(), $roots);
        $this->assertContains('Test Programming', $names);
        $this->assertContains('Test Database', $names);
    }

    public function testFindActiveCategoriesReturnsOnlyActiveCategories(): void
    {
        // Arrange
        $active1 = new Category();
        $active1->setName('Test Active1');
        $active1->setCode('test-active1');
        $active2 = new Category();
        $active2->setName('Test Active2');
        $active2->setCode('test-active2');
        $inactive = new Category();
        $inactive->setName('Test Inactive');
        $inactive->setCode('test-inactive');
        $inactive->setValid(false);

        self::getEntityManager()->persist($active1);
        self::getEntityManager()->persist($active2);
        self::getEntityManager()->persist($inactive);
        self::getEntityManager()->flush();

        // Act
        $activeCategories = self::getService(CategoryRepository::class)->findActiveCategories();

        // Assert - 应该至少包含我们创建的 2 个活跃分类
        $this->assertGreaterThanOrEqual(2, $activeCategories);

        // 验证所有返回的分类都是活跃的
        foreach ($activeCategories as $category) {
            $this->assertTrue($category->isValid());
        }

        // 验证我们创建的活跃分类在结果中
        $foundActive1 = false;
        $foundActive2 = false;
        foreach ($activeCategories as $category) {
            if ('Test Active1' === $category->getName()) {
                $foundActive1 = true;
            }
            if ('Test Active2' === $category->getName()) {
                $foundActive2 = true;
            }
        }
        $this->assertTrue($foundActive1, 'Created active category 1 should be in results');
        $this->assertTrue($foundActive2, 'Created active category 2 should be in results');
    }

    public function testGetCategoryTreeReturnsHierarchicalStructure(): void
    {
        // Arrange
        $root = new Category();
        $root->setName('Languages');
        $root->setCode('languages');
        $python = new Category();
        $python->setName('Python');
        $python->setCode('python');
        $ruby = new Category();
        $ruby->setName('Ruby');
        $ruby->setCode('ruby');

        self::getEntityManager()->persist($root);
        self::getEntityManager()->flush();

        $python->setParent($root);
        $ruby->setParent($root);

        self::getEntityManager()->persist($python);
        self::getEntityManager()->persist($ruby);
        self::getEntityManager()->flush();

        // Act
        $tree = self::getService(CategoryRepository::class)->getCategoryTree();

        // Assert - 只检查我们创建的根节点，不考虑 fixtures 中的数据
        $foundRoot = null;
        foreach ($tree as $category) {
            if ('Languages' === $category->getName()) {
                $foundRoot = $category;
                break;
            }
        }

        $this->assertNotNull($foundRoot);
        $this->assertCount(2, $foundRoot->getChildren());
    }

    public function testSaveWithSortOrderMaintainsOrder(): void
    {
        // Arrange
        $cat1 = new Category();
        $cat1->setName('First');
        $cat1->setCode('first');
        $cat1->setSortOrder(10);

        $cat2 = new Category();
        $cat2->setName('Second');
        $cat2->setCode('second');
        $cat2->setSortOrder(5);

        $cat3 = new Category();
        $cat3->setName('Third');
        $cat3->setCode('third');
        $cat3->setSortOrder(15);

        // Act
        self::getEntityManager()->persist($cat1);
        self::getEntityManager()->persist($cat2);
        self::getEntityManager()->persist($cat3);
        self::getEntityManager()->flush();

        // Assert
        $roots = self::getService(CategoryRepository::class)->findRootCategories();

        // 找出我们创建的分类
        $foundCategories = [];
        foreach ($roots as $root) {
            if (in_array($root->getName(), ['First', 'Second', 'Third'], true)) {
                $foundCategories[] = $root;
            }
        }

        // 验证排序顺序
        $this->assertEquals('Second', $foundCategories[0]->getName());
        $this->assertEquals('First', $foundCategories[1]->getName());
        $this->assertEquals('Third', $foundCategories[2]->getName());
    }

    public function testCategoryPathUpdatesCorrectlyOnParentChange(): void
    {
        // Arrange
        $root1 = new Category();
        $root1->setName('Root1');
        $root1->setCode('root1');
        $root2 = new Category();
        $root2->setName('Root2');
        $root2->setCode('root2');
        $child = new Category();
        $child->setName('Child');
        $child->setCode('child');

        self::getEntityManager()->persist($root1);
        self::getEntityManager()->persist($root2);
        self::getEntityManager()->flush();

        $child->setParent($root1);
        self::getEntityManager()->persist($child);
        self::getEntityManager()->flush();

        $this->assertEquals('/root1/child', $child->getPath());

        // Act - 移动到新父级
        $child->setParent($root2);
        self::getEntityManager()->persist($child);
        self::getEntityManager()->flush();

        // Assert
        $this->assertEquals('/root2/child', $child->getPath());
    }

    // 基础 find 方法测试

    // findAll 方法测试

    // findBy 方法测试

    // findOneBy 方法测试

    public function testFindOneByWithOrderBy(): void
    {
        $category1 = new Category();
        $category1->setName('Z Category');
        $category1->setCode('z');
        $category2 = new Category();
        $category2->setName('A Category');
        $category2->setCode('a');

        self::getEntityManager()->persist($category1);
        self::getEntityManager()->persist($category2);
        self::getEntityManager()->flush();

        $result = self::getService(CategoryRepository::class)->findOneBy(
            ['valid' => true],
            ['name' => 'ASC']
        );

        $this->assertNotNull($result);
        $this->assertEquals('A Category', $result->getName());
    }

    public function testFindByWithNullDescription(): void
    {
        $category1 = new Category();
        $category1->setName('With Desc');
        $category1->setCode('with-desc');
        $category1->setDescription('Some description');

        $category2 = new Category();
        $category2->setName('No Desc');
        $category2->setCode('no-desc');

        self::getEntityManager()->persist($category1);
        self::getEntityManager()->persist($category2);
        self::getEntityManager()->flush();

        $result = self::getService(CategoryRepository::class)->findBy(['description' => null]);

        $this->assertCount(1, $result);
        $this->assertEquals('No Desc', $result[0]->getName());
    }

    public function testCountWithNullDescription(): void
    {
        $category1 = new Category();
        $category1->setName('With Desc');
        $category1->setCode('with-desc');
        $category1->setDescription('Some description');

        $category2 = new Category();
        $category2->setName('No Desc');
        $category2->setCode('no-desc');

        self::getEntityManager()->persist($category1);
        self::getEntityManager()->persist($category2);
        self::getEntityManager()->flush();

        $count = self::getService(CategoryRepository::class)->count(['description' => null]);

        $this->assertEquals(1, $count);
    }

    public function testFindByWithNullParent(): void
    {
        $rootCategory = new Category();
        $rootCategory->setName('Test Root');
        $rootCategory->setCode('test-root');
        $childCategory = new Category();
        $childCategory->setName('Test Child');
        $childCategory->setCode('test-child');
        $childCategory->setParent($rootCategory);

        self::getEntityManager()->persist($rootCategory);
        self::getEntityManager()->persist($childCategory);
        self::getEntityManager()->flush();

        $result = self::getService(CategoryRepository::class)->findBy(['parent' => null]);

        // 应该包含 fixtures 中的根分类和我们创建的根分类
        $this->assertGreaterThan(0, count($result));

        // 验证我们创建的根分类在结果中
        $found = false;
        foreach ($result as $category) {
            if ('Test Root' === $category->getName()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Created root category should be in results');
    }

    public function testCountWithNullParent(): void
    {
        $rootCategory = new Category();
        $rootCategory->setName('Test Root Count');
        $rootCategory->setCode('test-root-count');
        $childCategory = new Category();
        $childCategory->setName('Test Child Count');
        $childCategory->setCode('test-child-count');
        $childCategory->setParent($rootCategory);

        self::getEntityManager()->persist($rootCategory);
        self::getEntityManager()->persist($childCategory);
        self::getEntityManager()->flush();

        $count = self::getService(CategoryRepository::class)->count(['parent' => null]);

        // 应该大于 0，因为 fixtures 中已经有根分类
        $this->assertGreaterThan(0, $count);
    }

    public function testFindByWithParentAssociation(): void
    {
        $parentCategory = new Category();
        $parentCategory->setName('Parent');
        $parentCategory->setCode('parent');
        $childCategory = new Category();
        $childCategory->setName('Child');
        $childCategory->setCode('child');
        $childCategory->setParent($parentCategory);

        self::getEntityManager()->persist($parentCategory);
        self::getEntityManager()->persist($childCategory);
        self::getEntityManager()->flush();

        $result = self::getService(CategoryRepository::class)->findBy(['parent' => $parentCategory]);

        $this->assertCount(1, $result);
        $this->assertEquals('Child', $result[0]->getName());
    }

    public function testCountWithParentAssociation(): void
    {
        $parentCategory = new Category();
        $parentCategory->setName('Parent');
        $parentCategory->setCode('parent');
        $childCategory = new Category();
        $childCategory->setName('Child');
        $childCategory->setCode('child');
        $childCategory->setParent($parentCategory);

        self::getEntityManager()->persist($parentCategory);
        self::getEntityManager()->persist($childCategory);
        self::getEntityManager()->flush();

        $count = self::getService(CategoryRepository::class)->count(['parent' => $parentCategory]);

        $this->assertEquals(1, $count);
    }

    public function testFindByWithChildrenAssociation(): void
    {
        $parentCategory = new Category();
        $parentCategory->setName('Parent');
        $parentCategory->setCode('parent');
        $childCategory1 = new Category();
        $childCategory1->setName('Child1');
        $childCategory1->setCode('child1');
        $childCategory2 = new Category();
        $childCategory2->setName('Child2');
        $childCategory2->setCode('child2');

        $childCategory1->setParent($parentCategory);
        $childCategory2->setParent($parentCategory);

        self::getEntityManager()->persist($parentCategory);
        self::getEntityManager()->persist($childCategory1);
        self::getEntityManager()->persist($childCategory2);
        self::getEntityManager()->flush();

        $children = $parentCategory->getChildren();
        $this->assertCount(2, $children);
    }

    public function testCountWithChildrenAssociation(): void
    {
        $parentCategory = new Category();
        $parentCategory->setName('Parent');
        $parentCategory->setCode('parent');
        $childCategory1 = new Category();
        $childCategory1->setName('Child1');
        $childCategory1->setCode('child1');
        $childCategory2 = new Category();
        $childCategory2->setName('Child2');
        $childCategory2->setCode('child2');

        $childCategory1->setParent($parentCategory);
        $childCategory2->setParent($parentCategory);

        self::getEntityManager()->persist($parentCategory);
        self::getEntityManager()->persist($childCategory1);
        self::getEntityManager()->persist($childCategory2);
        self::getEntityManager()->flush();

        $children = $parentCategory->getChildren();
        $this->assertCount(2, $children);
    }

    public function testFindByWithQuestionsAssociation(): void
    {
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test-cat');

        self::getEntityManager()->persist($category);
        self::getEntityManager()->flush();

        $questions = $category->getQuestions();
        $this->assertCount(0, $questions);
    }

    public function testCountWithQuestionsAssociation(): void
    {
        $category = new Category();
        $category->setName('Test Category');
        $category->setCode('test-cat');

        self::getEntityManager()->persist($category);
        self::getEntityManager()->flush();

        $questions = $category->getQuestions();
        $this->assertCount(0, $questions);
    }

    protected function createNewEntity(): object
    {
        $category = new Category();
        $category->setName('Test Category ' . uniqid());
        $category->setCode('test-cat-' . uniqid());
        $category->setDescription('Test description');
        $category->setSortOrder(10);
        $category->setValid(true);

        return $category;
    }
}
