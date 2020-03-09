<?php

namespace BristolSU\Service\Typeform\Models;

use BristolSU\Support\User\Contracts\UserAuthentication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class TypeformAuthCode extends Model
{

    protected $hidden = [
        'auth_code', 'expires_at', 'refresh_token'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    /**
     * Scope auth codes to show to the user to select.
     * 
     * These auth codes must both belong to the user, and have been created in the last 10 minutes.
     * 
     * @param Builder $query
     */
    public function scopeValid(Builder $query)
    {
        $query->where('user_id', app(UserAuthentication::class)->getUser()->control_id)
            ->where('created_at', '>=', Carbon::now()->subMinutes(10))
            ->orderBy('created_at', 'DESC');
    }

    public function isValid()
    {
        return $this->expires_at->isFuture();
    }

    public function setAuthCodeAttribute($authCode)
    {
        $this->attributes['auth_code'] = Crypt::encrypt($authCode);
    }

    public function getAuthCodeAttribute($authCode)
    {
        return Crypt::decrypt($authCode);
    }

    public function setRefreshTokenAttribute($refreshToken)
    {
        $this->attributes['refresh_token'] = Crypt::encrypt($refreshToken);
    }

    public function getRefreshTokenAttribute($refreshToken)
    {
        return Crypt::decrypt($refreshToken);
    }
    
}