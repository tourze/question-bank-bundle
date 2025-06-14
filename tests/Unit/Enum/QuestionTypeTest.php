<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Enum\QuestionType;

class QuestionTypeTest extends TestCase
{
    public function testGetLabel(): void
    {
        $this->assertEquals('单选题', QuestionType::SINGLE_CHOICE->getLabel());
        $this->assertEquals('多选题', QuestionType::MULTIPLE_CHOICE->getLabel());
        $this->assertEquals('判断题', QuestionType::TRUE_FALSE->getLabel());
        $this->assertEquals('填空题', QuestionType::FILL_BLANK->getLabel());
        $this->assertEquals('简答题', QuestionType::ESSAY->getLabel());
    }
    
    public function testRequiresOptions(): void
    {
        $this->assertTrue(QuestionType::SINGLE_CHOICE->requiresOptions());
        $this->assertTrue(QuestionType::MULTIPLE_CHOICE->requiresOptions());
        $this->assertTrue(QuestionType::TRUE_FALSE->requiresOptions());
        $this->assertFalse(QuestionType::FILL_BLANK->requiresOptions());
        $this->assertFalse(QuestionType::ESSAY->requiresOptions());
    }
    
    public function testGetMinOptions(): void
    {
        $this->assertEquals(2, QuestionType::SINGLE_CHOICE->getMinOptions());
        $this->assertEquals(2, QuestionType::MULTIPLE_CHOICE->getMinOptions());
        $this->assertEquals(2, QuestionType::TRUE_FALSE->getMinOptions());
        $this->assertEquals(0, QuestionType::FILL_BLANK->getMinOptions());
        $this->assertEquals(0, QuestionType::ESSAY->getMinOptions());
    }
    
    public function testGetMaxOptions(): void
    {
        $this->assertEquals(10, QuestionType::SINGLE_CHOICE->getMaxOptions());
        $this->assertEquals(10, QuestionType::MULTIPLE_CHOICE->getMaxOptions());
        $this->assertEquals(2, QuestionType::TRUE_FALSE->getMaxOptions());
        $this->assertEquals(0, QuestionType::FILL_BLANK->getMaxOptions());
        $this->assertEquals(0, QuestionType::ESSAY->getMaxOptions());
    }
}