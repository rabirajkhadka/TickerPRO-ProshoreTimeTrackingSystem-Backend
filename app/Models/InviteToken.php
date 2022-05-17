<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class InviteToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'role_id',
        'token',
        'tokenExpires',
        'invitedUserId',
    ];

    public function setTokenAttribute($token)
    {
        $this->attributes['token'] = Hash::make($token);
    }
}