<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\QuestionBankBundle\Repository\OptionRepository;

#[ORM\Entity(repositoryClass: OptionRepository::class)]
#[ORM\Table(name: 'question_bank_options', options: ['comment' => '题目选项表'])]
// 选项实体通过 QuestionFixtures 创建，不需要独立的 OptionFixtures 类
class Option implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use IpTraceableAware;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['comment' => '选项ID'])]
    #[ORM\CustomIdGenerator]
    #[Assert\Length(max: 36)]
    private string $id;

    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => '选项内容'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 65535)]
    private string $content;

    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否为正确答案'])]
    #[Assert\Type(type: 'bool')]
    private bool $isCorrect;

    #[IndexColumn]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '排序顺序'])]
    #[Assert\PositiveOrZero]
    private int $sortOrder = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '选项解释'])]
    #[Assert\Length(max: 65535)]
    private ?string $explanation = null;

    #[ORM\ManyToOne(targetEntity: Question::class, fetch: 'EXTRA_LAZY', inversedBy: 'options')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否有效'])]
    #[Assert\Type(type: 'bool')]
    private bool $valid = true;

    public function __construct()
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->createTime = new \DateTimeImmutable();
        $this->updateTime = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
        $this->updateTime = new \DateTimeImmutable();
    }

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }

    public function setIsCorrect(bool $isCorrect): void
    {
        $this->isCorrect = $isCorrect;
        $this->updateTime = new \DateTimeImmutable();
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation;
    }

    public function setExplanation(?string $explanation): void
    {
        $this->explanation = $explanation;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): void
    {
        $this->question = $question;
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
        return $this->content;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'content' => $this->getContent(),
            'image' => null, // question-bank-bundle 暂不支持图片，可扩展
            'correct' => $this->isCorrect(),
        ];
    }
}
