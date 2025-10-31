<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Event;

use Tourze\QuestionBankBundle\Entity\Category;

class CategoryCreatedEvent extends QuestionBankEvent
{
    public function __construct(
        private readonly Category $category,
    ) {
    }

    public function getCategory(): Category
    {
        return $this->category;
    }
}
