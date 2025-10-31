<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use Tourze\QuestionBankBundle\DependencyInjection\QuestionBankExtension;

/**
 * @internal
 */
#[CoversClass(QuestionBankExtension::class)]
final class QuestionBankExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private QuestionBankExtension $extension;

    private ContainerBuilder $container;

    public function testLoadRegistersDefaultParameters(): void
    {
        $this->extension->load([], $this->container);

        // 检查参数是否被注册（实际值由环境变量决定）
        $this->assertTrue($this->container->hasParameter('question_bank.question.max_options'));
        $this->assertTrue($this->container->hasParameter('question_bank.question.max_content_length'));
        $this->assertTrue($this->container->hasParameter('question_bank.category.max_depth'));
        $this->assertTrue($this->container->hasParameter('question_bank.category.cache_ttl'));
        $this->assertTrue($this->container->hasParameter('question_bank.tag.max_per_question'));
        $this->assertTrue($this->container->hasParameter('question_bank.tag.auto_slug'));
        $this->assertTrue($this->container->hasParameter('question_bank.search.engine'));
        $this->assertTrue($this->container->hasParameter('question_bank.search.min_query_length'));
    }

    public function testLoadLoadsServicesConfiguration(): void
    {
        $this->extension->load([], $this->container);

        // 验证服务配置文件被加载
        $this->assertTrue($this->container->hasParameter('question_bank.question.max_options'));
        $this->assertTrue($this->container->hasParameter('question_bank.search.engine'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new QuestionBankExtension();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
    }
}
