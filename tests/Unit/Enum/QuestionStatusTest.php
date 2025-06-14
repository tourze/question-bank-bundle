<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\Enum;

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

    public function test_getLabel_returnsCorrectLabels(): void
    {
        $this->assertEquals('草稿', QuestionStatus::DRAFT->getLabel());
        $this->assertEquals('已发布', QuestionStatus::PUBLISHED->getLabel());
        $this->assertEquals('已归档', QuestionStatus::ARCHIVED->getLabel());
    }

    public function test_getColor_returnsCorrectColors(): void
    {
        $this->assertEquals('warning', QuestionStatus::DRAFT->getColor());
        $this->assertEquals('success', QuestionStatus::PUBLISHED->getColor());
        $this->assertEquals('secondary', QuestionStatus::ARCHIVED->getColor());
    }

    public function test_isEditable_returnsCorrectValues(): void
    {
        $this->assertTrue(QuestionStatus::DRAFT->isEditable());
        $this->assertFalse(QuestionStatus::PUBLISHED->isEditable());
        $this->assertFalse(QuestionStatus::ARCHIVED->isEditable());
    }

    public function test_isUsable_returnsCorrectValues(): void
    {
        $this->assertFalse(QuestionStatus::DRAFT->isUsable());
        $this->assertTrue(QuestionStatus::PUBLISHED->isUsable());
        $this->assertFalse(QuestionStatus::ARCHIVED->isUsable());
    }

    public function test_canTransitionTo_withValidTransitions_returnsTrue(): void
    {
        // Draft can transition to Published
        $this->assertTrue(QuestionStatus::DRAFT->canTransitionTo(QuestionStatus::PUBLISHED));
        
        // Published can transition to Archived
        $this->assertTrue(QuestionStatus::PUBLISHED->canTransitionTo(QuestionStatus::ARCHIVED));
    }

    public function test_canTransitionTo_withInvalidTransitions_returnsFalse(): void
    {
        // Draft cannot transition to Archived directly
        $this->assertFalse(QuestionStatus::DRAFT->canTransitionTo(QuestionStatus::ARCHIVED));
        
        // Published cannot transition back to Draft
        $this->assertFalse(QuestionStatus::PUBLISHED->canTransitionTo(QuestionStatus::DRAFT));
        
        // Archived cannot transition to any other status
        $this->assertFalse(QuestionStatus::ARCHIVED->canTransitionTo(QuestionStatus::DRAFT));
        $this->assertFalse(QuestionStatus::ARCHIVED->canTransitionTo(QuestionStatus::PUBLISHED));
    }

    public function test_canTransitionTo_withSameStatus_returnsFalse(): void
    {
        $this->assertFalse(QuestionStatus::DRAFT->canTransitionTo(QuestionStatus::DRAFT));
        $this->assertFalse(QuestionStatus::PUBLISHED->canTransitionTo(QuestionStatus::PUBLISHED));
        $this->assertFalse(QuestionStatus::ARCHIVED->canTransitionTo(QuestionStatus::ARCHIVED));
    }

    public function test_getAvailableTransitions_returnsCorrectTransitions(): void
    {
        // Draft can only transition to Published
        $draftTransitions = QuestionStatus::DRAFT->getAvailableTransitions();
        $this->assertCount(1, $draftTransitions);
        $this->assertContains(QuestionStatus::PUBLISHED, $draftTransitions);

        // Published can only transition to Archived
        $publishedTransitions = QuestionStatus::PUBLISHED->getAvailableTransitions();
        $this->assertCount(1, $publishedTransitions);
        $this->assertContains(QuestionStatus::ARCHIVED, $publishedTransitions);

        // Archived has no available transitions
        $archivedTransitions = QuestionStatus::ARCHIVED->getAvailableTransitions();
        $this->assertCount(0, $archivedTransitions);
    }

    public function test_getAllStatuses_returnsAllCases(): void
    {
        $allStatuses = QuestionStatus::getAllStatuses();
        $this->assertCount(3, $allStatuses);
        $this->assertContains(QuestionStatus::DRAFT, $allStatuses);
        $this->assertContains(QuestionStatus::PUBLISHED, $allStatuses);
        $this->assertContains(QuestionStatus::ARCHIVED, $allStatuses);
    }

    public function test_fromString_withValidValue_returnsCorrectEnum(): void
    {
        $this->assertEquals(QuestionStatus::DRAFT, QuestionStatus::fromString('draft'));
        $this->assertEquals(QuestionStatus::PUBLISHED, QuestionStatus::fromString('published'));
        $this->assertEquals(QuestionStatus::ARCHIVED, QuestionStatus::fromString('archived'));
    }

    public function test_fromString_withInvalidValue_returnsNull(): void
    {
        $this->assertNull(QuestionStatus::fromString('invalid'));
        $this->assertNull(QuestionStatus::fromString(''));
        $this->assertNull(QuestionStatus::fromString('Draft')); // 大小写敏感
    }

    public function test_isValidTransition_withStaticMethod_returnsCorrectResults(): void
    {
        $this->assertTrue(QuestionStatus::isValidTransition(QuestionStatus::DRAFT, QuestionStatus::PUBLISHED));
        $this->assertTrue(QuestionStatus::isValidTransition(QuestionStatus::PUBLISHED, QuestionStatus::ARCHIVED));
        $this->assertFalse(QuestionStatus::isValidTransition(QuestionStatus::DRAFT, QuestionStatus::ARCHIVED));
        $this->assertFalse(QuestionStatus::isValidTransition(QuestionStatus::ARCHIVED, QuestionStatus::DRAFT));
    }
}