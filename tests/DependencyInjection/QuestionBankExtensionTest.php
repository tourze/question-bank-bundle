<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\QuestionBankBundle\DependencyInjection\QuestionBankExtension;

class QuestionBankExtensionTest extends TestCase
{
    private QuestionBankExtension $extension;
    private ContainerBuilder $container;

    public function test_load_registersDefaultParameters(): void
    {
        $this->extension->load([], $this->container);

        // 检查默认参数值
        $this->assertEquals(10, $this->container->getParameter('question_bank.question.max_options'));
        $this->assertEquals(5000, $this->container->getParameter('question_bank.question.max_content_length'));
        $this->assertEquals(5, $this->container->getParameter('question_bank.category.max_depth'));
        $this->assertEquals(3600, $this->container->getParameter('question_bank.category.cache_ttl'));
        $this->assertEquals(10, $this->container->getParameter('question_bank.tag.max_per_question'));
        $this->assertTrue($this->container->getParameter('question_bank.tag.auto_slug'));
        $this->assertEquals('database', $this->container->getParameter('question_bank.search.engine'));
        $this->assertEquals(2, $this->container->getParameter('question_bank.search.min_query_length'));
    }

    public function test_load_withEnvironmentVariables_overridesDefaults(): void
    {
        // 设置环境变量
        $_ENV['QUESTION_BANK_MAX_OPTIONS'] = '15';
        $_ENV['QUESTION_BANK_MAX_CONTENT_LENGTH'] = '8000';
        $_ENV['QUESTION_BANK_CATEGORY_MAX_DEPTH'] = '7';
        $_ENV['QUESTION_BANK_CATEGORY_CACHE_TTL'] = '7200';
        $_ENV['QUESTION_BANK_TAG_MAX_PER_QUESTION'] = '20';
        $_ENV['QUESTION_BANK_TAG_AUTO_SLUG'] = 'false';
        $_ENV['QUESTION_BANK_SEARCH_ENGINE'] = 'elasticsearch';
        $_ENV['QUESTION_BANK_SEARCH_MIN_QUERY_LENGTH'] = '3';

        $this->extension->load([], $this->container);

        // 检查环境变量覆盖的参数值
        $this->assertEquals(15, $this->container->getParameter('question_bank.question.max_options'));
        $this->assertEquals(8000, $this->container->getParameter('question_bank.question.max_content_length'));
        $this->assertEquals(7, $this->container->getParameter('question_bank.category.max_depth'));
        $this->assertEquals(7200, $this->container->getParameter('question_bank.category.cache_ttl'));
        $this->assertEquals(20, $this->container->getParameter('question_bank.tag.max_per_question'));
        $this->assertFalse($this->container->getParameter('question_bank.tag.auto_slug'));
        $this->assertEquals('elasticsearch', $this->container->getParameter('question_bank.search.engine'));
        $this->assertEquals(3, $this->container->getParameter('question_bank.search.min_query_length'));

        // 清理环境变量
        unset($_ENV['QUESTION_BANK_MAX_OPTIONS']);
        unset($_ENV['QUESTION_BANK_MAX_CONTENT_LENGTH']);
        unset($_ENV['QUESTION_BANK_CATEGORY_MAX_DEPTH']);
        unset($_ENV['QUESTION_BANK_CATEGORY_CACHE_TTL']);
        unset($_ENV['QUESTION_BANK_TAG_MAX_PER_QUESTION']);
        unset($_ENV['QUESTION_BANK_TAG_AUTO_SLUG']);
        unset($_ENV['QUESTION_BANK_SEARCH_ENGINE']);
        unset($_ENV['QUESTION_BANK_SEARCH_MIN_QUERY_LENGTH']);
    }

    public function test_load_loadsCoreServices(): void
    {
        $this->extension->load([], $this->container);

        // 验证服务配置文件被加载
        $this->assertTrue($this->container->hasParameter('question_bank.question.max_options'));
        $this->assertTrue($this->container->hasParameter('question_bank.search.engine'));
    }

    public function test_load_withBooleanEnvironmentVariables(): void
    {
        // 测试不同的布尔值表示
        $_ENV['QUESTION_BANK_TAG_AUTO_SLUG'] = '1';
        $this->extension->load([], $this->container);
        $this->assertTrue($this->container->getParameter('question_bank.tag.auto_slug'));

        $_ENV['QUESTION_BANK_TAG_AUTO_SLUG'] = '0';
        $this->extension->load([], $this->container);
        $this->assertFalse($this->container->getParameter('question_bank.tag.auto_slug'));

        $_ENV['QUESTION_BANK_TAG_AUTO_SLUG'] = 'yes';
        $this->extension->load([], $this->container);
        $this->assertTrue($this->container->getParameter('question_bank.tag.auto_slug'));

        $_ENV['QUESTION_BANK_TAG_AUTO_SLUG'] = 'no';
        $this->extension->load([], $this->container);
        $this->assertFalse($this->container->getParameter('question_bank.tag.auto_slug'));

        unset($_ENV['QUESTION_BANK_TAG_AUTO_SLUG']);
    }

    public function test_load_withInvalidIntegerValues_usesZero(): void
    {
        $_ENV['QUESTION_BANK_MAX_OPTIONS'] = 'invalid';

        $this->extension->load([], $this->container);

        $this->assertEquals(0, $this->container->getParameter('question_bank.question.max_options'));

        unset($_ENV['QUESTION_BANK_MAX_OPTIONS']);
    }

    public function test_load_handlesEmptyEnvironmentVariables(): void
    {
        $_ENV['QUESTION_BANK_SEARCH_ENGINE'] = '';

        $this->extension->load([], $this->container);

        // 空字符串应该被保留，不应该被替换为默认值
        $this->assertEquals('', $this->container->getParameter('question_bank.search.engine'));

        unset($_ENV['QUESTION_BANK_SEARCH_ENGINE']);
    }

    protected function setUp(): void
    {
        $this->extension = new QuestionBankExtension();
        $this->container = new ContainerBuilder();
    }

    protected function tearDown(): void
    {
        // 确保清理所有可能设置的环境变量
        unset($_ENV['QUESTION_BANK_MAX_OPTIONS']);
        unset($_ENV['QUESTION_BANK_MAX_CONTENT_LENGTH']);
        unset($_ENV['QUESTION_BANK_CATEGORY_MAX_DEPTH']);
        unset($_ENV['QUESTION_BANK_CATEGORY_CACHE_TTL']);
        unset($_ENV['QUESTION_BANK_TAG_MAX_PER_QUESTION']);
        unset($_ENV['QUESTION_BANK_TAG_AUTO_SLUG']);
        unset($_ENV['QUESTION_BANK_SEARCH_ENGINE']);
        unset($_ENV['QUESTION_BANK_SEARCH_MIN_QUERY_LENGTH']);
    }
} 