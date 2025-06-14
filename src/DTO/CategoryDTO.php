<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CategoryDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Assert\Regex(pattern: '/^[a-z0-9_]+$/', message: 'Code must contain only lowercase letters, numbers and underscores')]
    public string $code;

    public ?string $description = null;

    public int $sortOrder = 0;

    public bool $isActive = true;

    public ?string $parentId = null;

    public static function create(string $name, string $code): self
    {
        $dto = new self();
        $dto->name = $name;
        $dto->code = $code;
        
        return $dto;
    }
}
