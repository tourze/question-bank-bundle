<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Fixtures;

use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Entity\Option;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Entity\Tag;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

/**
 * 测试实体工厂类
 * 提供创建测试实体的便捷方法
 */
class TestEntityFactory
{
    public static function createCompleteQuestionSet(): array
    {
        $categories = self::createHierarchicalCategories();
        $tags = self::createPopularTags();

        $question1 = self::createSingleChoiceQuestion('PHP变量的作用域');
        $question1->addCategory($categories['php']);
        $question1->addTag($tags[0]); // PHP tag

        $question2 = self::createMultipleChoiceQuestion('JavaScript数据类型');
        $question2->addCategory($categories['javascript']);
        $question2->addTag($tags[1]); // JavaScript tag

        $question3 = self::createTrueFalseQuestion('面向对象编程概念');
        $question3->addCategory($categories['programming']);
        $question3->addTag($tags[3]); // OOP tag

        $question4 = self::createFillInBlankQuestion('SQL查询语法');
        $question4->addCategory($categories['webDev']);
        $question4->addTag($tags[2]); // MySQL tag

        $question5 = self::createEssayQuestion('设计模式的应用');
        $question5->addCategory($categories['programming']);
        $question5->addTag($tags[4]); // Design Patterns tag

        return [
            'categories' => $categories,
            'tags' => $tags,
            'questions' => [$question1, $question2, $question3, $question4, $question5],
        ];
    }

    public static function createHierarchicalCategories(): array
    {
        $technology = self::createCategory([
            'name' => 'Technology',
            'code' => 'technology',
            'sortOrder' => 1,
        ]);

        $programming = self::createCategory([
            'name' => 'Programming',
            'code' => 'programming',
            'sortOrder' => 1,
        ]);
        $programming->setParent($technology);

        $webDev = self::createCategory([
            'name' => 'Web Development',
            'code' => 'web-development',
            'sortOrder' => 2,
        ]);
        $webDev->setParent($technology);

        $php = self::createCategory([
            'name' => 'PHP',
            'code' => 'php',
            'sortOrder' => 1,
        ]);
        $php->setParent($programming);

        $javascript = self::createCategory([
            'name' => 'JavaScript',
            'code' => 'javascript',
            'sortOrder' => 2,
        ]);
        $javascript->setParent($programming);

        return [
            'technology' => $technology,
            'programming' => $programming,
            'webDev' => $webDev,
            'php' => $php,
            'javascript' => $javascript,
        ];
    }

    public static function createCategory(array $overrides = []): Category
    {
        $defaults = [
            'name' => 'Test Category',
            'code' => 'test-category',
            'description' => null,
            'sortOrder' => 0,
            'isActive' => true,
        ];

        $data = array_merge($defaults, $overrides);

        $category = new Category($data['name'], $data['code']);

        if ($data['description'] !== null) {
            $category->setDescription($data['description']);
        }

        $category->setSortOrder($data['sortOrder']);
        $category->setValid($data['isActive']);

        return $category;
    }

    public static function createPopularTags(): array
    {
        return [
            self::createTag(['name' => 'PHP', 'slug' => 'php', 'color' => '#787CB5']),
            self::createTag(['name' => 'JavaScript', 'slug' => 'javascript', 'color' => '#F7DF1E']),
            self::createTag(['name' => 'MySQL', 'slug' => 'mysql', 'color' => '#4479A1']),
            self::createTag(['name' => 'OOP', 'slug' => 'oop', 'color' => '#FF6B6B']),
            self::createTag(['name' => 'Design Patterns', 'slug' => 'design-patterns', 'color' => '#4ECDC4']),
        ];
    }

    public static function createTag(array $overrides = []): Tag
    {
        $defaults = [
            'name' => 'Test Tag',
            'slug' => null,
            'description' => null,
            'color' => null,
        ];

        $data = array_merge($defaults, $overrides);

        $slug = $data['slug'] ?? self::generateSlug($data['name']);
        $tag = new Tag($data['name'], $slug);

        if ($data['description'] !== null) {
            $tag->setDescription($data['description']);
        }

        if ($data['color'] !== null) {
            $tag->setColor($data['color']);
        }

        return $tag;
    }

