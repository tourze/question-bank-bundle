<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Unit\DTO;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\DTO\SearchCriteria;
use Tourze\QuestionBankBundle\Enum\QuestionStatus;
use Tourze\QuestionBankBundle\Enum\QuestionType;

class SearchCriteriaTest extends TestCase
{
    public function testConstructor(): void
    {
        $criteria = new SearchCriteria();
        
        $this->assertNull($criteria->getKeyword());
        $this->assertEmpty($criteria->getTypes());
        $this->assertEmpty($criteria->getStatuses());
        $this->assertNull($criteria->getMinDifficulty());
        $this->assertNull($criteria->getMaxDifficulty());
        $this->assertEmpty($criteria->getCategoryIds());
        $this->assertEmpty($criteria->getTagIds());
        $this->assertFalse($criteria->requireAllTags());
        $this->assertFalse($criteria->includeArchived());
        $this->assertSame(1, $criteria->getPage());
        $this->assertSame(20, $criteria->getLimit());
        $this->assertSame(['createTime' => 'DESC'], $criteria->getOrderBy());
    }
    
    public function testWithAllFields(): void
    {
        $criteria = new SearchCriteria();
        $criteria->setKeyword('test');
        $criteria->setTypes([QuestionType::MULTIPLE_CHOICE]);
        $criteria->setStatuses([QuestionStatus::PUBLISHED]);
        $criteria->setMinDifficulty(1);
        $criteria->setMaxDifficulty(5);
        $criteria->setCategoryIds(['cat1', 'cat2']);
        $criteria->setTagIds(['tag1', 'tag2']);
        $criteria->setRequireAllTags(true);
        $criteria->setIncludeArchived(true);
        $criteria->setPage(2);
        $criteria->setLimit(50);
        $criteria->setOrderBy(['title' => 'ASC']);
        
        $this->assertSame('test', $criteria->getKeyword());
        $this->assertSame([QuestionType::MULTIPLE_CHOICE], $criteria->getTypes());
        $this->assertSame([QuestionStatus::PUBLISHED], $criteria->getStatuses());
        $this->assertSame(1, $criteria->getMinDifficulty());
        $this->assertSame(5, $criteria->getMaxDifficulty());
        $this->assertSame(['cat1', 'cat2'], $criteria->getCategoryIds());
        $this->assertSame(['tag1', 'tag2'], $criteria->getTagIds());
        $this->assertTrue($criteria->requireAllTags());
        $this->assertTrue($criteria->includeArchived());
        $this->assertSame(2, $criteria->getPage());
        $this->assertSame(50, $criteria->getLimit());
        $this->assertSame(['title' => 'ASC'], $criteria->getOrderBy());
    }
}