<?php

namespace Src\Validation\Requests\Posts;

class CreateRequest
{
    public static function rules(): array
    {
        return [
            'title' => 'required|min:5|spacesalphnum',
            'content' => 'required|min:75'
        ];
    }
}