    private static function generateSlug(string $text): string
    {
        $slug = strtolower($text);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    public static function createSingleChoiceQuestion(string $title = 'Single Choice Question'): Question
    {
        return self::createQuestionWithOptions([
            'title' => $title,
            'type' => QuestionType::SINGLE_CHOICE,
            'options' => [
                ['content' => 'Correct Answer', 'isCorrect' => true, 'sortOrder' => 1],
                ['content' => 'Wrong Answer 1', 'isCorrect' => false, 'sortOrder' => 2],
                ['content' => 'Wrong Answer 2', 'isCorrect' => false, 'sortOrder' => 3],
                ['content' => 'Wrong Answer 3', 'isCorrect' => false, 'sortOrder' => 4],
            ],
        ]);
    }

    public static function createQuestionWithOptions(array $overrides = []): Question
    {
        $question = self::createQuestion($overrides);

        // 默认添加一些选项
        $options = $overrides['options'] ?? [
            ['content' => 'Option A', 'isCorrect' => true, 'sortOrder' => 1],
            ['content' => 'Option B', 'isCorrect' => false, 'sortOrder' => 2],
            ['content' => 'Option C', 'isCorrect' => false, 'sortOrder' => 3],
            ['content' => 'Option D', 'isCorrect' => false, 'sortOrder' => 4],
        ];

        foreach ($options as $optionData) {
            $option = self::createOption($optionData);
            $question->addOption($option);
        }

        return $question;
    }

    public static function createQuestion(array $overrides = []): Question
    {
        $defaults = [
            'title' => 'Test Question',
            'content' => 'Test question content',
            'type' => QuestionType::SINGLE_CHOICE,
            'difficulty' => 3,
            'score' => 10.0,
            'explanation' => null,
            'metadata' => null,
        ];

        $data = array_merge($defaults, $overrides);

        $question = new Question(
            $data['title'],
            $data['content'],
            $data['type'],
            new Difficulty($data['difficulty'])
        );

        if ($data['score'] !== 10.0) {
            $question->setScore($data['score']);
        }

        if ($data['explanation'] !== null) {
            $question->setExplanation($data['explanation']);
        }

        if ($data['metadata'] !== null) {
            $question->setMetadata($data['metadata']);
        }

        return $question;
    }

    public static function createOption(array $overrides = []): Option
    {
        $defaults = [
            'content' => 'Test Option',
            'isCorrect' => false,
            'sortOrder' => 0,
            'explanation' => null,
        ];

        $data = array_merge($defaults, $overrides);

        $option = new Option(
            $data['content'],
            $data['isCorrect'],
            $data['sortOrder']
        );

        if ($data['explanation'] !== null) {
            $option->setExplanation($data['explanation']);
        }

        return $option;
    }

    public static function createMultipleChoiceQuestion(string $title = 'Multiple Choice Question'): Question
    {
        return self::createQuestionWithOptions([
            'title' => $title,
            'type' => QuestionType::MULTIPLE_CHOICE,
            'options' => [
                ['content' => 'Correct Answer 1', 'isCorrect' => true, 'sortOrder' => 1],
                ['content' => 'Correct Answer 2', 'isCorrect' => true, 'sortOrder' => 2],
                ['content' => 'Wrong Answer 1', 'isCorrect' => false, 'sortOrder' => 3],
                ['content' => 'Wrong Answer 2', 'isCorrect' => false, 'sortOrder' => 4],
            ],
        ]);
    }

    public static function createTrueFalseQuestion(string $title = 'True/False Question'): Question
    {
        return self::createQuestionWithOptions([
            'title' => $title,
            'type' => QuestionType::TRUE_FALSE,
            'options' => [
                ['content' => 'True', 'isCorrect' => true, 'sortOrder' => 1],
                ['content' => 'False', 'isCorrect' => false, 'sortOrder' => 2],
            ],
        ]);
    }

    public static function createFillInBlankQuestion(string $title = 'Fill in Blank Question'): Question
    {
        return self::createQuestion([
            'title' => $title,
            'content' => 'PHP是一种___编程语言，主要用于___开发。',
            'type' => QuestionType::FILL_IN_BLANK,
        ]);
    }

    public static function createEssayQuestion(string $title = 'Essay Question'): Question
    {
        return self::createQuestion([
            'title' => $title,
            'content' => '请简述面向对象编程的三大特性及其作用。',
            'type' => QuestionType::ESSAY,
        ]);
    }
}