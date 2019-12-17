<?php

namespace BristolSU\Service\Typeform\Http\Controllers;

use BristolSU\Service\Typeform\Models\TypeformAuthCode;
use BristolSU\Support\Authentication\Contracts\UserAuthentication;
use Carbon\Carbon;

class OAuthCodeController
{

    public function index(UserAuthentication $userAuthentication)
    {
        return TypeformAuthCode::valid()->get();
    }
    
}