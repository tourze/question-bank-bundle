<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\DTO\OptionDTO;
use Tourze\QuestionBankBundle\DTO\QuestionDTO;
use Tourze\QuestionBankBundle\Enum\QuestionType;

class QuestionDTOTest extends TestCase
{
    public function test_constructor_setsDefaultValues(): void
    {
        $dto = new QuestionDTO();

        $this->assertEquals(3, $dto->difficulty);
        $this->assertEquals(10.0, $dto->score);
        $this->assertNull($dto->explanation);
        $this->assertNull($dto->metadata);
        $this->assertIsArray($dto->categoryIds);
        $this->assertEmpty($dto->categoryIds);
        $this->assertIsArray($dto->tagIds);
        $this->assertEmpty($dto->tagIds);
        $this->assertIsArray($dto->options);
        $this->assertEmpty($dto->options);
    }

    public function test_create_withValidData_createsCorrectDTO(): void
    {
        $dto = QuestionDTO::create(
            'Test Question',
            'What is PHP?',
            QuestionType::SINGLE_CHOICE
        );

        $this->assertEquals('Test Question', $dto->title);
        $this->assertEquals('What is PHP?', $dto->content);
        $this->assertEquals(QuestionType::SINGLE_CHOICE, $dto->type);
        $this->assertEquals(3, $dto->difficulty); // default
        $this->assertEquals(10.0, $dto->score); // default
    }

    public function test_propertyAssignment_worksCorrectly(): void
    {
        $dto = new QuestionDTO();
        
        $dto->title = 'Updated Title';
        $dto->content = 'Updated Content';
        $dto->type = QuestionType::MULTIPLE_CHOICE;
        $dto->difficulty = 5;
        $dto->score = 20.0;
        $dto->explanation = 'Test explanation';
        $dto->metadata = ['source' => 'book', 'page' => 123];
        $dto->categoryIds = ['cat1', 'cat2'];
        $dto->tagIds = ['tag1', 'tag2', 'tag3'];

        $this->assertEquals('Updated Title', $dto->title);
        $this->assertEquals('Updated Content', $dto->content);
        $this->assertEquals(QuestionType::MULTIPLE_CHOICE, $dto->type);
        $this->assertEquals(5, $dto->difficulty);
        $this->assertEquals(20.0, $dto->score);
        $this->assertEquals('Test explanation', $dto->explanation);
        $this->assertEquals(['source' => 'book', 'page' => 123], $dto->metadata);
        $this->assertEquals(['cat1', 'cat2'], $dto->categoryIds);
        $this->assertEquals(['tag1', 'tag2', 'tag3'], $dto->tagIds);
    }

    public function test_options_canBeAddedAndAccessed(): void
    {
        $dto = new QuestionDTO();
        
        $option1 = new OptionDTO();
        $option1->content = 'Option A';
        $option1->isCorrect = true;
        
        $option2 = new OptionDTO();
        $option2->content = 'Option B';
        $option2->isCorrect = false;
        
        $dto->options = [$option1, $option2];

        $this->assertCount(2, $dto->options);
        $this->assertEquals('Option A', $dto->options[0]->content);
        $this->assertTrue($dto->options[0]->isCorrect);
        $this->assertEquals('Option B', $dto->options[1]->content);
        $this->assertFalse($dto->options[1]->isCorrect);
    }

    public function test_categoryIds_canBeManipulated(): void
    {
        $dto = new QuestionDTO();
        
        $dto->categoryIds[] = 'cat1';
        $dto->categoryIds[] = 'cat2';
        $dto->categoryIds[] = 'cat3';

        $this->assertEquals(['cat1', 'cat2', 'cat3'], $dto->categoryIds);
        
        // Remove one category
        unset($dto->categoryIds[1]);
        $dto->categoryIds = array_values($dto->categoryIds);
        
        $this->assertEquals(['cat1', 'cat3'], $dto->categoryIds);
    }

    public function test_tagIds_canBeManipulated(): void
    {
        $dto = new QuestionDTO();
        
        $dto->tagIds[] = 'tag1';
        $dto->tagIds[] = 'tag2';
        $dto->tagIds[] = 'tag3';

        $this->assertEquals(['tag1', 'tag2', 'tag3'], $dto->tagIds);
        
        // Remove one tag
        $dto->tagIds = array_diff($dto->tagIds, ['tag2']);
        
        $this->assertEquals(['tag1', 'tag3'], array_values($dto->tagIds));
    }

    public function test_metadata_canStoreComplexData(): void
    {
        $dto = new QuestionDTO();
        
        $complexMetadata = [
            'source' => 'textbook',
            'chapter' => 5,
            'page' => 123,
            'author' => 'John Doe',
            'tags' => ['important', 'exam'],
            'nested' => [
                'level' => 'intermediate',
                'prerequisites' => ['basic-php', 'variables']
            ]
        ];
        
        $dto->metadata = $complexMetadata;

        $this->assertEquals($complexMetadata, $dto->metadata);
        $this->assertEquals('textbook', $dto->metadata['source']);
        $this->assertEquals(['important', 'exam'], $dto->metadata['tags']);
        $this->assertEquals('intermediate', $dto->metadata['nested']['level']);
    }

    public function test_difficultyRange_acceptsValidValues(): void
    {
        $dto = new QuestionDTO();
        
        // Test all valid difficulty levels
        for ($difficulty = 1; $difficulty <= 5; $difficulty++) {
            $dto->difficulty = $difficulty;
            $this->assertEquals($difficulty, $dto->difficulty);
        }
    }

    public function test_scoreValues_acceptsPositiveNumbers(): void
    {
        $dto = new QuestionDTO();
        
        $testScores = [0.5, 1.0, 5.0, 10.0, 15.5, 20.0, 50.0, 100.0];
        
        foreach ($testScores as $score) {
            $dto->score = $score;
            $this->assertEquals($score, $dto->score);
        }
    }

    public function test_allQuestionTypes_canBeAssigned(): void
    {
        $dto = new QuestionDTO();
        
        foreach (QuestionType::cases() as $type) {
            $dto->type = $type;
            $this->assertEquals($type, $dto->type);
        }
    }

    public function test_explanation_canBeSetAndCleared(): void
    {
        $dto = new QuestionDTO();
        
        $dto->explanation = 'This is an explanation';
        $this->assertEquals('This is an explanation', $dto->explanation);
        
        $dto->explanation = null;
        $this->assertNull($dto->explanation);
        
        $dto->explanation = '';
        $this->assertEquals('', $dto->explanation);
    }

    public function test_emptyArrays_remainEmptyWhenNotModified(): void
    {
        $dto = new QuestionDTO();
        
        $this->assertEmpty($dto->categoryIds);
        $this->assertEmpty($dto->tagIds);
        $this->assertEmpty($dto->options);
        
        // Verify they are actual arrays
        $this->assertIsArray($dto->categoryIds);
        $this->assertIsArray($dto->tagIds);
        $this->assertIsArray($dto->options);
    }
}