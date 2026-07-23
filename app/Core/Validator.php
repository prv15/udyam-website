<?php

namespace App\Core;

class Validator
{
    /**
     * Validation data.
     */
    protected array $data = [];

    /**
     * Validation rules.
     */
    protected array $rules = [];

    /**
     * Validation errors.
     */
    protected array $errors = [];

    /**
     * Validate given data.
     */
    public function validate(array $data, array $rules): bool
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->errors = [];

        foreach ($rules as $field => $ruleString) {

            $rules = explode('|', $ruleString);

            foreach ($rules as $rule) {

                $parameters = [];

                if (str_contains($rule, ':')) {

                    [$rule, $parameter] = explode(':', $rule, 2);

                    $parameters = explode(',', $parameter);

                }

                $method = 'validate' . ucfirst($rule);

                if (method_exists($this, $method)) {

                    $this->{$method}(
                        $field,
                        $this->data[$field] ?? null,
                        $parameters
                    );

                }

            }

        }

        return $this->passes();
    }

    /**
     * Required validation.
     */
    protected function validateRequired(
        string $field,
        mixed $value
    ): void {

        if ($value === null || trim((string) $value) === '') {

            $this->addError(
                $field,
                ucfirst(str_replace('_', ' ', $field)) . ' is required.'
            );

        }

    }

    /**
     * Minimum length validation.
     */
    protected function validateMin(
        string $field,
        mixed $value,
        array $parameters
    ): void {

        if ($value === null) {
            return;
        }

        $min = (int) ($parameters[0] ?? 0);

        if (mb_strlen((string) $value) < $min) {

            $this->addError(
                $field,
                ucfirst(str_replace('_', ' ', $field))
                . " must be at least {$min} characters."
            );

        }

    }

    /**
     * Maximum length validation.
     */
    protected function validateMax(
        string $field,
        mixed $value,
        array $parameters
    ): void {

        if ($value === null) {
            return;
        }

        $max = (int) ($parameters[0] ?? 0);

        if (mb_strlen((string) $value) > $max) {

            $this->addError(
                $field,
                ucfirst(str_replace('_', ' ', $field))
                . " may not exceed {$max} characters."
            );

        }

    }

    /**
     * Email validation.
     */
    protected function validateEmail(
        string $field,
        mixed $value
    ): void {

        if ($value === null || $value === '') {
            return;
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {

            $this->addError(
                $field,
                'Please enter a valid email address.'
            );

        }

    }

    /**
     * In validation.
     */
    protected function validateIn(
        string $field,
        mixed $value,
        array $parameters
    ): void {

        if ($value === null || $value === '') {
            return;
        }

        if (!in_array($value, $parameters, true)) {

            $this->addError(
                $field,
                ucfirst(str_replace('_', ' ', $field))
                . ' contains an invalid value.'
            );

        }

    }

    /**
     * Add validation error.
     */
    protected function addError(
        string $field,
        string $message
    ): void {

        $this->errors[$field][] = $message;

    }

    /**
     * Validation failed?
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Validation passed?
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * All errors.
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * First error of field.
     */
    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Has error?
     */
    public function has(string $field): bool
    {
        return isset($this->errors[$field]);
    }
}