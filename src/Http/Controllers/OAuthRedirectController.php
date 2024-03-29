<?php

namespace BristolSU\Service\Typeform\Http\Controllers;

use BristolSU\Service\Typeform\Models\TypeformAuthCode;
use BristolSU\Support\Authentication\Contracts\Authentication;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class OAuthRedirectController
{

    public function index(Request $request, Client $client)
    {
        $response = $client->post(config('typeform_service.urlAccessToken'), [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $request->input('code'),
                'client_id' => config('typeform_service.client_id'),
                'client_secret' => config('typeform_service.client_secret'),
                'redirect_uri' => config('app.url') . '/_connector/typeform/redirect'
            ],
        ]);
        $token = json_decode($response->getBody()->getContents(), true);
        $authCode = new TypeformAuthCode;
        $authCode->auth_code = $token['access_token'];
        $authCode->refresh_token = $token['refresh_token'];
        $authCode->expires_at = Carbon::now()->addSeconds($token['expires_in']);
        $authCode->user_id = app(Authentication::class)->getUser()->id();
        $authCode->save();

        return view('typeformservice::close_window');
    }

}
