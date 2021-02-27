<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserCreateResource;
use App\Http\Resources\UserTokenResource;
use App\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function login(UserRequest $request)
    {
        $user = User::query()
            ->where('login', $request->login)
            ->where('password', $request->password)
            ->first();

        if (!$user) {
            $user = User::create($request->all());
        }

        /** @var $user User */
        $this->getTokens($user);
        $user->api_token = Str::random(64);
        $user->save();

        return new UserTokenResource($user);
    }

    protected function getTokens(User $user)
    {
        $response = Http::post(getenv('SBER_GET_TOKEN_URL'), [
            'auth' => [
                'identity' => [
                    'methods' => ['password'],
                    'password' => [
                        'user' => [
                            'name' => $user->login
                        ],
                        'password' => $user->password,
                        'domain' => [
                            'name' => $user->login
                        ]
                    ]
                ],
                'scope' => [
                    'project' => [
                        'name' => getenv('SBER_GET_REGION_NAME')
                    ]
                ]
            ]
        ]);

        dd($response);
    }
}
