<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\DTO\OptionDTO;

/**
 * @internal
 */
#[CoversClass(OptionDTO::class)]
final class OptionDTOTest extends TestCase
{
    public function testCreate(): void
    {
        $dto = OptionDTO::create('Test content', true);

        $this->assertSame('Test content', $dto->content);
        $this->assertTrue($dto->isCorrect);
        $this->assertNull($dto->explanation);
        $this->assertSame(0, $dto->sortOrder);
    }

    public function testWithOptionalFields(): void
    {
        $dto = OptionDTO::create('Test content', false);
        $dto->explanation = 'Test explanation';
        $dto->sortOrder = 5;

        $this->assertSame('Test content', $dto->content);
        $this->assertFalse($dto->isCorrect);
        $this->assertSame('Test explanation', $dto->explanation);
        $this->assertSame(5, $dto->sortOrder);
    }
}
