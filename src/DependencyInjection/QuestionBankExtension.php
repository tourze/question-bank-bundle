<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class QuestionBankExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
