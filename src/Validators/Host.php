<?php

namespace Kanata\TheGrid\Validators;

use Attribute;
use Spatie\DataTransferObject\Validation\ValidationResult;
use Spatie\DataTransferObject\Validator;
use Symfony\Component\Validator\Constraints\Ip;
use Symfony\Component\Validator\Validation;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Host implements Validator
{
    public function __construct()
    {}

    public function validate(mixed $value): ValidationResult
    {
        $validator = Validation::createValidator();

        $violations = [];
        foreach ($validator->validate($value, [new Ip]) as $v) {
            $violations[] = $v->getMessage();
        }

        if (0 !== count($violations)) {
            $plural = count($violations) > 1;
            return ValidationResult::invalid('Host invalid for the following reason' . ($plural ? 's' : '') . ': ' . implode(', ', $violations));
        }

        return ValidationResult::valid();
    }
}