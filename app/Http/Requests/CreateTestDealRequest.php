<?php

namespace App\Http\Requests;

use App\Models\Card;
use Illuminate\Foundation\Http\FormRequest;

class CreateTestDealRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'users' => 'required|array|min:3|max:10',
            'users.*.id' => 'required|string|max:50',
            'users.*.name' => 'required|string|max:50',
            'users.*.cards' => 'required|array|size:2',
            'users.*.cards.*.suit' => 'required|in:' . implode(',', Card::SUITS),
            'users.*.cards.*.value' => 'required|in:' . implode(',', array_keys(Card::VALUES)),
            'tableCards' => 'required|array|size:5',
        ];
    }
}
