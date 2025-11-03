<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\QuestionBankBundle\Enum\QuestionStatus;

/**
 * @internal
 */
#[CoversClass(QuestionStatus::class)]
final class QuestionStatusTest extends AbstractEnumTestCase
{
    public function testCasesContainsAllExpectedValues(): void
    {
        $expectedValues = ['draft', 'published', 'archived'];
        $actualValues = array_map(fn ($case) => $case->value, QuestionStatus::cases());

        $this->assertEquals($expectedValues, $actualValues);
    }

    #[TestWith([QuestionStatus::DRAFT, 'draft', '草稿'])]
    #[TestWith([QuestionStatus::PUBLISHED, 'published', '已发布'])]
    #[TestWith([QuestionStatus::ARCHIVED, 'archived', '已归档'])]
    public function testValueAndLabelConsistency(QuestionStatus $status, string $expectedValue, string $expectedLabel): void
    {
        $this->assertEquals($expectedValue, $status->value);
        $this->assertEquals($expectedLabel, $status->getLabel());
    }

    public function testIsEditableReturnsTrueOnlyForDraft(): void
    {
        $this->assertTrue(QuestionStatus::DRAFT->isEditable());
        $this->assertFalse(QuestionStatus::PUBLISHED->isEditable());
        $this->assertFalse(QuestionStatus::ARCHIVED->isEditable());
    }

    public function testIsUsableReturnsTrueOnlyForPublished(): void
    {
        $this->assertFalse(QuestionStatus::DRAFT->isUsable());
        $this->assertTrue(QuestionStatus::PUBLISHED->isUsable());
        $this->assertFalse(QuestionStatus::ARCHIVED->isUsable());
    }

    public function testAllValuesAreUnique(): void
    {
        $values = array_map(fn ($case) => $case->value, QuestionStatus::cases());
        $uniqueValues = array_unique($values);

        $this->assertCount(count($values), $uniqueValues, 'All enum values must be unique');
    }

    public function testAllLabelsAreUnique(): void
    {
        $labels = array_map(fn ($case) => $case->getLabel(), QuestionStatus::cases());
        $uniqueLabels = array_unique($labels);

        $this->assertCount(count($labels), $uniqueLabels, 'All enum labels must be unique');
    }

    public function testAllCasesHaveLabels(): void
    {
        foreach (QuestionStatus::cases() as $case) {
            $label = $case->getLabel();
            $this->assertNotEmpty($label, "Label for {$case->value} should not be empty");
        }
    }

    public function testFromValueDraft(): void
    {
        $status = QuestionStatus::from('draft');
        $this->assertEquals(QuestionStatus::DRAFT, $status);
    }

    public function testFromValuePublished(): void
    {
        $status = QuestionStatus::from('published');
        $this->assertEquals(QuestionStatus::PUBLISHED, $status);
    }

    public function testFromValueArchived(): void
    {
        $status = QuestionStatus::from('archived');
        $this->assertEquals(QuestionStatus::ARCHIVED, $status);
    }

    public function testTryFromWithValidValueReturnsEnum(): void
    {
        $this->assertEquals(QuestionStatus::DRAFT, QuestionStatus::tryFrom('draft'));
        $this->assertEquals(QuestionStatus::PUBLISHED, QuestionStatus::tryFrom('published'));
        $this->assertEquals(QuestionStatus::ARCHIVED, QuestionStatus::tryFrom('archived'));
    }

    public function testStatusWorkflowDraftToPublished(): void
    {
        $draft = QuestionStatus::DRAFT;

        $this->assertTrue($draft->isEditable());
        $this->assertFalse($draft->isUsable());
    }

    public function testStatusWorkflowPublishedToArchived(): void
    {
        $published = QuestionStatus::PUBLISHED;

        $this->assertFalse($published->isEditable());
        $this->assertTrue($published->isUsable());
    }

    public function testStatusWorkflowArchivedState(): void
    {
        $archived = QuestionStatus::ARCHIVED;

        $this->assertFalse($archived->isEditable());
        $this->assertFalse($archived->isUsable());
    }

    public function testCanTransitionTo(): void
    {
        // 草稿只能转换为已发布
        $this->assertTrue(QuestionStatus::DRAFT->canTransitionTo(QuestionStatus::PUBLISHED));
        $this->assertFalse(QuestionStatus::DRAFT->canTransitionTo(QuestionStatus::ARCHIVED));
        $this->assertFalse(QuestionStatus::DRAFT->canTransitionTo(QuestionStatus::DRAFT));

        // 已发布只能转换为已归档
        $this->assertTrue(QuestionStatus::PUBLISHED->canTransitionTo(QuestionStatus::ARCHIVED));
        $this->assertFalse(QuestionStatus::PUBLISHED->canTransitionTo(QuestionStatus::DRAFT));
        $this->assertFalse(QuestionStatus::PUBLISHED->canTransitionTo(QuestionStatus::PUBLISHED));

        // 已归档不能转换为任何状态
        $this->assertFalse(QuestionStatus::ARCHIVED->canTransitionTo(QuestionStatus::DRAFT));
        $this->assertFalse(QuestionStatus::ARCHIVED->canTransitionTo(QuestionStatus::PUBLISHED));
        $this->assertFalse(QuestionStatus::ARCHIVED->canTransitionTo(QuestionStatus::ARCHIVED));
    }

    public function testToArray(): void
    {
        // toArray() 方法（来自 ItemTrait）返回当前实例的 value 和 label
        $draftArray = QuestionStatus::DRAFT->toArray();
        $this->assertArrayHasKey('value', $draftArray);
        $this->assertArrayHasKey('label', $draftArray);
        $this->assertEquals('draft', $draftArray['value']);
        $this->assertEquals('草稿', $draftArray['label']);

        $publishedArray = QuestionStatus::PUBLISHED->toArray();
        $this->assertArrayHasKey('value', $publishedArray);
        $this->assertArrayHasKey('label', $publishedArray);
        $this->assertEquals('published', $publishedArray['value']);
        $this->assertEquals('已发布', $publishedArray['label']);

        $archivedArray = QuestionStatus::ARCHIVED->toArray();
        $this->assertArrayHasKey('value', $archivedArray);
        $this->assertArrayHasKey('label', $archivedArray);
        $this->assertEquals('archived', $archivedArray['value']);
        $this->assertEquals('已归档', $archivedArray['label']);

        // 确保每个状态的 toArray() 都返回正确的数据
        foreach (QuestionStatus::cases() as $status) {
            $array = $status->toArray();
            $this->assertCount(2, $array);
            $this->assertArrayHasKey('value', $array);
            $this->assertArrayHasKey('label', $array);
            $this->assertEquals($status->value, $array['value']);
            $this->assertEquals($status->getLabel(), $array['label']);
        }
    }
}
