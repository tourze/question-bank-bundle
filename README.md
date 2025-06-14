# QuestionBankBundle

一个专注于题库管理的 Symfony Bundle，提供题目的存储、组织、检索等核心功能。

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

## 配置

在 `config/bundles.php` 中注册 Bundle：

```php
return [
    // ...
    Tourze\QuestionBankBundle\QuestionBankBundle::class => ['all' => true],
];
```

通过环境变量配置（在 `.env` 文件中）：

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

# 搜索配置
QUESTION_BANK_SEARCH_ENGINE=database      # 搜索引擎（默认：database）
QUESTION_BANK_SEARCH_MIN_QUERY_LENGTH=2   # 最小查询长度（默认：2）
```

## 使用示例

### 创建题目

```php
use Tourze\QuestionBankBundle\DTO\QuestionDTO;
use Tourze\QuestionBankBundle\DTO\OptionDTO;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Service\QuestionServiceInterface;

// 创建题目 DTO
$questionDTO = new QuestionDTO();
$questionDTO->title = '以下哪个是 PHP 的超全局变量？';
$questionDTO->content = '请选择正确答案';
$questionDTO->type = QuestionType::SINGLE_CHOICE;
$questionDTO->difficulty = 2;
$questionDTO->score = 10.0;
$questionDTO->explanation = '$_GET 是 PHP 的超全局变量，可以在脚本的任何地方访问';

// 添加选项
$questionDTO->options = [
    OptionDTO::create('$_GET', true),
    OptionDTO::create('$get', false),
    OptionDTO::create('$GET', false),
    OptionDTO::create('$_get', false),
];

// 创建题目
$question = $questionService->createQuestion($questionDTO);
```

### 搜索题目

```php
use Tourze\QuestionBankBundle\DTO\SearchCriteria;
use Tourze\QuestionBankBundle\Enum\QuestionType;

$criteria = new SearchCriteria();
$criteria->setKeyword('PHP')
    ->setTypes([QuestionType::SINGLE_CHOICE, QuestionType::MULTIPLE_CHOICE])
    ->setMinDifficulty(1)
    ->setMaxDifficulty(3)
    ->setLimit(20)
    ->setPage(1);

$result = $questionService->searchQuestions($criteria);

foreach ($result->getItems() as $question) {
    echo $question->getTitle() . "\n";
}
```

### 分类管理

```php
use Tourze\QuestionBankBundle\DTO\CategoryDTO;
use Tourze\QuestionBankBundle\Service\CategoryServiceInterface;

// 创建根分类
$programmingDTO = CategoryDTO::create('编程语言', 'programming');
$programming = $categoryService->createCategory($programmingDTO);

// 创建子分类
$phpDTO = CategoryDTO::create('PHP', 'php');
$phpDTO->parentId = $programming->getId()->toString();
$php = $categoryService->createCategory($phpDTO);

// 获取分类树
$tree = $categoryService->getCategoryTree();
```

### 标签管理

```php
use Tourze\QuestionBankBundle\Service\TagServiceInterface;

// 创建或查找标签
$tag = $tagService->findOrCreateTag('PHP基础');

// 获取热门标签
$popularTags = $tagService->getPopularTags(10);

// 搜索标签
$tags = $tagService->searchTags('PHP', 5);
```

## 实体说明

### Question（题目）

- `id`: UUID 主键
- `title`: 题目标题
- `content`: 题目内容（支持富文本）
- `type`: 题型（枚举）
- `difficulty`: 难度等级（1-5）
- `score`: 默认分值
- `explanation`: 答案解析
- `status`: 状态（草稿、已发布、已归档）
- `metadata`: 扩展元数据（JSON）
- `categories`: 所属分类（多对多）
- `tags`: 标签集合（多对多）

### Option（选项）

- `id`: UUID 主键
- `content`: 选项内容
- `isCorrect`: 是否正确答案
- `sortOrder`: 排序顺序
- `explanation`: 选项说明

### Category（分类）

- `id`: UUID 主键
- `name`: 分类名称
- `code`: 分类编码（唯一）
- `parent`: 父分类
- `level`: 层级深度
- `path`: 完整路径
- `questions`: 关联的题目（多对多）

### Tag（标签）

- `id`: UUID 主键
- `name`: 标签名称
- `slug`: 标签别名（唯一）
- `color`: 标签颜色
- `usageCount`: 使用次数

## 事件

Bundle 会发布以下事件，可以订阅这些事件进行扩展：

- `QuestionCreatedEvent`: 题目创建后
- `QuestionUpdatedEvent`: 题目更新后
- `QuestionDeletedEvent`: 题目删除后
- `CategoryReorganizedEvent`: 分类重组后
- `TagMergedEvent`: 标签合并后

## 扩展点

### 自定义题目验证器

```php
use Tourze\QuestionBankBundle\Validator\QuestionValidatorInterface;

class CustomQuestionValidator implements QuestionValidatorInterface
{
    public function validate(Question $question): ValidationResult
    {
        // 自定义验证逻辑
    }
}
```

### 自定义导入格式

```php
use Tourze\QuestionBankBundle\Importer\ImporterInterface;

class ExcelImporter implements ImporterInterface
{
    public function import(UploadedFile $file): ImportResult
    {
        // Excel 导入逻辑
    }
    
    public function supports(string $format): bool
    {
        return $format === 'xlsx';
    }
}
```

## 最佳实践

1. **使用 DTO 传输数据** - 不要直接暴露实体对象
2. **通过接口注入服务** - 便于测试和扩展
3. **监听事件进行扩展** - 而不是修改核心代码
4. **合理使用缓存** - 分类树和热门标签适合缓存
5. **批量操作使用事务** - 确保数据一致性

## License

MIT