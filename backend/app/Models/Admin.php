<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{
    protected $fillable = ['name', 'email', 'password', 'google2fa_secret'];
    protected $hidden = ['password', 'google2fa_secret'];
    protected $casts = [
        'google2fa_secret' => 'string',
        'temporary_lock_until' => 'datetime',
        'is_suspended' => 'boolean',
        'failed_login_count' => 'integer',
        'twofa_failed_count' => 'integer',
        'total_failed_count' => 'integer',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return ['role' => 'admin'];
    }
}
