<?php

namespace App\Http\Requests;

use App\Models\Action;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGameRequest extends FormRequest
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
