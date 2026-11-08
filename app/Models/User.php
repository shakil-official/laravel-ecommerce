<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Administrator account (the only kind of user in this project).
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    /** The password is hashed automatically when assigned. */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
