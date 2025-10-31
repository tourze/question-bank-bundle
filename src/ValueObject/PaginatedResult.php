<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\ValueObject;

/**
 * @template T
 *
 * @implements \IteratorAggregate<int, T>
 */
final class PaginatedResult implements \IteratorAggregate, \Countable
{
    /**
     * @param array<T> $items
     */
    public function __construct(
        private readonly array $items,
        private readonly int $total,
        private readonly int $page,
        private readonly int $limit,
    ) {
    }

    /**
     * @return array<T>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->total / $this->limit);
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->getTotalPages();
    }

    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }

    public function getFirstItem(): int
    {
        if (0 === $this->total) {
            return 0;
        }

        return ($this->page - 1) * $this->limit + 1;
    }

    public function getLastItem(): int
    {
        if (0 === $this->total) {
            return 0;
        }

        return min($this->page * $this->limit, $this->total);
    }

    /**
     * @return \Traversable<int, T>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return 0 === count($this->items);
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    public function isFirstPage(): bool
    {
        return 1 === $this->page;
    }

    public function isLastPage(): bool
    {
        return $this->page >= $this->getTotalPages();
    }
}
