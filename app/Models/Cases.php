<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cases extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Criteria
    public function criteria()
    {
        return $this->hasMany(Criteria::class, 'case_id');
    }

    // Alias untuk konsistensi
    public function criterias()
    {
        return $this->hasMany(Criteria::class, 'case_id');
    }

    // Relationship with Alternatives
    public function alternatives()
    {
        return $this->hasMany(Alternative::class, 'case_id');
    }

    // Relationship with Decisions
    public function decisions()
    {
        return $this->hasMany(Decision::class, 'case_id');
    }

    // Get criteria count
    public function getCriteriaCountAttribute()
    {
        return $this->criteria()->count();
    }

    // Get alternatives count
    public function getAlternativesCountAttribute()
    {
        return $this->alternatives()->count();
    }

    // Get decisions count
    public function getDecisionsCountAttribute()
    {
        return $this->decisions()->count();
    }

    // Scope for user's cases
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Get case with all counts
    public static function withAllCounts()
    {
        return static::withCount(['criteria', 'alternatives', 'decisions']);
    }
}