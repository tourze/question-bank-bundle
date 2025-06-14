<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class QuestionBankBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
