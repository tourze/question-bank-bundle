<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\QuestionBankBundle\Exception\CategoryHierarchyException;
use Tourze\QuestionBankBundle\Repository\CategoryRepository;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'question_bank_categories', options: ['comment' => '题库分类表'])]
#[UniqueEntity(fields: ['code'], message: '分类代码已存在')]
class Category implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use IpTraceableAware;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['comment' => '分类ID'])]
    #[ORM\CustomIdGenerator]
    #[Assert\Length(max: 36)]
    private string $id;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '分类名称'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $name;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 50, unique: true, options: ['comment' => '分类代码'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $code;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '分类描述'])]
    #[Assert\Length(max: 65535)]
    private ?string $description = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '排序顺序'])]
    #[Assert\PositiveOrZero]
    private int $sortOrder = 0;

    #[IndexColumn]
    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否有效'])]
    #[Assert\Type(type: 'bool')]
    private bool $valid = true;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true)]
    private ?self $parent = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(value: ['sortOrder' => 'ASC', 'name' => 'ASC'])]
    private Collection $children;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\ManyToMany(targetEntity: Question::class, mappedBy: 'categories', fetch: 'EXTRA_LAZY')]
    private Collection $questions;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '分类层级'])]
    #[Assert\PositiveOrZero]
    private int $level = 0;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '分类路径'])]
    #[Assert\Length(max: 255)]
    private string $path = '';

    public function __construct()
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->children = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->createTime = new \DateTimeImmutable();
        $this->updateTime = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
        $this->updatePath();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        if ($parent === $this) {
            throw new CategoryHierarchyException('Category cannot be its own parent');
        }

        if (null !== $parent && $this->isAncestorOf($parent)) {
            throw new CategoryHierarchyException('Cannot set descendant as parent');
        }

        if (null !== $this->parent && $this->parent->getChildren()->contains($this)) {
            $this->parent->getChildren()->removeElement($this);
        }

        $this->parent = $parent;

        if (null !== $parent && !$parent->getChildren()->contains($this)) {
            $parent->getChildren()->add($this);
        }

        $this->updateLevel();
        $this->updatePath();
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

    public function isAncestorOf(self $category): bool
    {
        $parent = $category->getParent();
        while (null !== $parent) {
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

        while (null !== $parent) {
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
        $this->level = null !== $this->parent ? $this->parent->getLevel() + 1 : 0;

        foreach ($this->children as $child) {
            $child->updateLevel();
        }
    }

    private function updatePath(): void
    {
        if (null !== $this->parent) {
            $this->path = $this->parent->getPath() . '/' . $this->code;
        } else {
            $this->path = '/' . $this->code;
        }

        foreach ($this->children as $child) {
            $child->updatePath();
        }
    }
}
