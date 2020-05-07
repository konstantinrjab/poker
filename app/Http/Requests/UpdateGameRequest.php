<?php

namespace App\Http\Requests;

use App\Models\Action;
use Request;

class UpdateGameRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'userId' => 'required|string',
            'action' => 'required|string|in:' . implode(',', Action::AVAILABLE_ACTIONS),
            'value' => 'int',
        ];
    }
}
