<?php

\Illuminate\Support\Facades\Route::get('code', [\BristolSU\Service\Typeform\Http\Controllers\OAuthCodeController::class, 'index']);
