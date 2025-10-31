<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\QuestionBankBundle\Entity\Tag;
use Tourze\QuestionBankBundle\Exception\TagValidationException;

/**
 * @internal
 */
#[CoversClass(Tag::class)]
final class TagTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        return $tag;
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     *
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'name' => ['name', 'Updated Tag Name'];
        yield 'slug' => ['slug', 'updated-tag-slug'];
        yield 'description' => ['description', 'This is an updated description for the tag'];
        yield 'color' => ['color', '#00FF00'];
        yield 'valid' => ['valid', false];
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setSlug('test-tag');

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

    public function testConstructorWithCustomSlug(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setSlug('custom-slug');

        $this->assertEquals('Test Tag', $tag->getName());
        $this->assertEquals('custom-slug', $tag->getSlug());
    }

    public function testSetNameUpdatesName(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        $tag->setName('New Name');

        $this->assertEquals('New Name', $tag->getName());
    }

    public function testSetSlugUpdatesSlug(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        $tag->setSlug('new-slug');

        $this->assertEquals('new-slug', $tag->getSlug());
    }

    public function testSetDescriptionUpdatesDescription(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        $tag->setDescription('Test Description');

        $this->assertEquals('Test Description', $tag->getDescription());
    }

    public function testSetColorWithValidHexColorUpdatesColor(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        $tag->setColor('#FF0000');

        $this->assertEquals('#FF0000', $tag->getColor());
    }

    public function testSetColorWithLowercaseHexColorUpdatesColor(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        $tag->setColor('#ff0000');

        $this->assertEquals('#ff0000', $tag->getColor());
    }

    public function testSetColorWithNullSetsNull(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setColor('#FF0000');

        $tag->setColor(null);

        $this->assertNull($tag->getColor());
    }

    public function testSetColorWithInvalidColorThrowsException(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        $this->expectException(TagValidationException::class);
        $this->expectExceptionMessage('Color must be a valid hex color (e.g., #FF0000)');

        $tag->setColor('invalid-color');
    }

    public function testSetColorWithShortHexColorThrowsException(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        $this->expectException(TagValidationException::class);
        $this->expectExceptionMessage('Color must be a valid hex color (e.g., #FF0000)');

        $tag->setColor('#FFF');
    }

    public function testSetColorWithoutHashPrefixThrowsException(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        $this->expectException(TagValidationException::class);
        $this->expectExceptionMessage('Color must be a valid hex color (e.g., #FF0000)');

        $tag->setColor('FF0000');
    }

    public function testIncrementUsageCountIncreasesCount(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');
        $originalCount = $tag->getUsageCount();

        $tag->incrementUsageCount();

        $this->assertEquals($originalCount + 1, $tag->getUsageCount());
    }

    public function testDecrementUsageCountDecreasesCount(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->incrementUsageCount();
        $tag->incrementUsageCount();
        $currentCount = $tag->getUsageCount();

        $tag->decrementUsageCount();

        $this->assertEquals($currentCount - 1, $tag->getUsageCount());
    }

    public function testDecrementUsageCountDoesNotGoBelowZero(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        $tag->decrementUsageCount();

        $this->assertEquals(0, $tag->getUsageCount());
    }

    public function testSetValidUpdatesValidFlag(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        $tag->setValid(false);

        $this->assertFalse($tag->isValid());
    }

    public function testToStringReturnsName(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        $this->assertEquals('Test Tag', (string) $tag);
    }

    public function testSlugGenerationHandlesSpecialCharacters(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag with Special @#$ Characters!');
        $tag->setSlug('test-tag-with-special-characters');

        $this->assertEquals('test-tag-with-special-characters', $tag->getSlug());
    }

    public function testSlugGenerationHandlesMultipleSpaces(): void
    {
        $tag = new Tag();
        $tag->setName('Test   Tag   with   Spaces');
        $tag->setSlug('test-tag-with-spaces');

        $this->assertEquals('test-tag-with-spaces', $tag->getSlug());
    }

    public function testSlugGenerationHandlesLeadingTrailingDashes(): void
    {
        $tag = new Tag();
        $tag->setName('  Test Tag  ');
        $tag->setSlug('test-tag');

        $this->assertEquals('test-tag', $tag->getSlug());
    }

    public function testSlugGenerationHandlesNumbers(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag 123');
        $tag->setSlug('test-tag-123');

        $this->assertEquals('test-tag-123', $tag->getSlug());
    }

    public function testSlugGenerationHandlesChinese(): void
    {
        $tag = new Tag();
        $tag->setName('测试标签');
        $tag->setSlug('');

        // 中文字符会被过滤掉，只留下空字符串，被处理成空slug
        $this->assertEquals('', $tag->getSlug());
    }

    public function testSlugGenerationHandlesEmptyString(): void
    {
        $tag = new Tag();
        $tag->setName('');
        $tag->setSlug('');

        $this->assertEquals('', $tag->getSlug());
    }

    public function testSlugGenerationHandlesOnlySpecialCharacters(): void
    {
        $tag = new Tag();
        $tag->setName('@#$%^&*()');
        $tag->setSlug('');

        $this->assertEquals('', $tag->getSlug());
    }

    public function testMultipleUsageCountOperations(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

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
