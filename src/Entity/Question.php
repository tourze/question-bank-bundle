<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\QuestionBankBundle\Enum\QuestionStatus;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Repository\QuestionRepository;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\Table(name: 'question_bank_questions')]
#[ORM\Index(columns: ['type'], name: 'idx_question_type')]
#[ORM\Index(columns: ['status'], name: 'idx_question_status')]
#[ORM\Index(columns: ['difficulty'], name: 'idx_question_difficulty')]
#[ORM\Index(columns: ['create_time'], name: 'idx_question_create_time')]
class Question implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(type: Types::STRING, enumType: QuestionType::class)]
    private QuestionType $type;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $difficulty;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private string $score = '10.00';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $explanation = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::STRING, enumType: QuestionStatus::class)]
    private QuestionStatus $status;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'questions', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(name: 'question_bank_question_categories')]
    private Collection $categories;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'questions', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(name: 'question_bank_question_tags')]
    private Collection $tags;

    /**
     * 问题选项
     * @var Collection<int, Option>
     */
    #[ORM\OneToMany(targetEntity: Option::class, mappedBy: 'question', cascade: ['persist', 'remove'], orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $options;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $valid = true;

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
        $this->updateTime = new \DateTimeImmutable();
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
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getScore(): float
    {
        return (float) $this->score;
    }

    public function setScore(float $score): self
    {
        $this->score = (string) $score;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation;
    }

    public function setExplanation(?string $explanation): self
    {
        $this->explanation = $explanation;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;
        $this->updateTime = new \DateTimeImmutable();
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
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function archive(): self
    {
        if ($this->status !== QuestionStatus::PUBLISHED) {
            throw new \LogicException('Only published questions can be archived');
        }
        
        $this->status = QuestionStatus::ARCHIVED;
        $this->updateTime = new \DateTimeImmutable();
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
            // 建立双向关联
            if (!$category->getQuestions()->contains($this)) {
                $category->getQuestions()->add($this);
            }
            $this->updateTime = new \DateTimeImmutable();
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->removeElement($category)) {
            // 移除双向关联
            if ($category->getQuestions()->contains($this)) {
                $category->getQuestions()->removeElement($this);
            }
            $this->updateTime = new \DateTimeImmutable();
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
            $this->updateTime = new \DateTimeImmutable();
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            $tag->decrementUsageCount();
            $this->updateTime = new \DateTimeImmutable();
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
            $this->updateTime = new \DateTimeImmutable();
        }

        return $this;
    }

    public function removeOption(Option $option): self
    {
        if ($this->options->removeElement($option)) {
            if ($option->getQuestion() === $this) {
                $option->setQuestion(null);
            }
            $this->updateTime = new \DateTimeImmutable();
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
        $this->updateTime = new \DateTimeImmutable();
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
}