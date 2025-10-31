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
use Tourze\QuestionBankBundle\Exception\TagValidationException;
use Tourze\QuestionBankBundle\Repository\TagRepository;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'question_bank_tags', options: ['comment' => '题库标签表'])]
#[UniqueEntity(fields: ['slug'], message: '标签slug已存在')]
class Tag implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use IpTraceableAware;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['comment' => '标签ID'])]
    #[ORM\CustomIdGenerator]
    #[Assert\Length(max: 36)]
    private string $id;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '标签名称'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 50, unique: true, options: ['comment' => '标签别名（URL友好）'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $slug;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '标签描述'])]
    #[Assert\Length(max: 65535)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 7, nullable: true, options: ['comment' => '标签颜色（十六进制）'])]
    #[Assert\Length(max: 7)]
    #[Assert\Regex(pattern: '/^#[0-9A-Fa-f]{6}$/', message: '颜色格式必须为十六进制（如 #FFFFFF）')]
    private ?string $color = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '使用次数统计'])]
    #[Assert\PositiveOrZero]
    private int $usageCount = 0;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\ManyToMany(targetEntity: Question::class, mappedBy: 'tags', fetch: 'EXTRA_LAZY')]
    private Collection $questions;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否有效'])]
    #[Assert\Type(type: 'bool')]
    private bool $valid = true;

    public function __construct()
    {
        $this->id = Uuid::v7()->toRfc4122();
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        if (null !== $color && 1 !== preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            throw new TagValidationException('Color must be a valid hex color (e.g., #FF0000)');
        }

        $this->color = $color;
    }

    public function getUsageCount(): int
    {
        return $this->usageCount;
    }

    public function incrementUsageCount(): self
    {
        ++$this->usageCount;

        return $this;
    }

    public function decrementUsageCount(): self
    {
        if ($this->usageCount > 0) {
            --$this->usageCount;
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

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
