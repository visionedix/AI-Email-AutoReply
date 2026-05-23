<?php

namespace App\Admin\Models;

use App\Models\User as BaseUser;

class User extends BaseUser
{
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
}
