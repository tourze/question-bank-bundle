<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends QuestionBankException
{
    public function __construct(private readonly ConstraintViolationListInterface $violations)
    {
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
        }

        parent::__construct('Validation failed: ' . implode('; ', $messages));
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
