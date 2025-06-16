# 题库管理模块设计文档

## 1. 模块概述

### 1.1 定位
`QuestionBankBundle` 是一个专注于题库管理的 Symfony Bundle，提供题目的存储、组织、检索等核心功能，不涉及考试、练习、统计等业务逻辑。

### 1.2 核心职责
- **题目管理**：创建、编辑、删除、查询题目
- **选项管理**：管理题目的选项（单选、多选、判断等）
- **分类管理**：题目的层级分类体系
- **标签管理**：题目的灵活标签系统
- **题目导入导出**：支持批量操作

### 1.3 非职责（明确排除）
- ❌ 考试管理（试卷生成、考试规则等）
- ❌ 练习管理（练习册、错题集等）
- ❌ 用户答题记录
- ❌ 统计分析
- ❌ 支付相关功能
- ❌ 权限控制（由上层业务处理）

## 2. 领域模型设计

### 2.1 核心实体

#### Question（题目）
```yaml
职责: 题目的基本信息和内容
属性:
  - id: UUID
  - title: 题目标题
  - content: 题目内容（支持富文本）
  - type: 题型（单选、多选、判断、填空、简答）
  - difficulty: 难度等级（1-5）
  - score: 默认分值
  - explanation: 答案解析
  - metadata: 扩展元数据（JSON）
  - category: 所属分类
  - tags: 标签集合
  - createdAt: 创建时间
  - updatedAt: 更新时间
  - status: 状态（草稿、已发布、已归档）
```

#### Option（选项）
```yaml
职责: 题目的选项信息
属性:
  - id: UUID
  - question: 所属题目
  - content: 选项内容
  - isCorrect: 是否正确答案
  - sortOrder: 排序顺序
  - explanation: 选项说明（可选）
```

#### Category（分类）
```yaml
职责: 题目的层级分类
属性:
  - id: UUID
  - name: 分类名称
  - code: 分类编码（唯一）
  - parent: 父分类
  - children: 子分类集合
  - description: 分类描述
  - sortOrder: 排序顺序
  - isActive: 是否激活
```

#### Tag（标签）
```yaml
职责: 题目的灵活标签
属性:
  - id: UUID
  - name: 标签名称
  - slug: 标签别名（唯一）
  - description: 标签描述
  - color: 标签颜色（用于UI展示）
  - usageCount: 使用次数
```

### 2.2 值对象

#### QuestionType（题型枚举）
```php
enum QuestionType: string {
    case SINGLE_CHOICE = 'single_choice';    // 单选题
    case MULTIPLE_CHOICE = 'multiple_choice'; // 多选题
    case TRUE_FALSE = 'true_false';          // 判断题
    case FILL_BLANK = 'fill_blank';          // 填空题
    case ESSAY = 'essay';                    // 简答题
}
```

#### QuestionStatus（题目状态枚举）
```php
enum QuestionStatus: string {
    case DRAFT = 'draft';           // 草稿
    case PUBLISHED = 'published';   // 已发布
    case ARCHIVED = 'archived';     // 已归档
}
```

#### Difficulty（难度值对象）
```php
class Difficulty {
    private int $level; // 1-5
    private string $label; // 简单、较易、中等、较难、困难
}
```

## 3. 服务层设计

### 3.1 核心服务

#### QuestionService
```yaml
职责: 题目的业务逻辑处理
方法:
  - createQuestion(QuestionDTO): Question
  - updateQuestion(id, QuestionDTO): Question
  - deleteQuestion(id): void
  - findQuestion(id): ?Question
  - searchQuestions(SearchCriteria): QuestionCollection
  - importQuestions(file): ImportResult
  - exportQuestions(criteria): ExportFile
  - validateQuestion(Question): ValidationResult
```

#### CategoryService
```yaml
职责: 分类的管理和树形结构维护
方法:
  - createCategory(CategoryDTO): Category
  - updateCategory(id, CategoryDTO): Category
  - deleteCategory(id): void
  - moveCategory(id, newParentId): void
  - getCategoryTree(): CategoryTree
  - getCategoryPath(id): CategoryPath
```

#### TagService
```yaml
职责: 标签的管理和关联
方法:
  - createTag(TagDTO): Tag
  - updateTag(id, TagDTO): Tag
  - deleteTag(id): void
  - findOrCreateTag(name): Tag
  - getPopularTags(limit): TagCollection
  - mergeTag(sourceId, targetId): void
```

