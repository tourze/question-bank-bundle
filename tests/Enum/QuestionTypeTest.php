<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\QuestionBankBundle\Enum\QuestionType;

/**
 * @internal
 */
#[CoversClass(QuestionType::class)]
final class QuestionTypeTest extends AbstractEnumTestCase
{
    #[TestWith([QuestionType::SINGLE_CHOICE, 'single_choice', '单选题'])]
    #[TestWith([QuestionType::MULTIPLE_CHOICE, 'multiple_choice', '多选题'])]
    #[TestWith([QuestionType::TRUE_FALSE, 'true_false', '判断题'])]
    #[TestWith([QuestionType::FILL_BLANK, 'fill_blank', '填空题'])]
    #[TestWith([QuestionType::ESSAY, 'essay', '简答题'])]
    public function testValueAndLabelConsistency(QuestionType $type, string $expectedValue, string $expectedLabel): void
    {
        $this->assertEquals($expectedValue, $type->value);
        $this->assertEquals($expectedLabel, $type->getLabel());
    }

    public function testRequiresOptionsReturnsCorrectValues(): void
    {
        $this->assertTrue(QuestionType::SINGLE_CHOICE->requiresOptions());
        $this->assertTrue(QuestionType::MULTIPLE_CHOICE->requiresOptions());
        $this->assertTrue(QuestionType::TRUE_FALSE->requiresOptions());
        $this->assertFalse(QuestionType::FILL_BLANK->requiresOptions());
        $this->assertFalse(QuestionType::ESSAY->requiresOptions());
    }

    public function testGetMinOptionsReturnsCorrectValues(): void
    {
        $this->assertEquals(2, QuestionType::SINGLE_CHOICE->getMinOptions());
        $this->assertEquals(2, QuestionType::MULTIPLE_CHOICE->getMinOptions());
        $this->assertEquals(2, QuestionType::TRUE_FALSE->getMinOptions());
        $this->assertEquals(0, QuestionType::FILL_BLANK->getMinOptions());
        $this->assertEquals(0, QuestionType::ESSAY->getMinOptions());
    }

    public function testGetMaxOptionsReturnsCorrectValues(): void
    {
        $this->assertEquals(10, QuestionType::SINGLE_CHOICE->getMaxOptions());
        $this->assertEquals(10, QuestionType::MULTIPLE_CHOICE->getMaxOptions());
        $this->assertEquals(2, QuestionType::TRUE_FALSE->getMaxOptions());
        $this->assertEquals(0, QuestionType::FILL_BLANK->getMaxOptions());
        $this->assertEquals(0, QuestionType::ESSAY->getMaxOptions());
    }

    public function testGetMinCorrectOptionCountReturnsCorrectValues(): void
    {
        $this->assertEquals(1, QuestionType::SINGLE_CHOICE->getMinCorrectOptionCount());
        $this->assertEquals(2, QuestionType::MULTIPLE_CHOICE->getMinCorrectOptionCount());
        $this->assertEquals(1, QuestionType::TRUE_FALSE->getMinCorrectOptionCount());
        $this->assertEquals(1, QuestionType::FILL_BLANK->getMinCorrectOptionCount());
        $this->assertEquals(1, QuestionType::ESSAY->getMinCorrectOptionCount());
    }

    public function testToArrayReturnsCorrectFormat(): void
    {
        // toArray() 方法（来自 ItemTrait）返回当前实例的 value 和 label
        $singleChoiceArray = QuestionType::SINGLE_CHOICE->toArray();
        $this->assertArrayHasKey('value', $singleChoiceArray);
        $this->assertArrayHasKey('label', $singleChoiceArray);
        $this->assertEquals('single_choice', $singleChoiceArray['value']);
        $this->assertEquals('单选题', $singleChoiceArray['label']);

        $multipleChoiceArray = QuestionType::MULTIPLE_CHOICE->toArray();
        $this->assertArrayHasKey('value', $multipleChoiceArray);
        $this->assertArrayHasKey('label', $multipleChoiceArray);
        $this->assertEquals('multiple_choice', $multipleChoiceArray['value']);
        $this->assertEquals('多选题', $multipleChoiceArray['label']);

        // 确保每个题型的 toArray() 都返回正确的数据
        foreach (QuestionType::cases() as $type) {
            $array = $type->toArray();
            $this->assertCount(2, $array);
            $this->assertArrayHasKey('value', $array);
            $this->assertArrayHasKey('label', $array);
            $this->assertEquals($type->value, $array['value']);
            $this->assertEquals($type->getLabel(), $array['label']);
        }
    }

