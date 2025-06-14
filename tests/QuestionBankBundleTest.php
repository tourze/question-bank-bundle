<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\QuestionBankBundle\QuestionBankBundle;

class QuestionBankBundleTest extends TestCase
{
    public function test_extendsBundle(): void
    {
        $bundle = new QuestionBankBundle();

        $this->assertInstanceOf(Bundle::class, $bundle);
    }

    public function test_getPath_returnsCorrectPath(): void
    {
        $bundle = new QuestionBankBundle();
        $expectedPath = dirname(__DIR__);

        $this->assertEquals($expectedPath, $bundle->getPath());
    }
}