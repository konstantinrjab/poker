<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateGameRequest extends FormRequest
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
            'bigBlind' => 'required|int|gte:smallBlind',
            'smallBlind' => 'required|int',
            'minPlayers' => 'required|int|min:3|max:10',
            'maxPlayers' => 'required|int|min:3|max:10',
            // TODO: write custom validator based on bigBlind
            'initialMoney' => 'required|int|gt:bigBlind',
        ];
    }
}
