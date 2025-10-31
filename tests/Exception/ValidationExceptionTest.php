<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\QuestionBankBundle\Exception\QuestionBankException;
use Tourze\QuestionBankBundle\Exception\ValidationException;

/**
 * @internal
 */
#[CoversClass(ValidationException::class)]
final class ValidationExceptionTest extends AbstractExceptionTestCase
{
    public function testConstructorWithSingleViolationCreatesProperMessage(): void
    {
        $violations = new ConstraintViolationList();
        $violation = new ConstraintViolation(
            'This field is required',
            null,
            [],
            null,
            'name',
            null
        );
        $violations->add($violation);

        $exception = new ValidationException($violations);

        $this->assertStringContainsString('Validation failed: name: This field is required', $exception->getMessage());
        $this->assertEquals($violations, $exception->getViolations());
    }

    public function testConstructorWithMultipleViolationsConcatenatesMessages(): void
    {
        $violations = new ConstraintViolationList();

        $violation1 = new ConstraintViolation(
            'This field is required',
            null,
            [],
            null,
            'name',
            null
        );
        $violations->add($violation1);

        $violation2 = new ConstraintViolation(
            'This value is too short',
            null,
            [],
            null,
            'description',
            null
        );
        $violations->add($violation2);

        $exception = new ValidationException($violations);

        $message = $exception->getMessage();
        $this->assertStringContainsString('name: This field is required', $message);
        $this->assertStringContainsString('description: This value is too short', $message);
        $this->assertStringContainsString(';', $message); // 检查分隔符
        $this->assertEquals($violations, $exception->getViolations());
    }

    public function testConstructorWithEmptyViolationsCreatesEmptyMessage(): void
    {
        $violations = new ConstraintViolationList();

        $exception = new ValidationException($violations);

        $this->assertEquals('Validation failed: ', $exception->getMessage());
        $this->assertEquals($violations, $exception->getViolations());
    }

    public function testGetViolationsReturnsOriginalViolations(): void
    {
        $violations = new ConstraintViolationList();
        $violation = new ConstraintViolation(
            'Test message',
            null,
            [],
            null,
            'testProperty',
            'testValue'
        );
        $violations->add($violation);

        $exception = new ValidationException($violations);

        $returnedViolations = $exception->getViolations();
        $this->assertSame($violations, $returnedViolations);
        $this->assertCount(1, $returnedViolations);
        $this->assertEquals('Test message', $returnedViolations[0]->getMessage());
        $this->assertEquals('testProperty', $returnedViolations[0]->getPropertyPath());
    }

    public function testInheritanceExtendsQuestionBankException(): void
    {
        $violations = new ConstraintViolationList();
        $exception = new ValidationException($violations);

        $this->assertInstanceOf(QuestionBankException::class, $exception);
    }

    public function testConstructorWithComplexViolationData(): void
    {
        $violations = new ConstraintViolationList();
        $violation = new ConstraintViolation(
            'Value {{ value }} is not valid',
            'Value {{ value }} is not valid',
            ['{{ value }}' => 'testValue'],
            'rootObject',
            'complex.nested.property',
            'invalidValue'
        );
        $violations->add($violation);

        $exception = new ValidationException($violations);

        $this->assertStringContainsString('complex.nested.property: Value {{ value }} is not valid', $exception->getMessage());
        $this->assertEquals($violations, $exception->getViolations());
    }
}
