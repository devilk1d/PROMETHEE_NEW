<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Decision extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'description', 
        'result_data', 
        'selected_alternatives',
        'user_id'
    ];

    protected $casts = [
        'result_data' => 'array',
        'selected_alternatives' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRankingAttribute()
    {
        return $this->result_data['ranking'] ?? [];
    }

    public function getFlowsAttribute()
    {
        return $this->result_data['flows'] ?? [];
    }

    public function getSelectedAlternativesNamesAttribute()
    {
        return Alternative::whereIn('id', $this->selected_alternatives ?? [])
            ->pluck('name')
            ->implode(', ');
    }
}