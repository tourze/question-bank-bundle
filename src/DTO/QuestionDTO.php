<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Tourze\QuestionBankBundle\Enum\QuestionType;

class QuestionDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $title;

    #[Assert\NotBlank]
    #[Assert\Length(max: 5000)]
    public string $content;

    #[Assert\NotNull]
    public QuestionType $type;

    #[Assert\Range(min: 1, max: 5)]
    public int $difficulty = 3;

    #[Assert\Positive]
    public float $score = 10.0;

    public ?string $explanation = null;

    public ?array $metadata = null;

    /**
     * @var array<string>
     */
    public array $categoryIds = [];

    /**
     * @var array<string>
     */
    #[Assert\Count(max: 10)]
    public array $tagIds = [];

    /**
     * @var array<OptionDTO>
     */
    #[Assert\Valid]
    public array $options = [];

    public static function create(string $title, string $content, QuestionType $type): self
    {
        $dto = new self();
        $dto->title = $title;
        $dto->content = $content;
        $dto->type = $type;
        
        return $dto;
    }
}