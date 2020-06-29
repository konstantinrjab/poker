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
            'name' => 'required|string|max:50',
            'bigBlind' => 'required|int|gte:smallBlind',
            'smallBlind' => 'required|int',
            // TODO: write custom validator based on bigBlind
            'initialMoney' => 'required|int|gt:bigBlind',
        ];
    }
}
