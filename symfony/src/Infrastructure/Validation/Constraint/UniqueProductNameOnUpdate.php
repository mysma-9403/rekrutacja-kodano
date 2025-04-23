<?php
declare(strict_types=1);

namespace App\Infrastructure\Validation\Constraint;

use App\Infrastructure\Validation\Validator\UniqueProductNameOnUpdateValidator;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class UniqueProductNameOnUpdate extends Constraint
{
    public string $message = '"{{ name }}" product exist.';

    public function validatedBy(): string
    {
        return UniqueProductNameOnUpdateValidator::class;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}