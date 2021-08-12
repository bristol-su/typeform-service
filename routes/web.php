<?php

\Illuminate\Support\Facades\Route::get('redirect', [\BristolSU\Service\Typeform\Http\Controllers\OAuthRedirectController::class, 'index']);
