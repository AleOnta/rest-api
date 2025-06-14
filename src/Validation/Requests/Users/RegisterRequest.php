<?php

namespace Src\Validation\Requests\Users;

class RegisterRequest
{
    public static function rules(): array
    {
        return [
            'email' => 'required|email|unique:users',
            'username' => 'required|min:5|alphnum|unique:users',
            'password' => 'required|min:10'
        ];
    }
}
