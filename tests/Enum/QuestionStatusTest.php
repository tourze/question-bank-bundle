<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Enum\QuestionStatus;

class QuestionStatusTest extends TestCase
{
    public function test_cases_containsAllExpectedValues(): void
    {
        $expectedValues = ['draft', 'published', 'archived'];
        $actualValues = array_map(fn($case) => $case->value, QuestionStatus::cases());

        $this->assertEquals($expectedValues, $actualValues);
    }

    public function test_getLabel_returnsCorrectTranslations(): void
    {
        $this->assertEquals('草稿', QuestionStatus::DRAFT->getLabel());
        $this->assertEquals('已发布', QuestionStatus::PUBLISHED->getLabel());
        $this->assertEquals('已归档', QuestionStatus::ARCHIVED->getLabel());
    }

    public function test_isEditable_returnsTrueOnlyForDraft(): void
    {
        $this->assertTrue(QuestionStatus::DRAFT->isEditable());
        $this->assertFalse(QuestionStatus::PUBLISHED->isEditable());
        $this->assertFalse(QuestionStatus::ARCHIVED->isEditable());
    }

    public function test_isUsable_returnsTrueOnlyForPublished(): void
    {
        $this->assertFalse(QuestionStatus::DRAFT->isUsable());
        $this->assertTrue(QuestionStatus::PUBLISHED->isUsable());
        $this->assertFalse(QuestionStatus::ARCHIVED->isUsable());
    }

    public function test_allCases_haveUniqueValues(): void
    {
        $values = array_map(fn($case) => $case->value, QuestionStatus::cases());
        $uniqueValues = array_unique($values);

        $this->assertCount(count($values), $uniqueValues);
    }

    public function test_allCases_haveLabels(): void
    {
        foreach (QuestionStatus::cases() as $case) {
            $label = $case->getLabel();
            $this->assertIsString($label);
            $this->assertNotEmpty($label);
        }
    }

    public function test_fromValue_draft(): void
    {
        $status = QuestionStatus::from('draft');
        $this->assertEquals(QuestionStatus::DRAFT, $status);
    }

    public function test_fromValue_published(): void
    {
        $status = QuestionStatus::from('published');
        $this->assertEquals(QuestionStatus::PUBLISHED, $status);
    }

    public function test_fromValue_archived(): void
    {
        $status = QuestionStatus::from('archived');
        $this->assertEquals(QuestionStatus::ARCHIVED, $status);
    }

    public function test_fromValue_invalidValue_throwsException(): void
    {
        $this->expectException(\ValueError::class);
        QuestionStatus::from('invalid');
    }

    public function test_statusWorkflow_draftToPublished(): void
    {
        $draft = QuestionStatus::DRAFT;

        $this->assertTrue($draft->isEditable());
        $this->assertFalse($draft->isUsable());
    }

    public function test_statusWorkflow_publishedToArchived(): void
    {
        $published = QuestionStatus::PUBLISHED;

        $this->assertFalse($published->isEditable());
        $this->assertTrue($published->isUsable());
    }

    public function test_statusWorkflow_archivedState(): void
    {
        $archived = QuestionStatus::ARCHIVED;

        $this->assertFalse($archived->isEditable());
        $this->assertFalse($archived->isUsable());
    }
}
