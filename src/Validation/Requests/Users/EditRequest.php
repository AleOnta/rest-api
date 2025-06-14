<?php

namespace Src\Validation\Requests\Users;

class EditRequest
{
    public static function rules(): array
    {
        return [
            'email' => 'email|unique:users',
            'username' => 'alphnum|min:5|unique:users'
        ];
    }
}
