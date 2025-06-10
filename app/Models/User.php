<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',  // Pastikan role ada di sini
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
            'role' => 'string', // Explicitly cast role as string
        ];
    }

    /**
     * Default attributes
     */
    protected $attributes = [
        'role' => 'user', // Default role
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Get the cases for the user.
     */
    public function cases()
    {
        return $this->hasMany(Cases::class);
    }

    /**
     * Get all criteria through cases
     */
    public function criteria()
    {
        return $this->hasManyThrough(Criteria::class, Cases::class);
    }

    /**
     * Get all alternatives through cases
     */
    public function alternatives()
    {
        return $this->hasManyThrough(Alternative::class, Cases::class);
    }

    /**
     * Get all decisions through cases
     */
    public function decisions()
    {
        return $this->hasManyThrough(Decision::class, Cases::class);
    }
}