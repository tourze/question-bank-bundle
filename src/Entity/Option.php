<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\QuestionBankBundle\Repository\OptionRepository;

#[ORM\Entity(repositoryClass: OptionRepository::class)]
#[ORM\Table(name: 'question_bank_options', options: ['comment' => '题目选项表'])]
#[ORM\Index(columns: ['sort_order'], name: 'idx_option_sort_order')]
class Option implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true, options: ['comment' => '选项ID'])]
    private Uuid $id;

    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => '选项内容'])]
    private string $content;

    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否为正确答案'])]
    private bool $isCorrect;

    #[IndexColumn]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '排序顺序'])]
    private int $sortOrder = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '选项解释'])]
    private ?string $explanation = null;

    #[ORM\ManyToOne(targetEntity: Question::class, fetch: 'EXTRA_LAZY', inversedBy: 'options')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否有效'])]
    private bool $valid = true;


    #[CreateIpColumn]
    #[ORM\Column(type: Types::STRING, length: 45, nullable: true, options: ['comment' => '创建IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(type: Types::STRING, length: 45, nullable: true, options: ['comment' => '更新IP'])]
    private ?string $updatedFromIp = null;

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

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;
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
        return $this->content;
    }

    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId()->toRfc4122(),
            'content' => $this->getContent(),
            'image' => null, // question-bank-bundle 暂不支持图片，可扩展
            'correct' => $this->isCorrect(),
        ];
    }
}
