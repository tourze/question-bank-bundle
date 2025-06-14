<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\QuestionBankBundle\Repository\OptionRepository;

#[ORM\Entity(repositoryClass: OptionRepository::class)]
#[ORM\Table(name: 'question_bank_options')]
#[ORM\Index(columns: ['sort_order'], name: 'idx_option_sort_order')]
class Option implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isCorrect;

    #[ORM\Column(type: Types::INTEGER)]
    private int $sortOrder = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $explanation = null;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'options', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $valid = true;

    public function __construct(string $content, bool $isCorrect = false, int $sortOrder = 0)
    {
        $this->id = Uuid::v7();
        $this->content = $content;
        $this->isCorrect = $isCorrect;
        $this->sortOrder = $sortOrder;
        $this->createTime = new \DateTimeImmutable();
        $this->updateTime = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
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

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }

    public function setIsCorrect(bool $isCorrect): self
    {
        $this->isCorrect = $isCorrect;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
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

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
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
        return $this->content;
    }
}