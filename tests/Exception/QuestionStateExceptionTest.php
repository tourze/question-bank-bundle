<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\QuestionBankBundle\Exception\QuestionStateException;

/**
 * @internal
 */
#[CoversClass(QuestionStateException::class)]
final class QuestionStateExceptionTest extends AbstractExceptionTestCase
{
}
