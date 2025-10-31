<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\QuestionBankBundle\Entity\Tag;

class TagFixtures extends Fixture
{
    public const TAG_PHP = 'tag_php';
    public const TAG_OOP = 'tag_oop';
    public const TAG_DATABASE = 'tag_database';
    public const TAG_WEB = 'tag_web';

    public function load(ObjectManager $manager): void
    {
        // 创建PHP标签
        $php = new Tag();
        $php->setName('PHP');
        $php->setSlug('php');
        $php->setColor('#787CB5');
        $php->setDescription('PHP编程语言相关');
        $manager->persist($php);
        $this->addReference(self::TAG_PHP, $php);

        // 创建OOP标签
        $oop = new Tag();
        $oop->setName('OOP');
        $oop->setSlug('oop');
        $oop->setColor('#FF6B6B');
        $oop->setDescription('面向对象编程');
        $manager->persist($oop);
        $this->addReference(self::TAG_OOP, $oop);

        // 创建数据库标签
        $database = new Tag();
        $database->setName('Database');
        $database->setSlug('database');
        $database->setColor('#4ECDC4');
        $database->setDescription('数据库相关');
        $manager->persist($database);
        $this->addReference(self::TAG_DATABASE, $database);

        // 创建Web开发标签
        $web = new Tag();
        $web->setName('Web');
        $web->setSlug('web');
        $web->setColor('#45B7D1');
        $web->setDescription('Web开发');
        $manager->persist($web);
        $this->addReference(self::TAG_WEB, $web);

        $manager->flush();
    }
}
