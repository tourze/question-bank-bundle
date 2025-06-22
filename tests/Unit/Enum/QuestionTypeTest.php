<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Enum\QuestionType;

class QuestionTypeTest extends TestCase
{
    public function test_getLabel_returnsCorrectLabels(): void
    {
        $this->assertEquals('单选题', QuestionType::SINGLE_CHOICE->getLabel());
        $this->assertEquals('多选题', QuestionType::MULTIPLE_CHOICE->getLabel());
        $this->assertEquals('判断题', QuestionType::TRUE_FALSE->getLabel());
        $this->assertEquals('填空题', QuestionType::FILL_BLANK->getLabel());
        $this->assertEquals('简答题', QuestionType::ESSAY->getLabel());
    }

    public function test_requiresOptions_returnsCorrectValues(): void
    {
        $this->assertTrue(QuestionType::SINGLE_CHOICE->requiresOptions());
        $this->assertTrue(QuestionType::MULTIPLE_CHOICE->requiresOptions());
        $this->assertTrue(QuestionType::TRUE_FALSE->requiresOptions());
        $this->assertFalse(QuestionType::FILL_BLANK->requiresOptions());
        $this->assertFalse(QuestionType::ESSAY->requiresOptions());
    }

    public function test_getMinOptions_returnsCorrectValues(): void
    {
        $this->assertEquals(2, QuestionType::SINGLE_CHOICE->getMinOptions());
        $this->assertEquals(2, QuestionType::MULTIPLE_CHOICE->getMinOptions());
        $this->assertEquals(2, QuestionType::TRUE_FALSE->getMinOptions());
        $this->assertEquals(0, QuestionType::FILL_BLANK->getMinOptions());
        $this->assertEquals(0, QuestionType::ESSAY->getMinOptions());
    }

    public function test_getMaxOptions_returnsCorrectValues(): void
    {
        $this->assertEquals(10, QuestionType::SINGLE_CHOICE->getMaxOptions());
        $this->assertEquals(10, QuestionType::MULTIPLE_CHOICE->getMaxOptions());
        $this->assertEquals(2, QuestionType::TRUE_FALSE->getMaxOptions());
        $this->assertEquals(0, QuestionType::FILL_BLANK->getMaxOptions());
        $this->assertEquals(0, QuestionType::ESSAY->getMaxOptions());
    }

    public function test_getMinCorrectOptionCount_returnsCorrectValues(): void
    {
        $this->assertEquals(1, QuestionType::SINGLE_CHOICE->getMinCorrectOptionCount());
        $this->assertEquals(2, QuestionType::MULTIPLE_CHOICE->getMinCorrectOptionCount());
        $this->assertEquals(1, QuestionType::TRUE_FALSE->getMinCorrectOptionCount());
        $this->assertEquals(1, QuestionType::FILL_BLANK->getMinCorrectOptionCount());
        $this->assertEquals(1, QuestionType::ESSAY->getMinCorrectOptionCount());
    }

    public function test_toArray_returnsCorrectFormat(): void
    {
        $array = QuestionType::SINGLE_CHOICE->toArray();
        
        $this->assertArrayHasKey('value', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertEquals('single_choice', $array['value']);
        $this->assertEquals('单选题', $array['label']);
    }

    public function test_allCases_haveConsistentValues(): void
    {
        $cases = QuestionType::cases();
        
        $this->assertCount(5, $cases);
        
        foreach ($cases as $case) {
            // 验证每个case都有标签
            $this->assertNotEmpty($case->getLabel());
            
            // 验证选项范围的逻辑一致性
            if ($case->requiresOptions()) {
                $this->assertGreaterThanOrEqual(0, $case->getMinOptions());
                $this->assertGreaterThanOrEqual($case->getMinOptions(), $case->getMaxOptions());
            } else {
                $this->assertEquals(0, $case->getMinOptions());
                $this->assertEquals(0, $case->getMaxOptions());
            }
            
            // 验证正确答案数量要求
            $this->assertGreaterThanOrEqual(1, $case->getMinCorrectOptionCount());
        }
    }

    public function test_values_areUnique(): void
    {
        $values = array_map(fn($case) => $case->value, QuestionType::cases());
        $uniqueValues = array_unique($values);
        
        $this->assertEquals(count($values), count($uniqueValues));
    }

    public function test_multipleChoiceSpecialRequirement(): void
    {
        // 多选题特殊规则：至少需要2个正确答案
        $this->assertEquals(2, QuestionType::MULTIPLE_CHOICE->getMinCorrectOptionCount());
        
        // 其他类型只需要1个正确答案
        $this->assertEquals(1, QuestionType::SINGLE_CHOICE->getMinCorrectOptionCount());
        $this->assertEquals(1, QuestionType::TRUE_FALSE->getMinCorrectOptionCount());
    }

    public function test_trueFalseSpecialConstraints(): void
    {
        // 判断题特殊约束：固定2个选项
        $this->assertEquals(2, QuestionType::TRUE_FALSE->getMinOptions());
        $this->assertEquals(2, QuestionType::TRUE_FALSE->getMaxOptions());
        $this->assertTrue(QuestionType::TRUE_FALSE->requiresOptions());
    }

    public function test_essayAndFillBlankNoOptions(): void
    {
        // 简答题和填空题不需要选项
        $this->assertFalse(QuestionType::ESSAY->requiresOptions());
        $this->assertFalse(QuestionType::FILL_BLANK->requiresOptions());
        $this->assertEquals(0, QuestionType::ESSAY->getMinOptions());
        $this->assertEquals(0, QuestionType::ESSAY->getMaxOptions());
        $this->assertEquals(0, QuestionType::FILL_BLANK->getMinOptions());
        $this->assertEquals(0, QuestionType::FILL_BLANK->getMaxOptions());
    }

    public function test_toArray_allCases(): void
    {
        foreach (QuestionType::cases() as $type) {
            $array = $type->toArray();
            
            $this->assertArrayHasKey('value', $array);
            $this->assertArrayHasKey('label', $array);
            $this->assertEquals($type->value, $array['value']);
            $this->assertEquals($type->getLabel(), $array['label']);
        }
    }
}