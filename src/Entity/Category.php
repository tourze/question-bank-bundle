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
use Tourze\QuestionBankBundle\Repository\CategoryRepository;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'question_bank_categories')]
#[ORM\UniqueConstraint(name: 'uniq_category_code', columns: ['code'])]
#[ORM\Index(columns: ['sort_order'], name: 'idx_category_sort_order')]
#[ORM\Index(columns: ['valid'], name: 'idx_category_valid')]
class Category implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 50, unique: true)]
    private string $code;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $sortOrder = 0;

    #[ORM\Column(type: Types::BOOLEAN)]
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

    #[ORM\Column(type: Types::INTEGER)]
    private int $level = 0;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $path = '';


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
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        $this->updateTime = new \DateTimeImmutable();
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

        // 从旧父级中移除
        if ($this->parent !== null && $this->parent->getChildren()->contains($this)) {
            $this->parent->getChildren()->removeElement($this);
        }

        $this->parent = $parent;

        // 添加到新父级中
        if ($parent !== null && !$parent->getChildren()->contains($this)) {
            $parent->getChildren()->add($this);
        }

        $this->updateLevel();
        $this->updatePath();
        $this->updateTime = new \DateTimeImmutable();

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

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * 检查当前分类是否是指定分类的祖先
     */
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

    /**
     * 检查当前分类是否是指定分类的后代
     */
    public function isDescendantOf(self $category): bool
    {
        return $category->isAncestorOf($this);
    }

    /**
     * 获取所有祖先分类（从根到父）
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
     * 获取从根到当前分类的完整路径
     * @return array<self>
     */
    public function getFullPath(): array
    {
        $path = $this->getAncestors();
        $path[] = $this;
        return $path;
    }

    /**
     * 更新层级
     */
    private function updateLevel(): void
    {
        $this->level = $this->parent ? $this->parent->getLevel() + 1 : 0;

        foreach ($this->children as $child) {
            $child->updateLevel();
        }
    }

    /**
     * 更新路径
     */
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