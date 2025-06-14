<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class QuestionBankExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        // 从 ENV 变量读取配置，使用默认值
        $container->setParameter('question_bank.question.max_options', 
            (int) ($_ENV['QUESTION_BANK_MAX_OPTIONS'] ?? 10));
        $container->setParameter('question_bank.question.max_content_length', 
            (int) ($_ENV['QUESTION_BANK_MAX_CONTENT_LENGTH'] ?? 5000));
        $container->setParameter('question_bank.category.max_depth', 
            (int) ($_ENV['QUESTION_BANK_CATEGORY_MAX_DEPTH'] ?? 5));
        $container->setParameter('question_bank.category.cache_ttl', 
            (int) ($_ENV['QUESTION_BANK_CATEGORY_CACHE_TTL'] ?? 3600));
        $container->setParameter('question_bank.tag.max_per_question', 
            (int) ($_ENV['QUESTION_BANK_TAG_MAX_PER_QUESTION'] ?? 10));
        $container->setParameter('question_bank.tag.auto_slug', 
            filter_var($_ENV['QUESTION_BANK_TAG_AUTO_SLUG'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
        $container->setParameter('question_bank.search.engine', 
            $_ENV['QUESTION_BANK_SEARCH_ENGINE'] ?? 'database');
        $container->setParameter('question_bank.search.min_query_length', 
            (int) ($_ENV['QUESTION_BANK_SEARCH_MIN_QUERY_LENGTH'] ?? 2));

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');
    }
}
