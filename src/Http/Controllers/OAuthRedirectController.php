<?php

namespace BristolSU\Service\Typeform\Http\Controllers;

use BristolSU\Service\Typeform\Models\TypeformAuthCode;
use BristolSU\Support\User\Contracts\UserAuthentication;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class OAuthRedirectController
{

    public function index(Request $request, Client $client)
    {
        // TODO move into OAuth
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
        $authCode->user_id = app(UserAuthentication::class)->getUser()->control_id;
        $authCode->save();
        
        return view('typeformservice::close_window');
    }

}