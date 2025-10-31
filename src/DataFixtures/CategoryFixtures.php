<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\QuestionBankBundle\Entity\Category;

class CategoryFixtures extends Fixture
{
    public const CATEGORY_ROOT = 'category_root';
    public const CATEGORY_TECHNOLOGY = 'category_technology';
    public const CATEGORY_PROGRAMMING = 'category_programming';
    public const CATEGORY_PHP = 'category_php';

    public function load(ObjectManager $manager): void
    {
        // 创建根分类
        $root = new Category();
        $root->setName('Root');
        $root->setCode('root');
        $root->setDescription('根分类');
        $manager->persist($root);
        $this->addReference(self::CATEGORY_ROOT, $root);

        // 创建技术分类
        $technology = new Category();
        $technology->setName('Technology');
        $technology->setCode('technology');
        $technology->setParent($root);
        $technology->setDescription('技术相关分类');
        $manager->persist($technology);
        $this->addReference(self::CATEGORY_TECHNOLOGY, $technology);

        // 创建编程分类
        $programming = new Category();
        $programming->setName('Programming');
        $programming->setCode('programming');
        $programming->setParent($technology);
        $programming->setDescription('编程相关分类');
        $manager->persist($programming);
        $this->addReference(self::CATEGORY_PROGRAMMING, $programming);

        // 创建PHP分类
        $php = new Category();
        $php->setName('PHP');
        $php->setCode('php');
        $php->setParent($programming);
        $php->setDescription('PHP编程语言');
        $manager->persist($php);
        $this->addReference(self::CATEGORY_PHP, $php);

        $manager->flush();
    }
}
