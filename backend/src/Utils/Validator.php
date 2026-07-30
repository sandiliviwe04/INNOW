<?php

namespace Innow\Utils;

class Validator {
    public static function validate(array $data, array $rules): array {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $value = trim($data[$field] ?? '');
            $fieldRules = explode('|', $ruleString);

            foreach ($fieldRules as $rule) {
                if ($rule === 'required' && ($value === '' || $value === null)) {
                    $errors[$field] = ucfirst($field) . ' is required.';
                } else if ($rule === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Please enter a valid email address.';
                } else if (str_starts_with($rule, 'min:')) {
                    $min = (int)explode(':', $rule)[1];
                    if (strlen($value) < $min) {
                        $errors[$field] = ucfirst($field) . " must be at least {$min} characters.";
                    }
                }
            }
        }

        return $errors;
    }
}
