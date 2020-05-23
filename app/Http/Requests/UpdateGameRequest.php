<?php

namespace App\Http\Requests;

use App\Models\Actions\Factories\ActionFactory;
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
            'userId' => 'required|string|max:50',
            'action' => 'required|string|in:' . implode(',', ActionFactory::AVAILABLE_ACTIONS),
            'value' => 'int|required_if:action,' . implode(',', [
                    ActionFactory::RAISE,
                ]),
        ];
    }
}
