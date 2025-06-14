<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\ValueObject;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

class DifficultyTest extends TestCase
{
    public function testCreateValidDifficulty(): void
    {
        $difficulty = new Difficulty(3);
        
        $this->assertEquals(3, $difficulty->getLevel());
        $this->assertEquals('中等', $difficulty->getLabel());
    }
    
    public function testCreateWithInvalidLevel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Difficulty level must be between 1 and 5, 6 given');
        
        new Difficulty(6);
    }
    
    public function testStaticFactoryMethods(): void
    {
        $easy = Difficulty::easy();
        $medium = Difficulty::medium();
        $hard = Difficulty::hard();
        
        $this->assertEquals(1, $easy->getLevel());
        $this->assertEquals(3, $medium->getLevel());
        $this->assertEquals(5, $hard->getLevel());
    }
    
    public function testComparison(): void
    {
        $easy = new Difficulty(1);
        $medium = new Difficulty(3);
        $hard = new Difficulty(5);
        
        $this->assertTrue($medium->isHarderThan($easy));
        $this->assertTrue($hard->isHarderThan($medium));
        $this->assertTrue($easy->isEasierThan($medium));
        $this->assertTrue($medium->isEasierThan($hard));
        
        $this->assertTrue($medium->equals(new Difficulty(3)));
        $this->assertFalse($medium->equals($easy));
    }
    
    public function testToString(): void
    {
        $difficulty = new Difficulty(4);
        
        $this->assertEquals('4', (string) $difficulty);
    }
}