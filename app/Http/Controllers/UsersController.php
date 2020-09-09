<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Entities\Database\User;

class UsersController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = new User($request->get('name'));
        $user->save();

        return UserResource::make($user);
    }
}
