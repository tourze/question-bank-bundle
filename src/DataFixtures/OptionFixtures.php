<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * 选项实体通过 QuestionFixtures 创建，不需要独立的 OptionFixtures 类
 *
 * 这个类仅用于满足 PHPStan 的检查要求，实际上不加载任何数据
 */
class OptionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 选项实体通过 QuestionFixtures 创建，这里不需要加载任何数据
    }
}
