<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\QuestionBankBundle\QuestionBankBundle;

/**
 * @internal
 */
#[CoversClass(QuestionBankBundle::class)]
#[RunTestsInSeparateProcesses]
final class QuestionBankBundleTest extends AbstractBundleTestCase
{
}
