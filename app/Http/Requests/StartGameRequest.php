<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartGameRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'userId' => 'required|string|max:50',
        ];
    }
}
