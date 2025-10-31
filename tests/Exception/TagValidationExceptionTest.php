<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\QuestionBankBundle\Exception\TagValidationException;

/**
 * @internal
 */
#[CoversClass(TagValidationException::class)]
final class TagValidationExceptionTest extends AbstractExceptionTestCase
{
}
