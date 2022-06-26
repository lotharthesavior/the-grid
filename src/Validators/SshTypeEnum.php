<?php

namespace Kanata\TheGrid\Validators;

use Attribute;
use Kanata\TheGrid\Services\Abstractions\SecureShell;
use Spatie\DataTransferObject\Validation\ValidationResult;
use Spatie\DataTransferObject\Validator;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class SshTypeEnum implements Validator
{
    public function __construct()
    {}

    public function validate(mixed $value): ValidationResult
    {
        $options = [SecureShell::PASSWORD_TYPE, SecureShell::PRIVATE_KEY_TYPE];

        if (!in_array($value, $options)) {
            return ValidationResult::invalid('Value should be within the available options: ' . implode(', ', $options));
        }

        return ValidationResult::valid();
    }
}