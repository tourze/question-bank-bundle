<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\QuestionBankBundle\Exception\TagNotFoundException;

/**
 * @internal
 */
#[CoversClass(TagNotFoundException::class)]
final class TagNotFoundExceptionTest extends AbstractExceptionTestCase
{
}
