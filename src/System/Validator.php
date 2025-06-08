<?php

namespace Src\System;

class Validator
{

    public static function validate(array $data, array $rules)
    {
        $errors = [];
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            foreach (explode('|', $ruleSet) as $rule) {

                if ($rule === 'required') {
                    if ($value === null) {
                        $errors = self::setError($errors, $field, "Field {$field} is required.");
                    }
                    continue;
                }

                if (str_starts_with($rule, 'min')) {
                    $min = explode(':', $rule)[1];
                    if (strlen($value) < $min) {
                        $errors = self::setError($errors, $field, "Field {$field} is required to be longer than {$min} characters");
                    }
                    continue;
                }

                if (str_starts_with($rule, 'max')) {
                    $max = explode(':', $rule)[1];
                    if (strlen($value) > $max) {
                        $errors = self::setError($errors, $field, "Field {$field} is required to be shorter than {$max} characters");
                    }
                    continue;
                }

                if (str_starts_with($rule, 'gt')) {

                    [$key, $greater] = explode(':', $rule);
                    $conditional = $key === 'gte'
                        ? $data[$field] >= $greater
                        : $data[$field] > $greater;

                    if (!$conditional) {

                        $errors = self::setError(
                            $errors,
                            $field,
                            $key === 'gte'
                                ? "Field {$field} must be greater or equal than {$greater}"
                                : "Field {$field} must be greater than {$greater}"
                        );
                    }
                    continue;
                }

                if (str_starts_with($rule, 'lt')) {

                    [$key, $lower] = explode(':', $rule);
                    $conditional = $key === 'lte'
                        ? $data[$field] <= $lower
                        : $data[$field] < $lower;

                    if (!$conditional) {

                        $errors = self::setError(
                            $errors,
                            $field,
                            $key === 'lte'
                                ? "Field {$field} must be lower or equal than {$lower}"
                                : "Field {$field} must be lower than {$lower}"
                        );
                    }
                    continue;
                }
            }
        }
        return $errors;
    }

    private static function setError(array $errors, string $field, string $message)
    {
        if (!isset($errors[$field])) {
            $errors[$field] = [];
        }
        # push error in array
        $errors[$field][] = $message;
        return $errors;
    }
}
