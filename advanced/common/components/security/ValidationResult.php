<?php

namespace common\components\security;

/**
 * ValidationResult
 *
 * Represents the result of a validation operation.
 * Used by UploadValidator and other security components to return
 * structured validation results.
 */
class ValidationResult
{
    /**
     * @var bool Whether the validation passed
     */
    public $isValid;

    /**
     * @var array List of error messages (empty if valid)
     */
    public $errors;

    /**
     * @param bool $isValid Whether the validation passed
     * @param array $errors List of error messages
     */
    public function __construct(bool $isValid, array $errors = [])
    {
        $this->isValid = $isValid;
        $this->errors = $errors;
    }

    /**
     * Check if the validation passed.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * Get all error messages.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the first error message, or null if no errors.
     *
     * @return string|null
     */
    public function getFirstError(): ?string
    {
        return !empty($this->errors) ? $this->errors[0] : null;
    }
}
