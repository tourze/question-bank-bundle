<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use Tourze\QuestionBankBundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // 初始化设置（如果需要）
    }

    public function testGetMenuItems(): void
    {
        // 从容器获取 AdminMenu 服务
        $adminMenu = self::getService(AdminMenu::class);
        $menuItems = $adminMenu->getMenuItems();

        $this->assertIsArray($menuItems);
        $this->assertArrayHasKey('question_bank', $menuItems);
        $questionBankMenu = $menuItems['question_bank'];
        $this->assertIsArray($questionBankMenu);
        $this->assertSame('题库管理', $questionBankMenu['label']);
        $this->assertArrayHasKey('submenu', $questionBankMenu);
    }

    public function testGetStatistics(): void
    {
        // 从容器获取 AdminMenu 服务
        $adminMenu = self::getService(AdminMenu::class);
        $statistics = $adminMenu->getStatistics();

        $this->assertIsArray($statistics);
        $this->assertArrayHasKey('questions', $statistics);
        $this->assertArrayHasKey('categories', $statistics);
        $this->assertArrayHasKey('tags', $statistics);
        $this->assertArrayHasKey('summary', $statistics);

        $questionStats = $statistics['questions'];
        $this->assertIsArray($questionStats);
        $this->assertArrayHasKey('total', $questionStats);
        $this->assertArrayHasKey('published', $questionStats);
        $this->assertArrayHasKey('draft', $questionStats);
        $this->assertArrayHasKey('archived', $questionStats);
    }
}
