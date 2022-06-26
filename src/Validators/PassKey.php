<?php

namespace Kanata\TheGrid\Validators;

use Attribute;
use Spatie\DataTransferObject\Validation\ValidationResult;
use Spatie\DataTransferObject\Validator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class PassKey implements Validator
{
    public function __construct()
    {}

    public function validate(mixed $value): ValidationResult
    {
        $validator = Validation::createValidator();
        $validations = [new Type('string'), new NotBlank];

        $violations = [];
        foreach ($validator->validate($value, $validations) as $v) {
            $violations[] = $v->getMessage();
        }

        if (0 !== count($violations)) {
            $plural = count($violations) > 1;
            return ValidationResult::invalid('PassKey invalid for the following reason' . ($plural ? 's' : '') . ': ' . implode(', ', $violations));
        }

        return ValidationResult::valid();
    }
}