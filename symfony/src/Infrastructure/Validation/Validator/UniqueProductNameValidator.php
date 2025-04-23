<?php
declare(strict_types=1);

namespace App\Infrastructure\Validation\Validator;

use App\Domain\Product\ProductRepositoryInterface;
use App\Infrastructure\Validation\Constraint\UniqueProductName;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UniqueProductNameValidator extends ConstraintValidator
{
    public function __construct(
        private ProductRepositoryInterface $repo
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueProductName) {
            return;
        }

        if (null === $value || '' === $value) {
            return;
        }

        if ($this->repo->existsByName((string) $value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ value }}', (string) $value)
                ->addViolation();
        }
    }
}