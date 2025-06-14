<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class OptionDTO
{
    #[Assert\NotBlank]
    public string $content;

    public bool $isCorrect = false;

    public int $sortOrder = 0;

    public ?string $explanation = null;

    public static function create(string $content, bool $isCorrect = false): self
    {
        $dto = new self();
        $dto->content = $content;
        $dto->isCorrect = $isCorrect;
        
        return $dto;
    }
}