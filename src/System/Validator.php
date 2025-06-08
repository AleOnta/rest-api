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
