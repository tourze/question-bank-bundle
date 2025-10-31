<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\QuestionBankBundle\Exception\QuestionBankException;

/**
 * @internal
 */
#[CoversClass(QuestionBankException::class)]
final class QuestionBankExceptionTest extends AbstractExceptionTestCase
{
}
