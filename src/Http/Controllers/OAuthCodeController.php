<?php

namespace BristolSU\Service\Typeform\Http\Controllers;

use BristolSU\Service\Typeform\Models\TypeformAuthCode;
use BristolSU\Support\User\Contracts\UserAuthentication;

class OAuthCodeController
{

    public function index(UserAuthentication $userAuthentication)
    {
        return TypeformAuthCode::valid()->get();
    }
    
}