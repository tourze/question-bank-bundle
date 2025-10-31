<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class TagDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    public string $name;

    #[Assert\Length(max: 50)]
    #[Assert\Regex(pattern: '/^[a-z0-9-]+$/', message: 'Slug must contain only lowercase letters, numbers and hyphens')]
    public ?string $slug = null;

    public ?string $description = null;

    #[Assert\Regex(pattern: '/^#[0-9A-Fa-f]{6}$/', message: 'Color must be a valid hex color')]
    public ?string $color = null;

    public static function create(string $name): self
    {
        $dto = new self();
        $dto->name = $name;

        return $dto;
    }
}
