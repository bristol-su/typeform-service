<?php

namespace BristolSU\Service\Typeform\Http\Controllers;

use BristolSU\Service\Typeform\Models\TypeformAuthCode;

class OAuthCodeController
{

    public function index()
    {
        return TypeformAuthCode::valid()->get();
    }

}
