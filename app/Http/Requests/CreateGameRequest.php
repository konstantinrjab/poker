<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateGameRequest extends FormRequest
{
    const INIT_MONEY_BIG_BLIND_COEF = 10;
    const SMALL_BLIND_BIG_BLIND_COEF = 2;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'userId' => 'required|string|max:50',
            'bigBlind' => ['required', 'int', function (string $attribute, int $value, \Closure $fail) {
                $smallBlindAmount = $this->input('smallBlind', 0);
                $minBigBlind = $smallBlindAmount * self::SMALL_BLIND_BIG_BLIND_COEF;
                if ($value < $minBigBlind) {
                    $fail($attribute . ' should be at least ' . $minBigBlind);
                }
            }],
            'smallBlind' => 'required|int',
            'minPlayers' => 'required|int|min:2|max:10',
            'maxPlayers' => 'required|int|min:2|max:10',
            'initialMoney' => ['required', 'int', function (string $attribute, int $value, \Closure $fail) {
                $bigBlindAmount = $this->input('bigBlind', 0);
                $minInitialMoney = $bigBlindAmount * self::INIT_MONEY_BIG_BLIND_COEF;
                if ($value < $minInitialMoney) {
                    $fail($attribute . ' should be at least ' . $minInitialMoney);
                }
            }],
            'timeout' => 'int|min:10|max:100'
        ];
    }
}
