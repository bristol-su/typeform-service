<?php

namespace BristolSU\Service\Typeform\Models;

use BristolSU\Support\User\Contracts\UserAuthentication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TypeformAuthCode extends Model
{

    protected $hidden = [
        'auth_code', 'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];
    
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
    
}