### 3.2 仓储接口

#### QuestionRepositoryInterface
```php
interface QuestionRepositoryInterface {
    public function save(Question $question): void;
    public function remove(Question $question): void;
    public function find(string $id): ?Question;
    public function findByCategory(Category $category): array;
    public function findByTags(array $tags): array;
    public function search(SearchCriteria $criteria): PaginatedResult;
    public function countByType(): array;
}
```

## 4. 应用服务层

### 4.1 查询服务

#### QuestionQueryService
```yaml
职责: 提供各种查询场景的优化实现
方法:
  - getQuestionDetail(id): QuestionDetail
  - getQuestionsByCategory(categoryId, pagination): PaginatedQuestions
  - getQuestionsByTags(tagIds, pagination): PaginatedQuestions
  - getRandomQuestions(criteria): QuestionCollection
  - getQuestionStatistics(): QuestionStats
```

### 4.2 命令处理器

使用命令模式处理写操作：
- CreateQuestionCommand / CreateQuestionHandler
- UpdateQuestionCommand / UpdateQuestionHandler
- DeleteQuestionCommand / DeleteQuestionHandler
- ImportQuestionsCommand / ImportQuestionsHandler

## 5. 基础设施层

### 5.1 持久化
- 使用 Doctrine ORM 进行数据持久化
- 支持 MySQL 和 PostgreSQL
- 使用 UUID 作为主键

### 5.2 缓存策略
- 分类树缓存（TTL: 1小时）
- 热门标签缓存（TTL: 30分钟）
- 题目统计缓存（TTL: 5分钟）

### 5.3 事件系统
发布领域事件供其他模块订阅：
- QuestionCreatedEvent
- QuestionUpdatedEvent
- QuestionDeletedEvent
- CategoryReorganizedEvent
- TagMergedEvent

## 6. 接口设计

### 6.1 服务接口
所有服务通过接口定义，便于扩展和测试：
```php
namespace Tourze\QuestionBankBundle\Service;

interface QuestionServiceInterface {
    // 方法签名...
}
```

### 6.2 DTO 设计
使用 DTO 进行数据传输，避免直接暴露实体：
```php
class QuestionDTO {
    public string $title;
    public string $content;
    public QuestionType $type;
    public int $difficulty;
    public ?string $categoryId;
    public array $tagIds;
    public array $options;
}
```

## 7. 扩展点

### 7.1 题目验证器
```php
interface QuestionValidatorInterface {
    public function validate(Question $question): ValidationResult;
}
```

### 7.2 导入导出格式
```php
interface ImporterInterface {
    public function import(UploadedFile $file): ImportResult;
    public function supports(string $format): bool;
}
```

### 7.3 搜索引擎适配
```php
interface SearchEngineInterface {
    public function index(Question $question): void;
    public function search(SearchCriteria $criteria): SearchResult;
}
```

## 9. 使用示例

### 9.1 创建题目
```php
$questionDTO = new QuestionDTO();
$questionDTO->title = '以下哪个是 PHP 的超全局变量？';
$questionDTO->content = '请选择正确答案';
$questionDTO->type = QuestionType::SINGLE_CHOICE;
$questionDTO->difficulty = 2;
$questionDTO->categoryId = $categoryId;
$questionDTO->tagIds = ['php', 'variables'];
$questionDTO->options = [
    ['content' => '$_GET', 'isCorrect' => true],
    ['content' => '$get', 'isCorrect' => false],
    ['content' => '$GET', 'isCorrect' => false],
    ['content' => '$_get', 'isCorrect' => false],
];

$question = $questionService->createQuestion($questionDTO);
```

### 9.2 搜索题目
```php
$criteria = new SearchCriteria();
$criteria->keyword = 'PHP';
$criteria->types = [QuestionType::SINGLE_CHOICE, QuestionType::MULTIPLE_CHOICE];
$criteria->categoryIds = [$categoryId];
$criteria->difficulty = new DifficultyRange(1, 3);

$result = $questionQueryService->searchQuestions($criteria);
```

## 10. 测试策略

### 10.1 单元测试
- 实体逻辑测试
- 服务层测试（使用 Mock）
- 值对象测试

### 10.2 集成测试
- Repository 测试（使用测试数据库）
- 完整流程测试
- 事件发布测试

### 10.3 性能测试
- 大数据量查询测试
- 并发导入测试
- 缓存效果测试
