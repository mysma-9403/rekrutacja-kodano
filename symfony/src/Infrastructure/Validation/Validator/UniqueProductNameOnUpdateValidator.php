<?php
declare(strict_types=1);

namespace App\Infrastructure\Validation\Validator;

use App\Domain\Product\ProductRepositoryInterface;
use App\Infrastructure\Validation\Constraint\UniqueProductNameOnUpdate;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UniqueProductNameOnUpdateValidator extends ConstraintValidator
{
    public function __construct(private readonly ProductRepositoryInterface $repo) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof UniqueProductNameOnUpdate) {
            return;
        }

        $name = $value->name;
        $id   = $value->id;

        $existing = $this->repo->findOneByName($name);
        if ($existing && (string)$existing->getId() !== (string)$id) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ name }}', $name)
                ->addViolation();
        }
    }
}