<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\QuestionBankBundle\Enum\QuestionStatus;
use Tourze\QuestionBankBundle\Enum\QuestionType;
use Tourze\QuestionBankBundle\Exception\QuestionStateException;
use Tourze\QuestionBankBundle\Repository\QuestionRepository;
use Tourze\QuestionBankBundle\ValueObject\Difficulty;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\Table(name: 'question_bank_questions', options: ['comment' => '题库问题表'])]
class Question implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use IpTraceableAware;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['comment' => '问题ID'])]
    #[ORM\CustomIdGenerator]
    #[Assert\Length(max: 36)]
    private string $id;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '问题标题'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $title;

    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => '问题内容'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 65535)]
    private string $content;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, enumType: QuestionType::class, options: ['comment' => '问题类型'])]
    #[Assert\Choice(choices: [QuestionType::SINGLE_CHOICE, QuestionType::MULTIPLE_CHOICE, QuestionType::TRUE_FALSE, QuestionType::FILL_BLANK, QuestionType::ESSAY])]
    private QuestionType $type;

    #[IndexColumn]
    #[ORM\Column(type: Types::SMALLINT, options: ['comment' => '难度级别'])]
    #[Assert\Range(min: 1, max: 10)]
    private int $difficulty;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, options: ['comment' => '分数'])]
    #[Assert\PositiveOrZero]
    #[Assert\Range(min: 0, max: 999.99)]
    #[Assert\Length(max: 6)]
    private string $score = '10.00';

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '问题解释'])]
    #[Assert\Length(max: 65535)]
    private ?string $explanation = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '扩展元数据'])]
    #[Assert\Type(type: 'array')]
    private ?array $metadata = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, enumType: QuestionStatus::class, options: ['comment' => '问题状态'])]
    #[Assert\Choice(choices: [QuestionStatus::DRAFT, QuestionStatus::PUBLISHED, QuestionStatus::ARCHIVED])]
    private QuestionStatus $status;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'questions', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(name: 'question_bank_question_categories')]
    private Collection $categories;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'questions', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(name: 'question_bank_question_tags')]
    private Collection $tags;

    /**
     * @var Collection<int, Option>
     */
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Option::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\OrderBy(value: ['sortOrder' => 'ASC'])]
    private Collection $options;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否有效'])]
    #[Assert\Type(type: 'bool')]
    private bool $valid = true;

    public function __construct()
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->status = QuestionStatus::DRAFT;
        $this->tags = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->createTime = new \DateTimeImmutable();
        $this->updateTime = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
        $this->updateTime = new \DateTimeImmutable();
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getType(): QuestionType
    {
        return $this->type;
    }

    public function setType(QuestionType $type): void
    {
        $this->type = $type;
    }

    public function getDifficulty(): Difficulty
    {
        return new Difficulty($this->difficulty);
    }

    public function setDifficulty(Difficulty $difficulty): void
    {
        $this->difficulty = $difficulty->getLevel();
    }

    /**
     * EasyAdmin helper method - get difficulty as int for forms
     */
    public function getDifficultyLevel(): int
    {
        return $this->difficulty;
    }

    /**
     * EasyAdmin helper method - set difficulty from int for forms
     */
    public function setDifficultyLevel(int $level): void
    {
        $this->difficulty = $level;
    }

    public function getScore(): float
    {
        return (float) $this->score;
    }

    public function setScore(float $score): void
    {
        $this->score = (string) $score;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation;
    }

    public function setExplanation(?string $explanation): void
    {
        $this->explanation = $explanation;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * @param array<string, mixed>|null $metadata
     */
    public function setMetadata(?array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getStatus(): QuestionStatus
    {
        return $this->status;
    }

    public function publish(): self
    {
        if (QuestionStatus::DRAFT !== $this->status) {
            throw new QuestionStateException('Only draft questions can be published');
        }

        $this->status = QuestionStatus::PUBLISHED;

        return $this;
    }

    public function archive(): self
    {
        if (QuestionStatus::PUBLISHED !== $this->status) {
            throw new QuestionStateException('Only published questions can be archived');
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

    /**
     * @return Collection<int, Option>
     */
    public function getCorrectOptions(): Collection
    {
        return $this->options->filter(fn (Option $option) => $option->isCorrect());
    }

    public function hasCorrectOption(): bool
    {
        return !$this->getCorrectOptions()->isEmpty();
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
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
     *
     * @return array<string, mixed>
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
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
            'type' => $this->getType()->toArray(),
            'analyse' => $this->getExplanation(),
            'correctLetters' => $correctLetters,
            'options' => $options,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveSecretArray(): array
    {
        $result = $this->retrieveApiArray();
        unset($result['analyse'], $result['correctLetters']);

        if (isset($result['options']) && is_array($result['options'])) {
            foreach ($result['options'] as $i => $option) {
                if (is_array($option)) {
                    unset($option['correct']);
                    $result['options'][$i] = $option;
                }
            }
        }

        return $result;
    }
}
