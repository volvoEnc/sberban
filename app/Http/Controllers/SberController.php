<?php

namespace App\Http\Controllers;

use App\Endpoint;
use App\Http\Resources\ServiceCountResource;
use App\Mapping\ServiceMethodCountUrls;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SberController extends Controller
{
    public function request(Request $request)
    {
        $endpoint = Endpoint::query()
            ->where('user_id', Auth::id())
            ->where('name', $request->name)
            ->first();

        if (!$endpoint) {
            throw new HttpResponseException(response(null, 404));
        }

        $method = 'get';
        if ($request->http_method == 'POST') {
            $method = 'post';
        }

        $url = $endpoint->uri . $request->url;
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders([
            'x-auth-token' => (Auth::user())->sber_token
        ])->$method($url, $request->params);

        return response(['data' => $response->json()], $response->status());
    }

    public function getActiveServices(Request $request)
    {
        $countServiceCollection = new Collection();
        $endpoints = Endpoint::query()->where('user_id', Auth::user()->id)->get();
        foreach ($endpoints as $endpoint) {
            if (ServiceMethodCountUrls::$methodsList[$endpoint->name] ?? false){
                $endpointMappingInfo = ServiceMethodCountUrls::$methodsList[$endpoint->name];
                $url = $endpoint->uri . $endpointMappingInfo['url'];
//                var_dump($url);
                /** @var \Illuminate\Http\Client\Response $response */
                $response = Http::withHeaders([
                    'x-auth-token' => (Auth::user())->sber_token
                ])->get($url);
                $count = $response->json()[$endpointMappingInfo['countMethod']] ?? null;
                if ($count) {
                    $endpoint->countElements = $count;
                    $countServiceCollection->add($endpoint);
                }
            }
        }
        return ServiceCountResource::collection($countServiceCollection);
    }
}
