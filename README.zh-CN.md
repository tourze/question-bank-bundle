# 题库管理包 (QuestionBankBundle)

[![PHP 8.1+](https://img.shields.io/badge/php-8.1%2B-blue.svg)](https://www.php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Build Status](https://img.shields.io/github/actions/workflow/status/your-org/php-monorepo/ci.yml?branch=master)](https://github.com/your-org/php-monorepo/actions)
[![Coverage Status](https://img.shields.io/codecov/c/github/your-org/php-monorepo.svg)](https://codecov.io/gh/your-org/php-monorepo)

[English](README.md) | [中文](README.zh-CN.md)

一个专注于题库管理的 Symfony Bundle，提供题目的存储、组织、检索等核心功能。

## 目录

- [特性](#特性)
- [安装](#安装)
- [Dependencies](#dependencies)
- [配置](#配置)
- [快速开始](#快速开始)
- [核心概念](#核心概念)
- [实体结构](#实体结构)
- [Advanced Usage](#advanced-usage)
- [最佳实践](#最佳实践)
- [测试](#测试)
- [开发](#开发)
- [常见问题](#常见问题)
- [贡献](#贡献)
- [许可证](#许可证)

## 特性

- 🎯 **专注题库管理** - 只关注题目本身，不涉及考试、练习等业务逻辑
- 📝 **多种题型支持** - 单选、多选、判断、填空、简答
- 🏷️ **灵活的组织方式** - 支持多分类和标签系统
- 🔍 **强大的搜索功能** - 支持多条件组合搜索
- 📊 **状态管理** - 草稿、已发布、已归档三种状态
- 🔒 **数据验证** - 完整的数据验证和业务规则检查

## 安装

```bash
composer require tourze/question-bank-bundle
```

## Dependencies

此包依赖以下核心组件：

### 系统要求
- **PHP**: 8.1 或更高版本
- **Symfony**: 6.4 或更高版本
- **Doctrine ORM**: 3.0 或更高版本

### 核心依赖
- `doctrine/orm` - ORM 数据库操作
- `doctrine/dbal` - 数据库抽象层
- `symfony/validator` - 数据验证
- `symfony/event-dispatcher` - 事件分发
- `symfony/uid` - UUID 生成

### 内部依赖 (Tourze Bundles)
- `tourze/doctrine-timestamp-bundle` - 时间戳字段支持
- `tourze/doctrine-user-bundle` - 用户关联字段支持
- `tourze/doctrine-ip-bundle` - IP 地址记录支持
- `tourze/doctrine-track-bundle` - 字段变更追踪
- `tourze/doctrine-indexed-bundle` - 索引管理
- `tourze/enum-extra` - 枚举扩展支持

## 配置

在 `config/bundles.php` 中注册 Bundle：

```php
return [
    // ...
    Tourze\QuestionBankBundle\QuestionBankBundle::class => ['all' => true],
];
```

### 环境变量配置

在 `.env` 文件中配置：

```bash
# 题目配置
QUESTION_BANK_MAX_OPTIONS=10              # 最大选项数（默认：10）
QUESTION_BANK_MAX_CONTENT_LENGTH=5000     # 最大内容长度（默认：5000）

# 分类配置
QUESTION_BANK_CATEGORY_MAX_DEPTH=5        # 最大层级深度（默认：5）
QUESTION_BANK_CATEGORY_CACHE_TTL=3600     # 缓存时间（秒）（默认：3600）

# 标签配置
QUESTION_BANK_TAG_MAX_PER_QUESTION=10     # 每题最大标签数（默认：10）
QUESTION_BANK_TAG_AUTO_SLUG=true          # 自动生成 slug（默认：true）
```

## 快速开始

### 创建题目

```php
use Tourze\QuestionBankBundle\DTO\QuestionDTO;
use Tourze\QuestionBankBundle\DTO\OptionDTO;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Service\QuestionServiceInterface;

// 创建题目
$questionDTO = new QuestionDTO();
$questionDTO->title = '以下哪个是 PHP 的超全局变量？';
$questionDTO->content = '请选择正确答案';
$questionDTO->type = QuestionType::SINGLE_CHOICE;
$questionDTO->difficulty = 2;
$questionDTO->score = 10.0;

// 添加选项
$questionDTO->options = [
    OptionDTO::create('$_GET', true),
    OptionDTO::create('$get', false),
    OptionDTO::create('$GET', false),
    OptionDTO::create('$_get', false),
];

// 保存题目
$question = $questionService->createQuestion($questionDTO);
```

### 搜索题目

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

### 分类管理

```php
use Tourze\QuestionBankBundle\DTO\CategoryDTO;
use Tourze\QuestionBankBundle\Service\CategoryServiceInterface;

// 创建分类
$categoryDTO = CategoryDTO::create('编程语言', 'programming');
$category = $categoryService->createCategory($categoryDTO);

// 创建子分类
$phpDTO = CategoryDTO::create('PHP', 'php');
$phpDTO->parentId = $category->getId()->toString();
$php = $categoryService->createCategory($phpDTO);
```

## 核心概念

### 题目类型

- **单选题** (`SINGLE_CHOICE`) - 只有一个正确答案
- **多选题** (`MULTIPLE_CHOICE`) - 可以有多个正确答案
- **判断题** (`TRUE_FALSE`) - 只有对错两个选项
- **填空题** (`FILL_IN_THE_BLANK`) - 需要填写答案
- **简答题** (`SHORT_ANSWER`) - 开放式回答

### 题目状态

- **草稿** (`DRAFT`) - 题目正在编辑中
- **已发布** (`PUBLISHED`) - 题目可以使用
- **已归档** (`ARCHIVED`) - 题目已停用

### 难度等级

题目难度分为 1-5 级：
- 1 级：非常简单
- 2 级：简单
- 3 级：中等
- 4 级：困难
- 5 级：非常困难

## 实体结构

### Question（题目）
- `id`: 唯一标识符
- `title`: 题目标题
- `content`: 题目内容
- `type`: 题目类型
- `difficulty`: 难度等级
- `score`: 默认分值
- `explanation`: 答案解析
- `status`: 发布状态

### Option（选项）
- `id`: 唯一标识符
- `content`: 选项内容
- `isCorrect`: 是否正确答案
- `sortOrder`: 排序顺序

### Category（分类）
- `id`: 唯一标识符
- `name`: 分类名称
- `code`: 分类编码
- `parent`: 父分类
- `level`: 层级深度
- `path`: 完整路径

### Tag（标签）
- `id`: 唯一标识符
- `name`: 标签名称
- `slug`: 标签别名
- `color`: 标签颜色
- `usageCount`: 使用次数

## 事件系统

Bundle 提供以下事件，可以订阅这些事件进行扩展：

- `QuestionCreatedEvent`: 题目创建后触发
- `QuestionUpdatedEvent`: 题目更新后触发
- `QuestionDeletedEvent`: 题目删除后触发
- `CategoryReorganizedEvent`: 分类重组后触发
- `TagMergedEvent`: 标签合并后触发

## Advanced Usage

### 批量操作和事务管理

```php
// 批量导入题目示例
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
            $result->addError('批量导入失败', $e->getMessage());
        }
        
        return $result;
    }
}
```

### 自定义搜索引擎

```php
// 集成 Elasticsearch 搜索
class ElasticsearchQuestionSearcher
{
    public function search(SearchCriteria $criteria): PaginatedResult
    {
        $query = [
            'bool' => [
                'must' => []
            ]
        ];
        
        // 添加关键词搜索
        if ($criteria->getKeyword()) {
            $query['bool']['must'][] = [
                'multi_match' => [
                    'query' => $criteria->getKeyword(),
                    'fields' => ['title^2', 'content']
                ]
            ];
        }
        
        // 添加分类过滤
        if ($criteria->getCategoryIds()) {
            $query['bool']['filter'][] = [
                'terms' => ['category_ids' => $criteria->getCategoryIds()]
            ];
        }
        
        return $this->executeSearch($query, $criteria);
    }
}
```

### 权限控制集成

```php
// 结合 Symfony Security 组件
class SecuredQuestionService
{
    public function __construct(
        private QuestionService $questionService,
        private Security $security
    ) {}
    
    public function createQuestion(QuestionDTO $dto): Question
    {
        // 检查用户权限
        if (!$this->security->isGranted('ROLE_QUESTION_CREATE')) {
            throw new AccessDeniedException('无权限创建题目');
        }
        
        return $this->questionService->createQuestion($dto);
    }
}
```

### 缓存优化策略

```php
// 分层缓存策略
class CachedQuestionService
{
    public function __construct(
        private QuestionService $questionService,
        private CacheInterface $cache,
        private CacheInterface $localCache
    ) {}
    
    public function getPopularQuestions(int $limit = 10): array
    {
        // L1 缓存：内存缓存，TTL 60秒
        $cacheKey = "popular_questions_{$limit}";
        
        return $this->localCache->get($cacheKey, function() use ($limit) {
            // L2 缓存：Redis 缓存，TTL 300秒
            return $this->cache->get("popular_questions_{$limit}", function() use ($limit) {
                return $this->questionService->getPopularQuestions($limit);
            });
        });
    }
}
```

### 题目导入导出

```php
// Excel 导入导出功能
class ExcelQuestionHandler
{
    public function exportToExcel(array $questions): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 设置表头
        $sheet->setCellValue('A1', '题目标题');
        $sheet->setCellValue('B1', '题目内容');
        $sheet->setCellValue('C1', '题目类型');
        $sheet->setCellValue('D1', '难度等级');
        
        // 填充数据
        foreach ($questions as $index => $question) {
            $row = $index + 2;
            $sheet->setCellValue("A{$row}", $question->getTitle());
            $sheet->setCellValue("B{$row}", $question->getContent());
            $sheet->setCellValue("C{$row}", $question->getType()->value);
            $sheet->setCellValue("D{$row}", $question->getDifficulty());
        }
        
        $writer = new Xlsx($spreadsheet);
        $filename = tempnam(sys_get_temp_dir(), 'questions_export');
        $writer->save($filename);
        
        return $filename;
    }
}
```

### API 接口封装

```php
// RESTful API 控制器
#[Route('/api/questions')]
class QuestionApiController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function list(
        Request $request,
        QuestionService $questionService
    ): JsonResponse {
        $criteria = SearchCriteria::fromRequest($request);
        $result = $questionService->searchQuestions($criteria);
        
        return $this->json([
            'data' => array_map(fn($q) => $q->toArray(), $result->getItems()),
            'pagination' => [
                'page' => $result->getPage(),
                'limit' => $result->getLimit(),
                'total' => $result->getTotal()
            ]
        ]);
    }
    
    #[Route('', methods: ['POST'])]
    public function create(
        Request $request,
        QuestionService $questionService,
        ValidatorInterface $validator
    ): JsonResponse {
        $dto = QuestionDTO::fromArray($request->toArray());
        
        $violations = $validator->validate($dto);
        if (count($violations) > 0) {
            return $this->json(['errors' => (string) $violations], 400);
        }
        
        $question = $questionService->createQuestion($dto);
        
        return $this->json($question->toArray(), 201);
    }
}
```

## 最佳实践

1. **使用 DTO 进行数据传输** - 避免直接操作实体对象
2. **通过服务接口进行操作** - 便于测试和扩展
3. **合理使用缓存** - 分类树和热门标签适合缓存
4. **批量操作使用事务** - 确保数据一致性
5. **监听事件进行扩展** - 而不是修改核心代码

## 测试

运行测试套件：

```bash
# 运行所有测试
./vendor/bin/phpunit packages/question-bank-bundle/tests

# 运行单元测试
./vendor/bin/phpunit packages/question-bank-bundle/tests/Unit

# 运行集成测试
./vendor/bin/phpunit packages/question-bank-bundle/tests/Integration
```

## 开发

### 代码质量检查

```bash
# PHPStan 静态分析
./vendor/bin/phpstan analyse packages/question-bank-bundle

# 代码格式检查与修复
./vendor/bin/php-cs-fixer fix packages/question-bank-bundle
```

## 常见问题

### Q: 如何实现题目随机抽取？

A: 使用 QuestionRepository 的 findRandomQuestions 方法：

```php
$randomQuestions = $questionRepository->findRandomQuestions(
    limit: 10,
    categoryIds: ['category-1', 'category-2'],
    difficulty: [3, 4, 5]
);
```

### Q: 如何实现题目版本控制？

A: 监听题目更新事件：

```php
class QuestionVersionListener
{
    public function onQuestionUpdated(QuestionUpdatedEvent $event): void
    {
        $question = $event->getQuestion();
        // 保存历史版本
        $this->versionService->createSnapshot($question);
    }
}
```

## 贡献

欢迎提交 Pull Request 和 Issue。开发前请阅读：

1. [贡献指南](CONTRIBUTING.md)
2. [代码规范](CODE_STYLE.md)
3. [测试指南](TESTING.md)

## 许可证

MIT
