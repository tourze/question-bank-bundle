<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;
use Tourze\QuestionBankBundle\QuestionBankBundle;

abstract class BaseIntegrationTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;
    protected ContainerInterface $container;

    protected static function createKernel(array $options = []): KernelInterface
    {
        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        return new IntegrationTestKernel($env, $debug, [
            \Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
            \Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
            \Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle::class => ['all' => true],
            QuestionBankBundle::class => ['all' => true],
        ]);
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $this->container = static::getContainer();
        $this->entityManager = $this->container->get(EntityManagerInterface::class);
        $this->setupDatabase();
    }

    protected function setupDatabase(): void
    {
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        // 删除并重新创建数据库模式
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        $this->cleanDatabase();
        self::ensureKernelShutdown();
        parent::tearDown();
    }

    protected function cleanDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        // 根据数据库平台处理外键检查
        if ($platform instanceof \Doctrine\DBAL\Platforms\SqlitePlatform) {
            $connection->executeStatement('PRAGMA foreign_keys = OFF');
        } elseif ($platform instanceof \Doctrine\DBAL\Platforms\MySQLPlatform) {
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        }

        // 按顺序清理表（先删除有外键的表）
        $tables = [
            'question_bank_question_tags',
            'question_bank_question_categories',
            'question_bank_options',
            'question_bank_questions',
            'question_bank_tags',
            'question_bank_categories',
        ];

        foreach ($tables as $table) {
            try {
                $connection->executeStatement("DELETE FROM {$table}");
            } catch (\Exception $e) {
                // 表可能不存在，忽略错误
            }
        }

        // 重新启用外键检查
        if ($platform instanceof \Doctrine\DBAL\Platforms\SqlitePlatform) {
            $connection->executeStatement('PRAGMA foreign_keys = ON');
        } elseif ($platform instanceof \Doctrine\DBAL\Platforms\MySQLPlatform) {
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
}