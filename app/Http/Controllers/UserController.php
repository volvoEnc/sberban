<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserCreateResource;
use App\Http\Resources\UserTokenResource;
use App\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function login(UserRequest $request)
    {
        $user = User::query()
            ->where('login', $request->login)
            ->where('password', $request->password)
            ->first();



        $user->api_token = Str::random(64);
        $user->save();

        return new UserTokenResource($user);
    }

    protected function getTokens()
    {

    }

}
