# QuestionBankBundle

[![PHP 8.1+](https://img.shields.io/badge/php-8.1%2B-blue.svg)](https://www.php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Build Status](https://img.shields.io/github/actions/workflow/status/your-org/php-monorepo/ci.yml?branch=master)](https://github.com/your-org/php-monorepo/actions)
[![Coverage Status](https://img.shields.io/codecov/c/github/your-org/php-monorepo.svg)](https://codecov.io/gh/your-org/php-monorepo)

[English](README.md) | [中文](README.zh-CN.md)

A Symfony Bundle focused on question bank management, providing core functionality for storing, organizing, and retrieving questions.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Dependencies](#dependencies)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Core Concepts](#core-concepts)
- [Entity Structure](#entity-structure)
- [Advanced Usage](#advanced-usage)
- [Best Practices](#best-practices)
- [Testing](#testing)
- [Development](#development)
- [FAQ](#faq)
- [Contributing](#contributing)
- [License](#license)

## Features

- 🎯 **Focused on Question Management** - Only concerns questions themselves, no exam or practice business logic
- 📝 **Multiple Question Types** - Single choice, multiple choice, true/false, fill-in-the-blank, short answer
- 🏷️ **Flexible Organization** - Multi-category and tag system support
- 🔍 **Powerful Search** - Multi-criteria combined search support
- 📊 **Status Management** - Draft, published, archived states
- 🔒 **Data Validation** - Complete data validation and business rule checking

## Installation

```bash
composer require tourze/question-bank-bundle
```

## Dependencies

This package depends on the following core components:

### System Requirements
- **PHP**: 8.1 or higher
- **Symfony**: 6.4 or higher
- **Doctrine ORM**: 3.0 or higher

### Bundle Dependencies
- `doctrine/doctrine-bundle`
- `symfony/framework-bundle`
- `symfony/validator`
- `tourze/doctrine-timestamp-bundle`
- `tourze/doctrine-track-bundle`
- `tourze/doctrine-user-bundle`

## Configuration

### Bundle Registration

Register the bundle in `config/bundles.php`:

```php
return [
    // ...
    Tourze\QuestionBankBundle\QuestionBankBundle::class => ['all' => true],
];
```

### Environment Configuration

Configure in `.env` file:

```bash
# Question Configuration
QUESTION_BANK_MAX_OPTIONS=10              # Max options count (default: 10)
QUESTION_BANK_MAX_CONTENT_LENGTH=5000     # Max content length (default: 5000)

# Category Configuration
QUESTION_BANK_CATEGORY_MAX_DEPTH=5        # Max hierarchy depth (default: 5)
QUESTION_BANK_CATEGORY_CACHE_TTL=3600     # Cache TTL in seconds (default: 3600)

# Tag Configuration
QUESTION_BANK_TAG_MAX_PER_QUESTION=10     # Max tags per question (default: 10)
QUESTION_BANK_TAG_AUTO_SLUG=true          # Auto generate slug (default: true)
```

## Quick Start

### Creating Questions

```php
use Tourze\QuestionBankBundle\DTO\QuestionDTO;
use Tourze\QuestionBankBundle\DTO\OptionDTO;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Service\QuestionServiceInterface;

// Create question
$questionDTO = new QuestionDTO();
$questionDTO->title = 'Which of the following is a PHP superglobal variable?';
$questionDTO->content = 'Please select the correct answer';
$questionDTO->type = QuestionType::SINGLE_CHOICE;
$questionDTO->difficulty = 2;
$questionDTO->score = 10.0;

// Add options
$questionDTO->options = [
    OptionDTO::create('$_GET', true),
    OptionDTO::create('$get', false),
    OptionDTO::create('$GET', false),
    OptionDTO::create('$_get', false),
];

// Save question
$question = $questionService->createQuestion($questionDTO);
```

### Searching Questions

```php
use Tourze\QuestionBankBundle\DTO\SearchCriteria;
use Tourze\QuestionBankBundle\Enum\QuestionType;

$criteria = new SearchCriteria();
$criteria->setKeyword('PHP')
    ->setTypes([QuestionType::SINGLE_CHOICE])
    ->setMinDifficulty(1)
    ->setMaxDifficulty(3)
    ->setLimit(20);

$result = $questionService->searchQuestions($criteria);
```

### Category Management

```php
use Tourze\QuestionBankBundle\DTO\CategoryDTO;
use Tourze\QuestionBankBundle\Service\CategoryServiceInterface;

// Create category
$categoryDTO = CategoryDTO::create('Programming Languages', 'programming');
$category = $categoryService->createCategory($categoryDTO);

// Create subcategory
$phpDTO = CategoryDTO::create('PHP', 'php');
$phpDTO->parentId = $category->getId()->toString();
$php = $categoryService->createCategory($phpDTO);
```

## Core Concepts

### Question Types

- **Single Choice** (`SINGLE_CHOICE`) - Only one correct answer
- **Multiple Choice** (`MULTIPLE_CHOICE`) - Can have multiple correct answers
- **True/False** (`TRUE_FALSE`) - Only true/false options
- **Fill in the Blank** (`FILL_IN_THE_BLANK`) - Requires text input
- **Short Answer** (`SHORT_ANSWER`) - Open-ended response

### Question Status

- **Draft** (`DRAFT`) - Question is being edited
- **Published** (`PUBLISHED`) - Question is ready for use
- **Archived** (`ARCHIVED`) - Question is disabled

### Difficulty Levels

Question difficulty ranges from 1-5:
- Level 1: Very Easy
- Level 2: Easy
- Level 3: Medium
- Level 4: Hard
- Level 5: Very Hard

## Entity Structure

### Question
- `id`: Unique identifier
- `title`: Question title
- `content`: Question content
- `type`: Question type
- `difficulty`: Difficulty level
- `score`: Default score
- `explanation`: Answer explanation
- `status`: Publication status

### Option
- `id`: Unique identifier
- `content`: Option content
- `isCorrect`: Whether it's a correct answer
- `sortOrder`: Sort order

### Category
- `id`: Unique identifier
- `name`: Category name
- `code`: Category code
- `parent`: Parent category
- `level`: Hierarchy level
- `path`: Full path

### Tag
- `id`: Unique identifier
- `name`: Tag name
- `slug`: Tag alias
- `color`: Tag color
- `usageCount`: Usage count

## Advanced Usage

### Batch Operations and Transaction Management

```php
// Batch question import example
class QuestionBatchImporter
{
    public function importQuestions(array $questionsData): ImportResult
    {
        $result = new ImportResult();
        
        $this->entityManager->beginTransaction();
        try {
            foreach ($questionsData as $data) {
                $dto = $this->buildQuestionDTO($data);
                $question = $this->questionService->createQuestion($dto);
                $result->addSuccess($question);
            }
            
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $result->addError('Batch import failed', $e->getMessage());
        }
        
        return $result;
    }
}
```

### Custom Search Engine

```php
// Elasticsearch integration
class ElasticsearchQuestionSearcher
{
    public function search(SearchCriteria $criteria): PaginatedResult
    {
        $query = [
            'bool' => [
                'must' => []
            ]
        ];
        
        // Add keyword search
        if ($criteria->getKeyword()) {
            $query['bool']['must'][] = [
                'multi_match' => [
                    'query' => $criteria->getKeyword(),
                    'fields' => ['title^2', 'content']
                ]
            ];
        }
        
        // Add category filter
        if ($criteria->getCategoryIds()) {
            $query['bool']['filter'][] = [
                'terms' => ['category_ids' => $criteria->getCategoryIds()]
            ];
        }
        
        return $this->executeSearch($query, $criteria);
    }
}
```

## Best Practices

1. **Use DTOs for data transfer** - Avoid direct entity manipulation
2. **Operate through service interfaces** - For better testing and extensibility
3. **Use caching wisely** - Category trees and popular tags are good candidates
4. **Use transactions for batch operations** - Ensure data consistency
5. **Listen to events for extensions** - Instead of modifying core code

## Testing

Run the test suite:

```bash
# Run all tests
./vendor/bin/phpunit packages/question-bank-bundle/tests

# Run unit tests
./vendor/bin/phpunit packages/question-bank-bundle/tests/Unit

# Run integration tests
./vendor/bin/phpunit packages/question-bank-bundle/tests/Integration
```

## Development

### Code Quality Checks

```bash
# PHPStan static analysis
./vendor/bin/phpstan analyse packages/question-bank-bundle

# Code style check and fix
./vendor/bin/php-cs-fixer fix packages/question-bank-bundle
```

## FAQ

### Q: How to implement random question selection?

A: Use the QuestionRepository's findRandom method:

```php
$randomQuestions = $questionRepository->findRandom(
    limit: 10,
    criteria: $searchCriteria
);
```

### Q: How to implement question versioning?

A: Listen to question update events:

```php
class QuestionVersionListener
{
    public function onQuestionUpdated(QuestionUpdatedEvent $event): void
    {
        $question = $event->getQuestion();
        // Save historical version
        $this->versionService->createSnapshot($question);
    }
}
```

## Contributing

Welcome to submit Pull Requests and Issues. Please read before development:

1. [Contributing Guide](CONTRIBUTING.md)
2. [Code Style](CODE_STYLE.md)
3. [Testing Guide](TESTING.md)

## License

MIT