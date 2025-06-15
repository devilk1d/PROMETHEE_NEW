<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alternative extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function criteriaValues()
    {
        return $this->hasMany(CriteriaValue::class);
    }

    // Get specific criteria value for this alternative
    public function getCriteriaValue($criteriaId)
    {
        $criteriaValue = $this->criteriaValues()->where('criteria_id', $criteriaId)->first();
        return $criteriaValue && $criteriaValue->is_selected ? $criteriaValue->value : 0;
    }

    // Check if alternative has specific criteria and it is selected
    public function hasCriteria($criteriaId)
    {
        return $this->criteriaValues()->where('criteria_id', $criteriaId)->where('is_selected', true)->exists();
    }

    // Add or update criteria value
    public function setCriteriaValue($criteriaId, $value, $isSelected = true)
    {
        return $this->criteriaValues()->updateOrCreate(
            ['criteria_id' => $criteriaId],
            ['value' => $value, 'is_selected' => $isSelected]
        );
    }

    // Get all criteria IDs associated with this alternative (only selected ones)
    public function getCriteriaIds()
    {
        return $this->criteriaValues()->where('is_selected', true)->pluck('criteria_id')->toArray();
    }
}