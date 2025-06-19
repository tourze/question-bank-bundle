<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\QuestionBankBundle\Enum\QuestionStatus;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Repository\QuestionRepository;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\Table(name: 'question_bank_questions', options: ['comment' => '题库问题表'])]
#[ORM\Index(name: 'idx_question_type', columns: ['type'])]
#[ORM\Index(name: 'idx_question_status', columns: ['status'])]
#[ORM\Index(name: 'idx_question_difficulty', columns: ['difficulty'])]
#[ORM\Index(name: 'idx_question_create_time', columns: ['create_time'])]
class Question implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\Column(type: Types::GUID, unique: true, options: ['comment' => '问题ID'])]
    private Uuid $id;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '问题标题'])]
    private string $title;

    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => '问题内容'])]
    private string $content;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, enumType: QuestionType::class, options: ['comment' => '问题类型'])]
    private QuestionType $type;

    #[IndexColumn]
    #[ORM\Column(type: Types::SMALLINT, options: ['comment' => '难度级别'])]
    private int $difficulty;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, options: ['comment' => '分数'])]
    private string $score = '10.00';

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '问题解释'])]
    private ?string $explanation = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '扩展元数据'])]
    private ?array $metadata = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, enumType: QuestionStatus::class, options: ['comment' => '问题状态'])]
    private QuestionStatus $status;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'questions', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(name: 'question_bank_question_categories')]
    private Collection $categories;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'questions', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(name: 'question_bank_question_tags')]
    private Collection $tags;

    /**
     * @var Collection<int, Option>
     */
    #[ORM\OneToMany(targetEntity: Option::class, mappedBy: 'question', cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $options;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否有效'])]
    private bool $valid = true;


    #[CreateIpColumn]
    #[ORM\Column(type: Types::STRING, length: 45, nullable: true, options: ['comment' => '创建IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(type: Types::STRING, length: 45, nullable: true, options: ['comment' => '更新IP'])]
    private ?string $updatedFromIp = null;

    public function __construct(
        string $title,
        string $content,
        QuestionType $type,
        Difficulty $difficulty
    ) {
        $this->id = Uuid::v7();
        $this->title = $title;
        $this->content = $content;
        $this->type = $type;
        $this->difficulty = $difficulty->getLevel();
        $this->status = QuestionStatus::DRAFT;
        $this->tags = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->createTime = new \DateTimeImmutable();
        $this->updateTime = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getType(): QuestionType
    {
        return $this->type;
    }

    public function getDifficulty(): Difficulty
    {
        return new Difficulty($this->difficulty);
    }

    public function setDifficulty(Difficulty $difficulty): self
    {
        $this->difficulty = $difficulty->getLevel();
        return $this;
    }

    public function getScore(): float
    {
        return (float) $this->score;
    }

    public function setScore(float $score): self
    {
        $this->score = (string) $score;
        return $this;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation;
    }

    public function setExplanation(?string $explanation): self
    {
        $this->explanation = $explanation;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getStatus(): QuestionStatus
    {
        return $this->status;
    }

    public function publish(): self
    {
        if ($this->status !== QuestionStatus::DRAFT) {
            throw new \LogicException('Only draft questions can be published');
        }

        $this->status = QuestionStatus::PUBLISHED;
        return $this;
    }

    public function archive(): self
    {
        if ($this->status !== QuestionStatus::PUBLISHED) {
            throw new \LogicException('Only published questions can be archived');
        }

        $this->status = QuestionStatus::ARCHIVED;
        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            if (!$category->getQuestions()->contains($this)) {
                $category->getQuestions()->add($this);
            }
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->removeElement($category)) {
            if ($category->getQuestions()->contains($this)) {
                $category->getQuestions()->removeElement($this);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->incrementUsageCount();
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            $tag->decrementUsageCount();
        }

        return $this;
    }

    /**
     * @return Collection<int, Option>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(Option $option): self
    {
        if (!$this->options->contains($option)) {
            $this->options->add($option);
            $option->setQuestion($this);
        }

        return $this;
    }

    public function removeOption(Option $option): self
    {
        if ($this->options->removeElement($option)) {
            if ($option->getQuestion() === $this) {
                $option->setQuestion(null);
            }
        }

        return $this;
    }

    public function getCorrectOptions(): Collection
    {
        return $this->options->filter(fn(Option $option) => $option->isCorrect());
    }

    public function hasCorrectOption(): bool
    {
        return !$this->getCorrectOptions()->isEmpty();
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): self
    {
        $this->valid = $valid;
        return $this;
    }


    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setCreatedFromIp(?string $createdFromIp): self
    {
        $this->createdFromIp = $createdFromIp;
        return $this;
    }

    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
    }

    public function setUpdatedFromIp(?string $updatedFromIp): self
    {
        $this->updatedFromIp = $updatedFromIp;
        return $this;
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    public function isUsable(): bool
    {
        return $this->status->isUsable();
    }

    /**
     * 完整信息（包含正确答案）
     */
    public function retrieveApiArray(): array
    {
        $options = [];
        $letterMap = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        $correctLetters = [];

        foreach ($this->getOptions() as $i => $option) {
            $letter = $letterMap[$i] ?? chr(65 + $i);
            $options[] = [
                ...$option->retrieveApiArray(),
                'letter' => $letter,
            ];
            if ($option->isCorrect()) {
                $correctLetters[] = $letter;
            }
        }

        return [
            'id' => $this->getId()->toRfc4122(),
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
            'type' => $this->getType()->toArray(),
            'analyse' => $this->getExplanation(),
            'correctLetters' => $correctLetters,
            'options' => $options,
        ];
    }

    public function retrieveSecretArray(): array
    {
        $result = $this->retrieveApiArray();
        unset($result['analyse']);
        unset($result['correctLetters']);
        foreach ($result['options'] as $i => $option) {
            unset($option['correct']);
            $result['options'][$i] = $option;
        }

        return $result;
    }
}
