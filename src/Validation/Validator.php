<?php

namespace Src\Validation;

use Src\System\DB;
use Src\Gateways\UserGateway;
use Src\Exceptions\InvalidParameterException;

class Validator
{
    public static function validate(array $data, array $rules)
    {
        $errors = [];
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            if ($value === '') $value = null;
            foreach (explode('|', $ruleSet) as $rule) {

                if ($rule === 'required') {
                    if ($value === null) {
                        $errors = self::setError($errors, $field, "Field {$field} is required.");
                    }
                    continue;
                }

                if ($value) {
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

                    if ($rule === 'email') {
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors = self::setError($errors, $field, 'The email address provided is invalid');
                        }
                        continue;
                    }

                    if ($rule === 'alphnum') {
                        if (!ctype_alnum($value)) {
                            $errors = self::setError($errors, $field, "Field {$field} can contain only alphanumeric characters");
                        }
                    }

                    if ($rule === 'spacesalphnum') {
                        if (!ctype_alnum(str_replace(" ", "", $value))) {
                            $errors = self::setError($errors, $field, "Field {$field} can contain only alphanumeric characters");
                        }
                    }

                    if (str_starts_with($rule, 'unique')) {
                        # retrieve database connection
                        $connection = new DB()->getConnection();
                        [$key, $table] = explode(':', $rule);
                        # if the given table parameter isn't included in the existing table list then throw an exception
                        if (!in_array($table, ['users'])) {
                            throw new InvalidParameterException("Invalid parameter given to request validation. Table '{$table}' doesn't exists.");
                        }
                        # validate field / column parameter
                        if (!in_array($field, ['username', 'email'])) {
                            throw new InvalidParameterException("Invalid parameter given to request validation. Column '{$field}' cannot be queried on users table.");
                        }
                        # istantiate the table gateway
                        $gateway = new UserGateway($connection);
                        # check existence
                        $validation = $field === 'email'
                            ? $gateway->findByEmail($value)
                            : $gateway->findByUsername($value);
                        # set error if $user exists
                        if ($validation) {
                            $errors = self::setError($errors, $field, "Field {$field} cannot be used, try something else.");
                        }
                        continue;
                    }
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