    public function testToSelectItemReturnsCorrectFormat(): void
    {
        $array = QuestionType::SINGLE_CHOICE->toSelectItem();

        $this->assertArrayHasKey('value', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertEquals('single_choice', $array['value']);
        $this->assertEquals('单选题', $array['label']);
    }

    public function testAllCasesHaveConsistentValues(): void
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

    public function testAllValuesAreUnique(): void
    {
        $values = array_map(fn ($case) => $case->value, QuestionType::cases());
        $uniqueValues = array_unique($values);

        $this->assertCount(count($values), $uniqueValues, 'All enum values must be unique');
    }

    public function testAllLabelsAreUnique(): void
    {
        $labels = array_map(fn ($case) => $case->getLabel(), QuestionType::cases());
        $uniqueLabels = array_unique($labels);

        $this->assertCount(count($labels), $uniqueLabels, 'All enum labels must be unique');
    }

    public function testFromWithValidValueReturnsEnum(): void
    {
        $this->assertEquals(QuestionType::SINGLE_CHOICE, QuestionType::from('single_choice'));
        $this->assertEquals(QuestionType::MULTIPLE_CHOICE, QuestionType::from('multiple_choice'));
        $this->assertEquals(QuestionType::TRUE_FALSE, QuestionType::from('true_false'));
        $this->assertEquals(QuestionType::FILL_BLANK, QuestionType::from('fill_blank'));
        $this->assertEquals(QuestionType::ESSAY, QuestionType::from('essay'));
    }

    public function testTryFromWithValidValueReturnsEnum(): void
    {
        $this->assertEquals(QuestionType::SINGLE_CHOICE, QuestionType::tryFrom('single_choice'));
        $this->assertEquals(QuestionType::MULTIPLE_CHOICE, QuestionType::tryFrom('multiple_choice'));
        $this->assertEquals(QuestionType::TRUE_FALSE, QuestionType::tryFrom('true_false'));
        $this->assertEquals(QuestionType::FILL_BLANK, QuestionType::tryFrom('fill_blank'));
        $this->assertEquals(QuestionType::ESSAY, QuestionType::tryFrom('essay'));
    }

    public function testMultipleChoiceSpecialRequirement(): void
    {
        // 多选题特殊规则：至少需要2个正确答案
        $this->assertEquals(2, QuestionType::MULTIPLE_CHOICE->getMinCorrectOptionCount());

        // 其他类型只需要1个正确答案
        $this->assertEquals(1, QuestionType::SINGLE_CHOICE->getMinCorrectOptionCount());
        $this->assertEquals(1, QuestionType::TRUE_FALSE->getMinCorrectOptionCount());
    }

    public function testTrueFalseSpecialConstraints(): void
    {
        // 判断题特殊约束：固定2个选项
        $this->assertEquals(2, QuestionType::TRUE_FALSE->getMinOptions());
        $this->assertEquals(2, QuestionType::TRUE_FALSE->getMaxOptions());
        $this->assertTrue(QuestionType::TRUE_FALSE->requiresOptions());
    }

    public function testEssayAndFillBlankNoOptions(): void
    {
        // 简答题和填空题不需要选项
        $this->assertFalse(QuestionType::ESSAY->requiresOptions());
        $this->assertFalse(QuestionType::FILL_BLANK->requiresOptions());
        $this->assertEquals(0, QuestionType::ESSAY->getMinOptions());
        $this->assertEquals(0, QuestionType::ESSAY->getMaxOptions());
        $this->assertEquals(0, QuestionType::FILL_BLANK->getMinOptions());
        $this->assertEquals(0, QuestionType::FILL_BLANK->getMaxOptions());
    }

    public function testToSelectItemAllCases(): void
    {
        foreach (QuestionType::cases() as $type) {
            $array = $type->toSelectItem();

            $this->assertArrayHasKey('value', $array);
            $this->assertArrayHasKey('label', $array);
            $this->assertEquals($type->value, $array['value']);
            $this->assertEquals($type->getLabel(), $array['label']);
        }
    }
}
