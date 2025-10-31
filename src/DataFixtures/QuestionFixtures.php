<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Entity\Option;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Entity\Tag;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

class QuestionFixtures extends Fixture implements DependentFixtureInterface
{
    public const QUESTION_SINGLE_CHOICE = 'question_single_choice';
    public const QUESTION_MULTIPLE_CHOICE = 'question_multiple_choice';
    public const QUESTION_TRUE_FALSE = 'question_true_false';

    public function load(ObjectManager $manager): void
    {
        // 获取分类
        $categories = [
            'technology' => $this->getReference(CategoryFixtures::CATEGORY_TECHNOLOGY, Category::class),
            'programming' => $this->getReference(CategoryFixtures::CATEGORY_PROGRAMMING, Category::class),
            'php' => $this->getReference(CategoryFixtures::CATEGORY_PHP, Category::class),
        ];

        // 获取标签
        $tags = [
            'php' => $this->getReference(TagFixtures::TAG_PHP, Tag::class),
            'oop' => $this->getReference(TagFixtures::TAG_OOP, Tag::class),
        ];

        // 创建问题和选项
        $this->createQuestionsWithOptions($manager, $categories, $tags);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            TagFixtures::class,
        ];
    }

    /**
     * @param array<string, Category> $categories
     * @param array<string, Tag> $tags
     */
    private function createQuestionsWithOptions(ObjectManager $manager, array $categories, array $tags): void
    {
        // 创建单选题
        $question1 = new Question();
        $question1->setTitle('PHP变量的作用域');
        $question1->setContent('以下关于PHP变量作用域的说法，哪个是正确的？');
        $question1->setType(QuestionType::SINGLE_CHOICE);
        $question1->setDifficulty(new Difficulty(3));
        $question1->addCategory($categories['php']);
        $question1->addTag($tags['php']);

        $option1 = new Option();
        $option1->setContent('全局变量可以在函数内部直接访问');
        $option1->setIsCorrect(false);
        $option1->setSortOrder(1);
        $option2 = new Option();
        $option2->setContent('局部变量只在函数内部有效');
        $option2->setIsCorrect(true);
        $option2->setSortOrder(2);
        $option3 = new Option();
        $option3->setContent('静态变量在函数调用后会被销毁');
        $option3->setIsCorrect(false);
        $option3->setSortOrder(3);
        $option4 = new Option();
        $option4->setContent('超全局变量需要使用global关键字声明');
        $option4->setIsCorrect(false);
        $option4->setSortOrder(4);

        $manager->persist($option1);
        $manager->persist($option2);
        $manager->persist($option3);
        $manager->persist($option4);

        $question1->addOption($option1);
        $question1->addOption($option2);
        $question1->addOption($option3);
        $question1->addOption($option4);

        $manager->persist($question1);
        $this->addReference(self::QUESTION_SINGLE_CHOICE, $question1);

        // 创建多选题
        $question2 = new Question();
        $question2->setTitle('面向对象编程特性');
        $question2->setContent('以下哪些是面向对象编程的特性？');
        $question2->setType(QuestionType::MULTIPLE_CHOICE);
        $question2->setDifficulty(new Difficulty(2));
        $question2->addCategory($categories['programming']);
        $question2->addTag($tags['oop']);

        $option5 = new Option();
        $option5->setContent('封装');
        $option5->setIsCorrect(true);
        $option5->setSortOrder(1);
        $option6 = new Option();
        $option6->setContent('继承');
        $option6->setIsCorrect(true);
        $option6->setSortOrder(2);
        $option7 = new Option();
        $option7->setContent('多态');
        $option7->setIsCorrect(true);
        $option7->setSortOrder(3);
        $option8 = new Option();
        $option8->setContent(' goto 语句');
        $option8->setIsCorrect(false);
        $option8->setSortOrder(4);

        $manager->persist($option5);
        $manager->persist($option6);
        $manager->persist($option7);
        $manager->persist($option8);

        $question2->addOption($option5);
        $question2->addOption($option6);
        $question2->addOption($option7);
        $question2->addOption($option8);

        $manager->persist($question2);
        $this->addReference(self::QUESTION_MULTIPLE_CHOICE, $question2);

        // 创建判断题
        $question3 = new Question();
        $question3->setTitle('面向对象编程');
        $question3->setContent('PHP支持面向对象编程');
        $question3->setType(QuestionType::TRUE_FALSE);
        $question3->setDifficulty(new Difficulty(1));
        $question3->addCategory($categories['programming']);
        $question3->addTag($tags['oop']);

        $option9 = new Option();
        $option9->setContent('True');
        $option9->setIsCorrect(true);
        $option9->setSortOrder(1);
        $option10 = new Option();
        $option10->setContent('False');
        $option10->setIsCorrect(false);
        $option10->setSortOrder(2);

        $manager->persist($option9);
        $manager->persist($option10);

        $question3->addOption($option9);
        $question3->addOption($option10);

        $manager->persist($question3);
        $this->addReference(self::QUESTION_TRUE_FALSE, $question3);
    }
}
