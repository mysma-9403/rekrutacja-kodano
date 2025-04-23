<?php

namespace App\Infrastructure\Validation\Constraint;

use App\Infrastructure\Validation\Validator\UniqueProductNameValidator;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueProductName extends Constraint
{
    public string $message = '"{{ value }}" exists in DB.'; //@TODO add translations

    public function validatedBy(): string
    {
        return UniqueProductNameValidator::class;
    }
}