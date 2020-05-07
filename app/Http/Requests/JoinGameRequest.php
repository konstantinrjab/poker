<?php

namespace App\Http\Requests;

use Request;

class JoinGameRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'userId' => 'required|string'
        ];
    }
}
