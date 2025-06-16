<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\QuestionBankBundle\Repository\CategoryRepository;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'question_bank_categories', options: ['comment' => '题库分类表'])]
#[UniqueEntity(fields: ['code'], message: '分类代码已存在')]
class Category implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true, options: ['comment' => '分类ID'])]
    private Uuid $id;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '分类名称'])]
    private string $name;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 50, unique: true, options: ['comment' => '分类代码'])]
    private string $code;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '分类描述'])]
    private ?string $description = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '排序顺序'])]
    private int $sortOrder = 0;

    #[IndexColumn]
    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否有效'])]
    private bool $valid = true;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true)]
    private ?self $parent = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['sortOrder' => 'ASC', 'name' => 'ASC'])]
    private Collection $children;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\ManyToMany(targetEntity: Question::class, mappedBy: 'categories', fetch: 'EXTRA_LAZY')]
    private Collection $questions;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '分类层级'])]
    private int $level = 0;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '分类路径'])]
    private string $path = '';

    #[CreatedByColumn]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '创建者'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '更新者'])]
    private ?string $updatedBy = null;

    #[CreateIpColumn]
    #[ORM\Column(type: Types::STRING, length: 45, nullable: true, options: ['comment' => '创建IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(type: Types::STRING, length: 45, nullable: true, options: ['comment' => '更新IP'])]
    private ?string $updatedFromIp = null;

    public function __construct(string $name, string $code)
    {
        $this->id = Uuid::v7();
        $this->name = $name;
        $this->code = $code;
        $this->children = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->createTime = new \DateTimeImmutable();
        $this->updateTime = new \DateTimeImmutable();
        $this->updatePath();
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
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        $this->updatePath();
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        if ($parent === $this) {
            throw new \InvalidArgumentException('Category cannot be its own parent');
        }

        if ($parent && $this->isAncestorOf($parent)) {
            throw new \InvalidArgumentException('Cannot set descendant as parent');
        }

        if ($this->parent !== null && $this->parent->getChildren()->contains($this)) {
            $this->parent->getChildren()->removeElement($this);
        }

        $this->parent = $parent;

        if ($parent !== null && !$parent->getChildren()->contains($this)) {
            $parent->getChildren()->add($this);
        }

        $this->updateLevel();
        $this->updatePath();

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;
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
        return $this->name;
    }

    public function isAncestorOf(self $category): bool
    {
        $parent = $category->getParent();
        while ($parent !== null) {
            if ($parent === $this) {
                return true;
            }
            $parent = $parent->getParent();
        }
        return false;
    }

    public function isDescendantOf(self $category): bool
    {
        return $category->isAncestorOf($this);
    }

    /**
     * @return array<self>
     */
    public function getAncestors(): array
    {
        $ancestors = [];
        $parent = $this->parent;

        while ($parent !== null) {
            array_unshift($ancestors, $parent);
            $parent = $parent->getParent();
        }

        return $ancestors;
    }

    /**
     * @return array<self>
     */
    public function getFullPath(): array
    {
        $path = $this->getAncestors();
        $path[] = $this;
        return $path;
    }

    private function updateLevel(): void
    {
        $this->level = $this->parent ? $this->parent->getLevel() + 1 : 0;

        foreach ($this->children as $child) {
            $child->updateLevel();
        }
    }

    private function updatePath(): void
    {
        if ($this->parent) {
            $this->path = $this->parent->getPath() . '/' . $this->code;
        } else {
            $this->path = '/' . $this->code;
        }

        foreach ($this->children as $child) {
            $child->updatePath();
        }
    }
}