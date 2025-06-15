<?php

namespace Src\Validation\Requests\Posts;

class EditRequest
{
    public static function rules(): array
    {
        return [
            'title' => 'min:5|spacesalphnum',
            'content' => 'min:75'
        ];
    }
}
