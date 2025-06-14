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
use Tourze\QuestionBankBundle\Repository\TagRepository;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'question_bank_tags')]
#[ORM\UniqueConstraint(name: 'uniq_tag_slug', columns: ['slug'])]
#[ORM\Index(columns: ['usage_count'], name: 'idx_tag_usage_count')]
class Tag implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 50, unique: true)]
    private string $slug;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 7, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $usageCount = 0;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\ManyToMany(targetEntity: Question::class, mappedBy: 'tags', fetch: 'EXTRA_LAZY')]
    private Collection $questions;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $valid = true;


    public function __construct(string $name, ?string $slug = null)
    {
        $this->id = Uuid::v7();
        $this->name = $name;
        $this->slug = $slug ?? $this->generateSlug($name);
        $this->questions = new ArrayCollection();
        $this->createTime = new \DateTimeImmutable();
        $this->updateTime = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        if ($color !== null && !preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            throw new \InvalidArgumentException('Color must be a valid hex color (e.g., #FF0000)');
        }
        
        $this->color = $color;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getUsageCount(): int
    {
        return $this->usageCount;
    }

    public function incrementUsageCount(): self
    {
        $this->usageCount++;
        return $this;
    }

    public function decrementUsageCount(): self
    {
        if ($this->usageCount > 0) {
            $this->usageCount--;
        }
        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
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
        return $this->name;
    }

    /**
     * 生成 slug
     */
    private function generateSlug(string $text): string
    {
        // 简单的 slug 生成逻辑
        $slug = strtolower($text);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
}