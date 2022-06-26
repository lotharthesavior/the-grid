<?php

namespace Kanata\TheGrid\Validators;

use Attribute;
use Spatie\DataTransferObject\Validation\ValidationResult;
use Spatie\DataTransferObject\Validator;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class PidCollection implements Validator
{
    public function __construct()
    {}

    public function validate(mixed $value): ValidationResult
    {
        $violations = $this->applyCollectionValidation($value);

        if (0 !== count($violations)) {
            $plural = count($violations) > 1;
            return ValidationResult::invalid('Pids invalid for the following reason' . ($plural ? 's' : '') . ': ' . implode(', ', $violations));
        }

        return ValidationResult::valid();
    }

    private function applyCollectionValidation(array $value): array
    {
        $validator = Validation::createValidator();
        $validations = [new Type('integer')];
        $violations = [];

        foreach ($value as $val) {
            foreach ($validator->validate($val, $validations) as $v) {
                $violations[] = '(PID ' . $val . '): ' . $v->getMessage();
            }

            // TODO: see if we can check if the pid is active
        }

        return $violations;
    }
}