<?php

namespace App\Http\Requests;

use App\Entities\Actions\BetAction;
use App\Entities\Actions\ActionFactory;
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
            'action' => 'required|string|in:' . implode(',', ActionFactory::getAvailableActions()),
            'value' => 'int|required_if:action,' . implode(',', [
                    BetAction::getName(),
                ]),
        ];
    }
}
