<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the shopping cart items for the user.
     */
    public function shoppingCartItems()
    {
        return $this->hasMany(shoppingCart::class, 'user_id');
    }

    public function isAdmin(): bool
    {
        // If Spatie trait is available use it
        if (method_exists($this, 'hasRole')) {
            return $this->hasRole('admin');
        }

        // Fallback: check a `role` attribute or `is_admin` boolean
        if (isset($this->role)) {
            return $this->role === 'admin';
        }

        if (isset($this->is_admin)) {
            return (bool) $this->is_admin;
        }

        return false;
    }
}
