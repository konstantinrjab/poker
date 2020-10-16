<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateGameRequest extends FormRequest
{
    const BIG_BLIND_COEF = 10;

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
            'initialMoney' => ['required', 'int', function (string $attribute, int $value, \Closure $fail) {
                $bigBlindAmount = $this->input('bigBlind', 0);
                $minInitialMoney = $bigBlindAmount * self::BIG_BLIND_COEF;
                if ($value < $minInitialMoney) {
                    $fail($attribute . ' should be at least ' . $minInitialMoney);
                }
            }],
        ];
    }
}
