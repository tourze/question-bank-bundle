<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\QuestionBankBundle\Repository\TagRepository;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'question_bank_tags', options: ['comment' => '题库标签表'])]
#[UniqueEntity(fields: ['slug'], message: '标签slug已存在')]
class Tag implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true, options: ['comment' => '标签ID'])]
    private Uuid $id;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '标签名称'])]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 50, unique: true, options: ['comment' => '标签别名（URL友好）'])]
    private string $slug;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '标签描述'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 7, nullable: true, options: ['comment' => '标签颜色（十六进制）'])]
    private ?string $color = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '使用次数统计'])]
    private int $usageCount = 0;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\ManyToMany(targetEntity: Question::class, mappedBy: 'tags', fetch: 'EXTRA_LAZY')]
    private Collection $questions;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否有效'])]
    private bool $valid = true;


    #[CreateIpColumn]
    #[ORM\Column(type: Types::STRING, length: 45, nullable: true, options: ['comment' => '创建IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(type: Types::STRING, length: 45, nullable: true, options: ['comment' => '更新IP'])]
    private ?string $updatedFromIp = null;

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
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
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

    private function generateSlug(string $text): string
    {
        $slug = strtolower($text);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
}
