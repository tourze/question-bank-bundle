<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends QuestionBankException
{
    private ConstraintViolationListInterface $violations;

    public function __construct(ConstraintViolationListInterface $violations)
    {
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
        }
        
        parent::__construct('Validation failed: ' . implode('; ', $messages));
        $this->violations = $violations;
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}