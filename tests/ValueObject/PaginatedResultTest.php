<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\ValueObject;

use PHPUnit\Framework\TestCase;
use Tourze\QuestionBankBundle\ValueObject\PaginatedResult;

class PaginatedResultTest extends TestCase
{
    public function test_constructor_setsAllProperties(): void
    {
        $items = ['item1', 'item2', 'item3'];
        $result = new PaginatedResult($items, 10, 2, 5);

        $this->assertEquals($items, $result->getItems());
        $this->assertEquals(10, $result->getTotal());
        $this->assertEquals(2, $result->getPage());
        $this->assertEquals(5, $result->getLimit());
    }

    public function test_getItems_returnsOriginalItems(): void
    {
        $items = ['item1', 'item2', 'item3'];
        $result = new PaginatedResult($items, 10, 1, 5);

        $this->assertEquals($items, $result->getItems());
        $this->assertCount(3, $result->getItems());
    }

    public function test_getTotal_returnsTotalCount(): void
    {
        $result = new PaginatedResult([], 25, 1, 10);

        $this->assertEquals(25, $result->getTotal());
    }

    public function test_getPage_returnsCurrentPage(): void
    {
        $result = new PaginatedResult([], 25, 3, 10);

        $this->assertEquals(3, $result->getPage());
    }

    public function test_getLimit_returnsLimit(): void
    {
        $result = new PaginatedResult([], 25, 1, 15);

        $this->assertEquals(15, $result->getLimit());
    }

    public function test_getTotalPages_calculatesCorrectly(): void
    {
        // 测试整除情况
        $result1 = new PaginatedResult([], 20, 1, 10);
        $this->assertEquals(2, $result1->getTotalPages());

        // 测试有余数情况
        $result2 = new PaginatedResult([], 25, 1, 10);
        $this->assertEquals(3, $result2->getTotalPages());

        // 测试0条记录
        $result3 = new PaginatedResult([], 0, 1, 10);
        $this->assertEquals(0, $result3->getTotalPages());

        // 测试少于一页的情况
        $result4 = new PaginatedResult([], 5, 1, 10);
        $this->assertEquals(1, $result4->getTotalPages());
    }

    public function test_hasNextPage_returnsTrueWhenNextPageExists(): void
    {
        $result = new PaginatedResult([], 25, 2, 10);

        $this->assertTrue($result->hasNextPage());
    }

    public function test_hasNextPage_returnsFalseWhenOnLastPage(): void
    {
        $result = new PaginatedResult([], 25, 3, 10);

        $this->assertFalse($result->hasNextPage());
    }

    public function test_hasNextPage_returnsFalseWhenNoItems(): void
    {
        $result = new PaginatedResult([], 0, 1, 10);

        $this->assertFalse($result->hasNextPage());
    }

    public function test_hasPreviousPage_returnsTrueWhenNotOnFirstPage(): void
    {
        $result = new PaginatedResult([], 25, 2, 10);

        $this->assertTrue($result->hasPreviousPage());
    }

    public function test_hasPreviousPage_returnsFalseWhenOnFirstPage(): void
    {
        $result = new PaginatedResult([], 25, 1, 10);

        $this->assertFalse($result->hasPreviousPage());
    }

    public function test_isEmpty_returnsTrueWhenNoItems(): void
    {
        $result = new PaginatedResult([], 0, 1, 10);

        $this->assertTrue($result->isEmpty());
    }

    public function test_isEmpty_returnsFalseWhenHasItems(): void
    {
        $result = new PaginatedResult(['item1'], 1, 1, 10);

        $this->assertFalse($result->isEmpty());
    }

    public function test_count_returnsNumberOfItems(): void
    {
        $items = ['item1', 'item2', 'item3'];
        $result = new PaginatedResult($items, 25, 1, 10);

        $this->assertEquals(3, $result->count());
        $this->assertCount(3, $result);
    }

    public function test_iterator_allowsIteration(): void
    {
        $items = ['item1', 'item2', 'item3'];
        $result = new PaginatedResult($items, 10, 1, 10);

        $iteratedItems = [];
        foreach ($result as $item) {
            $iteratedItems[] = $item;
        }

        $this->assertEquals($items, $iteratedItems);
    }

    public function test_iterator_worksWithEmptyResult(): void
    {
        $result = new PaginatedResult([], 0, 1, 10);

        $iteratedItems = [];
        foreach ($result as $item) {
            $iteratedItems[] = $item;
        }

        $this->assertEmpty($iteratedItems);
    }

    public function test_getOffset_calculatesCorrectly(): void
    {
        // 第1页，每页10条
        $result1 = new PaginatedResult([], 25, 1, 10);
        $this->assertEquals(0, $result1->getOffset());

        // 第2页，每页10条
        $result2 = new PaginatedResult([], 25, 2, 10);
        $this->assertEquals(10, $result2->getOffset());

        // 第3页，每页5条
        $result3 = new PaginatedResult([], 25, 3, 5);
        $this->assertEquals(10, $result3->getOffset());
    }

    public function test_isFirstPage_returnsTrueOnFirstPage(): void
    {
        $result = new PaginatedResult([], 25, 1, 10);

        $this->assertTrue($result->isFirstPage());
    }

    public function test_isFirstPage_returnsFalseOnOtherPages(): void
    {
        $result = new PaginatedResult([], 25, 2, 10);

        $this->assertFalse($result->isFirstPage());
    }

    public function test_isLastPage_returnsTrueOnLastPage(): void
    {
        $result = new PaginatedResult([], 25, 3, 10);

        $this->assertTrue($result->isLastPage());
    }

    public function test_isLastPage_returnsFalseOnOtherPages(): void
    {
        $result = new PaginatedResult([], 25, 2, 10);

        $this->assertFalse($result->isLastPage());
    }

    public function test_edgeCases(): void
    {
        // 测试大数值
        $largeResult = new PaginatedResult([], 1000000, 5000, 200);
        $this->assertEquals(5000, $largeResult->getTotalPages());

        // 测试单条记录
        $singleResult = new PaginatedResult(['item'], 1, 1, 10);
        $this->assertEquals(1, $singleResult->getTotalPages());
        $this->assertTrue($singleResult->isFirstPage());
        $this->assertTrue($singleResult->isLastPage());
    }

    public function test_realWorldScenario(): void
    {
        // 模拟真实的分页场景：总共127条记录，每页20条，当前第3页
        $currentPageItems = array_fill(0, 20, 'question');
        $result = new PaginatedResult($currentPageItems, 127, 3, 20);

        $this->assertCount(20, $result->getItems());
        $this->assertEquals(127, $result->getTotal());
        $this->assertEquals(3, $result->getPage());
        $this->assertEquals(20, $result->getLimit());
        $this->assertEquals(7, $result->getTotalPages()); // ceil(127/20) = 7
        $this->assertEquals(40, $result->getOffset()); // (3-1) * 20 = 40
        $this->assertTrue($result->hasPreviousPage());
        $this->assertTrue($result->hasNextPage());
        $this->assertFalse($result->isFirstPage());
        $this->assertFalse($result->isLastPage());
        $this->assertFalse($result->isEmpty());
    }
}