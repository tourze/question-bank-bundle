<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\Entity\Tag;

class TagTest extends TestCase
{
    public function test_constructor_setsDefaultValues(): void
    {
        $tag = new Tag('Test Tag');

        $this->assertEquals('Test Tag', $tag->getName());
        $this->assertEquals('test-tag', $tag->getSlug());
        $this->assertNull($tag->getDescription());
        $this->assertNull($tag->getColor());
        $this->assertEquals(0, $tag->getUsageCount());
        $this->assertTrue($tag->isValid());
        $this->assertCount(0, $tag->getQuestions());
        $this->assertInstanceOf(\DateTimeImmutable::class, $tag->getCreateTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $tag->getUpdateTime());
    }

    public function test_constructor_withCustomSlug(): void
    {
        $tag = new Tag('Test Tag', 'custom-slug');

        $this->assertEquals('Test Tag', $tag->getName());
        $this->assertEquals('custom-slug', $tag->getSlug());
    }

    public function test_setName_updatesName(): void
    {
        $tag = new Tag('Test Tag');
        
        $tag->setName('New Name');

        $this->assertEquals('New Name', $tag->getName());
    }

    public function test_setSlug_updatesSlug(): void
    {
        $tag = new Tag('Test Tag');
        
        $tag->setSlug('new-slug');

        $this->assertEquals('new-slug', $tag->getSlug());
    }

    public function test_setDescription_updatesDescription(): void
    {
        $tag = new Tag('Test Tag');
        
        $tag->setDescription('Test Description');

        $this->assertEquals('Test Description', $tag->getDescription());
    }

    public function test_setColor_withValidHexColor_updatesColor(): void
    {
        $tag = new Tag('Test Tag');
        
        $tag->setColor('#FF0000');

        $this->assertEquals('#FF0000', $tag->getColor());
    }

    public function test_setColor_withLowercaseHexColor_updatesColor(): void
    {
        $tag = new Tag('Test Tag');
        
        $tag->setColor('#ff0000');

        $this->assertEquals('#ff0000', $tag->getColor());
    }

    public function test_setColor_withNull_setsNull(): void
    {
        $tag = new Tag('Test Tag');
        $tag->setColor('#FF0000');
        
        $tag->setColor(null);

        $this->assertNull($tag->getColor());
    }

    public function test_setColor_withInvalidColor_throwsException(): void
    {
        $tag = new Tag('Test Tag');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Color must be a valid hex color (e.g., #FF0000)');
        
        $tag->setColor('invalid-color');
    }

    public function test_setColor_withShortHexColor_throwsException(): void
    {
        $tag = new Tag('Test Tag');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Color must be a valid hex color (e.g., #FF0000)');
        
        $tag->setColor('#FFF');
    }

    public function test_setColor_withoutHashPrefix_throwsException(): void
    {
        $tag = new Tag('Test Tag');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Color must be a valid hex color (e.g., #FF0000)');
        
        $tag->setColor('FF0000');
    }

    public function test_incrementUsageCount_increasesCount(): void
    {
        $tag = new Tag('Test Tag');
        $originalCount = $tag->getUsageCount();
        
        $tag->incrementUsageCount();

        $this->assertEquals($originalCount + 1, $tag->getUsageCount());
    }

    public function test_decrementUsageCount_decreasesCount(): void
    {
        $tag = new Tag('Test Tag');
        $tag->incrementUsageCount();
        $tag->incrementUsageCount();
        $currentCount = $tag->getUsageCount();
        
        $tag->decrementUsageCount();

        $this->assertEquals($currentCount - 1, $tag->getUsageCount());
    }

    public function test_decrementUsageCount_doesNotGoBelowZero(): void
    {
        $tag = new Tag('Test Tag');
        
        $tag->decrementUsageCount();

        $this->assertEquals(0, $tag->getUsageCount());
    }

    public function test_setValid_updatesValidFlag(): void
    {
        $tag = new Tag('Test Tag');
        
        $tag->setValid(false);

        $this->assertFalse($tag->isValid());
    }

    public function test_toString_returnsName(): void
    {
        $tag = new Tag('Test Tag');

        $this->assertEquals('Test Tag', (string) $tag);
    }

    public function test_slugGeneration_handlesSpecialCharacters(): void
    {
        $tag = new Tag('Test Tag with Special @#$ Characters!');

        $this->assertEquals('test-tag-with-special-characters', $tag->getSlug());
    }

    public function test_slugGeneration_handlesMultipleSpaces(): void
    {
        $tag = new Tag('Test   Tag   with   Spaces');

        $this->assertEquals('test-tag-with-spaces', $tag->getSlug());
    }

    public function test_slugGeneration_handlesLeadingTrailingDashes(): void
    {
        $tag = new Tag('  Test Tag  ');

        $this->assertEquals('test-tag', $tag->getSlug());
    }

    public function test_slugGeneration_handlesNumbers(): void
    {
        $tag = new Tag('Test Tag 123');

        $this->assertEquals('test-tag-123', $tag->getSlug());
    }

    public function test_slugGeneration_handlesChinese(): void
    {
        $tag = new Tag('测试标签');

        // 中文字符会被过滤掉，只留下空字符串，被处理成空slug
        $this->assertEquals('', $tag->getSlug());
    }

    public function test_slugGeneration_handlesEmptyString(): void
    {
        $tag = new Tag('');

        $this->assertEquals('', $tag->getSlug());
    }

    public function test_slugGeneration_handlesOnlySpecialCharacters(): void
    {
        $tag = new Tag('@#$%^&*()');

        $this->assertEquals('', $tag->getSlug());
    }

    public function test_multipleUsageCountOperations(): void
    {
        $tag = new Tag('Test Tag');
        
        $tag->incrementUsageCount();
        $tag->incrementUsageCount();
        $tag->incrementUsageCount();
        $this->assertEquals(3, $tag->getUsageCount());
        
        $tag->decrementUsageCount();
        $this->assertEquals(2, $tag->getUsageCount());
        
        $tag->decrementUsageCount();
        $tag->decrementUsageCount();
        $tag->decrementUsageCount(); // 这次应该不会减少到负数
        $this->assertEquals(0, $tag->getUsageCount());
    }
}